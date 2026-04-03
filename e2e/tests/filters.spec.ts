/**
 * Data filter tests.
 *
 * Runs all filter scenarios against a single shared environment (one container),
 * rather than spinning up a container per filter state like the model-based approach.
 *
 * Seed data (docker/seed.sql) provides 4 today-dated rows with distinct field values
 * so each filter can target a meaningful subset of rows.
 */
import { test, expect, Page, Browser } from "@playwright/test";
import { createTestEnv, TestEnvironment } from "../utils/test-environment";

let env: TestEnvironment;
let page: Page;

// ── Helpers ─────────────────────────────────────────────────────────────────

/** Submit the FilterForm via its Apply button and wait for the AJAX round-trip + page reload. */
async function submitFilter() {
  // FilterData() posts to /Data/Filter, then on success calls window.location.replace().
  // We need to wait for the full navigation, not just the AJAX response.
  await Promise.all([
    page.waitForNavigation({ waitUntil: "networkidle" }),
    page.click('#FilterForm input[type="submit"][onclick*="filterAutocomplete"]'),
  ]);
}

/** Apply a text/numeric field filter with Today date range. */
async function applyFieldFilter(field: string, value: string) {
  await page.fill(`#FilterForm [name="${field}"]`, value);
  // Use Today (value=1) — seed rows have datetime('now') dates
  await page.selectOption(
    '#FilterForm select[onchange*="changeDateInterval"]',
    "1"
  );
  await submitFilter();
}

/** Apply an autocomplete select filter (Order, Person, Type, Operator). */
async function applySelectFilter(selectField: string, value: string) {
  await page.fill(`#FilterForm [name="${selectField}"]`, value);
  await page.selectOption(
    '#FilterForm select[onchange*="changeDateInterval"]',
    "1"
  );
  await submitFilter();
}

/** Apply a date interval filter by option value. */
async function applyDateInterval(value: string) {
  await page.selectOption(
    '#FilterForm select[onchange*="changeDateInterval"]',
    value
  );
  await submitFilter();
}

/** Clear all filters via the "—" reset button, then re-apply Today so seed rows are visible. */
async function clearFilter() {
  // The "—" button clears all fields then submits the empty filter
  await Promise.all([
    page.waitForNavigation({ waitUntil: "networkidle" }),
    page.click('#FilterForm input[type="submit"][value="—"]'),
  ]);
  // After clearing, default SHOW_PERIOD=-2 dates may hide rows. Re-apply Today.
  await applyDateInterval("1");
  await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
}

// ── Test suite ──────────────────────────────────────────────────────────────

test.describe.serial("Data filters", () => {
  test.beforeAll(async ({ browser }: { browser: Browser }) => {
    env = await createTestEnv();
    page = await browser.newPage();
    await page.goto(env.url);

    // Login
    await page.fill('[name="Login"]', env.users.admin.login);
    await page.fill('[name="Password"]', env.users.admin.password);
    await page.click('[type="submit"]');
    await expect(page.locator("#LoginForm")).not.toBeVisible();

    // Apply Today filter so seeded rows (datetime('now')) are visible
    await applyDateInterval("1");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  });

  test.afterAll(async () => {
    await page?.close();
    await env?.dispose();
  });

  // ── Text field filters ──────────────────────────────────────────────

  test("filter by IDDoc", async () => {
    await applyFieldFilter("IDDoc", "gala-centerpiece");
    await expect(page.locator("#DataList tr.Data")).toHaveCount(1);
    await clearFilter();
  });

  test("filter by Note", async () => {
    await applyFieldFilter("Note", "Weekly office flower delivery");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });

  test("filter by PlaceTaken", async () => {
    // "Walk-in cooler" matches rows 1 and 3
    await applyFieldFilter("PlaceTaken", "Walk-in cooler");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });

  test("filter by PlaceDone", async () => {
    await applyFieldFilter("PlaceDone", "Grand Ballroom");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });

  test("filter by BookNote", async () => {
    await applyFieldFilter("BookNote", "Priority order");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });

  test("filter by TextOrder", async () => {
    await applyFieldFilter("TextOrder", "Seasonal mixed bouquets");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });

  test("filter by TextType", async () => {
    await applyFieldFilter("TextType", "Hand-tied garden bouquet");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });

  test("filter by PriceNote", async () => {
    await applyFieldFilter("PriceNote", "Loyalty discount");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });

  // ── Numeric field filters ───────────────────────────────────────────

  test("filter by ID", async () => {
    await applyFieldFilter("ID", "1");
    await expect(page.locator("#DataList tr.Data")).toHaveCount(1);
    await clearFilter();
  });

  test("filter by Sum", async () => {
    // Sum=85 matches gala-centerpiece row (LIKE match)
    await applyFieldFilter("Sum", "85");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });

  test("filter by Hours", async () => {
    // Hours=3 matches gala-centerpiece row
    await applyFieldFilter("Hours", "3");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });

  test("filter by TotalPrice", async () => {
    // TotalPrice=145 — exact match on gala-centerpiece row
    await applyFieldFilter("TotalPrice", "145");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });

  // ── Select (autocomplete) filters ───────────────────────────────────

  test("filter by Order", async () => {
    await applySelectFilter("OrderFilterSelect", "SPRING-GALA");
    // filterAutocomplete appends ", " on reload
    await expect(
      page.locator('#FilterForm [name="OrderFilterSelect"]')
    ).toHaveValue("SPRING-GALA, ");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });

  test("filter by Person", async () => {
    await applySelectFilter("PersonFilterSelect", "Alice");
    await expect(
      page.locator('#FilterForm [name="PersonFilterSelect"]')
    ).toHaveValue("Alice, ");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });

  test("filter by Type", async () => {
    await applySelectFilter("TypeFilterSelect", "BOUQUET");
    await expect(
      page.locator('#FilterForm [name="TypeFilterSelect"]')
    ).toHaveValue("BOUQUET, ");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });

  test("filter by Operator", async () => {
    await applySelectFilter("OperatorFilterSelect", "Alice");
    await expect(
      page.locator('#FilterForm [name="OperatorFilterSelect"]')
    ).toHaveValue("Alice, ");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });

  // ── Date interval filters ──────────────────────────────────────────

  test("filter by Today", async () => {
    await applyDateInterval("1");
    const dateFrom = await page
      .locator('#FilterForm [name="DateFrom"]')
      .inputValue();
    expect(dateFrom).toBeTruthy();
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  });

  test("filter by Week", async () => {
    await applyDateInterval("2");
    const dateFrom = await page
      .locator('#FilterForm [name="DateFrom"]')
      .inputValue();
    expect(dateFrom).toBeTruthy();
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  });

  test("filter by Month", async () => {
    await applyDateInterval("3");
    const dateFrom = await page
      .locator('#FilterForm [name="DateFrom"]')
      .inputValue();
    expect(dateFrom).toBeTruthy();
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  });

  test("filter by Year", async () => {
    await applyDateInterval("4");
    const dateFrom = await page
      .locator('#FilterForm [name="DateFrom"]')
      .inputValue();
    expect(dateFrom).toBeTruthy();
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  });

  test("filter by All Time", async () => {
    await applyDateInterval("5");
    const dateFrom = await page
      .locator('#FilterForm [name="DateFrom"]')
      .inputValue();
    expect(dateFrom).toBeTruthy();
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });

  // ── Edge cases ─────────────────────────────────────────────────────

  test("empty filter result", async () => {
    await page.fill('#FilterForm [name="IDDoc"]', "NONEXISTENT-XXXX");
    await page.selectOption(
      '#FilterForm select[onchange*="changeDateInterval"]',
      "1"
    );
    await submitFilter();
    await expect(page.locator("#DataList tr.Data")).toHaveCount(0);
    await clearFilter();
  });

  test("saved filter", async () => {
    // "Weekly flowers" saved filter has Note="Weekly office flower delivery"
    await applyFieldFilter("Note", "Weekly office flower delivery");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });

  test("combined Order + Type filter", async () => {
    // SPRING-GALA + BOUQUET should match rows 1 and 2 (both Alice/SPRING-GALA/BOUQUET)
    await page.fill('#FilterForm [name="OrderFilterSelect"]', "SPRING-GALA");
    await page.fill('#FilterForm [name="TypeFilterSelect"]', "BOUQUET");
    await page.selectOption(
      '#FilterForm select[onchange*="changeDateInterval"]',
      "1"
    );
    await submitFilter();
    await expect(
      page.locator('#FilterForm [name="OrderFilterSelect"]')
    ).toHaveValue("SPRING-GALA, ");
    await expect(
      page.locator('#FilterForm [name="TypeFilterSelect"]')
    ).toHaveValue("BOUQUET, ");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await clearFilter();
  });
});
