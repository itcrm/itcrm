import { Network, GenericContainer, Wait, type StartedTestContainer } from "testcontainers";
import { MariaDbContainer } from "@testcontainers/mariadb";
import { Kysely, MysqlDialect, Generated } from "kysely";
import { createPool } from "mysql2/promise";
import { readFileSync, mkdirSync, writeFileSync } from "fs";
import { join } from "path";

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

export async function createTestEnv() {
  const network = await new Network().start();

  const mariadb = await new MariaDbContainer("mariadb:10.11")
    .withDatabase(DB_DATABASE)
    .withUsername(DB_USER)
    .withUserPassword(DB_PASSWORD)
    .withNetwork(network)
    .withNetworkAliases("db")
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

  // PHP app container — points to MariaDB via Docker network alias "db"
  const container = new GenericContainer("itcrm-php-e2e:latest")
    .withNetwork(network)
    .withEnvironment({
      DB_HOST: "db",
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
      admin: { login: "testadmin", password: "testpass" },
    },
    async dispose() {
      if (coverageEnabled) {
        await copyCoverageFromContainer(php);
      }
      await Promise.allSettled([db.destroy(), php.stop(), mariadb.stop()]);
      await network.stop();
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
