-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS organ_bank_system;

-- Use the OrganBank database
USE organ_bank_system;

-- Drop tables if they exist (for development/resetting purposes)
DROP TABLE IF EXISTS api_keys;
DROP TABLE IF EXISTS audit_log;
DROP TABLE IF EXISTS transplants;
DROP TABLE IF EXISTS organ_status_log;
DROP TABLE IF EXISTS organ_medical_markers;
DROP TABLE IF EXISTS procured_organs;
DROP TABLE IF EXISTS donation_events;
DROP TABLE IF EXISTS user_platform_intents;
DROP TABLE IF EXISTS medical_marker_values;
DROP TABLE IF EXISTS medical_marker_types;
DROP TABLE IF EXISTS organ_types;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS organizations;
DROP TABLE IF EXISTS users;

-- Table: users
CREATE TABLE users (
    id VARCHAR(36) PRIMARY KEY,
    email VARCHAR(191) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20),
    street_address VARCHAR(255),
    city VARCHAR(255),
    state_province VARCHAR(255),
    country VARCHAR(255),
    postal_code VARCHAR(20),
    date_of_birth DATE,
    gender VARCHAR(50),
    preferred_language VARCHAR(50),
    email_verified BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at DATETIME,
    profile_picture_url TEXT
);


-- Table: organizations
CREATE TABLE organizations (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(191) UNIQUE NOT NULL,
    type VARCHAR(255),
    street_address VARCHAR(255),
    city VARCHAR(255),
    state_province VARCHAR(255),
    country VARCHAR(255),
    postal_code VARCHAR(20),
    phone_number VARCHAR(20),
    email VARCHAR(255),
    website_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: user_roles
CREATE TABLE user_roles (
    user_id VARCHAR(36) NOT NULL,
    organization_id VARCHAR(36) NOT NULL,
    role_name VARCHAR(100) NOT NULL,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, organization_id, role_name),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
);

-- Table: organ_types
CREATE TABLE organ_types (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT
);

-- Table: medical_marker_types
CREATE TABLE medical_marker_types (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    data_type VARCHAR(50),
    description TEXT
);

-- Table: medical_marker_values
CREATE TABLE medical_marker_values (
    id VARCHAR(36) PRIMARY KEY,
    marker_type_id VARCHAR(36) NOT NULL,
    value VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (marker_type_id) REFERENCES medical_marker_types(id) ON DELETE CASCADE
);

-- Table: user_platform_intents
CREATE TABLE user_platform_intents (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    organ_type_id VARCHAR(36) NOT NULL,
    declared_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organ_type_id) REFERENCES organ_types(id) ON DELETE CASCADE
);

-- Table: donation_events
CREATE TABLE donation_events (
    id VARCHAR(36) PRIMARY KEY,
    source_organization_id VARCHAR(36) NOT NULL,
    donation_type VARCHAR(255),
    donor_external_id VARCHAR(255),
    event_start_timestamp DATETIME,
    event_end_timestamp DATETIME,
    status VARCHAR(255),
    cause_of_death TEXT,
    clinical_summary TEXT,
    notes TEXT,
    created_by_user_id VARCHAR(36) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (source_organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: procured_organs
CREATE TABLE procured_organs (
    id VARCHAR(36) PRIMARY KEY,
    donation_event_id VARCHAR(36) NOT NULL,
    organ_type_id VARCHAR(36) NOT NULL,
    current_organization_id VARCHAR(36) NOT NULL,
    organ_external_id VARCHAR(255),
    procurement_timestamp DATETIME,
    preservation_timestamp DATETIME,
    estimated_warm_ischemia_time_minutes INT,
    estimated_cold_ischemia_time_minutes INT,
    expiry_timestamp DATETIME,
    status VARCHAR(255),
    description TEXT,
    blood_type VARCHAR(10),
    clinical_notes TEXT,
    packaging_details TEXT,
    created_by_user_id VARCHAR(36) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (donation_event_id) REFERENCES donation_events(id) ON DELETE CASCADE,
    FOREIGN KEY (organ_type_id) REFERENCES organ_types(id) ON DELETE CASCADE,
    FOREIGN KEY (current_organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: organ_medical_markers
CREATE TABLE organ_medical_markers (
    procured_organ_id VARCHAR(36) NOT NULL,
    medical_marker_value_id VARCHAR(36) NOT NULL,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    recorded_by_user_id VARCHAR(36),
    PRIMARY KEY (procured_organ_id, medical_marker_value_id),
    FOREIGN KEY (procured_organ_id) REFERENCES procured_organs(id) ON DELETE CASCADE,
    FOREIGN KEY (medical_marker_value_id) REFERENCES medical_marker_values(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: organ_status_log
CREATE TABLE organ_status_log (
    id VARCHAR(36) PRIMARY KEY,
    procured_organ_id VARCHAR(36) NOT NULL,
    old_status VARCHAR(255),
    new_status VARCHAR(255),
    status_notes TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    changed_by_user_id VARCHAR(36) NOT NULL,
    FOREIGN KEY (procured_organ_id) REFERENCES procured_organs(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: transplants
CREATE TABLE transplants (
    id VARCHAR(36) PRIMARY KEY,
    procured_organ_id VARCHAR(36) NOT NULL,
    transplant_center_id VARCHAR(36) NOT NULL,
    recipient_external_id VARCHAR(255),
    transplant_timestamp DATETIME,
    outcome VARCHAR(255),
    notes TEXT,
    recorded_by_user_id VARCHAR(36) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (procured_organ_id) REFERENCES procured_organs(id) ON DELETE CASCADE,
    FOREIGN KEY (transplant_center_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: audit_log
CREATE TABLE audit_log (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36),
    action VARCHAR(255),
    entity_type VARCHAR(255),
    entity_id VARCHAR(36),
    changes JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table: api_keys
CREATE TABLE api_keys (
    id VARCHAR(36) PRIMARY KEY,
    key_hash VARCHAR(200) UNIQUE NOT NULL,
    organization_id VARCHAR(36),
    role_name VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    last_used_at DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
);