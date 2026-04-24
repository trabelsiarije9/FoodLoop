# FoodLoop

FoodLoop is a PHP prototype aligned with the project UML classes and can run on MySQL or Oracle through PDO.

## Requirements

- PHP with `pdo_mysql` for MySQL, or `pdo_oci` for Oracle
- XAMPP or any MySQL/MariaDB server if you use MySQL
- An Oracle schema already created if you use Oracle

## Default database configuration

- Driver: `mysql`
- Host: `127.0.0.1`
- Port: `3306`
- Database: `footloop1`
- User: `root`
- Password: empty by default on XAMPP

You can override those values with `DB_DRIVER`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_CHARSET`, `DB_COLLATION`, and `DB_SERVICE_NAME`.

## Oracle configuration

Use values similar to:

- `DB_DRIVER=oracle`
- `DB_HOST=127.0.0.1`
- `DB_PORT=1521`
- `DB_DATABASE=FREEPDB1`
- `DB_SERVICE_NAME=FREEPDB1`
- `DB_USERNAME=foodloop`
- `DB_PASSWORD=your_password`

On first load, FoodLoop initializes the schema from `database/schema.oracle.sql` and the demo data from `database/seed.oracle.sql` if the connected Oracle schema is empty.

## Run locally

For MySQL, start Apache and MySQL in XAMPP, then open the project in the browser. On first load, FoodLoop creates the database schema and loads demo data from `database/schema.sql` and `database/seed.sql`.

For Oracle, configure the Oracle environment variables first, ensure the target schema exists, then open the project in the browser. On first load, FoodLoop loads `database/schema.oracle.sql` and `database/seed.oracle.sql` into that schema when it is empty.
