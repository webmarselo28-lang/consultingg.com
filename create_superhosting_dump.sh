#!/bin/bash
set -euo pipefail

echo "== Install pg client if missing =="
if ! command -v pg_dump >/dev/null 2>&1; then
  sudo apt-get update -y
  sudo apt-get install -y postgresql-client
fi

# --- SuperHosting credentials ---
export SH_DB_HOST="localhost"
export SH_DB_PORT="5432"
export SH_DB_NAME="yogahonc_consultingg8"
export SH_DB_USER="yogahonc_consultingg88"
export SH_DB_PASS="PoloSport88*"

export SUPERHOSTING_DB_URL="postgresql://${SH_DB_USER}:${SH_DB_PASS}@${SH_DB_HOST}:${SH_DB_PORT}/${SH_DB_NAME}"

echo "== Testing DB connection =="
psql "$SUPERHOSTING_DB_URL" -c "SELECT version(), current_database();" | sed -n '1,5p'

echo "== Creating SuperHosting PG10 dump =="
pg_dump \
  --no-owner --no-acl \
  --quote-all-identifiers \
  --file=dump_superhosting_pg10.sql \
  "$SUPERHOSTING_DB_URL"

echo "== Cleaning for PG10 compatibility =="
sed -i '/^[[:space:]]*SET[[:space:]]\+default_table_access_method[[:space:]]*=/d' dump_superhosting_pg10.sql
sed -i 's/^\(CREATE[[:space:]]\+FUNCTION[[:space:]]\+generate_property_code\)/CREATE OR REPLACE FUNCTION generate_property_code/' dump_superhosting_pg10.sql
sed -i 's/^CREATE EXTENSION IF NOT EXISTS "uuid-ossp";/-- CREATE EXTENSION IF NOT EXISTS "uuid-ossp";/' dump_superhosting_pg10.sql || true
sed -i 's/^CREATE EXTENSION IF NOT EXISTS "pgcrypto";/-- CREATE EXTENSION IF NOT EXISTS "pgcrypto";/' dump_superhosting_pg10.sql || true

echo "== Compressing =="
gzip -f -9 dump_superhosting_pg10.sql

ls -lh dump_superhosting_pg10.sql.gz
echo "✅ Done. Dump file: dump_superhosting_pg10.sql.gz"

echo "Import on SuperHosting later:"
echo "  gunzip -c dump_superhosting_pg10.sql.gz | psql -h localhost -U ${SH_DB_USER} -d ${SH_DB_NAME}"
