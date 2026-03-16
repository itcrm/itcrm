import { Page, expect } from "@playwright/test";
import { TestEnvironment } from "./test-environment";

export async function login(page: Page, env: TestEnvironment) {
  await page.goto(env.url);
  await page.fill('[name="Login"]', env.users.admin.login);
  await page.fill('[name="Password"]', env.users.admin.password);
  await page.click('[type="submit"]');
  await expect(page.locator("#LoginForm")).not.toBeVisible();
}
