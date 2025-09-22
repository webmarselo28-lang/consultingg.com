#!/bin/bash
# Export Supabase data to SuperHosting PostgreSQL
# Run this script to export schema and data from Supabase

# Supabase connection details
SUPABASE_HOST="db.mveeovfztfczibtvkpas.supabase.co"
SUPABASE_USER="postgres"
SUPABASE_DB="postgres"
SUPABASE_PORT="5432"

# Output file
OUTPUT_FILE="supabase_backup.sql"

echo "🚀 Exporting Supabase database..."
echo "Host: $SUPABASE_HOST"
echo "Database: $SUPABASE_DB"
echo "Output: $OUTPUT_FILE"

# Export schema and data (excluding system tables)
pg_dump \
  --host="$SUPABASE_HOST" \
  --port="$SUPABASE_PORT" \
  --username="$SUPABASE_USER" \
  --dbname="$SUPABASE_DB" \
  --no-owner \
  --no-privileges \
  --no-tablespaces \
  --exclude-schema=auth \
  --exclude-schema=storage \
  --exclude-schema=realtime \
  --exclude-schema=supabase_functions \
  --exclude-schema=extensions \
  --exclude-schema=graphql \
  --exclude-schema=graphql_public \
  --exclude-schema=pgbouncer \
  --exclude-schema=pgsodium \
  --exclude-schema=pgsodium_masks \
  --exclude-schema=vault \
  --exclude-table-data=auth.* \
  --exclude-table-data=storage.* \
  --file="$OUTPUT_FILE"

if [ $? -eq 0 ]; then
    echo "✅ Export completed successfully!"
    echo "📁 File: $OUTPUT_FILE"
    echo "📊 Size: $(du -h $OUTPUT_FILE | cut -f1)"
    echo ""
    echo "Next steps:"
    echo "1. Review the exported file"
    echo "2. Run import_to_superhosting.sh to import to SuperHosting"
else
    echo "❌ Export failed!"
    exit 1
fi