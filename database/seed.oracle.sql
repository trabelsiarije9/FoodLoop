INSERT INTO roles (id_role, nom, code, created_at) VALUES (1, 'Utilisateur', 'citizen', TO_TIMESTAMP('2026-04-20 08:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO roles (id_role, nom, code, created_at) VALUES (2, 'ProprietaireCommerce', 'business_owner', TO_TIMESTAMP('2026-04-20 08:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO roles (id_role, nom, code, created_at) VALUES (3, 'AdminAssociation', 'association_admin', TO_TIMESTAMP('2026-04-20 08:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO roles (id_role, nom, code, created_at) VALUES (4, 'SuperAdmin', 'super_admin', TO_TIMESTAMP('2026-04-20 08:00:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO categories (id_cat, nom, description) VALUES (1, 'Boulangerie', 'Pains, viennoiseries et sandwichs prepares.');
INSERT INTO categories (id_cat, nom, description) VALUES (2, 'Plats cuisines', 'Repas cuits, portions chaudes et boxes pretes a recuperer.');
INSERT INTO categories (id_cat, nom, description) VALUES (3, 'Epicerie fraiche', 'Paniers melanges fruits, legumes et produits frais.');
INSERT INTO categories (id_cat, nom, description) VALUES (4, 'Produits laitiers', 'Laitages et produits a DLC courte.');

INSERT INTO utilisateurs (id_util, role_id, prenom, nom, email, mot_de_passe, num_tel, adresse, created_at, updated_at)
VALUES (1, 1, 'Amal', 'Ben Ali', 'amal@foodloop.test', 'hash-demo-utilisateur-1', '22123456', '12 Rue de Marseille, Tunis', TO_TIMESTAMP('2026-04-20 08:10:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 08:10:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO utilisateurs (id_util, role_id, prenom, nom, email, mot_de_passe, num_tel, adresse, created_at, updated_at)
VALUES (2, 1, 'Sami', 'Trabelsi', 'sami@foodloop.test', 'hash-demo-utilisateur-2', '55123456', '8 Avenue Habib Bourguiba, Sfax', TO_TIMESTAMP('2026-04-20 08:12:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 08:12:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO zones_geographiques (id_zone, nom, code_postal, ville_nom, gouvernorat, user_id, created_at)
VALUES (1, 'Tunis Centre', 1000, 'Tunis', 'Tunis', 1, TO_TIMESTAMP('2026-04-20 08:20:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO zones_geographiques (id_zone, nom, code_postal, ville_nom, gouvernorat, user_id, created_at)
VALUES (2, 'La Marsa', 2070, 'La Marsa', 'Tunis', 1, TO_TIMESTAMP('2026-04-20 08:22:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO zones_geographiques (id_zone, nom, code_postal, ville_nom, gouvernorat, user_id, created_at)
VALUES (3, 'Sfax Medina', 3000, 'Sfax', 'Sfax', 2, TO_TIMESTAMP('2026-04-20 08:24:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO zones_geographiques (id_zone, nom, code_postal, ville_nom, gouvernorat, user_id, created_at)
VALUES (4, 'Sousse Ville', 4000, 'Sousse', 'Sousse', NULL, TO_TIMESTAMP('2026-04-20 08:26:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO organizations (id, name, organization_type, created_at)
VALUES (1, 'Association Nour', 'association', TO_TIMESTAMP('2026-04-20 08:30:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO organizations (id, name, organization_type, created_at)
VALUES (2, 'Banque Alimentaire Sfax', 'association', TO_TIMESTAMP('2026-04-20 08:32:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO addresses (id, label, street_line, city, postal_code, gouvernorat, created_at)
VALUES (1, 'Siege Tunis', '14 Rue de Palestine', 'Tunis', '1002', 'Tunis', TO_TIMESTAMP('2026-04-20 08:35:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO addresses (id, label, street_line, city, postal_code, gouvernorat, created_at)
VALUES (2, 'Depot La Marsa', '22 Avenue Taieb Mhiri', 'La Marsa', '2070', 'Tunis', TO_TIMESTAMP('2026-04-20 08:36:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO addresses (id, label, street_line, city, postal_code, gouvernorat, created_at)
VALUES (3, 'Hub Sfax', '5 Rue de la Republique', 'Sfax', '3000', 'Sfax', TO_TIMESTAMP('2026-04-20 08:37:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO association_addresses (id, organization_id, address_id, is_primary) VALUES (1, 1, 1, 1);
INSERT INTO association_addresses (id, organization_id, address_id, is_primary) VALUES (2, 1, 2, 0);
INSERT INTO association_addresses (id, organization_id, address_id, is_primary) VALUES (3, 2, 3, 1);

INSERT INTO admins_association (id_admin_association, organization_id, nom_association, created_at)
VALUES (1, 1, 'Association Nour', TO_TIMESTAMP('2026-04-20 08:40:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO admins_association (id_admin_association, organization_id, nom_association, created_at)
VALUES (2, 2, 'Banque Alimentaire Sfax', TO_TIMESTAMP('2026-04-20 08:42:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO proprietaires_commerce (id_commerce, nom_commerce, type_commerce, business_licence, created_at)
VALUES (1, 'Boulangerie El Medina', 'Boulangerie', 'LIC-BEM-2026', TO_TIMESTAMP('2026-04-20 08:45:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO proprietaires_commerce (id_commerce, nom_commerce, type_commerce, business_licence, created_at)
VALUES (2, 'Carthage Kitchen', 'Restaurant', 'LIC-CK-2026', TO_TIMESTAMP('2026-04-20 08:47:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO proprietaires_commerce (id_commerce, nom_commerce, type_commerce, business_licence, created_at)
VALUES (3, 'Green Basket Market', 'Supermarche', NULL, TO_TIMESTAMP('2026-04-20 08:49:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO super_admins (id_super_admin, mot_de_passe, admin_level, created_at)
VALUES (1, 'hash-demo-super-admin', 'global', TO_TIMESTAMP('2026-04-20 08:50:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO annonces (id_annonce, titre, description, type_aliment, quantite, unite, localisation, statut, public_visibility_at, pickup_start, pickup_end, date_expiration, categorie_id, zone_id, proprietaire_id, created_at, updated_at)
VALUES (1, 'Sandwichs invendus du matin', 'Lot de sandwichs frais a redistribuer avant la fin d apres-midi.', 'Boulangerie', 35, 'pieces', 'Rue de Rome', 'priority_access', TO_TIMESTAMP('2026-04-20 15:05:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 15:30:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 16:30:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 18:30:00', 'YYYY-MM-DD HH24:MI:SS'), 1, 1, 1, TO_TIMESTAMP('2026-04-20 14:05:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 14:05:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO annonces (id_annonce, titre, description, type_aliment, quantite, unite, localisation, statut, public_visibility_at, pickup_start, pickup_end, date_expiration, categorie_id, zone_id, proprietaire_id, created_at, updated_at)
VALUES (2, 'Couscous vegetal midi', 'Barquettes chaudes disponibles pour reservation publique.', 'Plat cuisine', 18, 'barquettes', 'La Marsa Plage', 'available', TO_TIMESTAMP('2026-04-20 11:40:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 18:00:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 19:00:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 21:00:00', 'YYYY-MM-DD HH24:MI:SS'), 2, 2, 2, TO_TIMESTAMP('2026-04-20 10:40:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 12:10:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO annonces (id_annonce, titre, description, type_aliment, quantite, unite, localisation, statut, public_visibility_at, pickup_start, pickup_end, date_expiration, categorie_id, zone_id, proprietaire_id, created_at, updated_at)
VALUES (3, 'Paniers fruits et laitages', 'Paniers mixtes pour collecte associative rapide.', 'Epicerie', 24, 'packs', 'Sfax Medina', 'reserved', TO_TIMESTAMP('2026-04-20 10:20:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 15:30:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 16:30:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 19:30:00', 'YYYY-MM-DD HH24:MI:SS'), 3, 3, 3, TO_TIMESTAMP('2026-04-20 09:20:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 13:10:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO annonces (id_annonce, titre, description, type_aliment, quantite, unite, localisation, statut, public_visibility_at, pickup_start, pickup_end, date_expiration, categorie_id, zone_id, proprietaire_id, created_at, updated_at)
VALUES (4, 'Salades pretes a recuperer', 'Portions fraiches a recuperer le soir meme.', 'Repas frais', 12, 'box', 'Sousse Corniche', 'available', TO_TIMESTAMP('2026-04-20 12:15:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 17:30:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 18:30:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 20:00:00', 'YYYY-MM-DD HH24:MI:SS'), 2, 4, 2, TO_TIMESTAMP('2026-04-20 11:15:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 11:45:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO annonces (id_annonce, titre, description, type_aliment, quantite, unite, localisation, statut, public_visibility_at, pickup_start, pickup_end, date_expiration, categorie_id, zone_id, proprietaire_id, created_at, updated_at)
VALUES (5, 'Yaourts proches de date', 'Lot deja recupere lors de la collecte de midi.', 'Produits laitiers', 20, 'pots', 'Bab Bhar', 'picked_up', TO_TIMESTAMP('2026-04-20 08:45:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 12:00:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 13:00:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 13:15:00', 'YYYY-MM-DD HH24:MI:SS'), 4, 1, 3, TO_TIMESTAMP('2026-04-20 07:45:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 13:05:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO utilisateur_categories (utilisateur_id, categorie_id) VALUES (1, 2);
INSERT INTO utilisateur_categories (utilisateur_id, categorie_id) VALUES (1, 4);
INSERT INTO utilisateur_categories (utilisateur_id, categorie_id) VALUES (2, 1);
INSERT INTO utilisateur_categories (utilisateur_id, categorie_id) VALUES (2, 3);

INSERT INTO utilisateur_zones (utilisateur_id, zone_id) VALUES (1, 1);
INSERT INTO utilisateur_zones (utilisateur_id, zone_id) VALUES (1, 2);
INSERT INTO utilisateur_zones (utilisateur_id, zone_id) VALUES (2, 3);
INSERT INTO utilisateur_zones (utilisateur_id, zone_id) VALUES (2, 4);

INSERT INTO consultations_annonces (id_consultation, utilisateur_id, annonce_id, date_consultation)
VALUES (1, 1, 2, TO_TIMESTAMP('2026-04-20 12:20:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO consultations_annonces (id_consultation, utilisateur_id, annonce_id, date_consultation)
VALUES (2, 1, 4, TO_TIMESTAMP('2026-04-20 12:45:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO consultations_annonces (id_consultation, utilisateur_id, annonce_id, date_consultation)
VALUES (3, 2, 3, TO_TIMESTAMP('2026-04-20 13:10:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO consultations_annonces (id_consultation, utilisateur_id, annonce_id, date_consultation)
VALUES (4, 2, 5, TO_TIMESTAMP('2026-04-20 13:25:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO suggestions_ia (id, food_item_id, risk_level, exp_prob, action, gen_at, created_at)
VALUES (1, 1, 'high', 91.50, 'Maintenir l''acces prioritaire pendant 60 minutes puis liberer automatiquement le solde aux utilisateurs.', TO_TIMESTAMP('2026-04-20 14:10:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 14:10:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO suggestions_ia (id, food_item_id, risk_level, exp_prob, action, gen_at, created_at)
VALUES (2, 2, 'medium', 54.25, 'Envoyer un rappel pickup_reminder a 17h30 et proposer une remise si le stock reste incomplet.', TO_TIMESTAMP('2026-04-20 12:15:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 12:15:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO reservations (id_reservation, annonce_id, utilisateur_id, admin_association_id, quantite_reservee, statut, date_reservation, date_pickup, created_at)
VALUES (1, 2, 1, NULL, 2, 'completed', TO_TIMESTAMP('2026-04-20 12:50:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 18:20:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 12:50:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO reservations (id_reservation, annonce_id, utilisateur_id, admin_association_id, quantite_reservee, statut, date_reservation, date_pickup, created_at)
VALUES (2, 1, NULL, 1, 20, 'approved', TO_TIMESTAMP('2026-04-20 14:15:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 15:45:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 14:15:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO reservations (id_reservation, annonce_id, utilisateur_id, admin_association_id, quantite_reservee, statut, date_reservation, date_pickup, created_at)
VALUES (3, 4, 2, NULL, 1, 'picked_up', TO_TIMESTAMP('2026-04-20 13:05:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 17:40:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 13:05:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO reservations (id_reservation, annonce_id, utilisateur_id, admin_association_id, quantite_reservee, statut, date_reservation, date_pickup, created_at)
VALUES (4, 3, NULL, 2, 12, 'approved', TO_TIMESTAMP('2026-04-20 13:30:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 16:00:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 13:30:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO paiements (id_paiement, reservation_id, montant, methode_paiement, date_paiement, statut, created_at)
VALUES (1, 1, 12.50, 'par carte', TO_TIMESTAMP('2026-04-20 12:52:00', 'YYYY-MM-DD HH24:MI:SS'), 'confirme', TO_TIMESTAMP('2026-04-20 12:52:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO paiements (id_paiement, reservation_id, montant, methode_paiement, date_paiement, statut, created_at)
VALUES (2, 3, 5.00, 'espece', TO_TIMESTAMP('2026-04-20 13:08:00', 'YYYY-MM-DD HH24:MI:SS'), 'confirme', TO_TIMESTAMP('2026-04-20 13:08:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO recus (id_recu, paiement_id, montant, date_emission, created_at)
VALUES (1, 1, 12.50, TO_TIMESTAMP('2026-04-20 12:53:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 12:53:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO recus (id_recu, paiement_id, montant, date_emission, created_at)
VALUES (2, 2, 5.00, TO_TIMESTAMP('2026-04-20 13:09:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 13:09:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO pickups (id, reservation_id, scheduled_at, picked_up_at, receiver_name, receiver_phone, pickup_code, status, created_at)
VALUES (1, 1, TO_TIMESTAMP('2026-04-20 18:20:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 18:24:00', 'YYYY-MM-DD HH24:MI:SS'), 'Amal Ben Ali', '22123456', 'PU-AML-001', 'done', TO_TIMESTAMP('2026-04-20 12:55:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO pickups (id, reservation_id, scheduled_at, picked_up_at, receiver_name, receiver_phone, pickup_code, status, created_at)
VALUES (2, 2, TO_TIMESTAMP('2026-04-20 15:45:00', 'YYYY-MM-DD HH24:MI:SS'), NULL, 'Equipe Nour', '70001010', 'PU-NOUR-002', 'scheduled', TO_TIMESTAMP('2026-04-20 14:20:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO pickups (id, reservation_id, scheduled_at, picked_up_at, receiver_name, receiver_phone, pickup_code, status, created_at)
VALUES (3, 3, TO_TIMESTAMP('2026-04-20 17:40:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 17:42:00', 'YYYY-MM-DD HH24:MI:SS'), 'Sami Trabelsi', '55123456', 'PU-SAM-003', 'done', TO_TIMESTAMP('2026-04-20 13:10:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO pickups (id, reservation_id, scheduled_at, picked_up_at, receiver_name, receiver_phone, pickup_code, status, created_at)
VALUES (4, 4, TO_TIMESTAMP('2026-04-20 16:00:00', 'YYYY-MM-DD HH24:MI:SS'), NULL, 'Equipe Sfax', '74444000', 'PU-SFAX-004', 'in_progress', TO_TIMESTAMP('2026-04-20 13:35:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO distributions (id_distribution, reservation_id, admin_association_id, quantite_distrib, date_distrib, statut, created_at)
VALUES (1, 2, 1, 20, TO_TIMESTAMP('2026-04-20 16:10:00', 'YYYY-MM-DD HH24:MI:SS'), 'completed', TO_TIMESTAMP('2026-04-20 14:25:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO distributions (id_distribution, reservation_id, admin_association_id, quantite_distrib, date_distrib, statut, created_at)
VALUES (2, 4, 2, 12, TO_TIMESTAMP('2026-04-20 16:20:00', 'YYYY-MM-DD HH24:MI:SS'), 'in_progress', TO_TIMESTAMP('2026-04-20 13:45:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO reservation_distributions (reservation_id, distribution_id) VALUES (2, 1);
INSERT INTO reservation_distributions (reservation_id, distribution_id) VALUES (4, 2);

INSERT INTO reports (id, generated_by, report_type, period_start, period_end, total_food_saved_kg, total_reservations, total_distributions, created_at)
VALUES (1, 1, 'daily', DATE '2026-04-20', DATE '2026-04-20', 148.50, 4, 2, TO_TIMESTAMP('2026-04-20 18:30:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO reports (id, generated_by, report_type, period_start, period_end, total_food_saved_kg, total_reservations, total_distributions, created_at)
VALUES (2, 1, 'weekly', DATE '2026-04-14', DATE '2026-04-20', 612.80, 18, 11, TO_TIMESTAMP('2026-04-20 18:40:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO report_consultations_commerce (id, report_id, proprietaire_id, consulted_at)
VALUES (1, 1, 1, TO_TIMESTAMP('2026-04-20 18:35:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO report_consultations_commerce (id, report_id, proprietaire_id, consulted_at)
VALUES (2, 1, 2, TO_TIMESTAMP('2026-04-20 18:36:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO report_consultations_commerce (id, report_id, proprietaire_id, consulted_at)
VALUES (3, 2, 3, TO_TIMESTAMP('2026-04-20 18:45:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO report_consult_super_admin (id, report_id, super_admin_id, consulted_at)
VALUES (1, 1, 1, TO_TIMESTAMP('2026-04-20 18:33:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO report_consult_super_admin (id, report_id, super_admin_id, consulted_at)
VALUES (2, 2, 1, TO_TIMESTAMP('2026-04-20 18:43:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO notifications (id_notification, utilisateur_id, admin_association_id, proprietaire_id, annonce_id, reservation_id, report_id, message_notification, type, channel, sent_at, read_at, status, created_at)
VALUES (1, 1, NULL, NULL, 2, 1, NULL, 'Votre reservation sur Couscous vegetal midi est terminee avec succes.', 'reservation_update', 'web', TO_TIMESTAMP('2026-04-20 12:55:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('2026-04-20 13:02:00', 'YYYY-MM-DD HH24:MI:SS'), 'read', TO_TIMESTAMP('2026-04-20 12:55:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO notifications (id_notification, utilisateur_id, admin_association_id, proprietaire_id, annonce_id, reservation_id, report_id, message_notification, type, channel, sent_at, read_at, status, created_at)
VALUES (2, NULL, 1, NULL, 1, 2, NULL, 'Une annonce prioritaire vient d''etre publiee pour votre organisation.', 'new_listing', 'email', TO_TIMESTAMP('2026-04-20 14:12:00', 'YYYY-MM-DD HH24:MI:SS'), NULL, 'queued', TO_TIMESTAMP('2026-04-20 14:12:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO notifications (id_notification, utilisateur_id, admin_association_id, proprietaire_id, annonce_id, reservation_id, report_id, message_notification, type, channel, sent_at, read_at, status, created_at)
VALUES (3, NULL, NULL, 2, 4, 3, NULL, 'Un pickup utilisateur est confirme sur Salades pretes a recuperer.', 'pickup_reminder', 'web', TO_TIMESTAMP('2026-04-20 17:00:00', 'YYYY-MM-DD HH24:MI:SS'), NULL, 'queued', TO_TIMESTAMP('2026-04-20 17:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO notifications (id_notification, utilisateur_id, admin_association_id, proprietaire_id, annonce_id, reservation_id, report_id, message_notification, type, channel, sent_at, read_at, status, created_at)
VALUES (4, NULL, 2, NULL, 3, 4, NULL, 'Le lot reserve doit etre redistribue avant la fermeture du point de collecte.', 'expiration_alert', 'sms', TO_TIMESTAMP('2026-04-20 15:20:00', 'YYYY-MM-DD HH24:MI:SS'), NULL, 'queued', TO_TIMESTAMP('2026-04-20 15:20:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO notifications (id_notification, utilisateur_id, admin_association_id, proprietaire_id, annonce_id, reservation_id, report_id, message_notification, type, channel, sent_at, read_at, status, created_at)
VALUES (5, NULL, NULL, 1, NULL, NULL, 1, 'Le rapport journalier est pret a etre consulte.', 'report_ready', 'email', TO_TIMESTAMP('2026-04-20 18:31:00', 'YYYY-MM-DD HH24:MI:SS'), NULL, 'queued', TO_TIMESTAMP('2026-04-20 18:31:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO contact_messages (id, fullname, email, organization, role_label, message, created_at)
VALUES (1, 'Meriem Hadded', 'meriem@example.com', 'Collectif Zero Gaspillage', 'benevole', 'Nous souhaitons rejoindre la plateforme pour la collecte du soir.', TO_TIMESTAMP('2026-04-20 10:30:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO contact_messages (id, fullname, email, organization, role_label, message, created_at)
VALUES (2, 'Nabil Gharbi', 'nabil@example.com', 'Epicerie du Lac', 'commercant', 'Pouvez-vous nous aider a parametrer les rappels de pickup ?', TO_TIMESTAMP('2026-04-20 11:10:00', 'YYYY-MM-DD HH24:MI:SS'));
