PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS roles (
    id_role INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(100) NOT NULL UNIQUE,
    code VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id_cat INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS utilisateurs (
    id_util INTEGER PRIMARY KEY AUTOINCREMENT,
    role_id INTEGER NOT NULL,
    prenom VARCHAR(120) NOT NULL,
    nom VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    num_tel VARCHAR(30) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id_role)
);

CREATE TABLE IF NOT EXISTS zones_geographiques (
    id_zone INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(100) NOT NULL,
    code_postal INTEGER NOT NULL,
    ville_nom VARCHAR(120) NOT NULL,
    gouvernorat VARCHAR(120) NOT NULL,
    user_id INTEGER NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id_util)
);

CREATE TABLE IF NOT EXISTS organizations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(180) NOT NULL,
    organization_type VARCHAR(100) NOT NULL DEFAULT 'association',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS addresses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    label VARCHAR(120) NULL,
    street_line VARCHAR(255) NOT NULL,
    city VARCHAR(120) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    gouvernorat VARCHAR(120) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS association_addresses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    organization_id INTEGER NOT NULL,
    address_id INTEGER NOT NULL,
    is_primary INTEGER NOT NULL DEFAULT 1 CHECK (is_primary IN (0, 1)),
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (address_id) REFERENCES addresses(id)
);

CREATE TABLE IF NOT EXISTS admins_association (
    id_admin_association INTEGER PRIMARY KEY AUTOINCREMENT,
    organization_id INTEGER NOT NULL,
    nom_association VARCHAR(180) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
);

CREATE TABLE IF NOT EXISTS proprietaires_commerce (
    id_commerce INTEGER PRIMARY KEY AUTOINCREMENT,
    nom_commerce VARCHAR(120) NOT NULL,
    type_commerce VARCHAR(120) NOT NULL,
    business_licence VARCHAR(120) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS super_admins (
    id_super_admin INTEGER PRIMARY KEY AUTOINCREMENT,
    mot_de_passe VARCHAR(255) NOT NULL,
    admin_level VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS annonces (
    id_annonce INTEGER PRIMARY KEY AUTOINCREMENT,
    titre VARCHAR(180) NOT NULL,
    description TEXT NULL,
    type_aliment VARCHAR(120) NOT NULL,
    quantite INTEGER NOT NULL CHECK (quantite >= 0),
    unite VARCHAR(50) NOT NULL,
    localisation VARCHAR(190) NOT NULL,
    statut TEXT NOT NULL CHECK (statut IN ('available', 'reserved', 'priority_access', 'picked_up', 'expired', 'cancelled')),
    public_visibility_at DATETIME NOT NULL,
    pickup_start DATETIME NOT NULL,
    pickup_end DATETIME NOT NULL,
    date_expiration DATETIME NOT NULL,
    categorie_id INTEGER NOT NULL,
    zone_id INTEGER NOT NULL,
    proprietaire_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id_cat),
    FOREIGN KEY (zone_id) REFERENCES zones_geographiques(id_zone),
    FOREIGN KEY (proprietaire_id) REFERENCES proprietaires_commerce(id_commerce)
);

CREATE TABLE IF NOT EXISTS utilisateur_categories (
    utilisateur_id INTEGER NOT NULL,
    categorie_id INTEGER NOT NULL,
    PRIMARY KEY (utilisateur_id, categorie_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id_util) ON DELETE CASCADE,
    FOREIGN KEY (categorie_id) REFERENCES categories(id_cat) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS utilisateur_zones (
    utilisateur_id INTEGER NOT NULL,
    zone_id INTEGER NOT NULL,
    PRIMARY KEY (utilisateur_id, zone_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id_util) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones_geographiques(id_zone) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS consultations_annonces (
    id_consultation INTEGER PRIMARY KEY AUTOINCREMENT,
    utilisateur_id INTEGER NOT NULL,
    annonce_id INTEGER NOT NULL,
    date_consultation DATETIME NOT NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id_util) ON DELETE CASCADE,
    FOREIGN KEY (annonce_id) REFERENCES annonces(id_annonce) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS suggestions_ia (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    food_item_id INTEGER NOT NULL,
    predicted_risk_level TEXT NOT NULL CHECK (predicted_risk_level IN ('low', 'medium', 'high')),
    predicted_expiration_probability DECIMAL(5, 2) NOT NULL,
    recommended_action TEXT NOT NULL,
    generated_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (food_item_id) REFERENCES annonces(id_annonce)
);

CREATE TABLE IF NOT EXISTS reservations (
    id_reservation INTEGER PRIMARY KEY AUTOINCREMENT,
    annonce_id INTEGER NOT NULL,
    utilisateur_id INTEGER NULL,
    admin_association_id INTEGER NULL,
    quantite_reservee INTEGER NOT NULL CHECK (quantite_reservee > 0),
    statut TEXT NOT NULL CHECK (statut IN ('pending', 'approved', 'rejected', 'picked_up', 'completed', 'cancelled')),
    date_reservation DATETIME NOT NULL,
    date_pickup DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (annonce_id) REFERENCES annonces(id_annonce) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id_util) ON DELETE CASCADE,
    FOREIGN KEY (admin_association_id) REFERENCES admins_association(id_admin_association) ON DELETE CASCADE,
    CHECK (
        ((utilisateur_id IS NOT NULL) AND (admin_association_id IS NULL))
        OR
        ((utilisateur_id IS NULL) AND (admin_association_id IS NOT NULL))
    )
);

CREATE TABLE IF NOT EXISTS paiements (
    id_paiement INTEGER PRIMARY KEY AUTOINCREMENT,
    reservation_id INTEGER NOT NULL UNIQUE,
    montant DECIMAL(10, 2) NOT NULL,
    methode_paiement TEXT NOT NULL CHECK (methode_paiement IN ('par carte', 'espece')),
    date_paiement DATETIME NOT NULL,
    statut TEXT NOT NULL CHECK (statut IN ('confirme', 'annule')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id_reservation) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS recus (
    id_recu INTEGER PRIMARY KEY AUTOINCREMENT,
    paiement_id INTEGER NOT NULL UNIQUE,
    montant DECIMAL(10, 2) NOT NULL,
    date_emission DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paiement_id) REFERENCES paiements(id_paiement) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pickups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reservation_id INTEGER NOT NULL,
    scheduled_at DATETIME NOT NULL,
    picked_up_at DATETIME NULL,
    receiver_name VARCHAR(150) NULL,
    receiver_phone VARCHAR(30) NULL,
    pickup_code VARCHAR(50) NULL,
    status TEXT NOT NULL DEFAULT 'scheduled' CHECK (status IN ('scheduled', 'in_progress', 'done', 'missed')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id_reservation)
);

CREATE TABLE IF NOT EXISTS distributions (
    id_distribution INTEGER PRIMARY KEY AUTOINCREMENT,
    reservation_id INTEGER NOT NULL,
    admin_association_id INTEGER NOT NULL,
    quantite_distrib INTEGER NOT NULL CHECK (quantite_distrib >= 0),
    date_distrib DATETIME NOT NULL,
    statut VARCHAR(80) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id_reservation) ON DELETE CASCADE,
    FOREIGN KEY (admin_association_id) REFERENCES admins_association(id_admin_association) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    generated_by INTEGER NOT NULL,
    report_type TEXT NOT NULL CHECK (report_type IN ('daily', 'weekly', 'monthly', 'impact', 'custom')),
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    total_food_saved_kg DECIMAL(12, 2) NOT NULL DEFAULT 0,
    total_reservations INTEGER NOT NULL DEFAULT 0,
    total_distributions INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES super_admins(id_super_admin)
);

CREATE TABLE IF NOT EXISTS notifications (
    id_notification INTEGER PRIMARY KEY AUTOINCREMENT,
    utilisateur_id INTEGER NULL,
    admin_association_id INTEGER NULL,
    proprietaire_id INTEGER NULL,
    annonce_id INTEGER NULL,
    reservation_id INTEGER NULL,
    report_id INTEGER NULL,
    message_notification TEXT NOT NULL,
    type TEXT NOT NULL CHECK (type IN ('pickup_reminder', 'new_listing', 'expiration_alert', 'reservation_update', 'report_ready')),
    channel TEXT NOT NULL CHECK (channel IN ('sms', 'web', 'email')),
    sent_at DATETIME NULL,
    read_at DATETIME NULL,
    status TEXT NOT NULL CHECK (status IN ('queued', 'read', 'failed')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id_util) ON DELETE CASCADE,
    FOREIGN KEY (admin_association_id) REFERENCES admins_association(id_admin_association) ON DELETE CASCADE,
    FOREIGN KEY (proprietaire_id) REFERENCES proprietaires_commerce(id_commerce) ON DELETE CASCADE,
    FOREIGN KEY (annonce_id) REFERENCES annonces(id_annonce) ON DELETE SET NULL,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id_reservation) ON DELETE SET NULL,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE SET NULL,
    CHECK (
        ((utilisateur_id IS NOT NULL) AND (admin_association_id IS NULL) AND (proprietaire_id IS NULL))
        OR
        ((utilisateur_id IS NULL) AND (admin_association_id IS NOT NULL) AND (proprietaire_id IS NULL))
        OR
        ((utilisateur_id IS NULL) AND (admin_association_id IS NULL) AND (proprietaire_id IS NOT NULL))
    )
);

CREATE TABLE IF NOT EXISTS contact_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fullname VARCHAR(180) NOT NULL,
    email VARCHAR(190) NOT NULL,
    organization VARCHAR(180) NULL,
    role_label VARCHAR(100) NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS automation_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    action_type TEXT NOT NULL CHECK (action_type IN ('notification_sent', 'status_update', 'report_generated', 'reservation_released', 'ai_recommendation_generated')),
    reference_table VARCHAR(100) NOT NULL,
    reference_id INTEGER NOT NULL,
    status TEXT NOT NULL CHECK (status IN ('success', 'failed')),
    message TEXT NULL,
    executed_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_annonces_visibility ON annonces(public_visibility_at, statut);
CREATE INDEX IF NOT EXISTS idx_reservations_status ON reservations(statut);
CREATE INDEX IF NOT EXISTS idx_notifications_status ON notifications(status, type);
CREATE INDEX IF NOT EXISTS idx_pickups_status ON pickups(status);
CREATE INDEX IF NOT EXISTS idx_reports_type ON reports(report_type);
