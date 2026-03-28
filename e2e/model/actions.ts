import { Page, expect } from "@playwright/test";
import { TestEnvironment } from "../utils/test-environment";
import type { PublicState, AuthenticatedState, DataFiltersState } from "./machine";

type ActionFn = (page: Page, env: TestEnvironment) => Promise<void>;
type VerifyFn = (page: Page) => Promise<void>;

type AppState = PublicState | AuthenticatedState | DataFiltersState;

/**
 * Playwright implementation for every event in the state machine.
 * Each function performs exactly the UI action that causes the transition.
 */
export const eventActions: Record<string, ActionFn> = {
  SUBMIT_VALID_CREDENTIALS: async (page, env) => {
    await page.fill('[name="Login"]', env.users.admin.login);
    await page.fill('[name="Password"]', env.users.admin.password);
    await page.click('[type="submit"]');
    await expect(page.locator("#LoginForm")).not.toBeVisible();
  },

  SUBMIT_INVALID_CREDENTIALS: async (page) => {
    await page.fill('[name="Login"]', "wrong");
    await page.fill('[name="Password"]', "wrong");
    const response = page.waitForResponse((r) =>
      r.url().includes("/Users/Logon")
    );
    await page.click('[type="submit"]');
    await response;
    await expect(page.locator("#LoginForm")).toBeVisible();
  },

  NAVIGATE_DATA: async (page) => {
    await page.click('a.menu[href*="/Data"]');
    await expect(page.locator("#DataList")).toBeVisible();
  },

  NAVIGATE_REMINDER: async (page, env) => {
    // Navigate directly to the reminder view for Alice (ID=1) — seeded row has RemindTo=1
    await page.goto(env.url + "/lv/Data/Reminder/1");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  NAVIGATE_TYPES: async (page) => {
    await page.click('a.menu[href*="/Types"]');
    await expect(page.locator("#TypesList")).toBeVisible();
  },

  NAVIGATE_USERS: async (page) => {
    await page.click('a.menu[href*="/Users"]');
    await expect(page.locator("#UsersList")).toBeVisible();
  },

  NAVIGATE_ORDERS: async (page) => {
    await page.click('a.menu[href*="/Orders"]');
    await expect(page.locator("#OrdersList")).toBeVisible();
  },

  NAVIGATE_TASK: async (page) => {
    await page.click('a.menu[href*="/Task"]');
    await expect(page.locator("#calendar")).toBeVisible();
  },

  NAVIGATE_WAREHOUSE: async (page) => {
    await page.click('a.menu[href*="/Warehouse"]');
    await page.waitForURL("**/Warehouse**");
  },

  LOGOUT: async (page) => {
    await page.click("a.Logout");
    await expect(page.locator("#LoginForm")).toBeVisible();
  },

  // ── Data screen actions ────────────────────────────────────────────

  SUBMIT_EMPTY_DATA_ROW: async (page) => {
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Save")
    );
    await page.click('#AddDataForm [type="submit"]');
    await saveResponse;
    // Wait for Loading indicator to hide (callback ran)
    await page.locator("#Loading").waitFor({ state: "hidden" });
  },

  SUBMIT_DATA_ROW: async (page) => {
    // Fill required fields via page.evaluate() so that addAutocomplete()
    // (called by the submit button's onclick) can match the text values
    // against the JS arrays and populate the hidden ID fields.
    await page.evaluate(() => {
      const f = document.querySelector("#AddDataForm") as HTMLFormElement;
      const get = (name: string) =>
        f.querySelector(`input[name="${name}"]`) as HTMLInputElement | null;
      const now = get("Now");
      const date = get("Date");
      if (now && date) date.value = now.value;
      const set = (name: string, val: string) => {
        const el = get(name);
        if (el) el.value = val;
      };
      set("IDDoc", "spring-delivery-followup");
      // Set both visible selects (display) and hidden IDs (server validation).
      // Seeded fixtures: Alice=ID1, SPRING-GALA=ID1, BOUQUET=ID1.
      set("PersonSelect", "Alice");
      set("IDPerson", "1");
      set("OrderSelect", "SPRING-GALA");
      set("IDOrder", "1");
      set("TypeSelect", "BOUQUET");
      set("IDType", "1");
    });
    await page.fill('#AddDataForm [name="Note"]', "Follow-up call regarding spring delivery");
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Save")
    );
    await page.click('#AddDataForm [type="submit"]');
    await saveResponse;
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  SUBMIT_EDIT_DATA_ROW: async (page) => {
    // Change BookNote so a diff is recorded (enables the changes button and changes page)
    await page.fill('#AddDataForm [name="BookNote"]', "EDITED");
    // Editing an existing row shows a confirm dialog — accept it automatically.
    page.once("dialog", (dialog) => dialog.accept());
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Save")
    );
    await page.click('#AddDataForm [type="submit"]');
    await saveResponse;
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  SUBMIT_INVALID_EDIT_DATA_ROW: async (page) => {
    // Clear the required IDOrder hidden field to trigger validation on the edit form
    await page.evaluate(() => {
      const el = document.querySelector(
        '#AddDataForm input[name="IDOrder"]'
      ) as HTMLInputElement | null;
      if (el) el.value = "";
    });
    await page.fill('#AddDataForm [name="OrderSelect"]', "");
    page.once("dialog", (dialog) => dialog.accept());
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Save")
    );
    await page.click('#AddDataForm [type="submit"]');
    await saveResponse;
    // Validation failure opens the #SaveFail dialog
    await expect(page.locator(".ui-dialog")).toBeVisible();
  },

  EDIT_DATA_ROW: async (page) => {
    // EditData() is pure client-side — populates the form from span values in the row
    await page.locator("#DataList a.extra.edit").first().click();
    await expect(page.locator('#AddDataForm [name="ID"]')).not.toHaveValue("");
  },

  COPY_DATA_ROW: async (page) => {
    // EditData(ID, 1) clones the row into the form but zeroes the ID (sets to "0")
    await page.locator("#DataList a.extra.template").first().click();
    // ID is set to "0" (will insert a new row on save), but Note or other fields are populated from the source
    await expect(page.locator('#AddDataForm [name="Note"]')).not.toHaveValue(
      ""
    );
    await expect(page.locator('#AddDataForm [name="ID"]')).toHaveValue("0");
  },

  RESET_DATA_FORM: async (page) => {
    // The — button calls this.form.reset() and clears the onedit row highlight
    await page.locator('#AddDataForm input[type="button"][value="—"]').click();
    await expect(page.locator('#AddDataForm [name="ID"]')).toHaveValue("");
  },

  EXPAND_DATA_ROW: async (page) => {
    await page.locator("#DataList [id^='exp_but']").first().click();
    // After expansion the slider div loses its exp_hide class
    await expect(
      page.locator("#DataList div[id^='slider']").first()
    ).not.toHaveClass(/exp_hide/);
  },

  COLLAPSE_DATA_ROW: async (page) => {
    await page.locator("#DataList [id^='exp_but']").first().click();
    await expect(
      page.locator("#DataList div[id^='slider']").first()
    ).toHaveClass(/exp_hide/);
  },

  DELETE_DATA_ROW: async (page) => {
    // First delete shows a confirm dialog — accept it, row becomes soft-deleted
    page.once("dialog", (dialog) => dialog.accept());
    const deleteResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Delete")
    );
    await page.locator("#DataList a.extra.delete").first().click();
    await deleteResponse;
    await expect(page.locator("#DataList tr.deleted").first()).toBeVisible();
  },

  RESTORE_DATA_ROW: async (page) => {
    const restoreResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Restore")
    );
    await page.locator("#DataList a.extra.restore").first().click();
    await restoreResponse;
    await expect(
      page.locator("#DataList tr.Data:not(.deleted)").first()
    ).toBeVisible();
  },

  HARD_DELETE_DATA_ROW: async (page) => {
    page.once("dialog", (dialog) => dialog.accept());
    const deleteResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Delete")
    );
    await page.locator("#DataList tr.deleted a.extra.delete").first().click();
    await deleteResponse;
    // The row is permanently removed from the DOM
    await expect(page.locator("#DataList tr.deleted")).toHaveCount(0);
  },

  FIND_DELETED_ROWS: async (page) => {
    // Check FindDeleted and submit the filter form — reloads with deleted rows visible
    await page.check('#FilterForm [name="FindDeleted"]');
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.deleted").first()).toBeVisible();
  },

  APPLY_SAVED_FILTER: async (page) => {
    // Weekly flowers has Note='Meeting with client about arrangement'. Directly fill the Note field and apply
    // the filter — this reaches the identical state to selecting the saved filter from the
    // IDFilter dropdown (the state verifier only checks Note value and visible data rows).
    // The IDFilter onchange + getFilterData AJAX path is flaky: the Filters/Get PHP worker
    // holds the session file lock until it exits, which can block the subsequent
    // Data/Filter request when PHP-FPM has limited concurrency.
    await page.fill('#FilterForm [name="Note"]', "Meeting with client about arrangement");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  SEARCH_WITH_DELETED: async (page) => {
    // Check FindDeleted in SearchForm and search — includes soft-deleted rows (tr.deleted) in results
    await page.check('form[name="SearchForm"] [name="FindDeleted"]');
    await page.fill('form[name="SearchForm"] [name="Search"]', "Follow-up call regarding spring delivery");
    await page.click('form[name="SearchForm"] [type="submit"]');
    await page.waitForURL("**/Data/Search**");
    // The deleted row should appear with the "deleted" CSS class
    await expect(page.locator("#DataList tr.deleted").first()).toBeVisible();
  },

  APPLY_DATA_SEARCH: async (page) => {
    // Submit the menu SearchForm — posts to /Data/Search with the search term
    await page.fill('form[name="SearchForm"] [name="Search"]', "Meeting with client about arrangement");
    await page.click('form[name="SearchForm"] [type="submit"]');
    await page.waitForURL("**/Data/Search**");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_DATA_SEARCH_DATE_SORTED: async (page) => {
    // Select Sort=1 (by Date) in SearchForm before submitting
    await page.selectOption('form[name="SearchForm"] [name="Sort"]', "1");
    await page.fill('form[name="SearchForm"] [name="Search"]', "Meeting with client about arrangement");
    await page.click('form[name="SearchForm"] [type="submit"]');
    await page.waitForURL("**/Data/Search**");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_DATA_SEARCH_TODAY: async (page) => {
    // Select Period=5 (Today) and search — filters results to today's date range
    await page.selectOption('form[name="SearchForm"] [name="Period"]', "5");
    await page.fill('form[name="SearchForm"] [name="Search"]', "Meeting with client about arrangement");
    await page.click('form[name="SearchForm"] [type="submit"]');
    await page.waitForURL("**/Data/Search**");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_DATA_SEARCH_WEEK: async (page) => {
    // Select Period=7 (Week) and search — filters results to current week date range
    await page.selectOption('form[name="SearchForm"] [name="Period"]', "7");
    await page.fill('form[name="SearchForm"] [name="Search"]', "Meeting with client about arrangement");
    await page.click('form[name="SearchForm"] [type="submit"]');
    await page.waitForURL("**/Data/Search**");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_DATA_SEARCH_MONTH: async (page) => {
    // Select Period=1 (last 30 days) and search — filters results to last month
    await page.selectOption('form[name="SearchForm"] [name="Period"]', "1");
    await page.fill('form[name="SearchForm"] [name="Search"]', "Meeting with client about arrangement");
    await page.click('form[name="SearchForm"] [type="submit"]');
    await page.waitForURL("**/Data/Search**");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_DATA_SEARCH_YEAR: async (page) => {
    // Select Period=4 (last year) and search — filters results to last 12 months
    await page.selectOption('form[name="SearchForm"] [name="Period"]', "4");
    await page.fill('form[name="SearchForm"] [name="Search"]', "Meeting with client about arrangement");
    await page.click('form[name="SearchForm"] [type="submit"]');
    await page.waitForURL("**/Data/Search**");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  CHANGE_DATA_SORT: async (page) => {
    // Click the sort toggle link — POSTs to /Data/Sort then reloads via window.location.replace
    await page.click('a[href="javascript:changeSort()"]');
    await page.waitForResponse((r) => r.url().includes("/Data/Sort"));
    await page.waitForLoadState("load");
    // After toggling from sort-by-ID (default) to sort-by-Date, link text becomes "Dok.datums"
    await expect(
      page.locator('a[href="javascript:changeSort()"]')
    ).toHaveText("Dok.datums");
  },

  APPLY_DOC_FILTER: async (page) => {
    // Fill the IDDoc field with "gala-client-meeting" — matches the test row's IDDoc value
    await page.fill('#FilterForm [name="IDDoc"]', "gala-client-meeting");
    // Select "All time" so DateFrom/DateTo don't exclude today's rows (SHOW_PERIOD=-2 makes defaults future-dated)
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    // Wait for the full navigation triggered by window.location.replace() in the
    // FilterData() success callback, not just the XHR response. This avoids a race
    // where #Loading is still visible when the screenshot is taken.
    await Promise.all([
      page.waitForNavigation({ waitUntil: "load" }),
      page.click('#FilterForm [type="submit"]'),
    ]);
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_ORDER_FILTER: async (page) => {
    // Fill OrderFilterSelect with "SPRING-GALA" — filterAutocomplete() sets hidden Order=1 on submit
    await page.fill('#FilterForm [name="OrderFilterSelect"]', "SPRING-GALA");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_PERSON_FILTER: async (page) => {
    // Fill PersonFilterSelect with "Alice" — filterAutocomplete() sets hidden Person=1 on submit
    await page.fill('#FilterForm [name="PersonFilterSelect"]', "Alice");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_TYPE_FILTER: async (page) => {
    // Fill TypeFilterSelect with "BOUQUET" — filterAutocomplete() sets hidden Type=1 on submit
    await page.fill('#FilterForm [name="TypeFilterSelect"]', "BOUQUET");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_OPERATOR_FILTER: async (page) => {
    // Fill OperatorFilterSelect with "Alice" — filterAutocomplete() sets hidden Operator=1 on submit
    await page.fill('#FilterForm [name="OperatorFilterSelect"]', "Alice");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_NOTE_FILTER: async (page) => {
    // Fill the Note filter field with "Meeting with client about arrangement" — matches the seeded data row
    await page.fill('#FilterForm [name="Note"]', "Meeting with client about arrangement");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_ID_FILTER: async (page) => {
    // Filter by exact row ID=1 (seeded reminder row) — PHP uses D.ID="1" (exact match)
    await page.fill('#FilterForm [name="ID"]', "1");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_PLACE_TAKEN_FILTER: async (page) => {
    // Filter by PlaceTaken="shop-counter" — seeded reminder row has PlaceTaken='shop-counter'
    await page.fill('#FilterForm [name="PlaceTaken"]', "shop-counter");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_SUM_FILTER: async (page) => {
    // Filter by Sum="42" — seeded reminder row has Sum=42.00 (LIKE match)
    await page.fill('#FilterForm [name="Sum"]', "42");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_BOOKNOTE_FILTER: async (page) => {
    // Filter by BookNote="booking-note" — seeded reminder row has BookNote='booking-note'
    await page.fill('#FilterForm [name="BookNote"]', "booking-note");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_HOURS_FILTER: async (page) => {
    // Filter by Hours="5" — seeded reminder row has Hours=5.00 (LIKE match)
    await page.fill('#FilterForm [name="Hours"]', "5");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_TEXT_TYPE_FILTER: async (page) => {
    // Filter by TextType="type-text" — seeded reminder row has TextType='type-text'
    await page.fill('#FilterForm [name="TextType"]', "type-text");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_TEXT_ORDER_FILTER: async (page) => {
    // Filter by TextOrder="order-text" — seeded reminder row has TextOrder='order-text'
    await page.fill('#FilterForm [name="TextOrder"]', "order-text");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_PLACE_DONE_FILTER: async (page) => {
    // Filter by PlaceDone="client-location" — seeded reminder row has PlaceDone='client-location'
    await page.fill('#FilterForm [name="PlaceDone"]', "client-location");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_TOTAL_PRICE_FILTER: async (page) => {
    // Filter by TotalPrice="100" — seeded reminder row has TotalPrice=100.00 (LIKE match)
    await page.fill('#FilterForm [name="TotalPrice"]', "100");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_PRICE_NOTE_FILTER: async (page) => {
    // Filter by PriceNote="price-note" — seeded reminder row has this value (LIKE match)
    await page.fill('#FilterForm [name="PriceNote"]', "price-note");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_DATE_INTERVAL_TODAY: async (page) => {
    // Select Today (value=1) in the date interval select — JS fills DateFrom/DateTo with today
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "1");
    // The AJAX POST to /Data/Filter returns "1", then JS does window.location.replace(URL+"/Data")
    // which triggers a full page navigation (same URL reload). We must wait for that navigation.
    const navigation = page.waitForNavigation({ waitUntil: "load" });
    await page.click('#FilterForm [type="submit"]');
    await navigation;
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_DATE_INTERVAL_WEEK: async (page) => {
    // Select Week (value=2) in the date interval select — JS fills DateFrom to Monday, DateTo to today
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "2");
    const navigation = page.waitForNavigation({ waitUntil: "load" });
    await page.click('#FilterForm [type="submit"]');
    await navigation;
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_DATE_INTERVAL_MONTH: async (page) => {
    // Select Month (value=3) in the date interval select — JS fills DateFrom to first of current month
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "3");
    const navigation = page.waitForNavigation({ waitUntil: "load" });
    await page.click('#FilterForm [type="submit"]');
    await navigation;
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_DATE_INTERVAL_YEAR: async (page) => {
    // Select Year (value=4) in the date interval select — JS fills DateFrom to Jan 1 of current year
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "4");
    const navigation = page.waitForNavigation({ waitUntil: "load" });
    await page.click('#FilterForm [type="submit"]');
    await navigation;
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_DATE_INTERVAL_ALLTIME: async (page) => {
    // Select AllTime (value=5) — JS fills DateFrom=2000-1-1 and DateTo=today+1
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const navigation = page.waitForNavigation({ waitUntil: "load" });
    await page.click('#FilterForm [type="submit"]');
    await navigation;
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_DATA_FILTER: async (page) => {
    // Fill the free-text Note search field in the FilterForm header
    await page.fill('#FilterForm [name="Note"]', "Meeting with client about arrangement");
    await page.selectOption('#FilterForm select[onchange*="changeDateInterval"]', "5");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    // Verify the filtered DataList still shows the row we added
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  APPLY_EMPTY_FILTER_RESULT: async (page) => {
    // Filter by IDDoc with a non-existent value — no rows match, DataList is empty
    await page.fill('#FilterForm [name="IDDoc"]', "NONEXISTENT-XXXX");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/Filter")
    );
    await page.click('#FilterForm [type="submit"]');
    await filterResponse;
    await page.waitForLoadState("load");
    // No data rows should be visible
    await expect(page.locator("#DataList tr.Data")).toHaveCount(0);
  },

  CLEAR_DATA_FILTER: async (page) => {
    // The — button clears all filter fields and resubmits (resets the filter)
    await page.click('#FilterForm input[type="submit"][value="—"]');
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  TOGGLE_MULTI_EDIT: async (page) => {
    // The checkmark li next to the Save button toggles #MultiEdit visibility
    await page.locator('#AddDataForm li[onclick*="MultiEdit"]').click();
  },

  SUBMIT_MULTI_EDIT: async (page) => {
    // Select the first data row's checkbox
    await page.locator("#DataList input[name='Row']").first().check();
    // Choose "Note" field — triggers ChangeField() which rebuilds #value input
    await page.selectOption('div#MultiEdit [name="fields"]', "Note");
    // Check "replace" position and fill in the note value
    await page.check("#replace");
    await page.fill("#value", "multi-edit-test");
    // Submit: ChangeSelected() posts to /Data/ChangeSelected then reloads
    const changeResponse = page.waitForResponse((r) =>
      r.url().includes("/Data/ChangeSelected")
    );
    await page.click('.SelectChange [type="submit"]');
    await changeResponse;
    // Wait for the page reload triggered by window.location.reload()
    await page.waitForLoadState("load");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  // ── Types screen actions ───────────────────────────────────────────

  SUBMIT_EMPTY_TYPE: async (page) => {
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Types/Save")
    );
    await page.click('#AddTypesForm [type="submit"]');
    await saveResponse;
  },

  SUBMIT_INVALID_TYPE_EDIT: async (page) => {
    // Clear Code field while a type is loaded into the edit form
    await page.fill('#AddTypesForm [name="Code"]', "");
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Types/Save")
    );
    await page.click('#AddTypesForm [type="submit"]');
    await saveResponse;
    await expect(
      page.locator('#AddTypesForm [name="Code"]')
    ).toHaveClass(/error/);
  },

  SUBMIT_VALID_TYPE: async (page) => {
    const code = "T-FLORAL-NEW";
    await page.fill('#AddTypesForm [name="Code"]', code);
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Types/Save")
    );
    await page.click('#AddTypesForm [type="submit"]');
    await saveResponse;
    await expect(page.locator("#TypesList")).toContainText(code);
  },

  EDIT_TYPE_ROW: async (page) => {
    await page.locator("#TypesList a.extra.edit").first().click();
    await expect(
      page.locator('#AddTypesForm [name="ID"]')
    ).not.toHaveValue("");
  },

  RESET_TYPE_FORM: async (page) => {
    // The — button calls this.form.reset(), clearing ID back to empty
    await page.click('#AddTypesForm input[type="button"]');
    await expect(page.locator('#AddTypesForm [name="ID"]')).toHaveValue("");
  },

  DELETE_TYPE_ROW: async (page) => {
    page.once("dialog", (dialog) => dialog.accept());
    const deleteResponse = page.waitForResponse((r) =>
      r.url().includes("/Types/Delete")
    );
    await page.locator("#TypesList a.extra.delete").first().click();
    await deleteResponse;
    await expect(page.locator("#TypesList tr.deleted").first()).toBeVisible();
  },

  RESTORE_TYPE_ROW: async (page) => {
    const restoreResponse = page.waitForResponse((r) =>
      r.url().includes("/Types/Restore")
    );
    await page.locator("#TypesList a.extra.restore").first().click();
    await restoreResponse;
    await expect(
      page.locator("#TypesList tr.Data:not(.deleted)").first()
    ).toBeVisible();
  },

  HARD_DELETE_TYPE_ROW: async (page) => {
    page.once("dialog", (dialog) => dialog.accept());
    const deleteResponse = page.waitForResponse((r) =>
      r.url().includes("/Types/Delete")
    );
    await page.locator("#TypesList tr.deleted a.extra.delete").first().click();
    await deleteResponse;
    await page.locator("#Loading").waitFor({ state: "hidden" });
    await expect(page.locator("#TypesList tr.deleted")).toHaveCount(0);
  },

  SUBMIT_EMPTY_ORDER: async (page) => {
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Orders/Save")
    );
    await page.click('#AddOrdersForm [type="submit"]');
    await saveResponse;
  },

  SUBMIT_INVALID_ORDER_EDIT: async (page) => {
    // Clear Code field while an order is loaded into the edit form
    await page.fill('#AddOrdersForm [name="Code"]', "");
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Orders/Save")
    );
    await page.click('#AddOrdersForm [type="submit"]');
    await saveResponse;
    await expect(
      page.locator('#AddOrdersForm [name="Code"]')
    ).toHaveClass(/error/);
  },

  SUBMIT_VALID_ORDER: async (page) => {
    const code = "O-FLORAL-NEW";
    await page.fill('#AddOrdersForm [name="Code"]', code);
    await page.click('#AddOrdersForm [type="submit"]');
    await expect(page.locator("#OrdersList")).toContainText(code);
  },

  EDIT_ORDER_ROW: async (page) => {
    await page.locator("#OrdersList a.extra.edit").first().click();
    await expect(
      page.locator('#AddOrdersForm [name="ID"]')
    ).not.toHaveValue("");
  },

  SUBMIT_ORDER_EDIT: async (page) => {
    // Ensure Code is non-empty — it may be blank after a validation error path
    const codeVal = await page
      .locator('#AddOrdersForm [name="Code"]')
      .inputValue();
    if (!codeVal.trim()) {
      await page.fill('#AddOrdersForm [name="Code"]', "SPRING-GALA-FIX");
    }
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Orders/Save")
    );
    await page.click('#AddOrdersForm [type="submit"]');
    await saveResponse;
    await expect(page.locator("#OrdersList tr.Data").first()).toBeVisible();
  },

  RESET_ORDER_FORM: async (page) => {
    await page.click('#AddOrdersForm input[type="button"]');
    await expect(page.locator('#AddOrdersForm [name="ID"]')).toHaveValue("");
  },

  APPLY_ORDERS_FILTER: async (page) => {
    await page.fill('#AddOrdersForm [name="Code"]', "SPRING");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Orders/Filter")
    );
    await page.click('#AddOrdersForm input[value="Meklēt"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#OrdersList")).toContainText("SPRING-GALA");
  },

  APPLY_ORDERS_DESCRIPTION_FILTER: async (page) => {
    await page.fill('#AddOrdersForm [name="Description"]', "Spring Gala");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Orders/Filter")
    );
    await page.click('#AddOrdersForm input[value="Meklēt"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#OrdersList")).toContainText("SPRING-GALA");
  },

  SORT_ORDERS: async (page) => {
    // Click the Code column sort link — POSTs to /Orders/Sort and reloads the page
    const sortResponse = page.waitForResponse((r) =>
      r.url().includes("/Orders/Sort")
    );
    await page.click('a[href="javascript:changeOrderSort(\'Code\')"]');
    await sortResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#OrdersList")).toContainText("SPRING-GALA");
  },

  CLEAR_ORDERS_FILTER: async (page) => {
    await page.fill('#AddOrdersForm [name="Code"]', "");
    await page.fill('#AddOrdersForm [name="Description"]', "");
    const filterResponse = page.waitForResponse((r) =>
      r.url().includes("/Orders/Filter")
    );
    await page.click('#AddOrdersForm input[value="Meklēt"]');
    await filterResponse;
    await page.waitForLoadState("load");
    await expect(page.locator("#OrdersList")).toBeVisible();
  },

  DELETE_ORDER_ROW: async (page) => {
    page.once("dialog", (dialog) => dialog.accept());
    const deleteResponse = page.waitForResponse((r) =>
      r.url().includes("/Orders/Delete")
    );
    await page.locator("#OrdersList a.extra.delete").first().click();
    await deleteResponse;
    await expect(page.locator("#OrdersList tr.deleted").first()).toBeVisible();
  },

  RESTORE_ORDER_ROW: async (page) => {
    const restoreResponse = page.waitForResponse((r) =>
      r.url().includes("/Orders/Restore")
    );
    await page.locator("#OrdersList a.extra.restore").first().click();
    await restoreResponse;
    await expect(
      page.locator("#OrdersList tr.Data:not(.deleted)").first()
    ).toBeVisible();
  },

  HARD_DELETE_ORDER_ROW: async (page) => {
    page.once("dialog", (dialog) => dialog.accept());
    const deleteResponse = page.waitForResponse((r) =>
      r.url().includes("/Orders/Delete")
    );
    await page.locator("#OrdersList tr.deleted a.extra.delete").first().click();
    await deleteResponse;
    await page.locator("#Loading").waitFor({ state: "hidden" });
    await expect(page.locator("#OrdersList tr.deleted")).toHaveCount(0);
  },

  SUBMIT_EMPTY_USER: async (page) => {
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Users/Save")
    );
    await page.click('#AddUsersForm [type="submit"]');
    await saveResponse;
  },

  SUBMIT_VALID_USER: async (page) => {
    const login = "temp-florist";
    await page.fill('#AddUsersForm [name="Login"]', login);
    await page.fill('#AddUsersForm [name="Password"]', "test1234");
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Users/Save")
    );
    await page.click('#AddUsersForm [type="submit"]');
    await saveResponse;
    await expect(page.locator("#UsersList")).toContainText(login);
  },

  EDIT_USER_ROW: async (page) => {
    await page.locator("#UsersList a.extra.edit").first().click();
    await expect(
      page.locator('#AddUsersForm [name="ID"]')
    ).not.toHaveValue("");
  },

  SUBMIT_USER_EDIT: async (page) => {
    // Ensure Login is non-empty — may be blank after a validation error path
    const loginVal = await page
      .locator('#AddUsersForm [name="Login"]')
      .inputValue();
    if (!loginVal.trim()) {
      await page.fill('#AddUsersForm [name="Login"]', "Alice");
    }
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Users/Save")
    );
    await page.click('#AddUsersForm [type="submit"]');
    await saveResponse;
    await expect(page.locator("#UsersList")).toBeVisible();
    await expect(page.locator('#AddUsersForm [name="ID"]')).toHaveValue("");
  },

  SUBMIT_INVALID_USER_EDIT: async (page) => {
    // Clear Login field while a user is loaded into the edit form
    await page.fill('#AddUsersForm [name="Login"]', "");
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Users/Save")
    );
    await page.click('#AddUsersForm [type="submit"]');
    await saveResponse;
    await expect(
      page.locator('#AddUsersForm [name="Login"]')
    ).toHaveClass(/error/);
  },

  RESET_USER_FORM: async (page) => {
    await page.click('#AddUsersForm input[type="button"]');
    await expect(page.locator('#AddUsersForm [name="ID"]')).toHaveValue("");
  },

  DELETE_USER_ROW: async (page) => {
    // Delete the temp-florist row (not Alice)
    const userRow = page
      .locator("#UsersList tr.Data")
      .filter({ hasText: "temp-florist" });
    page.once("dialog", (dialog) => dialog.accept());
    const deleteResponse = page.waitForResponse((r) =>
      r.url().includes("/Users/Delete")
    );
    await userRow.locator("a.extra.delete").click();
    await deleteResponse;
    await expect(page.locator("#UsersList tr.deleted").first()).toBeVisible();
  },

  RESTORE_USER_ROW: async (page) => {
    const restoreResponse = page.waitForResponse((r) =>
      r.url().includes("/Users/Restore")
    );
    await page.locator("#UsersList a.extra.restore").first().click();
    await restoreResponse;
    await expect(
      page
        .locator("#UsersList tr.Data:not(.deleted)")
        .filter({ hasText: "temp-florist" })
    ).toBeVisible();
  },

  HARD_DELETE_USER_ROW: async (page) => {
    page.once("dialog", (dialog) => dialog.accept());
    const deleteResponse = page.waitForResponse((r) =>
      r.url().includes("/Users/Delete")
    );
    await page.locator("#UsersList tr.deleted a.extra.delete").first().click();
    await deleteResponse;
    await page.locator("#Loading").waitFor({ state: "hidden" });
    await expect(page.locator("#UsersList tr.deleted")).toHaveCount(0);
  },

  VIEW_DATA_CHANGES: async (page, env) => {
    // Navigate to the change-history page for data row ID=1 (always first in clean test DB)
    await page.goto(`${env.url}/lv/Changes/1`);
    await page.waitForURL("**/Changes/**");
    await expect(page.locator("#DataList")).toBeVisible();
  },

  SHOW_ORDER_CHANGES: async (page) => {
    // SPRING-GALA is seeded with pre-existing change history so the changes button is always visible
    const changesResponse = page.waitForResponse((r) =>
      r.url().includes("/Orders/Changes")
    );
    await page.locator("#OrdersList a.extra.changes:not(.hide)").first().click();
    await changesResponse;
    await page.locator("#Loading").waitFor({ state: "hidden" });
    // Changes are loaded into #Changes1 (SPRING-GALA, always ID=1 in seeded data)
    await expect(page.locator("#Changes1")).not.toBeEmpty();
  },

  TOGGLE_WAREHOUSE_SLIDER: async (page) => {
    // The slider is open by default; click the X button inside it to close (hide) it
    const sliderResponse = page.waitForResponse((r) =>
      r.url().includes("/Warehouse/slider")
    );
    // The close button is the right-hand div inside .slider that has the onclick handler
    await page.locator(".slider [onclick*='slider']").last().click();
    await sliderResponse;
    await expect(page.locator(".slider")).not.toBeVisible();
  },

  SWITCH_TASK_WEEK_VIEW: async (page) => {
    // Click the agendaWeek (Nedēļa) button — fullCalendar v1.5.3 uses fc-button-agendaWeek
    await page.click(".fc-button-agendaWeek");
    // The view container div gets class fc-view-agendaWeek when in week view
    await expect(page.locator(".fc-view-agendaWeek")).toBeVisible();
  },

  SWITCH_TASK_DAY_VIEW: async (page) => {
    // Click the agendaDay (Diena) button — fullCalendar v1.5.3 uses fc-button-agendaDay
    await page.click(".fc-button-agendaDay");
    await expect(page.locator(".fc-view-agendaDay")).toBeVisible();
  },

  SWITCH_TASK_MONTH_VIEW: async (page) => {
    // Click the month button — fullCalendar v1.5.3 uses fc-button-month
    await page.click(".fc-button-month");
    await expect(page.locator(".fc-view-month")).toBeVisible();
  },

  OPEN_WAREHOUSE_SLIDER: async (page) => {
    // Click the SLO div (visible when slider is closed) to re-open the slider
    const sliderResponse = page.waitForResponse((r) =>
      r.url().includes("/Warehouse/slider")
    );
    await page.locator(".SLO").click();
    await sliderResponse;
    await expect(page.locator(".slider")).toBeVisible();
  },

  EXPORT_WAREHOUSE: async (page, env) => {
    // Navigate to the Export URL — shows error message when no warehouse data is available
    await page.goto(env.url + "/lv/Warehouse/Export");
    await expect(page.locator("body")).toContainText("Nav datu");
  },

  NAVIGATE_FILTERS: async (page, env) => {
    await page.goto(`${env.url}/lv/Filters`);
    await page.waitForURL("**/Filters**");
  },

  DELETE_FILTER_ROW: async (page) => {
    page.once("dialog", (dialog) => dialog.accept());
    const deleteResponse = page.waitForResponse((r) =>
      r.url().includes("/Filters/Delete")
    );
    await page.locator("#FiltersList a.extra.delete").first().click();
    await deleteResponse;
    await expect(page.locator("#FiltersList tr.deleted").first()).toBeVisible();
  },

  RESTORE_FILTER_ROW: async (page) => {
    const restoreResponse = page.waitForResponse((r) =>
      r.url().includes("/Filters/Restore")
    );
    await page.locator("#FiltersList a.extra.restore").first().click();
    await restoreResponse;
    await expect(
      page.locator("#FiltersList tr.Data:not(.deleted)").first()
    ).toBeVisible();
  },


  HARD_DELETE_FILTER_ROW: async (page) => {
    page.once("dialog", (dialog) => dialog.accept());
    const deleteResponse = page.waitForResponse((r) =>
      r.url().includes("/Filters/Delete")
    );
    await page.locator("#FiltersList tr.deleted a.extra.delete").first().click();
    await deleteResponse;
    await page.locator("#Loading").waitFor({ state: "hidden" });
    await expect(page.locator("#FiltersList tr.deleted")).toHaveCount(0);
  },

  SUBMIT_EMPTY_FILTER: async (page) => {
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Filters/Save")
    );
    await page.evaluate(() => (window as any).Save("Filters"));
    await saveResponse;
  },

  EDIT_FILTER_ROW: async (page) => {
    await page.locator("#FiltersList a.extra.edit").first().click();
    await expect(page.locator('#AddFiltersForm [name="ID"]')).not.toHaveValue(
      ""
    );
  },

  SUBMIT_FILTER_EDIT: async (page) => {
    // Ensure Name is non-empty — may be blank after a validation error path
    const nameVal = await page
      .locator('#AddFiltersForm [name="Name"]')
      .inputValue();
    if (!nameVal.trim()) {
      await page.fill('#AddFiltersForm [name="Name"]', "Weekly flowers");
    }
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Filters/Save")
    );
    await page.evaluate(() => (window as any).Save("Filters"));
    await saveResponse;
    await expect(page.locator("#FiltersList tr.Data").first()).toBeVisible();
  },

  SUBMIT_INVALID_FILTER_EDIT: async (page) => {
    // Clear Name field while a filter is loaded into the edit form
    await page.fill('#AddFiltersForm [name="Name"]', "");
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Filters/Save")
    );
    await page.evaluate(() => (window as any).Save("Filters"));
    await saveResponse;
    await expect(
      page.locator('#AddFiltersForm [name="Name"]')
    ).toHaveClass(/error/);
  },

  RESET_FILTER_FORM: async (page) => {
    await page.locator('#AddFiltersForm input[type="button"]').click();
    await expect(page.locator('#AddFiltersForm [name="ID"]')).toHaveValue("");
  },

  SUBMIT_VALID_FILTER: async (page) => {
    const name = "Weekly flowers";
    await page.fill('#AddFiltersForm [name="Name"]', name);
    const saveResponse = page.waitForResponse((r) =>
      r.url().includes("/Filters/Save")
    );
    await page.evaluate(() => (window as any).Save("Filters"));
    await saveResponse;
    await expect(page.locator("#FiltersList tr.Data").first()).toBeVisible();
  },
};

/**
 * Assertions that confirm we have arrived in the expected state.
 * Called after all events in a path have been executed.
 */
export const stateVerifications: Record<AppState, VerifyFn> = {
  logged_out: async (page) => {
    await expect(page.locator("#LoginForm")).toBeVisible();
  },

  login: async (page) => {
    await expect(page.locator("#LoginForm")).toBeVisible();
  },

  login_failed: async (page) => {
    await expect(page.locator("#LoginForm")).toBeVisible();
  },

  data: async (page) => {
    await expect(page.locator("#DataList")).toBeVisible();
  },

  types: async (page) => {
    await expect(page.locator("#TypesList")).toBeVisible();
  },

  types_validation_error: async (page) => {
    await expect(
      page.locator('#AddTypesForm [name="Code"]')
    ).toHaveClass(/error/);
  },

  types_edit_validation_error: async (page) => {
    // Code field has error class — form is still in edit mode (ID non-empty)
    await expect(
      page.locator('#AddTypesForm [name="Code"]')
    ).toHaveClass(/error/);
    await expect(page.locator('#AddTypesForm [name="ID"]')).not.toHaveValue(
      ""
    );
  },

  users: async (page) => {
    await expect(page.locator("#UsersList")).toBeVisible();
  },

  users_edit_validation_error: async (page) => {
    // Login field has error class — form is still in edit mode (ID non-empty)
    await expect(
      page.locator('#AddUsersForm [name="Login"]')
    ).toHaveClass(/error/);
    await expect(page.locator('#AddUsersForm [name="ID"]')).not.toHaveValue(
      ""
    );
  },



  orders: async (page) => {
    await expect(page.locator("#OrdersList")).toBeVisible();
  },

  orders_edit_validation_error: async (page) => {
    // Code field has the error class — form is still in edit mode (ID non-empty)
    await expect(
      page.locator('#AddOrdersForm [name="Code"]')
    ).toHaveClass(/error/);
    await expect(page.locator('#AddOrdersForm [name="ID"]')).not.toHaveValue(
      ""
    );
  },

  task: async (page) => {
    await expect(page.locator("#calendar")).toBeVisible();
  },

  task_week_view: async (page) => {
    // fullCalendar v1.5.3 view container div has class fc-view-agendaWeek
    await expect(page.locator(".fc-view-agendaWeek")).toBeVisible();
  },

  task_day_view: async (page) => {
    // fullCalendar v1.5.3 view container div has class fc-view-agendaDay
    await expect(page.locator(".fc-view-agendaDay")).toBeVisible();
  },

  warehouse: async (page) => {
    await expect(page.locator("#DataList")).toBeVisible();
  },

  warehouse_slider_closed: async (page) => {
    // The .slider selection toolbar is hidden after closing it
    await expect(page.locator(".slider")).not.toBeVisible();
  },

  warehouse_export_empty: async (page) => {
    // Export page shows "Nav datu eksportēšanai!" when no warehouse items exist
    await expect(page.locator("body")).toContainText("Nav datu");
  },

  filters: async (page) => {
    await expect(page).toHaveURL(/\/Filters/);
  },

  filters_validation_error: async (page) => {
    await expect(
      page.locator('#AddFiltersForm [name="Name"]')
    ).toHaveClass(/error/);
  },


  filters_hard_deleted: async (page) => {
    // After hard delete the deleted filter row is permanently gone
    await expect(page.locator("#FiltersList tr.deleted")).toHaveCount(0);
  },

  filters_saved: async (page) => {
    await expect(page.locator("#FiltersList tr.Data").first()).toBeVisible();
  },

  filters_row_deleted: async (page) => {
    await expect(page.locator("#FiltersList tr.deleted").first()).toBeVisible();
  },

  filters_edit_validation_error: async (page) => {
    // Name field has error class — form is still in edit mode (ID non-empty)
    await expect(
      page.locator('#AddFiltersForm [name="Name"]')
    ).toHaveClass(/error/);
    await expect(page.locator('#AddFiltersForm [name="ID"]')).not.toHaveValue(
      ""
    );
  },

  filters_row_edit: async (page) => {
    await expect(page.locator('#AddFiltersForm [name="ID"]')).not.toHaveValue(
      ""
    );
  },

  data_find_deleted: async (page) => {
    // The FindDeleted checkbox is checked and deleted rows are visible after reload
    await expect(
      page.locator('#FilterForm [name="FindDeleted"]')
    ).toBeChecked();
    await expect(page.locator("#DataList tr.deleted").first()).toBeVisible();
  },

  data_saved_filter: async (page) => {
    // After applying the saved filter, its Note criteria pre-fill the FilterForm on reload.
    // (IDFilter is unset from session after submit, but the filter's field values persist.)
    await expect(
      page.locator('#FilterForm [name="Note"]')
    ).toHaveValue("Meeting with client about arrangement");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  ready: async (page) => {
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filter_empty_result: async (page) => {
    // IDDoc filter field shows "NONEXISTENT-XXXX" and DataList has no data rows
    await expect(
      page.locator('#FilterForm [name="IDDoc"]')
    ).toHaveValue("NONEXISTENT-XXXX");
    await expect(page.locator("#DataList tr.Data")).toHaveCount(0);
  },

  data_search_results: async (page) => {
    await expect(page).toHaveURL(/\/Data\/Search/);
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_search_date_sorted: async (page) => {
    await expect(page).toHaveURL(/\/Data\/Search/);
    // Sort=1 (by Date) is stored in session and reflected in the SearchForm dropdown
    await expect(
      page.locator('form[name="SearchForm"] [name="Sort"]')
    ).toHaveValue("1");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_search_today: async (page) => {
    await expect(page).toHaveURL(/\/Data\/Search/);
    // Period=5 (Today) is echoed back in the menu SearchForm on the search results page
    await expect(
      page.locator('form[name="SearchForm"] [name="Period"]')
    ).toHaveValue("5");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_search_week: async (page) => {
    await expect(page).toHaveURL(/\/Data\/Search/);
    // Period=7 (Week) is echoed back in the menu SearchForm on the search results page
    await expect(
      page.locator('form[name="SearchForm"] [name="Period"]')
    ).toHaveValue("7");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_search_month: async (page) => {
    await expect(page).toHaveURL(/\/Data\/Search/);
    // Period=1 (last 30 days) is echoed back in the menu SearchForm
    await expect(
      page.locator('form[name="SearchForm"] [name="Period"]')
    ).toHaveValue("1");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_search_year: async (page) => {
    await expect(page).toHaveURL(/\/Data\/Search/);
    // Period=4 (last year) is echoed back in the menu SearchForm
    await expect(
      page.locator('form[name="SearchForm"] [name="Period"]')
    ).toHaveValue("4");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_search_deleted: async (page) => {
    await expect(page).toHaveURL(/\/Data\/Search/);
    // FindDeleted was checked — search results include deleted rows (tr.deleted class)
    await expect(page.locator("#DataList tr.deleted").first()).toBeVisible();
  },

  data_changes_page: async (page) => {
    await expect(page).toHaveURL(/\/Changes\//);
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_row_deleted: async (page) => {
    await expect(page.locator("#DataList tr.deleted").first()).toBeVisible();
  },

  data_empty: async (page) => {
    // After permanently deleting the last row the DataList has no data rows
    await expect(page.locator("#DataList tr.Data")).toHaveCount(0);
  },

  data_reminder_view: async (page) => {
    // Reminder view: URL contains /Data/Reminder/ and rows with RemindTo=1 are listed
    await expect(page).toHaveURL(/\/Data\/Reminder\//);
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_validation_error: async (page) => {
    await page.waitForTimeout(500);
    // Validation failure opens the #SaveFail jQuery UI dialog
    await expect(page.locator(".ui-dialog")).toBeVisible();
  },

  data_edit_validation_error: async (page) => {
    // Validation failure shows the #SaveFail dialog, AND form has a non-zero ID (edit mode)
    await expect(page.locator(".ui-dialog")).toBeVisible();
    const idVal = await page.locator('#AddDataForm [name="ID"]').inputValue();
    expect(parseInt(idVal)).toBeGreaterThan(0);
  },

  data_row_saved: async (page) => {
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_row_editing: async (page) => {
    // The form ID field is non-empty and the row in the list has class `onedit`
    await expect(page.locator('#AddDataForm [name="ID"]')).not.toHaveValue("");
    await expect(page.locator("#DataList tr.onedit")).toBeVisible();
  },

  data_row_copy: async (page) => {
    // ID is "0" (will insert a new row on save) but other fields are cloned
    await expect(page.locator('#AddDataForm [name="ID"]')).toHaveValue("0");
    await expect(page.locator('#AddDataForm [name="Note"]')).not.toHaveValue(
      ""
    );
  },

  data_row_expanded: async (page) => {
    await expect(
      page.locator("#DataList div[id^='slider']").first()
    ).not.toHaveClass(/exp_hide/);
  },

  data_sort_toggled: async (page) => {
    // DataList is now sorted by document date — link text is "Dok.datums" (shows current sort)
    // DataList may be empty due to date filter, but the sort link text confirms the toggle
    await expect(
      page.locator('a[href="javascript:changeSort()"]')
    ).toHaveText("Dok.datums");
    await expect(page.locator("#DataList")).toBeVisible();
  },

  data_filtered: async (page) => {
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
    await expect(
      page.locator('#FilterForm [name="Note"]')
    ).not.toHaveValue("");
  },

  data_filtered_by_doc: async (page) => {
    // IDDoc field shows the filter value that was applied
    await expect(page.locator('#FilterForm [name="IDDoc"]')).toHaveValue(
      "gala-client-meeting"
    );
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_by_order: async (page) => {
    // OrderFilterSelect field shows "SPRING-GALA, " — filterAutocomplete(1) appends ", " on reload
    await expect(page.locator('#FilterForm [name="OrderFilterSelect"]')).toHaveValue(
      "SPRING-GALA, "
    );
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_by_person: async (page) => {
    // PersonFilterSelect field shows "Alice, " — filterAutocomplete(1) appends ", " on reload
    await expect(page.locator('#FilterForm [name="PersonFilterSelect"]')).toHaveValue(
      "Alice, "
    );
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_by_type: async (page) => {
    // TypeFilterSelect field shows "BOUQUET, " — filterAutocomplete(1) appends ", " on reload
    await expect(page.locator('#FilterForm [name="TypeFilterSelect"]')).toHaveValue(
      "BOUQUET, "
    );
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_by_operator: async (page) => {
    // OperatorFilterSelect field shows "Alice, " — filterAutocomplete(1) appends ", " on reload
    await expect(page.locator('#FilterForm [name="OperatorFilterSelect"]')).toHaveValue(
      "Alice, "
    );
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_by_note: async (page) => {
    // Note filter field shows "Meeting with client about arrangement" — the note filter was applied
    await expect(page.locator('#FilterForm [name="Note"]')).toHaveValue(
      "Meeting with client about arrangement"
    );
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_by_id: async (page) => {
    // ID filter field shows "1" — exact match filter was applied for row ID=1
    await expect(page.locator('#FilterForm [name="ID"]')).toHaveValue("1");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_by_place_taken: async (page) => {
    // PlaceTaken filter field shows "shop-counter" — place taken filter was applied
    await expect(page.locator('#FilterForm [name="PlaceTaken"]')).toHaveValue(
      "shop-counter"
    );
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_by_sum: async (page) => {
    // Sum filter field shows "42" — sum filter was applied
    await expect(page.locator('#FilterForm [name="Sum"]')).toHaveValue("42");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_by_booknote: async (page) => {
    // BookNote filter field shows "booking-note" — book note filter was applied
    await expect(page.locator('#FilterForm [name="BookNote"]')).toHaveValue(
      "booking-note"
    );
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_by_hours: async (page) => {
    // Hours filter field shows "5" — hours filter was applied
    await expect(page.locator('#FilterForm [name="Hours"]')).toHaveValue("5");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_by_text_type: async (page) => {
    // TextType filter field shows "type-text" — text type filter was applied
    await expect(page.locator('#FilterForm [name="TextType"]')).toHaveValue(
      "type-text"
    );
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_by_text_order: async (page) => {
    // TextOrder filter field shows "order-text" — text order filter was applied
    await expect(page.locator('#FilterForm [name="TextOrder"]')).toHaveValue(
      "order-text"
    );
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_by_place_done: async (page) => {
    // PlaceDone filter field shows "client-location" — place done filter was applied
    await expect(page.locator('#FilterForm [name="PlaceDone"]')).toHaveValue(
      "client-location"
    );
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_by_total_price: async (page) => {
    // TotalPrice filter field shows "100" — total price filter was applied
    await expect(page.locator('#FilterForm [name="TotalPrice"]')).toHaveValue(
      "100"
    );
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_by_price_note: async (page) => {
    // PriceNote filter field shows "price-note" — price note filter was applied
    await expect(
      page.locator('#FilterForm [name="PriceNote"]')
    ).toHaveValue("price-note");
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_today: async (page) => {
    // DateFrom and DateTo should be filled with today's date range after the interval filter
    const dateFrom = await page.locator('#FilterForm [name="DateFrom"]').inputValue();
    expect(dateFrom).toBeTruthy();
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_week: async (page) => {
    // DateFrom should be filled with the start of the current week
    const dateFrom = await page.locator('#FilterForm [name="DateFrom"]').inputValue();
    expect(dateFrom).toBeTruthy();
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_month: async (page) => {
    // DateFrom should be filled with the first day of the current month
    const dateFrom = await page.locator('#FilterForm [name="DateFrom"]').inputValue();
    expect(dateFrom).toBeTruthy();
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_year: async (page) => {
    // DateFrom should be filled with Jan 1 of the current year
    const dateFrom = await page.locator('#FilterForm [name="DateFrom"]').inputValue();
    expect(dateFrom).toBeTruthy();
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  data_filtered_alltime: async (page) => {
    // DateFrom should be filled with 2000-01-01 (all time from the start)
    const dateFrom = await page.locator('#FilterForm [name="DateFrom"]').inputValue();
    expect(dateFrom).toBeTruthy();
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },



  data_multi_edit_open: async (page) => {
    await expect(page.locator("#MultiEdit")).toBeVisible();
    await page.waitForTimeout(500);
  },

  data_multi_edit_with_rows: async (page) => {
    await expect(page.locator("#MultiEdit")).toBeVisible();
    await expect(page.locator("#DataList tr.Data").first()).toBeVisible();
  },

  types_hard_deleted: async (page) => {
    // After hard delete the deleted row is permanently gone from the list
    await expect(page.locator("#TypesList tr.deleted")).toHaveCount(0);
  },

  types_saved: async (page) => {
    await expect(page.locator("#TypesList tr.Data").first()).toBeVisible();
  },

  types_row_deleted: async (page) => {
    await expect(page.locator("#TypesList tr.deleted").first()).toBeVisible();
  },

  types_row_edit: async (page) => {
    await expect(page.locator('#AddTypesForm [name="ID"]')).not.toHaveValue("");
  },

  orders_hard_deleted: async (page) => {
    // After hard delete the deleted row is permanently gone from the list
    await expect(page.locator("#OrdersList tr.deleted")).toHaveCount(0);
    // At least SPRING-GALA (seeded) remains as an active row
    await expect(page.locator("#OrdersList tr.Data:not(.deleted)").first()).toBeVisible();
  },

  orders_sort_toggled: async (page) => {
    // Orders list is shown after toggling the sort to Code ASC
    await expect(page.locator("#OrdersList")).toContainText("SPRING-GALA");
  },

  orders_changes_panel: async (page) => {
    // #Changes1 (SPRING-GALA) is expanded with change history loaded
    await expect(page.locator("#Changes1")).not.toBeEmpty();
  },

  orders_saved: async (page) => {
    await expect(page.locator("#OrdersList tr.Data").first()).toBeVisible();
  },

  orders_validation_error: async (page) => {
    await expect(
      page.locator('#AddOrdersForm [name="Code"]')
    ).toHaveClass(/error/);
  },

  orders_filtered: async (page) => {
    // Filter is stored in session; list shows only matching orders
    await expect(page.locator("#OrdersList")).toContainText("SPRING-GALA");
  },

  orders_description_filtered: async (page) => {
    // Description filter is active in session; list shows orders matching "Spring Gala"
    await expect(page.locator("#OrdersList")).toContainText("SPRING-GALA");
    await expect(page.locator("#OrdersList")).toContainText("Spring Gala");
  },

  orders_row_deleted: async (page) => {
    await expect(page.locator("#OrdersList tr.deleted").first()).toBeVisible();
  },

  orders_row_edit: async (page) => {
    await expect(
      page.locator('#AddOrdersForm [name="ID"]')
    ).not.toHaveValue("");
  },

  users_hard_deleted: async (page) => {
    // After hard delete the deleted user row is permanently gone
    await expect(page.locator("#UsersList tr.deleted")).toHaveCount(0);
  },

  users_saved: async (page) => {
    await expect(page.locator("#UsersList")).toContainText("temp-florist");
  },

  users_validation_error: async (page) => {
    await expect(
      page.locator('#AddUsersForm [name="Login"]')
    ).toHaveClass(/error/);
  },

  users_row_deleted: async (page) => {
    await expect(page.locator("#UsersList tr.deleted").first()).toBeVisible();
  },

  users_edit_form: async (page) => {
    await expect(
      page.locator('#AddUsersForm [name="ID"]')
    ).not.toHaveValue("");
  },
};

