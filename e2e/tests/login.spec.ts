import { test, expect } from "@playwright/test";
import { createTestEnv, TestEnvironment } from "../utils/test-environment";
import { standardMasks } from "../utils/screenshot";

let env: TestEnvironment;

test.beforeAll(async () => {
  env = await createTestEnv();
});

test.afterAll(async () => {
  await env.dispose();
});

test("login succeeds with valid credentials", async ({ page }) => {
  await page.goto(env.url);
  await page.locator("#LoginCards > div", { hasText: env.users.admin.login }).click();
  await expect(page.locator("#LoginCards")).not.toBeVisible();
});

test("login page screenshot", async ({ page }) => {
  await page.goto(env.url);
  await expect(page.locator("#LoginCards")).toBeVisible();
  await expect(page.locator("#LoginCards")).not.toContainText("Old Account");
  await expect(page).toHaveScreenshot("login-page.png");
});

test("logged in page screenshot", async ({ page }) => {
  await page.goto(env.url);
  await page.locator("#LoginCards > div", { hasText: env.users.admin.login }).click();
  await expect(page.locator("#LoginCards")).not.toBeVisible();
  await expect(page).toHaveScreenshot("logged-in-page.png", {
    mask: standardMasks(page),
  });
});

// Server-side rejection paths. The picker UI only surfaces active users, but the
// /Users/Logon endpoint must still reject crafted POSTs with a distinct message.
// Kept at the end of the file because the disabled-user test mutates DB state.
test("login rejects unknown login", async ({ request }) => {
  const response = await request.post(`${env.url}/lv/Users/Logon`, {
    form: { Login: "no-such-user" },
    headers: { "X-Requested-With": "XMLHttpRequest" },
  });
  expect(await response.text()).toContain("Lietotājs nav atrasts");
});

test("login rejects disabled user", async ({ request }) => {
  // Seeded 'disabled' user has Status=0 and is hidden from the picker.
  const response = await request.post(`${env.url}/lv/Users/Logon`, {
    form: { Login: "disabled" },
    headers: { "X-Requested-With": "XMLHttpRequest" },
  });
  expect(await response.text()).toContain("Lietotājs ir atslēgts");
});

test("login rejects soft-deleted user", async ({ request }) => {
  // Soft-delete John (ID 2 per seed insertion order) to set Status=-1.
  await request.post(`${env.url}/lv/Users/Delete`, {
    form: { ID: "2" },
    headers: { "X-Requested-With": "XMLHttpRequest" },
  });
  const response = await request.post(`${env.url}/lv/Users/Logon`, {
    form: { Login: "John" },
    headers: { "X-Requested-With": "XMLHttpRequest" },
  });
  expect(await response.text()).toContain("Lietotājs ir atslēgts");
});
