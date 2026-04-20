# FoodLoop

FoodLoop is a MySQL-backed PHP prototype aligned with the project UML classes.

## Requirements

- XAMPP or any MySQL/MariaDB server
- PHP with `pdo_mysql`

## Default database configuration

- Host: `127.0.0.1`
- Port: `3306`
- Database: `foodloop`
- User: `root`
- Password: empty by default on XAMPP

You can override those values with `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_CHARSET`, and `DB_COLLATION`.

## Run locally

Start Apache and MySQL in XAMPP, then open the project in the browser. On first load, FoodLoop creates the database schema and loads demo data from `database/schema.sql` and `database/seed.sql`.
