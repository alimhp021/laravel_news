# docker-entrypoint-initdb.d/10-enable-pgvector.sh
#!/usr/bin/env bash
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" \
  -c "CREATE EXTENSION IF NOT EXISTS vector;"

# Also create in template1 so new DBs get it by default
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "template1" \
  -c "CREATE EXTENSION IF NOT EXISTS vector;"
