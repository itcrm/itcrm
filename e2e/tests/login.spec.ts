import { test, expect, Page } from "@playwright/test";
import { createTestEnv, TestEnvironment } from "../utils/test-environment";
import { standardMasks } from "../utils/screenshot";

const LOGON_URL = "/lv/Users/Logon";

let env: TestEnvironment;

test.beforeAll(async () => {
  env = await createTestEnv();
});

test.afterAll(async () => {
  await env.dispose();
});

async function submitLogin(page: Page, login: string, password: string) {
  await page.fill('[name="Login"]', login);
  await page.fill('[name="Password"]', password);
  await page.click('[type="submit"]');
}

test("login succeeds with valid credentials", async ({ page }) => {
  await page.goto(env.url);
  await submitLogin(page, env.users.admin.login, env.users.admin.password);
  await expect(page.locator("#LoginForm")).not.toBeVisible();
});

test("login fails with wrong password", async ({ page }) => {
  await page.goto(env.url);
  const responsePromise = page.waitForResponse((r) => r.url().includes(LOGON_URL));
  await submitLogin(page, env.users.admin.login, "wrongpassword");
  await responsePromise;
  await expect(page.locator("#LoginForm")).toBeVisible();
});

test("login page screenshot", async ({ page }) => {
  await page.goto(env.url);
  await expect(page.locator("#LoginForm")).toBeVisible();
  await expect(page).toHaveScreenshot("login-page.png");
});

test("logged in page screenshot", async ({ page }) => {
  await page.goto(env.url);
  await submitLogin(page, env.users.admin.login, env.users.admin.password);
  await expect(page.locator("#LoginForm")).not.toBeVisible();
  await expect(page).toHaveScreenshot("logged-in-page.png", {
    mask: standardMasks(page),
  });
});
