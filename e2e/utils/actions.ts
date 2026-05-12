import { Page, expect } from "@playwright/test";
import { TestEnvironment } from "./test-environment";

export async function login(page: Page, env: TestEnvironment) {
  await page.goto(env.url);
  await page.locator("#LoginCards > div", { hasText: env.users.admin.login }).click();
  await expect(page.locator("#LoginCards")).not.toBeVisible();
}
