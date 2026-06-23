#!/bin/bash
echo "Waiting for SQL Server to be ready..."

for i in {1..30}; do
    /opt/mssql-tools18/bin/sqlcmd -S db -U sa -P "$SA_PASSWORD" -No -Q "SELECT 1" > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo "SQL Server is ready."
        break
    fi
    echo "Attempt $i: not ready yet, waiting 5s..."
    sleep 5
done

echo "Running DDL script..."
/opt/mssql-tools18/bin/sqlcmd \
  -S db -U sa -P "$SA_PASSWORD" -No \
  -i /docker-entrypoint-initdb.d/init.sql

echo "Database initialized."