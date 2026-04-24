#!/bin/sh
set -eu

: "${NOCO_URL:=http://nocodb:8080}"
: "${NOCO_ADMIN_EMAIL:?NOCO_ADMIN_EMAIL must be set}"
: "${NOCO_ADMIN_PASSWORD:?NOCO_ADMIN_PASSWORD must be set}"
: "${NOCO_BASE_TITLE:=itcrm}"
: "${NOCO_SQLITE_PATH:=/usr/app/data/itcrm/database.sqlite}"

command -v curl >/dev/null 2>&1 && command -v jq >/dev/null 2>&1 \
  || apk add --no-cache curl jq >/dev/null

echo "[init] waiting for NocoDB at ${NOCO_URL}..."
for i in $(seq 1 60); do
  if curl -fsS "${NOCO_URL}/api/v1/health" >/dev/null 2>&1; then
    break
  fi
  sleep 2
  if [ "$i" = "60" ]; then
    echo "[init] NocoDB did not become healthy in time" >&2
    exit 1
  fi
done

echo "[init] signing in as ${NOCO_ADMIN_EMAIL}"
# /api/v1/health flips to 200 before NocoDB finishes its default-org / ensure-org-user
# migrations, during which signin returns 403. Retry until auth is actually usable.
signin_body=$(jq -cn --arg e "$NOCO_ADMIN_EMAIL" --arg p "$NOCO_ADMIN_PASSWORD" '{email:$e,password:$p}')
TOKEN=""
for i in $(seq 1 60); do
  resp=$(curl -sS -X POST "${NOCO_URL}/api/v1/auth/user/signin" \
    -H 'Content-Type: application/json' -d "$signin_body" || true)
  TOKEN=$(printf '%s' "$resp" | jq -r '.token // empty' 2>/dev/null || true)
  if [ -n "$TOKEN" ]; then
    break
  fi
  sleep 2
  if [ "$i" = "60" ]; then
    echo "[init] signin never succeeded — last response: $resp" >&2
    exit 1
  fi
done

existing=$(curl -fsS "${NOCO_URL}/api/v1/db/meta/projects" -H "xc-auth: ${TOKEN}" \
  | jq -r --arg t "$NOCO_BASE_TITLE" '.list[]? | select(.title==$t) | .id' \
  | head -n1)

if [ -n "$existing" ]; then
  # A base row is created before syncMigration runs, so a past failure can leave
  # a sourceless shell that would latch this short-circuit on every retry. Only
  # treat it as configured when a source is actually attached; otherwise delete
  # and recreate so the sidecar self-heals.
  source_count=$(curl -fsS "${NOCO_URL}/api/v1/db/meta/projects/${existing}" \
    -H "xc-auth: ${TOKEN}" | jq -r '(.sources // []) | length')
  if [ "${source_count:-0}" -gt 0 ]; then
    echo "[init] base '${NOCO_BASE_TITLE}' already configured (${existing})"
    exit 0
  fi
  echo "[init] base '${NOCO_BASE_TITLE}' exists but has no sources — deleting and recreating"
  curl -fsS -X DELETE "${NOCO_URL}/api/v1/db/meta/projects/${existing}" \
    -H "xc-auth: ${TOKEN}" >/dev/null
fi

echo "[init] creating base '${NOCO_BASE_TITLE}' for ${NOCO_SQLITE_PATH}"
# NocoDB 0.265.1 reads the sqlite filename at config.connection.connection.filename
# (see KnexMigratorv2._initDbWithSql / Source.getConnectionConfig upstream). The
# outer `connection` wraps the Knex config that NocoDB then passes to Knex.
body=$(jq -cn --arg title "$NOCO_BASE_TITLE" --arg fn "$NOCO_SQLITE_PATH" '{
  title: $title,
  external: true,
  sources: [{
    type: "sqlite3",
    config: {
      client: "sqlite3",
      connection: {
        client: "sqlite3",
        connection: { filename: $fn },
        useNullAsDefault: true
      }
    },
    inflection_column: "camelize",
    inflection_table: "camelize"
  }]
}')

project_id=""
for i in $(seq 1 30); do
  resp=$(curl -sS -X POST "${NOCO_URL}/api/v1/db/meta/projects" \
    -H "xc-auth: ${TOKEN}" -H 'Content-Type: application/json' \
    -d "$body" || true)
  project_id=$(printf '%s' "$resp" | jq -r '.id // empty' 2>/dev/null || true)
  if [ -n "$project_id" ]; then
    break
  fi
  sleep 2
  if [ "$i" = "30" ]; then
    echo "[init] project create never succeeded — last response: $resp" >&2
    exit 1
  fi
done

echo "[init] syncing meta for project ${project_id}"
curl -fsS -X POST "${NOCO_URL}/api/v1/db/meta/projects/${project_id}/meta-diff" \
  -H "xc-auth: ${TOKEN}" >/dev/null

echo "[init] done."
