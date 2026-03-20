#!/bin/bash
set -e

# Run SonarQube analysis.
# Starts the server (auth disabled) if not running, waits for readiness,
# runs the scanner, and opens the dashboard.

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

CONTAINER_NAME="sonarqube-server"
IMAGE_NAME="sonarqube:latest"
PROJECT_KEY="itcrm"

SONAR_HOST_URL=${SONAR_HOST_URL:-http://host.docker.internal:9000}
SONAR_LOCAL_URL=${SONAR_LOCAL_URL:-http://localhost:9000}

# --- 1. Ensure SonarQube server is running ---

if docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo "SonarQube is already running."
elif docker ps -aq -f "name=$CONTAINER_NAME" | grep -q .; then
    echo "Restarting stopped SonarQube container..."
    docker start "$CONTAINER_NAME"
else
    echo "Starting SonarQube server..."
    docker run -d --name "$CONTAINER_NAME" -p 9000:9000 \
        -e SONAR_FORCEAUTHENTICATION=false \
        "$IMAGE_NAME"
fi

# --- 2. Wait for readiness ---

echo "Waiting for SonarQube to be ready..."
for i in $(seq 1 60); do
    HEALTH=$(curl -s "$SONAR_LOCAL_URL/api/system/status" 2>/dev/null \
        | grep -o '"status":"[^"]*"' | cut -d'"' -f4)
    if [ "$HEALTH" = "UP" ]; then
        echo "SonarQube is ready."
        break
    fi
    if [ "$i" = "60" ]; then
        echo "Error: SonarQube did not become ready in time."
        exit 1
    fi
    printf "  waiting (%d/60)...\r" "$i"
    sleep 5
done

# --- 3. Generate scanner token ---

TOKEN_NAME="itcrm-scanner"
curl -s -u admin:admin \
    -X POST "$SONAR_LOCAL_URL/api/user_tokens/revoke" \
    -d "name=$TOKEN_NAME" > /dev/null 2>&1

SONAR_TOKEN=$(curl -s -u admin:admin \
    -X POST "$SONAR_LOCAL_URL/api/user_tokens/generate" \
    -d "name=$TOKEN_NAME" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)

if [ -z "$SONAR_TOKEN" ]; then
    echo "Error: Failed to generate scanner token."
    exit 1
fi

# --- 4. Run scanner ---

echo "Running SonarQube analysis..."
docker run \
    --rm \
    -e SONAR_HOST_URL="$SONAR_HOST_URL" \
    -e SONAR_TOKEN="$SONAR_TOKEN" \
    -v "$PROJECT_ROOT:/usr/src" \
    sonarsource/sonar-scanner-cli \
    -Dproject.settings=/usr/src/.sonar/sonar-project.properties

if [ $? -eq 0 ]; then
    echo "Done. Opening dashboard..."
    OPEN_CMD="open"
    command -v xdg-open > /dev/null 2>&1 && OPEN_CMD="xdg-open"
    $OPEN_CMD "$SONAR_LOCAL_URL/dashboard?id=$PROJECT_KEY"
else
    echo "Analysis failed! Check the logs above."
    exit 1
fi
