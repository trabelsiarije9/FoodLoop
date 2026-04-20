CREATE TABLE IF NOT EXISTS roles (
    id_role BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    code VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_roles_nom (nom),
    UNIQUE KEY uq_roles_code (code)
);

CREATE TABLE IF NOT EXISTS categories (
    id_cat BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    UNIQUE KEY uq_categories_nom (nom)
);

CREATE TABLE IF NOT EXISTS utilisateurs (
    id_util BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    prenom VARCHAR(120) NOT NULL,
    nom VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    num_tel VARCHAR(30) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_utilisateurs_role FOREIGN KEY (role_id) REFERENCES roles(id_role),
    UNIQUE KEY uq_utilisateurs_email (email)
);

CREATE TABLE IF NOT EXISTS zones_geographiques (
    id_zone BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    code_postal INT UNSIGNED NOT NULL,
    ville_nom VARCHAR(120) NOT NULL,
    gouvernorat VARCHAR(120) NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_zones_user FOREIGN KEY (user_id) REFERENCES utilisateurs(id_util)
);

CREATE TABLE IF NOT EXISTS organizations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    organization_type VARCHAR(100) NOT NULL DEFAULT 'association',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS addresses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(120) NULL,
    street_line VARCHAR(255) NOT NULL,
    city VARCHAR(120) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    gouvernorat VARCHAR(120) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS association_addresses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    address_id BIGINT UNSIGNED NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_org_addresses_org FOREIGN KEY (organization_id) REFERENCES organizations(id),
    CONSTRAINT fk_org_addresses_address FOREIGN KEY (address_id) REFERENCES addresses(id)
);

CREATE TABLE IF NOT EXISTS admins_association (
    id_admin_association BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    nom_association VARCHAR(180) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_admins_association_organization FOREIGN KEY (organization_id) REFERENCES organizations(id)
);

CREATE TABLE IF NOT EXISTS proprietaires_commerce (
    id_commerce BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom_commerce VARCHAR(120) NOT NULL,
    type_commerce VARCHAR(120) NOT NULL,
    business_licence VARCHAR(120) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS super_admins (
    id_super_admin BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mot_de_passe VARCHAR(255) NOT NULL,
    admin_level VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS annonces (
    id_annonce BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(180) NOT NULL,
    description TEXT NULL,
    type_aliment VARCHAR(120) NOT NULL,
    quantite INT UNSIGNED NOT NULL,
    unite VARCHAR(50) NOT NULL,
    localisation VARCHAR(190) NOT NULL,
    statut ENUM('available', 'reserved', 'priority_access', 'picked_up', 'expired', 'cancelled') NOT NULL,
    public_visibility_at DATETIME NOT NULL,
    pickup_start DATETIME NOT NULL,
    pickup_end DATETIME NOT NULL,
    date_expiration DATETIME NOT NULL,
    categorie_id BIGINT UNSIGNED NOT NULL,
    zone_id BIGINT UNSIGNED NOT NULL,
    proprietaire_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_annonces_categorie FOREIGN KEY (categorie_id) REFERENCES categories(id_cat),
    CONSTRAINT fk_annonces_zone FOREIGN KEY (zone_id) REFERENCES zones_geographiques(id_zone),
    CONSTRAINT fk_annonces_proprietaire FOREIGN KEY (proprietaire_id) REFERENCES proprietaires_commerce(id_commerce)
);

CREATE TABLE IF NOT EXISTS utilisateur_categories (
    utilisateur_id BIGINT UNSIGNED NOT NULL,
    categorie_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (utilisateur_id, categorie_id),
    CONSTRAINT fk_utilisateur_categories_user FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id_util) ON DELETE CASCADE,
    CONSTRAINT fk_utilisateur_categories_categorie FOREIGN KEY (categorie_id) REFERENCES categories(id_cat) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS utilisateur_zones (
    utilisateur_id BIGINT UNSIGNED NOT NULL,
    zone_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (utilisateur_id, zone_id),
    CONSTRAINT fk_utilisateur_zones_user FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id_util) ON DELETE CASCADE,
    CONSTRAINT fk_utilisateur_zones_zone FOREIGN KEY (zone_id) REFERENCES zones_geographiques(id_zone) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS consultations_annonces (
    id_consultation BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id BIGINT UNSIGNED NOT NULL,
    annonce_id BIGINT UNSIGNED NOT NULL,
    date_consultation DATETIME NOT NULL,
    CONSTRAINT fk_consultations_user FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id_util) ON DELETE CASCADE,
    CONSTRAINT fk_consultations_annonce FOREIGN KEY (annonce_id) REFERENCES annonces(id_annonce) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS suggestions_ia (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    food_item_id BIGINT UNSIGNED NOT NULL,
    predicted_risk_level ENUM('low', 'medium', 'high') NOT NULL,
    predicted_expiration_probability DECIMAL(5, 2) NOT NULL,
    recommended_action TEXT NOT NULL,
    generated_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ai_predictions_food FOREIGN KEY (food_item_id) REFERENCES annonces(id_annonce)
);

CREATE TABLE IF NOT EXISTS reservations (
    id_reservation BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    annonce_id BIGINT UNSIGNED NOT NULL,
    utilisateur_id BIGINT UNSIGNED NULL,
    admin_association_id BIGINT UNSIGNED NULL,
    quantite_reservee INT UNSIGNED NOT NULL,
    statut ENUM('pending', 'approved', 'rejected', 'picked_up', 'completed', 'cancelled') NOT NULL,
    date_reservation DATETIME NOT NULL,
    date_pickup DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reservations_annonce FOREIGN KEY (annonce_id) REFERENCES annonces(id_annonce) ON DELETE CASCADE,
    CONSTRAINT fk_reservations_user FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id_util) ON DELETE CASCADE,
    CONSTRAINT fk_reservations_association FOREIGN KEY (admin_association_id) REFERENCES admins_association(id_admin_association) ON DELETE CASCADE,
    CONSTRAINT chk_reservations_demandeur CHECK (
        ((utilisateur_id IS NOT NULL) AND (admin_association_id IS NULL))
        OR
        ((utilisateur_id IS NULL) AND (admin_association_id IS NOT NULL))
    )
);

CREATE TABLE IF NOT EXISTS paiements (
    id_paiement BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reservation_id BIGINT UNSIGNED NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    methode_paiement ENUM('par carte', 'espece') NOT NULL,
    date_paiement DATETIME NOT NULL,
    statut ENUM('confirme', 'annule') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_paiements_reservation FOREIGN KEY (reservation_id) REFERENCES reservations(id_reservation) ON DELETE CASCADE,
    UNIQUE KEY uq_paiements_reservation (reservation_id)
);

CREATE TABLE IF NOT EXISTS recus (
    id_recu BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    paiement_id BIGINT UNSIGNED NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    date_emission DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_recus_paiement FOREIGN KEY (paiement_id) REFERENCES paiements(id_paiement) ON DELETE CASCADE,
    UNIQUE KEY uq_recus_paiement (paiement_id)
);

CREATE TABLE IF NOT EXISTS pickups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reservation_id BIGINT UNSIGNED NOT NULL,
    scheduled_at DATETIME NOT NULL,
    picked_up_at DATETIME NULL,
    receiver_name VARCHAR(150) NULL,
    receiver_phone VARCHAR(30) NULL,
    pickup_code VARCHAR(50) NULL,
    status ENUM('scheduled', 'in_progress', 'done', 'missed') NOT NULL DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pickups_reservation FOREIGN KEY (reservation_id) REFERENCES reservations(id_reservation)
);

CREATE TABLE IF NOT EXISTS distributions (
    id_distribution BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reservation_id BIGINT UNSIGNED NOT NULL,
    admin_association_id BIGINT UNSIGNED NOT NULL,
    quantite_distrib INT UNSIGNED NOT NULL,
    date_distrib DATETIME NOT NULL,
    statut ENUM('planned', 'in_progress', 'completed', 'cancelled') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_distributions_reservation FOREIGN KEY (reservation_id) REFERENCES reservations(id_reservation) ON DELETE CASCADE,
    CONSTRAINT fk_distributions_association FOREIGN KEY (admin_association_id) REFERENCES admins_association(id_admin_association) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reservation_distributions (
    reservation_id BIGINT UNSIGNED NOT NULL,
    distribution_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (reservation_id, distribution_id),
    CONSTRAINT fk_reservation_distributions_reservation FOREIGN KEY (reservation_id) REFERENCES reservations(id_reservation) ON DELETE CASCADE,
    CONSTRAINT fk_reservation_distributions_distribution FOREIGN KEY (distribution_id) REFERENCES distributions(id_distribution) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    generated_by BIGINT UNSIGNED NOT NULL,
    report_type ENUM('daily', 'weekly', 'monthly', 'impact', 'custom') NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    total_food_saved_kg DECIMAL(12, 2) NOT NULL DEFAULT 0,
    total_reservations INT UNSIGNED NOT NULL DEFAULT 0,
    total_distributions INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reports_generated_by FOREIGN KEY (generated_by) REFERENCES super_admins(id_super_admin)
);

CREATE TABLE IF NOT EXISTS report_consultations_commerce (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id BIGINT UNSIGNED NOT NULL,
    proprietaire_id BIGINT UNSIGNED NOT NULL,
    consulted_at DATETIME NOT NULL,
    CONSTRAINT fk_report_consultations_commerce_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    CONSTRAINT fk_report_consultations_commerce_owner FOREIGN KEY (proprietaire_id) REFERENCES proprietaires_commerce(id_commerce) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS report_consultations_super_admin (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id BIGINT UNSIGNED NOT NULL,
    super_admin_id BIGINT UNSIGNED NOT NULL,
    consulted_at DATETIME NOT NULL,
    CONSTRAINT fk_report_consultations_super_admin_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    CONSTRAINT fk_report_consultations_super_admin_admin FOREIGN KEY (super_admin_id) REFERENCES super_admins(id_super_admin) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
    id_notification BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id BIGINT UNSIGNED NULL,
    admin_association_id BIGINT UNSIGNED NULL,
    proprietaire_id BIGINT UNSIGNED NULL,
    annonce_id BIGINT UNSIGNED NULL,
    reservation_id BIGINT UNSIGNED NULL,
    report_id BIGINT UNSIGNED NULL,
    message_notification TEXT NOT NULL,
    type ENUM('pickup_reminder', 'new_listing', 'expiration_alert', 'reservation_update', 'report_ready') NOT NULL,
    channel ENUM('sms', 'web', 'email') NOT NULL,
    sent_at DATETIME NULL,
    read_at DATETIME NULL,
    status ENUM('queued', 'read', 'failed') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id_util) ON DELETE CASCADE,
    CONSTRAINT fk_notifications_association FOREIGN KEY (admin_association_id) REFERENCES admins_association(id_admin_association) ON DELETE CASCADE,
    CONSTRAINT fk_notifications_commerce FOREIGN KEY (proprietaire_id) REFERENCES proprietaires_commerce(id_commerce) ON DELETE CASCADE,
    CONSTRAINT fk_notifications_annonce FOREIGN KEY (annonce_id) REFERENCES annonces(id_annonce) ON DELETE SET NULL,
    CONSTRAINT fk_notifications_reservation FOREIGN KEY (reservation_id) REFERENCES reservations(id_reservation) ON DELETE SET NULL,
    CONSTRAINT fk_notifications_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE SET NULL,
    CONSTRAINT chk_notifications_recipient CHECK (
        ((utilisateur_id IS NOT NULL) AND (admin_association_id IS NULL) AND (proprietaire_id IS NULL))
        OR
        ((utilisateur_id IS NULL) AND (admin_association_id IS NOT NULL) AND (proprietaire_id IS NULL))
        OR
        ((utilisateur_id IS NULL) AND (admin_association_id IS NULL) AND (proprietaire_id IS NOT NULL))
    )
);

CREATE TABLE IF NOT EXISTS contact_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(180) NOT NULL,
    email VARCHAR(190) NOT NULL,
    organization VARCHAR(180) NULL,
    role_label VARCHAR(100) NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS automation_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_type ENUM('notification_sent', 'status_update', 'report_generated', 'reservation_released', 'ai_recommendation_generated') NOT NULL,
    reference_table VARCHAR(100) NOT NULL,
    reference_id BIGINT UNSIGNED NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    message TEXT NULL,
    executed_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_annonces_visibility ON annonces(public_visibility_at, statut);
CREATE INDEX idx_reservations_status ON reservations(statut);
CREATE INDEX idx_notifications_status ON notifications(status, type);
CREATE INDEX idx_pickups_status ON pickups(status);
CREATE INDEX idx_reports_type ON reports(report_type);
