# FoodLoop

FoodLoop is a PHP prototype aligned with the project UML classes and configured for Oracle through PDO.

## Requirements

- PHP with `pdo_oci`
- An Oracle schema already created

## Default database configuration

- Driver: `oracle`
- Host: `127.0.0.1`
- Port: `1521`
- Database: `FREEPDB1`
- User: `foodloop`
- Password: set in your Oracle environment

You can override those values with `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_CHARSET`, and `DB_SERVICE_NAME`.

## Oracle configuration

Use values similar to:

- `DB_HOST=127.0.0.1`
- `DB_PORT=1521`
- `DB_DATABASE=FREEPDB1`
- `DB_SERVICE_NAME=FREEPDB1`
- `DB_USERNAME=foodloop`
- `DB_PASSWORD=your_password`

On first load, FoodLoop initializes the schema from `database/schema.oracle.sql` and the demo data from `database/seed.oracle.sql` if the connected Oracle schema is empty.

## Run locally

Configure the Oracle environment variables first, ensure the target schema exists, then open the project in the browser. On first load, FoodLoop loads `database/schema.oracle.sql` and `database/seed.oracle.sql` into that schema when it is empty.
