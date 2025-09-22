#!/bin/bash
# Import Supabase data to SuperHosting PostgreSQL
# Run this script after exporting from Supabase

# SuperHosting connection details
SUPERHOSTING_HOST="localhost"
SUPERHOSTING_USER="superhosting_user"
SUPERHOSTING_DB="superhosting_postgresql"
SUPERHOSTING_PORT="5432"

# Input file
INPUT_FILE="supabase_backup.sql"

echo "🚀 Importing to SuperHosting PostgreSQL..."
echo "Host: $SUPERHOSTING_HOST"
echo "Database: $SUPERHOSTING_DB"
echo "Input: $INPUT_FILE"

# Check if backup file exists
if [ ! -f "$INPUT_FILE" ]; then
    echo "❌ Backup file $INPUT_FILE not found!"
    echo "Please run export_supabase_data.sh first"
    exit 1
fi

# Import data
psql \
  --host="$SUPERHOSTING_HOST" \
  --port="$SUPERHOSTING_PORT" \
  --username="$SUPERHOSTING_USER" \
  --dbname="$SUPERHOSTING_DB" \
  --file="$INPUT_FILE"

if [ $? -eq 0 ]; then
    echo "✅ Import completed successfully!"
    echo ""
    echo "Verifying import..."
    
    # Verify tables exist
    psql \
      --host="$SUPERHOSTING_HOST" \
      --port="$SUPERHOSTING_PORT" \
      --username="$SUPERHOSTING_USER" \
      --dbname="$SUPERHOSTING_DB" \
      --command="\dt"
    
    echo ""
    echo "✅ Migration completed!"
    echo "Update your .env.production with SuperHosting database credentials"
else
    echo "❌ Import failed!"
    exit 1
fi