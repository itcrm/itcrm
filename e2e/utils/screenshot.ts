import { Page, Locator } from "@playwright/test";

/**
 * Wait for any in-progress jQuery UI animations (e.g. dialog fade-in) to finish
 * before taking a screenshot, so modals are never captured half-visible.
 */
export async function waitForAnimations(page: Page): Promise<void> {
  await page.waitForFunction(() => {
    const jq = (window as any).jQuery || (window as any).$;
    return !jq || jq(":animated").length === 0;
  });
  // Wait for the page-lock loading overlay to be hidden before screenshotting.
  const pagelock = page.locator("#pagelock");
  if (await pagelock.count() > 0) {
    await pagelock.waitFor({ state: "hidden" });
  }
  // Wait for the jQuery #Loading AJAX indicator (shown during filter/save XHR calls)
  // to be hidden. It is shown by Loading(f,1) and hidden by Loading(f,0) in the
  // success callback, which runs asynchronously after the HTTP response arrives.
  await page.waitForFunction(() => {
    const el = document.getElementById("Loading");
    if (!el) return true;
    return (
      el.style.display === "none" ||
      window.getComputedStyle(el).display === "none"
    );
  }).catch(() => {
    // Page may navigate away while waiting — that's fine, loader is gone.
  });
}

/**
 * Elements that contain live / time-varying data and must be masked in every
 * screenshot so that captures don't fail due to clock drift or date changes.
 */
export function standardMasks(page: Page): Locator[] {
  return [
    // Data page: doc-date input, live clock input, filter date range inputs
    page.locator('#AddDataForm input[name="Date"]'),
    page.locator("#timedate"),
    page.locator('input[name="DateFrom"]'),
    page.locator('input[name="DateTo"]'),
    // DataList rows: Date + AddDate cell (seeded with NOW(), changes every run)
    page.locator("#DataList tr.Data td:nth-child(3)"),
    // Task/calendar page: FullCalendar month+year title and today-highlighted cell
    page.locator(".fc-header-title h2"),
    page.locator(".fc-state-highlight"),
  ];
}
