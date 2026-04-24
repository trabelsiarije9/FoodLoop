CREATE DATABASE IF NOT EXISTS foodloop
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE foodloop;

CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(30) NULL,
    profile_image VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE business_owners (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    business_license VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_business_owners_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE association_admins (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    association_approval_code VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_association_admins_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE regular_users (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    notification_radius_km INT UNSIGNED NOT NULL DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_regular_users_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE system_admins (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    admin_level VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_system_admins_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE addresses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    label VARCHAR(100) NOT NULL,
    address_line VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    governorate VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NULL,
    latitude DECIMAL(10, 7) NULL,
    longitude DECIMAL(10, 7) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_addresses_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE organizations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_user_id BIGINT UNSIGNED NOT NULL,
    organization_type ENUM('business', 'association') NOT NULL,
    name VARCHAR(180) NOT NULL,
    legal_identifier VARCHAR(100) NULL,
    email VARCHAR(190) NULL,
    phone VARCHAR(30) NULL,
    description TEXT NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_organizations_owner FOREIGN KEY (owner_user_id) REFERENCES users(id)
);

CREATE TABLE organization_addresses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    address_id BIGINT UNSIGNED NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_org_addresses_org FOREIGN KEY (organization_id) REFERENCES organizations(id),
    CONSTRAINT fk_org_addresses_address FOREIGN KEY (address_id) REFERENCES addresses(id)
);

CREATE TABLE food_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL
);

CREATE TABLE food_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    quantity DECIMAL(10, 2) NOT NULL,
    unit VARCHAR(30) NOT NULL,
    production_date DATETIME NULL,
    expiration_date DATETIME NOT NULL,
    pickup_start DATETIME NOT NULL,
    pickup_end DATETIME NOT NULL,
    pickup_address_id BIGINT UNSIGNED NOT NULL,
    status ENUM('draft', 'available', 'priority_access', 'reserved', 'picked_up', 'distributed', 'expired', 'cancelled') NOT NULL DEFAULT 'draft',
    priority_until DATETIME NULL,
    image_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_food_items_org FOREIGN KEY (organization_id) REFERENCES organizations(id),
    CONSTRAINT fk_food_items_category FOREIGN KEY (category_id) REFERENCES food_categories(id),
    CONSTRAINT fk_food_items_user FOREIGN KEY (created_by) REFERENCES users(id),
    CONSTRAINT fk_food_items_address FOREIGN KEY (pickup_address_id) REFERENCES addresses(id)
);

CREATE TABLE reservations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    food_item_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    organization_id BIGINT UNSIGNED NULL,
    reserved_quantity DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'picked_up', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    reserved_at DATETIME NOT NULL,
    pickup_confirmed_at DATETIME NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reservations_food FOREIGN KEY (food_item_id) REFERENCES food_items(id),
    CONSTRAINT fk_reservations_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_reservations_org FOREIGN KEY (organization_id) REFERENCES organizations(id)
);

CREATE TABLE pickups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reservation_id BIGINT UNSIGNED NOT NULL,
    scheduled_at DATETIME NOT NULL,
    picked_up_at DATETIME NULL,
    receiver_name VARCHAR(150) NULL,
    receiver_phone VARCHAR(30) NULL,
    pickup_code VARCHAR(50) NULL,
    status ENUM('scheduled', 'in_progress', 'done', 'missed') NOT NULL DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pickups_reservation FOREIGN KEY (reservation_id) REFERENCES reservations(id)
);

CREATE TABLE distributions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reservation_id BIGINT UNSIGNED NOT NULL,
    association_id BIGINT UNSIGNED NULL,
    beneficiary_count INT UNSIGNED NULL,
    distributed_at DATETIME NULL,
    distribution_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_distributions_reservation FOREIGN KEY (reservation_id) REFERENCES reservations(id),
    CONSTRAINT fk_distributions_association FOREIGN KEY (association_id) REFERENCES organizations(id)
);

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    food_item_id BIGINT UNSIGNED NULL,
    type ENUM('new_listing', 'priority_alert', 'expiration_alert', 'reservation_update', 'pickup_reminder', 'report_ready') NOT NULL,
    channel ENUM('email', 'sms', 'web') NOT NULL,
    subject VARCHAR(190) NOT NULL,
    message TEXT NOT NULL,
    sent_at DATETIME NULL,
    read_at DATETIME NULL,
    status ENUM('queued', 'sent', 'failed', 'read') NOT NULL DEFAULT 'queued',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_notifications_food FOREIGN KEY (food_item_id) REFERENCES food_items(id)
);

CREATE TABLE user_notification_preferences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    receive_email TINYINT(1) NOT NULL DEFAULT 1,
    receive_sms TINYINT(1) NOT NULL DEFAULT 0,
    receive_web TINYINT(1) NOT NULL DEFAULT 1,
    radius_km INT UNSIGNED NOT NULL DEFAULT 10,
    category_filter JSON NULL,
    CONSTRAINT fk_notification_preferences_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE ai_predictions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    food_item_id BIGINT UNSIGNED NOT NULL,
    predicted_risk_level ENUM('low', 'medium', 'high') NOT NULL,
    predicted_expiration_probability DECIMAL(5, 2) NOT NULL,
    recommended_action TEXT NOT NULL,
    generated_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ai_predictions_food FOREIGN KEY (food_item_id) REFERENCES food_items(id)
);

CREATE TABLE automation_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_type ENUM('notification_sent', 'status_update', 'report_generated', 'reservation_released', 'ai_recommendation_generated') NOT NULL,
    reference_table VARCHAR(100) NOT NULL,
    reference_id BIGINT UNSIGNED NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    message TEXT NULL,
    executed_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    generated_by BIGINT UNSIGNED NOT NULL,
    report_type ENUM('daily', 'weekly', 'monthly', 'impact', 'custom') NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    total_food_saved_kg DECIMAL(12, 2) NOT NULL DEFAULT 0,
    total_reservations INT UNSIGNED NOT NULL DEFAULT 0,
    total_distributions INT UNSIGNED NOT NULL DEFAULT 0,
    generated_file VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reports_user FOREIGN KEY (generated_by) REFERENCES users(id)
);

CREATE TABLE contact_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(180) NOT NULL,
    email VARCHAR(190) NOT NULL,
    organization VARCHAR(180) NULL,
    role_label VARCHAR(100) NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_role_id ON users(role_id);
CREATE INDEX idx_organizations_type ON organizations(organization_type);
CREATE INDEX idx_food_items_status ON food_items(status);
CREATE INDEX idx_food_items_expiration_date ON food_items(expiration_date);
CREATE INDEX idx_food_items_org_status ON food_items(organization_id, status);
CREATE INDEX idx_reservations_food_item_id ON reservations(food_item_id);
CREATE INDEX idx_reservations_user_id ON reservations(user_id);
CREATE INDEX idx_notifications_user_status ON notifications(user_id, status);
CREATE INDEX idx_ai_predictions_food_item_id ON ai_predictions(food_item_id);
CREATE INDEX idx_automation_logs_reference ON automation_logs(reference_table, reference_id);

INSERT INTO roles (code, name, description) VALUES
('business_owner', 'Proprietaire de commerce', 'Restaurants, boulangeries, superettes et autres commerces alimentaires partenaires.'),
('association_admin', 'Responsable associatif', 'Associations caritatives avec acces prioritaire aux invendus alimentaires.'),
('regular_user', 'Utilisateur regulier', 'Personnes qui consultent les annonces et reservent les lots disponibles.'),
('system_admin', 'Administrateur systeme', 'Supervision de la plateforme, moderation des contenus et suivi des rapports.');

INSERT INTO food_categories (name, description) VALUES
('Boulangerie', 'Pains, viennoiseries, gateaux et produits de boulangerie.'),
('Produits frais', 'Fruits, legumes et produits frais de saison.'),
('Plats prepares', 'Repas cuisines et portions pretes a emporter.'),
('Produits laitiers', 'Lait, yaourts, fromages et desserts frais.'),
('Boissons', 'Jus, eaux et boissons gazeuses.'),
('Epicerie', 'Produits emballes a longue conservation.');

INSERT INTO users (
    role_id,
    first_name,
    last_name,
    email,
    password_hash,
    phone,
    is_active,
    last_login_at
)
SELECT id, 'Karim', 'Mansouri', 'admin@foodloop.tn', '$2y$10$j2LZebFXS4PDTOH4TLWq2uR2E3DaNDhvndsIcfbyITABzQWOZXAfa', '+21620100100', 1, DATE_SUB(NOW(), INTERVAL 2 HOUR)
FROM roles
WHERE code = 'system_admin'
UNION ALL
SELECT id, 'Yassine', 'Ben Salem', 'yassine@delicefood.tn', '$2y$10$j2LZebFXS4PDTOH4TLWq2uR2E3DaNDhvndsIcfbyITABzQWOZXAfa', '+21621111222', 1, DATE_SUB(NOW(), INTERVAL 5 HOUR)
FROM roles
WHERE code = 'business_owner'
UNION ALL
SELECT id, 'Mariem', 'Gharbi', 'mariem@carrefourlac2.tn', '$2y$10$j2LZebFXS4PDTOH4TLWq2uR2E3DaNDhvndsIcfbyITABzQWOZXAfa', '+21622111333', 1, DATE_SUB(NOW(), INTERVAL 7 HOUR)
FROM roles
WHERE code = 'business_owner'
UNION ALL
SELECT id, 'Hichem', 'Trabelsi', 'hichem@moulindor-cun.tn', '$2y$10$j2LZebFXS4PDTOH4TLWq2uR2E3DaNDhvndsIcfbyITABzQWOZXAfa', '+21623111444', 1, DATE_SUB(NOW(), INTERVAL 4 HOUR)
FROM roles
WHERE code = 'business_owner'
UNION ALL
SELECT id, 'Amina', 'Ben Amor', 'amina@rahma-tunis.tn', '$2y$10$j2LZebFXS4PDTOH4TLWq2uR2E3DaNDhvndsIcfbyITABzQWOZXAfa', '+21624111555', 1, DATE_SUB(NOW(), INTERVAL 1 HOUR)
FROM roles
WHERE code = 'association_admin'
UNION ALL
SELECT id, 'Sami', 'Jebali', 'sami@nour-sfax.tn', '$2y$10$j2LZebFXS4PDTOH4TLWq2uR2E3DaNDhvndsIcfbyITABzQWOZXAfa', '+21625111666', 1, DATE_SUB(NOW(), INTERVAL 3 HOUR)
FROM roles
WHERE code = 'association_admin'
UNION ALL
SELECT id, 'Ahmed', 'Ben Ali', 'ahmed.benali@mail.tn', '$2y$10$j2LZebFXS4PDTOH4TLWq2uR2E3DaNDhvndsIcfbyITABzQWOZXAfa', '+21626111777', 1, DATE_SUB(NOW(), INTERVAL 9 HOUR)
FROM roles
WHERE code = 'regular_user'
UNION ALL
SELECT id, 'Leila', 'Mansour', 'leila.mansour@mail.tn', '$2y$10$j2LZebFXS4PDTOH4TLWq2uR2E3DaNDhvndsIcfbyITABzQWOZXAfa', '+21627111888', 1, DATE_SUB(NOW(), INTERVAL 6 HOUR)
FROM roles
WHERE code = 'regular_user'
UNION ALL
SELECT id, 'Fares', 'Gdiri', 'fares.gdiri@mail.tn', '$2y$10$j2LZebFXS4PDTOH4TLWq2uR2E3DaNDhvndsIcfbyITABzQWOZXAfa', '+21628111999', 1, DATE_SUB(NOW(), INTERVAL 8 HOUR)
FROM roles
WHERE code = 'regular_user'
UNION ALL
SELECT id, 'Ines', 'Khelifi', 'ines.khelifi@mail.tn', '$2y$10$j2LZebFXS4PDTOH4TLWq2uR2E3DaNDhvndsIcfbyITABzQWOZXAfa', '+21629112000', 1, DATE_SUB(NOW(), INTERVAL 10 HOUR)
FROM roles
WHERE code = 'regular_user';

INSERT INTO business_owners (user_id, business_license) VALUES
((SELECT id FROM users WHERE email = 'yassine@delicefood.tn'), 'RC-TN-DEL-2026-001'),
((SELECT id FROM users WHERE email = 'mariem@carrefourlac2.tn'), 'RC-TN-CAR-2026-004'),
((SELECT id FROM users WHERE email = 'hichem@moulindor-cun.tn'), 'RC-TN-MDO-2026-007');

INSERT INTO association_admins (user_id, association_approval_code) VALUES
((SELECT id FROM users WHERE email = 'amina@rahma-tunis.tn'), 'ASS-RAHMA-2026-011'),
((SELECT id FROM users WHERE email = 'sami@nour-sfax.tn'), 'ASS-NOUR-2026-015');

INSERT INTO regular_users (user_id, notification_radius_km) VALUES
((SELECT id FROM users WHERE email = 'ahmed.benali@mail.tn'), 12),
((SELECT id FROM users WHERE email = 'leila.mansour@mail.tn'), 8),
((SELECT id FROM users WHERE email = 'fares.gdiri@mail.tn'), 15),
((SELECT id FROM users WHERE email = 'ines.khelifi@mail.tn'), 10);

INSERT INTO system_admins (user_id, admin_level) VALUES
((SELECT id FROM users WHERE email = 'admin@foodloop.tn'), 'super');

INSERT INTO addresses (
    user_id,
    label,
    address_line,
    city,
    governorate,
    postal_code,
    latitude,
    longitude
) VALUES
((SELECT id FROM users WHERE email = 'admin@foodloop.tn'), 'Domicile', '12 Rue de Syrie', 'Tunis', 'Tunis', '1002', 36.8065000, 10.1815000),
((SELECT id FROM users WHERE email = 'yassine@delicefood.tn'), 'Domicile', '8 Rue Ibn Khaldoun', 'La Marsa', 'Tunis', '2070', 36.8789000, 10.3248000),
((SELECT id FROM users WHERE email = 'mariem@carrefourlac2.tn'), 'Domicile', '14 Residence El Hana', 'Le Kram', 'Tunis', '2015', 36.8437000, 10.3031000),
((SELECT id FROM users WHERE email = 'hichem@moulindor-cun.tn'), 'Domicile', '3 Rue de la Liberte', 'Ariana', 'Ariana', '2080', 36.8663000, 10.1647000),
((SELECT id FROM users WHERE email = 'amina@rahma-tunis.tn'), 'Domicile', '27 Rue de Palestine', 'Tunis', 'Tunis', '1000', 36.7982000, 10.1779000),
((SELECT id FROM users WHERE email = 'sami@nour-sfax.tn'), 'Domicile', '55 Avenue Habib Bourguiba', 'Sfax Ville', 'Sfax', '3000', 34.7406000, 10.7603000),
((SELECT id FROM users WHERE email = 'ahmed.benali@mail.tn'), 'Domicile', '6 Rue Ennasr', 'Ariana', 'Ariana', '2037', 36.8620000, 10.1640000),
((SELECT id FROM users WHERE email = 'leila.mansour@mail.tn'), 'Domicile', '19 Rue de Marseille', 'Sousse', 'Sousse', '4000', 35.8256000, 10.6369000),
((SELECT id FROM users WHERE email = 'fares.gdiri@mail.tn'), 'Domicile', '4 Cite El Khadra', 'Tunis', 'Tunis', '1003', 36.8356000, 10.1819000),
((SELECT id FROM users WHERE email = 'ines.khelifi@mail.tn'), 'Domicile', '21 Rue Ibn Rochd', 'Nabeul', 'Nabeul', '8000', 36.4513000, 10.7353000),
(NULL, 'Point de collecte', '22 Avenue Taieb Mhiri', 'La Marsa', 'Tunis', '2070', 36.8791000, 10.3239000),
(NULL, 'Point de collecte', '12 Rue du Lac Michigan', 'Les Berges du Lac', 'Tunis', '1053', 36.8480000, 10.2797000),
(NULL, 'Point de collecte', '7 Avenue Hedi Nouira', 'Centre Urbain Nord', 'Ariana', '1082', 36.8511000, 10.1947000),
(NULL, 'Siege', '45 Rue de Palestine', 'Tunis', 'Tunis', '1002', 36.7989000, 10.1788000),
(NULL, 'Siege', '10 Avenue 14 Janvier', 'Sfax Ville', 'Sfax', '3000', 34.7392000, 10.7583000);

INSERT INTO organizations (
    owner_user_id,
    organization_type,
    name,
    legal_identifier,
    email,
    phone,
    description,
    is_verified
) VALUES
((SELECT id FROM users WHERE email = 'yassine@delicefood.tn'), 'business', 'Delice La Marsa', 'MF-TN-458901C', 'contact@delicelamarsa.tn', '+21671345000', 'Point de collecte Delice pour yaourts, desserts et produits laitiers encore consommables.', 1),
((SELECT id FROM users WHERE email = 'mariem@carrefourlac2.tn'), 'business', 'Carrefour Market Lac 2', 'MF-TN-774201P', 'lac2@carrefourmarket.tn', '+21670988010', 'Superette partenaire qui publie des paniers de fruits, boissons et produits du quotidien.', 1),
((SELECT id FROM users WHERE email = 'hichem@moulindor-cun.tn'), 'business', 'Moulin d Or Centre Urbain Nord', 'MF-TN-902110R', 'cun@moulindor.tn', '+21670844020', 'Magasin partenaire pour sandwiches, viennoiseries et biscuits Moulin d Or.', 1),
((SELECT id FROM users WHERE email = 'amina@rahma-tunis.tn'), 'association', 'Association Rahma Tunis', 'ASS-TN-RAHMA-19', 'contact@rahmatunis.tn', '+21671555660', 'Association de quartier qui redistribue rapidement les denrees aux familles fragiles.', 1),
((SELECT id FROM users WHERE email = 'sami@nour-sfax.tn'), 'association', 'Association Nour El Khir Sfax', 'ASS-TN-NOUR-08', 'bureau@nourkhir.tn', '+21674222330', 'Association locale qui organise des collectes et des distributions solidaires a Sfax.', 1);

INSERT INTO organization_addresses (organization_id, address_id, is_primary) VALUES
(
    (SELECT id FROM organizations WHERE name = 'Delice La Marsa'),
    (SELECT id FROM addresses WHERE label = 'Point de collecte' AND address_line = '22 Avenue Taieb Mhiri'),
    1
),
(
    (SELECT id FROM organizations WHERE name = 'Carrefour Market Lac 2'),
    (SELECT id FROM addresses WHERE label = 'Point de collecte' AND address_line = '12 Rue du Lac Michigan'),
    1
),
(
    (SELECT id FROM organizations WHERE name = 'Moulin d Or Centre Urbain Nord'),
    (SELECT id FROM addresses WHERE label = 'Point de collecte' AND address_line = '7 Avenue Hedi Nouira'),
    1
),
(
    (SELECT id FROM organizations WHERE name = 'Association Rahma Tunis'),
    (SELECT id FROM addresses WHERE label = 'Siege' AND address_line = '45 Rue de Palestine'),
    1
),
(
    (SELECT id FROM organizations WHERE name = 'Association Nour El Khir Sfax'),
    (SELECT id FROM addresses WHERE label = 'Siege' AND address_line = '10 Avenue 14 Janvier'),
    1
);

INSERT INTO food_items (
    organization_id,
    category_id,
    created_by,
    title,
    description,
    quantity,
    unit,
    production_date,
    expiration_date,
    pickup_start,
    pickup_end,
    pickup_address_id,
    status,
    priority_until,
    image_path
) VALUES
(
    (SELECT id FROM organizations WHERE name = 'Delice La Marsa'),
    (SELECT id FROM food_categories WHERE name = 'Produits laitiers'),
    (SELECT id FROM users WHERE email = 'yassine@delicefood.tn'),
    'Yaourts Delice fraise',
    'Lot de 48 pots Delice, chaine du froid respectee et retrait rapide recommande.',
    48.00,
    'pot',
    DATE_SUB(NOW(), INTERVAL 1 DAY),
    DATE_ADD(NOW(), INTERVAL 2 DAY),
    TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00'),
    TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '13:00:00'),
    (SELECT id FROM addresses WHERE label = 'Point de collecte' AND address_line = '22 Avenue Taieb Mhiri'),
    'available',
    NULL,
    'storage/foods/yaourts-delice-fraise.jpg'
),
(
    (SELECT id FROM organizations WHERE name = 'Moulin d Or Centre Urbain Nord'),
    (SELECT id FROM food_categories WHERE name = 'Plats prepares'),
    (SELECT id FROM users WHERE email = 'hichem@moulindor-cun.tn'),
    'Mini sandwiches thon Moulin d Or',
    'Portions preparees le matin meme, adaptees a une redistribution rapide en soiree.',
    20.00,
    'piece',
    DATE_SUB(NOW(), INTERVAL 10 HOUR),
    DATE_ADD(NOW(), INTERVAL 20 HOUR),
    TIMESTAMP(CURDATE(), '18:00:00'),
    TIMESTAMP(CURDATE(), '21:00:00'),
    (SELECT id FROM addresses WHERE label = 'Point de collecte' AND address_line = '7 Avenue Hedi Nouira'),
    'priority_access',
    DATE_ADD(NOW(), INTERVAL 6 HOUR),
    'storage/foods/mini-sandwiches-moulin-dor.jpg'
),
(
    (SELECT id FROM organizations WHERE name = 'Carrefour Market Lac 2'),
    (SELECT id FROM food_categories WHERE name = 'Produits frais'),
    (SELECT id FROM users WHERE email = 'mariem@carrefourlac2.tn'),
    'Paniers de fruits Carrefour',
    'Paniers composes de bananes, pommes et oranges encore en bon etat pour consommation rapide.',
    12.00,
    'panier',
    DATE_SUB(NOW(), INTERVAL 8 HOUR),
    DATE_ADD(NOW(), INTERVAL 30 HOUR),
    TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00'),
    TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00:00'),
    (SELECT id FROM addresses WHERE label = 'Point de collecte' AND address_line = '12 Rue du Lac Michigan'),
    'reserved',
    NULL,
    'storage/foods/paniers-fruits-carrefour.jpg'
),
(
    (SELECT id FROM organizations WHERE name = 'Carrefour Market Lac 2'),
    (SELECT id FROM food_categories WHERE name = 'Boissons'),
    (SELECT id FROM users WHERE email = 'mariem@carrefourlac2.tn'),
    'Boga Lim 1L',
    'Bouteilles scellees Boga Lim, parfaites pour un lot familial ou associatif.',
    30.00,
    'bouteille',
    DATE_SUB(NOW(), INTERVAL 7 DAY),
    DATE_ADD(NOW(), INTERVAL 25 DAY),
    TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00'),
    TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '19:00:00'),
    (SELECT id FROM addresses WHERE label = 'Point de collecte' AND address_line = '12 Rue du Lac Michigan'),
    'available',
    NULL,
    'storage/foods/boga-lim-1l.jpg'
),
(
    (SELECT id FROM organizations WHERE name = 'Moulin d Or Centre Urbain Nord'),
    (SELECT id FROM food_categories WHERE name = 'Boulangerie'),
    (SELECT id FROM users WHERE email = 'hichem@moulindor-cun.tn'),
    'Croissants Moulin d Or',
    'Viennoiseries du jour retirees avant fermeture et deja affectees a un retrait confirme.',
    18.00,
    'piece',
    DATE_SUB(NOW(), INTERVAL 14 HOUR),
    DATE_ADD(NOW(), INTERVAL 12 HOUR),
    TIMESTAMP(CURDATE(), '07:00:00'),
    TIMESTAMP(CURDATE(), '10:00:00'),
    (SELECT id FROM addresses WHERE label = 'Point de collecte' AND address_line = '7 Avenue Hedi Nouira'),
    'picked_up',
    NULL,
    'storage/foods/croissants-moulin-dor.jpg'
),
(
    (SELECT id FROM organizations WHERE name = 'Carrefour Market Lac 2'),
    (SELECT id FROM food_categories WHERE name = 'Produits laitiers'),
    (SELECT id FROM users WHERE email = 'mariem@carrefourlac2.tn'),
    'Lait Vitalait demi ecreme 1L',
    'Briques Vitalait encore conformes, reservees pour une redistribution associative dans la journee.',
    24.00,
    'brique',
    DATE_SUB(NOW(), INTERVAL 2 DAY),
    DATE_ADD(NOW(), INTERVAL 4 DAY),
    TIMESTAMP(CURDATE(), '11:00:00'),
    TIMESTAMP(CURDATE(), '15:00:00'),
    (SELECT id FROM addresses WHERE label = 'Point de collecte' AND address_line = '12 Rue du Lac Michigan'),
    'distributed',
    NULL,
    'storage/foods/lait-vitalait-1l.jpg'
),
(
    (SELECT id FROM organizations WHERE name = 'Carrefour Market Lac 2'),
    (SELECT id FROM food_categories WHERE name = 'Epicerie'),
    (SELECT id FROM users WHERE email = 'mariem@carrefourlac2.tn'),
    'Mini paquets Saida',
    'Biscuits Saida en portions individuelles, emballage intact et ideal pour des paniers solidaires.',
    40.00,
    'paquet',
    DATE_SUB(NOW(), INTERVAL 10 DAY),
    DATE_ADD(NOW(), INTERVAL 30 DAY),
    TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00'),
    TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '17:00:00'),
    (SELECT id FROM addresses WHERE label = 'Point de collecte' AND address_line = '12 Rue du Lac Michigan'),
    'available',
    NULL,
    'storage/foods/mini-paquets-saida.jpg'
);

INSERT INTO reservations (
    food_item_id,
    user_id,
    organization_id,
    reserved_quantity,
    status,
    reserved_at,
    pickup_confirmed_at,
    notes
) VALUES
(
    (SELECT id FROM food_items WHERE title = 'Mini sandwiches thon Moulin d Or'),
    (SELECT id FROM users WHERE email = 'amina@rahma-tunis.tn'),
    (SELECT id FROM organizations WHERE name = 'Association Rahma Tunis'),
    12.00,
    'approved',
    DATE_SUB(NOW(), INTERVAL 1 HOUR),
    NULL,
    'Reservation prioritaire validee pour une redistribution en fin de journee.'
),
(
    (SELECT id FROM food_items WHERE title = 'Paniers de fruits Carrefour'),
    (SELECT id FROM users WHERE email = 'leila.mansour@mail.tn'),
    NULL,
    2.00,
    'approved',
    DATE_SUB(NOW(), INTERVAL 3 HOUR),
    NULL,
    'Deux paniers reserves pour un retrait demain en debut de matinee.'
),
(
    (SELECT id FROM food_items WHERE title = 'Croissants Moulin d Or'),
    (SELECT id FROM users WHERE email = 'ahmed.benali@mail.tn'),
    NULL,
    6.00,
    'picked_up',
    DATE_SUB(NOW(), INTERVAL 1 DAY),
    DATE_SUB(NOW(), INTERVAL 6 HOUR),
    'Retrait effectue sans incident au point de collecte.'
),
(
    (SELECT id FROM food_items WHERE title = 'Lait Vitalait demi ecreme 1L'),
    (SELECT id FROM users WHERE email = 'sami@nour-sfax.tn'),
    (SELECT id FROM organizations WHERE name = 'Association Nour El Khir Sfax'),
    24.00,
    'completed',
    DATE_SUB(NOW(), INTERVAL 1 DAY),
    DATE_SUB(NOW(), INTERVAL 4 HOUR),
    'Lot affecte a une distribution associative le jour meme.'
),
(
    (SELECT id FROM food_items WHERE title = 'Yaourts Delice fraise'),
    (SELECT id FROM users WHERE email = 'fares.gdiri@mail.tn'),
    NULL,
    4.00,
    'rejected',
    DATE_SUB(NOW(), INTERVAL 5 HOUR),
    NULL,
    'Demande refusee car le volume demande depassait la quantite encore libre.'
),
(
    (SELECT id FROM food_items WHERE title = 'Mini paquets Saida'),
    (SELECT id FROM users WHERE email = 'ines.khelifi@mail.tn'),
    NULL,
    5.00,
    'pending',
    DATE_SUB(NOW(), INTERVAL 2 HOUR),
    NULL,
    'Demande en attente de validation par le commerce.'
);

INSERT INTO pickups (
    reservation_id,
    scheduled_at,
    picked_up_at,
    receiver_name,
    receiver_phone,
    pickup_code,
    status
) VALUES
(
    (SELECT id FROM reservations WHERE notes = 'Reservation prioritaire validee pour une redistribution en fin de journee.'),
    TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '19:00:00'),
    NULL,
    'Amina Ben Amor',
    '+21624111555',
    'RAHMA-2401',
    'scheduled'
),
(
    (SELECT id FROM reservations WHERE notes = 'Deux paniers reserves pour un retrait demain en debut de matinee.'),
    TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:30:00'),
    NULL,
    'Leila Mansour',
    '+21627111888',
    'LEILA-5312',
    'scheduled'
),
(
    (SELECT id FROM reservations WHERE notes = 'Retrait effectue sans incident au point de collecte.'),
    TIMESTAMP(CURDATE(), '09:15:00'),
    DATE_SUB(NOW(), INTERVAL 6 HOUR),
    'Ahmed Ben Ali',
    '+21626111777',
    'AHMED-1188',
    'done'
),
(
    (SELECT id FROM reservations WHERE notes = 'Lot affecte a une distribution associative le jour meme.'),
    TIMESTAMP(CURDATE(), '12:30:00'),
    DATE_SUB(NOW(), INTERVAL 4 HOUR),
    'Sami Jebali',
    '+21625111666',
    'NOUR-7720',
    'done'
);

INSERT INTO distributions (
    reservation_id,
    association_id,
    beneficiary_count,
    distributed_at,
    distribution_notes
) VALUES
(
    (SELECT id FROM reservations WHERE notes = 'Lot affecte a une distribution associative le jour meme.'),
    (SELECT id FROM organizations WHERE name = 'Association Nour El Khir Sfax'),
    18,
    DATE_SUB(NOW(), INTERVAL 2 HOUR),
    'Distribution realisee dans le quartier El Ain aupres de familles etudiantes et de personnes agees.'
);

INSERT INTO notifications (
    user_id,
    food_item_id,
    type,
    channel,
    subject,
    message,
    sent_at,
    read_at,
    status
) VALUES
(
    (SELECT id FROM users WHERE email = 'amina@rahma-tunis.tn'),
    (SELECT id FROM food_items WHERE title = 'Mini sandwiches thon Moulin d Or'),
    'priority_alert',
    'email',
    'Acces prioritaire pour les sandwiches Moulin d Or',
    'Un lot de sandwiches Moulin d Or est reserve en priorite pour votre association jusqu a ce soir.',
    DATE_SUB(NOW(), INTERVAL 1 HOUR),
    NULL,
    'sent'
),
(
    (SELECT id FROM users WHERE email = 'leila.mansour@mail.tn'),
    (SELECT id FROM food_items WHERE title = 'Paniers de fruits Carrefour'),
    'reservation_update',
    'web',
    'Votre reservation Carrefour est confirmee',
    'Votre panier de fruits Carrefour est confirme pour demain entre 10h et 18h.',
    DATE_SUB(NOW(), INTERVAL 2 HOUR),
    DATE_SUB(NOW(), INTERVAL 1 HOUR),
    'read'
),
(
    (SELECT id FROM users WHERE email = 'ahmed.benali@mail.tn'),
    (SELECT id FROM food_items WHERE title = 'Croissants Moulin d Or'),
    'pickup_reminder',
    'sms',
    'Retrait enregistre',
    'Le retrait des croissants Moulin d Or a ete enregistre avec succes ce matin.',
    DATE_SUB(NOW(), INTERVAL 6 HOUR),
    NULL,
    'sent'
),
(
    (SELECT id FROM users WHERE email = 'sami@nour-sfax.tn'),
    (SELECT id FROM food_items WHERE title = 'Lait Vitalait demi ecreme 1L'),
    'reservation_update',
    'email',
    'Distribution Vitalait terminee',
    'Le lot Vitalait a ete marque comme distribue pour Association Nour El Khir Sfax.',
    DATE_SUB(NOW(), INTERVAL 3 HOUR),
    NULL,
    'sent'
),
(
    (SELECT id FROM users WHERE email = 'ines.khelifi@mail.tn'),
    (SELECT id FROM food_items WHERE title = 'Mini paquets Saida'),
    'new_listing',
    'web',
    'Nouvelle annonce Saida disponible',
    'Des mini paquets Saida sont disponibles au Carrefour Market Lac 2.',
    NULL,
    NULL,
    'queued'
),
(
    (SELECT id FROM users WHERE email = 'admin@foodloop.tn'),
    NULL,
    'report_ready',
    'email',
    'Rapport hebdomadaire pret',
    'Le rapport hebdomadaire FoodLoop est pret pour consultation.',
    DATE_SUB(NOW(), INTERVAL 30 MINUTE),
    NULL,
    'sent'
),
(
    (SELECT id FROM users WHERE email = 'fares.gdiri@mail.tn'),
    (SELECT id FROM food_items WHERE title = 'Yaourts Delice fraise'),
    'reservation_update',
    'email',
    'Reservation non retenue',
    'Votre demande sur les yaourts Delice na pas pu etre acceptee.',
    DATE_SUB(NOW(), INTERVAL 4 HOUR),
    NULL,
    'sent'
);

INSERT INTO user_notification_preferences (
    user_id,
    receive_email,
    receive_sms,
    receive_web,
    radius_km,
    category_filter
) VALUES
((SELECT id FROM users WHERE email = 'yassine@delicefood.tn'), 1, 1, 1, 20, '["Produits laitiers","Boulangerie"]'),
((SELECT id FROM users WHERE email = 'mariem@carrefourlac2.tn'), 1, 0, 1, 15, '["Produits frais","Boissons","Epicerie"]'),
((SELECT id FROM users WHERE email = 'hichem@moulindor-cun.tn'), 1, 1, 1, 18, '["Boulangerie","Plats prepares"]'),
((SELECT id FROM users WHERE email = 'amina@rahma-tunis.tn'), 1, 1, 1, 25, '["Plats prepares","Produits laitiers","Epicerie"]'),
((SELECT id FROM users WHERE email = 'sami@nour-sfax.tn'), 1, 1, 1, 30, '["Produits laitiers","Boissons","Epicerie"]'),
((SELECT id FROM users WHERE email = 'ahmed.benali@mail.tn'), 1, 0, 1, 12, '["Boulangerie","Plats prepares"]'),
((SELECT id FROM users WHERE email = 'leila.mansour@mail.tn'), 1, 0, 1, 8, '["Produits frais","Boissons"]'),
((SELECT id FROM users WHERE email = 'fares.gdiri@mail.tn'), 0, 1, 1, 15, '["Produits laitiers","Epicerie"]'),
((SELECT id FROM users WHERE email = 'ines.khelifi@mail.tn'), 1, 0, 1, 10, '["Epicerie","Boissons"]');

INSERT INTO ai_predictions (
    food_item_id,
    predicted_risk_level,
    predicted_expiration_probability,
    recommended_action,
    generated_at
) VALUES
(
    (SELECT id FROM food_items WHERE title = 'Yaourts Delice fraise'),
    'low',
    18.50,
    'Maintenir le lot Delice en annonce standard et declencher une alerte douze heures avant expiration.',
    DATE_SUB(NOW(), INTERVAL 20 MINUTE)
),
(
    (SELECT id FROM food_items WHERE title = 'Mini sandwiches thon Moulin d Or'),
    'high',
    82.00,
    'Prioriser les associations proches et reduire la fenetre de collecte a ce soir.',
    DATE_SUB(NOW(), INTERVAL 15 MINUTE)
),
(
    (SELECT id FROM food_items WHERE title = 'Lait Vitalait demi ecreme 1L'),
    'medium',
    54.00,
    'Confirmer rapidement la distribution et conserver une trace du retrait associatif.',
    DATE_SUB(NOW(), INTERVAL 10 MINUTE)
),
(
    (SELECT id FROM food_items WHERE title = 'Mini paquets Saida'),
    'low',
    12.00,
    'Laisser le lot visible au grand public et pousser une notification locale.',
    DATE_SUB(NOW(), INTERVAL 5 MINUTE)
);

INSERT INTO reports (
    generated_by,
    report_type,
    period_start,
    period_end,
    total_food_saved_kg,
    total_reservations,
    total_distributions,
    generated_file
) VALUES
(
    (SELECT id FROM users WHERE email = 'admin@foodloop.tn'),
    'weekly',
    DATE_SUB(CURDATE(), INTERVAL 7 DAY),
    CURDATE(),
    187.50,
    6,
    1,
    'storage/reports/rapport-hebdomadaire-foodloop.pdf'
),
(
    (SELECT id FROM users WHERE email = 'admin@foodloop.tn'),
    'impact',
    DATE_SUB(CURDATE(), INTERVAL 30 DAY),
    CURDATE(),
    642.00,
    28,
    11,
    'storage/reports/rapport-impact-foodloop.pdf'
);

INSERT INTO automation_logs (
    action_type,
    reference_table,
    reference_id,
    status,
    message,
    executed_at
) VALUES
(
    'notification_sent',
    'notifications',
    (SELECT id FROM notifications WHERE subject = 'Acces prioritaire pour les sandwiches Moulin d Or'),
    'success',
    'Alerte prioritaire envoyee a Amina Ben Amor.',
    DATE_SUB(NOW(), INTERVAL 55 MINUTE)
),
(
    'status_update',
    'food_items',
    (SELECT id FROM food_items WHERE title = 'Lait Vitalait demi ecreme 1L'),
    'success',
    'Le lot Vitalait a ete marque comme distribue apres confirmation du retrait.',
    DATE_SUB(NOW(), INTERVAL 3 HOUR)
),
(
    'ai_recommendation_generated',
    'ai_predictions',
    (SELECT id FROM ai_predictions WHERE food_item_id = (SELECT id FROM food_items WHERE title = 'Mini sandwiches thon Moulin d Or')),
    'success',
    'Recommandation IA calculee pour accelerer la collecte du lot prioritaire.',
    DATE_SUB(NOW(), INTERVAL 14 MINUTE)
),
(
    'report_generated',
    'reports',
    (SELECT id FROM reports WHERE report_type = 'weekly'),
    'success',
    'Rapport hebdomadaire genere et notifie a ladministration.',
    DATE_SUB(NOW(), INTERVAL 25 MINUTE)
),
(
    'reservation_released',
    'reservations',
    (SELECT id FROM reservations WHERE notes = 'Demande refusee car le volume demande depassait la quantite encore libre.'),
    'success',
    'Quantite liberee apres refus de la reservation de Fares Gdiri.',
    DATE_SUB(NOW(), INTERVAL 4 HOUR)
);

INSERT INTO contact_messages (fullname, email, organization, role_label, message) VALUES
('Nadia Ben Youssef', 'nadia.benyoussef@cantine.tn', 'Lycee Ibn Rachiq', 'Responsable cantine', 'Bonjour, nous souhaitons proposer nos excedents de repas deux fois par semaine via FoodLoop.'),
('Walid Hammami', 'walid.hammami@solidaires.tn', 'Association Jeunes Solidaires', 'Coordinateur', 'Nous cherchons a rejoindre le reseau pour recuperer des produits laitiers et des paniers de fruits sur Tunis.'),
('Rim Bouazizi', 'rim.bouazizi@mail.tn', NULL, 'Citoyenne', 'Je souhaite etre informee quand des lots sont disponibles a Nabeul pour retrait en fin de journee.');
