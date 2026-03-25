import { Network, GenericContainer, Wait, type StartedTestContainer, type StartedNetwork } from "testcontainers";
import { MariaDbContainer } from "@testcontainers/mariadb";
import { Kysely, MysqlDialect, Generated } from "kysely";
import { createPool } from "mysql2/promise";
import { readFileSync, mkdirSync, writeFileSync } from "fs";
import { join } from "path";
import { randomBytes } from "crypto";

// Minimal DB types — extend as more tables are needed in tests
interface UsersTable {
  ID: Generated<number>;
  Login: string;
  Password: string;
  Color: string;
  Name: string;
  Phone: string;
  AddDate: Date;
  Status: number;
}

export interface DB {
  Users: UsersTable;
}

const DB_USER = "itcrm";
const DB_PASSWORD = "itcrmpass";
const DB_DATABASE = "itcrm";

// Share a single Docker network across all tests in this worker process.
// Each test still gets its own DB + PHP containers for full isolation,
// but reusing the network avoids exhausting Docker's address pool.
let sharedNetwork: StartedNetwork | null = null;
let networkRefCount = 0;

async function acquireNetwork(): Promise<StartedNetwork> {
  if (!sharedNetwork) {
    sharedNetwork = await new Network().start();
  }
  networkRefCount++;
  return sharedNetwork;
}

async function releaseNetwork(): Promise<void> {
  networkRefCount--;
  if (networkRefCount === 0 && sharedNetwork) {
    await sharedNetwork.stop();
    sharedNetwork = null;
  }
}

export async function createTestEnv() {
  const network = await acquireNetwork();
  const dbAlias = `db-${randomBytes(4).toString("hex")}`;

  const mariadb = await new MariaDbContainer("mariadb:10.11")
    .withDatabase(DB_DATABASE)
    .withUsername(DB_USER)
    .withUserPassword(DB_PASSWORD)
    .withNetwork(network)
    .withNetworkAliases(dbAlias)
    .start();

  await mariadb.copyContentToContainer([
    {
      content: readFileSync(join(__dirname, "../../docker/schema.sql"), "utf-8"),
      target: "/tmp/schema.sql",
    },
    {
      content: readFileSync(join(__dirname, "../../docker/seed.sql"), "utf-8"),
      target: "/tmp/seed.sql",
    },
  ]);
  await mariadb.exec(["bash", "-c", `mariadb -u ${DB_USER} -p${DB_PASSWORD} ${DB_DATABASE} < /tmp/schema.sql`]);
  await mariadb.exec(["bash", "-c", `mariadb -u ${DB_USER} -p${DB_PASSWORD} ${DB_DATABASE} < /tmp/seed.sql`]);

  // Kysely client — connects from host via mapped port for direct DB queries in tests
  const db = new Kysely<DB>({
    dialect: new MysqlDialect({
      pool: createPool({
        host: mariadb.getHost(),
        port: mariadb.getMappedPort(3306),
        database: DB_DATABASE,
        user: DB_USER,
        password: DB_PASSWORD,
      }),
    }),
  });

  const coverageEnabled = process.env.COVERAGE === "1";

  // PHP app container — points to MariaDB via its unique network alias
  const container = new GenericContainer("itcrm-php-e2e:latest")
    .withNetwork(network)
    .withEnvironment({
      DB_HOST: dbAlias,
      DB_PORT: "3306",
      DB_USER,
      DB_PASSWORD,
      DB_DATABASE,
      APP_ENV: "production",
      APP_DEBUG: "false",
      ...(coverageEnabled ? { COVERAGE_ENABLED: "1" } : {}),
    })
    .withExposedPorts(80)
    .withWaitStrategy(
      Wait.forHttp("/", 80).forStatusCode(200).withStartupTimeout(30_000)
    );

  const php = await container.start();

  const url = `http://${php.getHost()}:${php.getMappedPort(80)}`;

  return {
    url,
    db,
    users: {
      admin: { login: "Alice", password: "Alice123" },
    },
    async dispose() {
      if (coverageEnabled) {
        await copyCoverageFromContainer(php);
      }
      await Promise.allSettled([db.destroy(), php.stop(), mariadb.stop()]);
      await releaseNetwork();
    },
  };
}

export type TestEnvironment = Awaited<ReturnType<typeof createTestEnv>>;

const COVERAGE_DIR = join(__dirname, "../../coverage-raw");

async function copyCoverageFromContainer(php: StartedTestContainer) {
  mkdirSync(COVERAGE_DIR, { recursive: true });

  // List coverage files in the container
  const { output: fileList } = await php.exec([
    "sh",
    "-c",
    "ls /tmp/coverage/*.json 2>/dev/null || true",
  ]);

  const files = fileList.trim().split("\n").filter(Boolean);
  if (files.length === 0) return;

  // Copy each coverage file from the container
  for (const filePath of files) {
    const { output } = await php.exec(["cat", filePath.trim()]);
    const basename = filePath.trim().split("/").pop()!;
    writeFileSync(join(COVERAGE_DIR, basename), output);
  }
}
