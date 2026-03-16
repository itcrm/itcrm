import { execSync } from "child_process";
import { join } from "path";

export default async function globalSetup() {
  console.log("Building itcrm PHP e2e image...");
  execSync("docker build -t itcrm-php-e2e:latest .", {
    cwd: join(__dirname, ".."),
    stdio: "inherit",
  });
  console.log("Image ready: itcrm-php-e2e:latest");
}
