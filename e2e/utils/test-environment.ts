import { GenericContainer, Wait, type StartedTestContainer } from "testcontainers";
import { mkdirSync, writeFileSync } from "fs";
import { join } from "path";

export async function createTestEnv() {
  const containerDbPath = "/var/www/html/data/database.sqlite";

  const coverageEnabled = process.env.COVERAGE === "1";

  // PHP app container with SQLite — the entrypoint creates the DB from
  // schema.sql + seed.sql on first boot when the file doesn't exist.
  const container = new GenericContainer("itcrm-php-e2e:latest")
    .withEnvironment({
      DB_PATH: containerDbPath,
      APP_ENV: "production",
      APP_DEBUG: "false",
      ...(coverageEnabled ? { COVERAGE_ENABLED: "1" } : {}),
    })
    .withExposedPorts(80)
    .withWaitStrategy(
      Wait.forHttp("/", 80).forStatusCode(200).withStartupTimeout(30_000)
    );

  const php = await container.start();

  const url = `http://${php.getHost()}:${php.getMappedPort(80)}`;

  return {
    url,
    users: {
      admin: { login: "Alice", password: "Alice123" },
    },
    async dispose() {
      if (coverageEnabled) {
        await copyCoverageFromContainer(php);
      }
      await php.stop();
    },
  };
}

export type TestEnvironment = Awaited<ReturnType<typeof createTestEnv>>;

const COVERAGE_DIR = join(__dirname, "../../coverage-raw");

async function copyCoverageFromContainer(php: StartedTestContainer) {
  mkdirSync(COVERAGE_DIR, { recursive: true });

  // List coverage files in the container
  const { output: fileList } = await php.exec([
    "sh",
    "-c",
    "ls /tmp/coverage/*.json 2>/dev/null || true",
  ]);

  const files = fileList.trim().split("\n").filter(Boolean);
  if (files.length === 0) return;

  // Copy each coverage file from the container
  for (const filePath of files) {
    const { output } = await php.exec(["cat", filePath.trim()]);
    const basename = filePath.trim().split("/").pop()!;
    writeFileSync(join(COVERAGE_DIR, basename), output);
  }
}
