import { test, expect } from "@playwright/test";
import { createTestEnv, TestEnvironment } from "../utils/test-environment";
import { login } from "../utils/actions";

let env: TestEnvironment;

test.beforeAll(async () => {
  env = await createTestEnv();
});

test.afterAll(async () => {
  await env.dispose();
});

test.beforeEach(async ({ page }) => {
  await login(page, env);
  await page.goto(`${env.url}/lv/Types`);
});

test("saving a type with empty code shows validation error on the field", async ({
  page,
}) => {
  const saveResponse = page.waitForResponse((r) =>
    r.url().includes("/Types/Save")
  );
  await page.click('#AddTypesForm [type="submit"]');
  await saveResponse;

  await expect(page.locator('#AddTypesForm [name="Code"]')).toHaveClass(
    /error/
  );
});

test("saving a type with a valid code adds it to the list", async ({ page }) => {
  const code = `T-${Date.now()}`;
  await page.fill('#AddTypesForm [name="Code"]', code);

  const saveResponse = page.waitForResponse((r) =>
    r.url().includes("/Types/Save")
  );
  await page.click('#AddTypesForm [type="submit"]');
  await saveResponse;

  await expect(page.locator("#TypesList")).toContainText(code);
});
