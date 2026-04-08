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
('business_owner', 'Food Business Owner', 'Restaurants, bakeries, supermarkets and other food businesses.'),
('association_admin', 'Association Administrator', 'Charitable organizations with priority access to surplus food.'),
('regular_user', 'Regular User', 'End users who can browse and reserve available food.'),
('system_admin', 'System Administrator', 'Platform supervision, moderation and reporting.');

INSERT INTO food_categories (name, description) VALUES
('Bakery', 'Bread, pastries and bakery products'),
('Fresh Produce', 'Fruits, vegetables and fresh products'),
('Prepared Meals', 'Cooked dishes ready for pickup'),
('Dairy', 'Milk, cheese and dairy products'),
('Beverages', 'Juices and drinks');

INSERT INTO users (role_id, first_name, last_name, email, password_hash, phone, is_active)
SELECT roles.id, 'System', 'Admin', 'admin@foodloop.tn', '$2y$10$j2LZebFXS4PDTOH4TLWq2uR2E3DaNDhvndsIcfbyITABzQWOZXAfa', '+21600000000', 1
FROM roles
WHERE roles.code = 'system_admin';

INSERT INTO system_admins (user_id, admin_level)
SELECT users.id, 'super'
FROM users
WHERE users.email = 'admin@foodloop.tn';
