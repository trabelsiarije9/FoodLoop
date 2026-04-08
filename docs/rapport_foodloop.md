# Rapport Detaille - FoodLoop

## 1. Contexte du projet

FoodLoop est une plateforme web de gestion et de redistribution des surplus alimentaires en Tunisie.
Son objectif est de reduire le gaspillage alimentaire en mettant en relation :

- les commercants alimentaires,
- les associations caritatives,
- les utilisateurs standards.

Le systeme doit permettre de publier les invendus, de donner une priorite aux associations,
de gerer les reservations, d'organiser les retraits, d'automatiser certaines actions et de
produire des rapports d'impact.

## 2. Objectifs fonctionnels

La plateforme doit permettre :

- l'inscription et l'authentification de plusieurs types d'utilisateurs,
- la gestion des roles et des droits d'acces,
- la publication et la modification des lots alimentaires,
- la reservation prioritaire par les associations,
- la reservation par les utilisateurs ordinaires apres la periode prioritaire,
- la gestion des notifications,
- le suivi des retraits et de la distribution,
- la generation de rapports et d'indicateurs,
- l'integration d'une couche d'automatisation et d'intelligence artificielle.

## 3. Entites principales

### 3.1 Role

Cette entite definit les profils de la plateforme.

Attributs principaux :

- `id`
- `code`
- `name`
- `description`

Exemples :

- `business_owner`
- `association_admin`
- `regular_user`
- `system_admin`

### 3.2 User

Cette entite represente tout utilisateur connecte a la plateforme.

Attributs principaux :

- `id`
- `role_id`
- `first_name`
- `last_name`
- `email`
- `password_hash`
- `phone`
- `is_active`
- `last_login_at`

### 3.3 Address

Cette entite stocke les adresses geographiques des utilisateurs ou des points de retrait.

Attributs principaux :

- `id`
- `user_id`
- `label`
- `address_line`
- `city`
- `governorate`
- `postal_code`
- `latitude`
- `longitude`

### 3.4 Organization

Cette table represente les structures collectives.
Une organisation peut etre un commerce ou une association.

Attributs principaux :

- `id`
- `owner_user_id`
- `organization_type`
- `name`
- `legal_identifier`
- `email`
- `phone`
- `description`
- `is_verified`

### 3.5 FoodCategory

Permet de classifier les produits alimentaires.

Attributs :

- `id`
- `name`
- `description`

### 3.6 FoodItem

Entite centrale du systeme. Elle represente un lot de nourriture disponible.

Attributs principaux :

- `id`
- `organization_id`
- `category_id`
- `created_by`
- `title`
- `description`
- `quantity`
- `unit`
- `production_date`
- `expiration_date`
- `pickup_start`
- `pickup_end`
- `pickup_address_id`
- `status`
- `priority_until`

### 3.7 Reservation

Stocke la demande de reservation d'un lot alimentaire.

Attributs principaux :

- `id`
- `food_item_id`
- `user_id`
- `organization_id`
- `reserved_quantity`
- `status`
- `reserved_at`
- `pickup_confirmed_at`
- `notes`

### 3.8 Pickup

Permet de suivre le retrait physique du lot reserve.

Attributs principaux :

- `id`
- `reservation_id`
- `scheduled_at`
- `picked_up_at`
- `receiver_name`
- `receiver_phone`
- `pickup_code`
- `status`

### 3.9 Distribution

Suivi de la redistribution finale, principalement lorsqu'une association distribue le lot.

Attributs principaux :

- `id`
- `reservation_id`
- `association_id`
- `beneficiary_count`
- `distributed_at`
- `distribution_notes`

### 3.10 Notification

Permet la gestion des alertes et messages systeme.

Attributs principaux :

- `id`
- `user_id`
- `food_item_id`
- `type`
- `channel`
- `subject`
- `message`
- `sent_at`
- `read_at`
- `status`

### 3.11 UserNotificationPreference

Permet de memoriser les preferences de reception des notifications.

Attributs principaux :

- `id`
- `user_id`
- `receive_email`
- `receive_sms`
- `receive_web`
- `radius_km`
- `category_filter`

### 3.12 AIPrediction

Entite dediee aux recommandations intelligentes produites par le systeme d'IA.

Attributs principaux :

- `id`
- `food_item_id`
- `predicted_risk_level`
- `predicted_expiration_probability`
- `recommended_action`
- `generated_at`

### 3.13 AutomationLog

Journalise les traitements automatises.

Attributs principaux :

- `id`
- `action_type`
- `reference_table`
- `reference_id`
- `status`
- `message`
- `executed_at`

### 3.14 Report

Permet de memoriser les rapports generes.

Attributs principaux :

- `id`
- `generated_by`
- `report_type`
- `period_start`
- `period_end`
- `total_food_saved_kg`
- `total_reservations`
- `total_distributions`
- `generated_file`

## 4. Relations entre les entites

Les relations principales sont :

- un `Role` possede plusieurs `User`,
- un `User` peut posseder une ou plusieurs `Address`,
- un `User` peut gerer une ou plusieurs `Organization`,
- une `Organization` possede une ou plusieurs adresses via `organization_addresses`,
- une `Organization` publie plusieurs `FoodItem`,
- un `FoodCategory` regroupe plusieurs `FoodItem`,
- un `FoodItem` peut avoir plusieurs `Reservation`,
- une `Reservation` appartient a un seul `User`,
- une `Reservation` peut etre liee a une `Organization` si elle est faite par une association,
- une `Reservation` peut avoir un `Pickup`,
- une `Reservation` peut avoir une `Distribution`,
- un `User` peut recevoir plusieurs `Notification`,
- un `FoodItem` peut etre associe a plusieurs `Notification`,
- un `FoodItem` peut avoir plusieurs `AIPrediction`,
- un `User` possede une seule configuration dans `UserNotificationPreference`,
- un `User` peut generer plusieurs `Report`.

## 4.1 Verification des exigences UML

Le diagramme de classes respecte les contraintes demandees :

- il contient plus de cinq classes persistantes,
- il contient une association de type un a plusieurs : `Role` -> `User` ou `Organization` -> `FoodItem`,
- il contient une association de type plusieurs a plusieurs porteuse de donnees : la relation entre `User` et `FoodItem` est portee par la classe `Reservation`, qui contient ses propres attributs comme `reserved_quantity`, `status` et `reserved_at`,
- il contient maintenant une association de generalisation.

### Generalisation ajoutee

La generalisation est definie entre la classe abstraite `User` et ses sous-classes :

- `BusinessOwner`
- `AssociationAdmin`
- `RegularUser`
- `SystemAdmin`

Cette generalisation signifie que tous ces profils partagent les attributs communs d'un utilisateur :

- identifiant,
- nom,
- prenom,
- email,
- mot de passe,
- telephone,
- etat du compte.

Puis chaque sous-classe peut porter des caracteristiques specifiques au metier.

Exemple :

- `BusinessOwner` represente un commercant qui publie des surplus,
- `AssociationAdmin` represente une association qui beneficie de la priorite,
- `RegularUser` represente un utilisateur standard,
- `SystemAdmin` represente l'administrateur technique de la plateforme.

## 5. Regles de gestion

### 5.1 Gestion des roles

- un commercant peut publier des lots,
- une association peut reserver en priorite,
- un utilisateur standard ne voit les lots qu'apres la priorite,
- un administrateur systeme supervise l'ensemble.

### 5.2 Gestion des lots alimentaires

- chaque lot doit etre rattache a un commerce,
- chaque lot possede une date d'expiration,
- chaque lot possede un statut metier,
- chaque lot doit definir une fenetre de retrait,
- un lot ne doit pas etre reserve au-dela de la quantite disponible.

### 5.3 Gestion des reservations

- une reservation appartient a un lot et a un utilisateur,
- une reservation d'association peut etre rattachee a l'organisation,
- un lot peut passer successivement par les statuts : `available`, `priority_access`, `reserved`, `picked_up`, `distributed`, `expired`.

### 5.4 Gestion des notifications

- les associations recoivent les alertes prioritaires,
- les utilisateurs peuvent recevoir des alertes par email, sms ou web,
- les lots proches de l'expiration peuvent declencher des notifications automatiques.

### 5.5 IA et automatisation

- le module IA evalue le risque d'expiration des lots,
- il peut recommander une redistribution rapide,
- les robots logiciels peuvent envoyer des alertes, mettre a jour les statuts et produire des rapports.

## 6. Structure orientee objet recommandee

Pour une architecture MVC en PHP orientee objet, les classes suivantes sont recommandees.

### 6.1 Classes metier

- `Role`
- `User`
- `Address`
- `Organization`
- `FoodCategory`
- `FoodItem`
- `Reservation`
- `Pickup`
- `Distribution`
- `Notification`
- `UserNotificationPreference`
- `AIPrediction`
- `AutomationLog`
- `Report`

### 6.2 Classes techniques

- `Database`
- `BaseModel`
- `AuthController`
- `FoodItemController`
- `ReservationController`
- `OrganizationController`
- `NotificationController`
- `ReportController`
- `DashboardController`

### 6.3 Repositories recommandes

- `UserRepository`
- `OrganizationRepository`
- `FoodItemRepository`
- `ReservationRepository`
- `NotificationRepository`
- `ReportRepository`

### 6.4 Services recommandes

- `AuthService`
- `ReservationService`
- `NotificationService`
- `FoodAllocationService`
- `AIPredictionService`
- `AutomationService`
- `ReportingService`

## 7. Exemple de liaisons entre classes

- `User` est liee a `Role`
- `Organization` est liee a `User`
- `FoodItem` est liee a `Organization`, `FoodCategory` et `Address`
- `Reservation` est liee a `FoodItem`, `User` et parfois `Organization`
- `Pickup` depend de `Reservation`
- `Distribution` depend de `Reservation`
- `Notification` cible `User` et eventuellement `FoodItem`
- `AIPrediction` est attachee a `FoodItem`
- `Report` est genere par `User`

## 8. Justification de la normalisation

Le schema propose est bien structure car :

- il separe les utilisateurs des organisations,
- il evite la duplication des adresses,
- il isole les categories alimentaires,
- il trace distinctement la reservation, le retrait et la distribution,
- il permet une evolution vers des modules d'IA, d'automatisation et de reporting sans casser le coeur metier.

## 9. Avantages du schema

- extensible,
- securise,
- compatible MVC,
- adapte a PDO et MySQL,
- clair pour la maintenance,
- approprie pour des tableaux de bord et des statistiques.

## 10. Conclusion

Le schema relationnel propose pour FoodLoop couvre les besoins essentiels du projet :

- gestion des utilisateurs et des roles,
- gestion des commercants et associations,
- publication des surplus alimentaires,
- priorisation des associations,
- reservations et retraits,
- notifications,
- automatisation,
- intelligence artificielle,
- reporting d'impact.

Il constitue une base solide pour developper l'application web en PHP avec une architecture MVC, PDO et programmation orientee objet.
