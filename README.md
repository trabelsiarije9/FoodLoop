# FoodLoop

FoodLoop is now a database-driven PHP prototype aligned with the supplied class diagram.

The application models:

- `Utilisateur` plus the extra `utilisateur non connecte` access state
- `ProprietaireCommerce`
- `AdminAssociation`
- `SuperAdmin`
- `Annonce`, `Categorie`, `ZoneGeographique`, `Tag`
- `Reservation`, `Paiement`, `Recu`, `Distribution`
- `Notification`, `SuggestionIA`, `Rapport`

## What Changed

- The old hard-coded repository was replaced with a PDO + SQLite data layer.
- The database is auto-bootstrapped from `database/schema.sql` and `database/seed.sql`.
- The interface now reads live data for announcements, reservations, payments, distributions, notifications, AI suggestions and reports.
- The guest state is explicit: guests can search and view announcements, but cannot reserve or trigger transactional actions.
- Role switching is available in the UI to simulate authenticated contexts for the UML actors.

## Architecture

- `index.php`: front controller with runtime error fallback
- `app/bootstrap.php`: session boot + autoload
- `app/Infrastructure/Database.php`: PDO SQLite connection
- `app/Infrastructure/DatabaseBootstrapper.php`: schema/seed initializer
- `app/Support/ActorContext.php`: role matrix and guest/authenticated permissions
- `app/Support/PlatformClock.php`: fixed demo reference time
- `app/Support/UmlModelCatalog.php`: UML coverage catalog used by the UI
- `app/Controllers/HomeController.php`: request orchestration
- `app/Models/PlatformRepository.php`: SQL queries and joined view models
- `app/Views/templates/home.php`: UML-aligned interface
- `database/schema.sql`: relational schema
- `database/seed.sql`: demo dataset

## Requirements

- PHP with `pdo_sqlite` enabled

## Run Locally

```bash
php -S localhost:8000
```

Then open `http://localhost:8000`.

On first load, the application creates `storage/foodloop.sqlite` automatically.
