#!/bin/bash

# Database export script for ConsultingG Real Estate
# Exports Replit PostgreSQL database to SQL and binary formats

set -e

# Create exports directory if it doesn't exist
mkdir -p exports

# Get current date for filename
DATE=$(date +%Y%m%d)

echo "Starting database export..."

# Export SQL dump
echo "Exporting SQL dump..."
pg_dump "$DATABASE_URL" > exports/db_$DATE.sql

# Export binary dump (custom format)
echo "Exporting binary dump..."
pg_dump -Fc "$DATABASE_URL" -f exports/db_$DATE.dump

# Export schema only
echo "Exporting schema only..."
pg_dump --schema-only "$DATABASE_URL" > exports/schema_$DATE.sql

# Export data only
echo "Exporting data only..."
pg_dump --data-only "$DATABASE_URL" > exports/data_$DATE.sql

echo "Export completed successfully!"
echo "Files created in exports/:"
ls -la exports/db_$DATE.*

echo ""
echo "To import these dumps into SuperHosting PostgreSQL:"
echo "1. SQL dump: psql -U username -d database_name -f exports/db_$DATE.sql"
echo "2. Binary dump: pg_restore -U username -d database_name exports/db_$DATE.dump"
echo "3. Schema only: psql -U username -d database_name -f exports/schema_$DATE.sql"
echo "4. Data only: psql -U username -d database_name -f exports/data_$DATE.sql"