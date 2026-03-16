import { readFileSync, readdirSync, writeFileSync, mkdirSync, existsSync } from "fs";
import { join } from "path";

const RAW_DIR = join(__dirname, "../coverage-raw");
const OUT_DIR = join(__dirname, "../coverage");
const SRC_ROOT = "/var/www/html/";

interface CoverageData {
  [file: string]: { [line: string]: number };
}

// Merge all per-request coverage files into a single map
function mergeCoverage(): CoverageData {
  if (!existsSync(RAW_DIR)) {
    console.error("No coverage-raw/ directory found. Did you run tests with COVERAGE=1?");
    process.exit(1);
  }

  const files = readdirSync(RAW_DIR).filter((f) => f.endsWith(".json"));
  if (files.length === 0) {
    console.error("No coverage files found in coverage-raw/");
    process.exit(1);
  }

  console.log(`Merging ${files.length} coverage files...`);

  const merged: CoverageData = {};

  for (const file of files) {
    const data: CoverageData = JSON.parse(readFileSync(join(RAW_DIR, file), "utf-8"));

    for (const [filePath, lines] of Object.entries(data)) {
      // Only include project source files
      if (!filePath.startsWith(SRC_ROOT)) continue;
      // Skip vendor/library files
      if (filePath.includes("/tcpdf/") || filePath.includes("/docker/")) continue;

      if (!merged[filePath]) {
        merged[filePath] = {};
      }

      for (const [line, status] of Object.entries(lines)) {
        const existing = merged[filePath][line];
        // 1 = executed, -1 = executable but not hit, -2 = not executable
        // If any request hit the line, mark it as covered
        if (existing === undefined || existing < status) {
          merged[filePath][line] = status;
        }
      }
    }
  }

  return merged;
}

function computeStats(merged: CoverageData) {
  const stats: {
    file: string;
    executable: number;
    covered: number;
    pct: number;
  }[] = [];

  let totalExecutable = 0;
  let totalCovered = 0;

  for (const [filePath, lines] of Object.entries(merged)) {
    const relativePath = filePath.replace(SRC_ROOT, "");
    let executable = 0;
    let covered = 0;

    for (const status of Object.values(lines)) {
      if (status === -2) continue; // not executable
      executable++;
      if (status === 1) covered++;
    }

    if (executable > 0) {
      const pct = Math.round((covered / executable) * 10000) / 100;
      stats.push({ file: relativePath, executable, covered, pct });
      totalExecutable += executable;
      totalCovered += covered;
    }
  }

  stats.sort((a, b) => a.file.localeCompare(b.file));

  return {
    files: stats,
    totalExecutable,
    totalCovered,
    totalPct:
      totalExecutable > 0
        ? Math.round((totalCovered / totalExecutable) * 10000) / 100
        : 0,
  };
}

function pctColor(pct: number): string {
  if (pct >= 80) return "#4caf50";
  if (pct >= 50) return "#ff9800";
  return "#f44336";
}

function escapeHtml(s: string): string {
  return s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function generateHtmlReport(merged: CoverageData) {
  const stats = computeStats(merged);

  // Generate per-file detail pages
  const filePages: { relativePath: string; htmlFile: string }[] = [];

  for (const [filePath, lines] of Object.entries(merged)) {
    const relativePath = filePath.replace(SRC_ROOT, "");
    const htmlFile = relativePath.replace(/\//g, "_") + ".html";
    filePages.push({ relativePath, htmlFile });

    // Try to read source from the local project
    const localPath = join(__dirname, "..", relativePath);
    let sourceLines: string[];
    try {
      sourceLines = readFileSync(localPath, "utf-8").split("\n");
    } catch {
      // Source not available locally, show coverage data only
      sourceLines = [];
    }

    const maxLine = Math.max(
      sourceLines.length,
      ...Object.keys(lines).map(Number)
    );

    let body = "";
    for (let i = 1; i <= maxLine; i++) {
      const status = lines[String(i)];
      const source = sourceLines[i - 1] ?? "";
      let bg = "transparent";
      if (status === 1) bg = "#e8f5e9";
      else if (status === -1) bg = "#ffebee";

      body += `<tr style="background:${bg}"><td class="ln">${i}</td><td class="code"><pre>${escapeHtml(source)}</pre></td></tr>\n`;
    }

    const fileHtml = `<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Coverage: ${escapeHtml(relativePath)}</title>
<style>
  body { font-family: monospace; margin: 0; padding: 20px; }
  h1 { font-size: 16px; }
  a { color: #1976d2; }
  table { border-collapse: collapse; width: 100%; }
  .ln { color: #999; text-align: right; padding: 0 12px 0 8px; user-select: none; border-right: 1px solid #ddd; width: 1%; white-space: nowrap; }
  .code { padding: 0 8px; white-space: pre; }
  .code pre { margin: 0; }
  tr:hover { outline: 1px solid #ccc; }
</style></head><body>
<p><a href="index.html">&larr; Back to summary</a></p>
<h1>${escapeHtml(relativePath)}</h1>
<table>${body}</table>
</body></html>`;

    writeFileSync(join(OUT_DIR, htmlFile), fileHtml);
  }

  // Generate index page
  let rows = "";
  for (const s of stats.files) {
    const htmlFile = s.file.replace(/\//g, "_") + ".html";
    const bar = `<div style="background:#eee;width:200px;height:14px;display:inline-block;vertical-align:middle"><div style="background:${pctColor(s.pct)};width:${s.pct}%;height:100%"></div></div>`;
    rows += `<tr>
      <td><a href="${htmlFile}">${escapeHtml(s.file)}</a></td>
      <td style="text-align:right">${s.covered}/${s.executable}</td>
      <td style="text-align:right;font-weight:bold;color:${pctColor(s.pct)}">${s.pct}%</td>
      <td>${bar}</td>
    </tr>\n`;
  }

  const indexHtml = `<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>PHP Code Coverage</title>
<style>
  body { font-family: -apple-system, sans-serif; margin: 0; padding: 20px; }
  h1 { font-size: 20px; }
  table { border-collapse: collapse; width: 100%; max-width: 900px; }
  th, td { padding: 6px 12px; border-bottom: 1px solid #eee; text-align: left; }
  th { background: #fafafa; font-weight: 600; }
  a { color: #1976d2; text-decoration: none; }
  a:hover { text-decoration: underline; }
  .summary { font-size: 24px; margin: 12px 0 24px; }
</style></head><body>
<h1>PHP Code Coverage Report</h1>
<div class="summary" style="color:${pctColor(stats.totalPct)}">
  ${stats.totalPct}% <span style="font-size:14px;color:#666">(${stats.totalCovered}/${stats.totalExecutable} lines)</span>
</div>
<table>
  <thead><tr><th>File</th><th>Lines</th><th>Coverage</th><th></th></tr></thead>
  <tbody>${rows}</tbody>
</table>
</body></html>`;

  writeFileSync(join(OUT_DIR, "index.html"), indexHtml);

  return stats;
}

// Main
mkdirSync(OUT_DIR, { recursive: true });
const merged = mergeCoverage();
const stats = generateHtmlReport(merged);

console.log("");
console.log("Coverage Summary");
console.log("=".repeat(60));
for (const s of stats.files) {
  const bar = s.pct >= 80 ? "✓" : s.pct >= 50 ? "◐" : "✗";
  console.log(`  ${bar} ${s.pct.toString().padStart(6)}%  ${s.file}`);
}
console.log("-".repeat(60));
console.log(`  Total: ${stats.totalPct}% (${stats.totalCovered}/${stats.totalExecutable} lines)`);
console.log("");
console.log(`HTML report: coverage/index.html`);
