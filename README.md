# FoodLoop

Base de projet simple construite avec :

- HTML
- CSS
- PHP
- JavaScript

## Base de donnees et conception

Les nouveaux livrables ajoutes pour la conception de la plateforme sont :

- `database/schema_foodloop.sql` : schema MySQL complet
- `docs/rapport_foodloop.md` : rapport detaille sur les entites, classes et relations
- `docs/diagramme_classes_foodloop.puml` : diagramme UML en PlantUML

## Structure

- `index.php` : page d'accueil
- `assets/css/style.css` : styles du site
- `assets/js/app.js` : interactions JavaScript

## Lancer le projet

Si PHP est installe sur votre machine :

```powershell
php -S localhost:8000
```

Ensuite, ouvrez `http://localhost:8000`.

## Idees d'evolution

- Ajouter une vraie base de donnees MySQL
- Creer une page de connexion administrateur
- Integrer un panier dynamique cote serveur
- Connecter le formulaire de contact a un traitement PHP
