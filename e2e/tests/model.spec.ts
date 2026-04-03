/**
 * Model-based test suite.
 *
 * The XState machines in e2e/model/machine.ts describe every screen (state)
 * and every discoverable action (event/transition) in the application.
 *
 * getShortestPaths derives one test path per reachable state, guaranteeing:
 *   - every state is reached and verified
 *   - every state gets a screenshot snapshot (update with --update-snapshots / -u)
 *   - adding a new state + event to the machine automatically adds a test
 *
 * publicMachine  — unauthenticated routes (login, login_failed).
 *                  Tests start from page.goto(env.url).
 *
 * authenticatedMachine — all authenticated routes.
 *                        Tests log in as setup, then follow the path.
 *                        LOGOUT is a root-level event available from every state.
 */
import { test, expect, Page } from "@playwright/test";
import { AnyStateMachine } from "xstate";
import { getShortestPaths } from "@xstate/graph";
import { createTestEnv, TestEnvironment } from "../utils/test-environment";
import { publicMachine, authenticatedMachine } from "../model/machine";
import { eventActions, stateVerifications } from "../model/actions";
import { standardMasks, waitForAnimations } from "../utils/screenshot";

async function walkPath(
  page: Page,
  env: TestEnvironment,
  steps: { event: { type: string } }[]
) {
  for (const step of steps) {
    const eventType = step.event.type;
    if (eventType === "xstate.init") continue;
    const action = eventActions[eventType];
    if (!action) {
      throw new Error(
        `No Playwright action registered for event "${eventType}". ` +
          `Add it to e2e/model/actions.ts.`
      );
    }
    await action(page, env);
  }
}

function registerPaths(machine: AnyStateMachine, setup?: (page: Page, env: TestEnvironment) => Promise<void>) {
  for (const path of getShortestPaths(machine)) {
    const stateName = String(path.state.value);

    test(`[model] ${stateName}`, async ({ page }) => {
      const env: TestEnvironment = await createTestEnv();
      try {
        await page.goto(env.url);
        if (setup) await setup(page, env);
        await walkPath(page, env, path.steps);

        const verify = stateVerifications[stateName as keyof typeof stateVerifications];
        if (verify) await verify(page);

        await page.waitForLoadState("networkidle");
        await waitForAnimations(page);
        await expect(page).toHaveScreenshot(`${stateName}.png`, {
          fullPage: true,
          mask: standardMasks(page),
        });
      } finally {
        await env.dispose();
      }
    });
  }
}

registerPaths(publicMachine);
registerPaths(authenticatedMachine, (page, env) =>
  eventActions["SUBMIT_VALID_CREDENTIALS"](page, env)
);
