-- =====================================================================
-- Community Complaint and Incident Reporting System (Barangay)
-- Database schema in Third Normal Form (3NF)
-- Target: MySQL 8 / MariaDB 10.4+ (XAMPP compatible)
-- Roles: admin (barangay staff / investigators) and complainant
-- =====================================================================

DROP DATABASE IF EXISTS barangay_complaint_db;
CREATE DATABASE barangay_complaint_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE barangay_complaint_db;

-- =====================================================================
-- 1. LOOKUP / REFERENCE TABLES
-- =====================================================================

CREATE TABLE roles (
    role_id     TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_name   VARCHAR(30)  NOT NULL UNIQUE,      -- admin, complainant
    description VARCHAR(150) NULL
) ENGINE=InnoDB;

CREATE TABLE departments (                          -- barangay offices/committees
    department_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL UNIQUE,
    description     VARCHAR(255) NULL,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;

CREATE TABLE areas (                                -- puroks / zones / sitios
    area_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    area_name   VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    is_active   BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;

CREATE TABLE report_types (                         -- complaint / concern / incident
    report_type_id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type_name      VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE complaint_categories (                 -- incident classification
    category_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description   VARCHAR(255) NULL,
    is_active     BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;

CREATE TABLE priority_levels (
    priority_id   TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    priority_name VARCHAR(30) NOT NULL UNIQUE,
    sort_order    TINYINT UNSIGNED NOT NULL
) ENGINE=InnoDB;

CREATE TABLE complaint_statuses (
    status_id   TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL UNIQUE,
    sort_order  TINYINT UNSIGNED NOT NULL,          -- used by the timeline/tracker
    badge_color VARCHAR(20) NULL                    -- optional, for Status Badge
) ENGINE=InnoDB;

-- =====================================================================
-- 2. USERS & AUTHENTICATION
-- =====================================================================

CREATE TABLE users (
    user_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id        TINYINT UNSIGNED NOT NULL,
    department_id  INT UNSIGNED NULL,               -- only for admin/staff
    area_id        INT UNSIGNED NULL,               -- resident's purok (complainants)
    first_name     VARCHAR(60)  NOT NULL,
    middle_name    VARCHAR(60)  NULL,
    last_name      VARCHAR(60)  NOT NULL,
    email          VARCHAR(120) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20)  NULL,
    address        VARCHAR(255) NULL,
    is_active      BOOLEAN NOT NULL DEFAULT TRUE,
    remember_token VARCHAR(100) NULL,               -- used by Laravel "Remember me"
    verification_doc_path VARCHAR(255) NULL,        -- proof of residency / ID file path
    verification_doc_type VARCHAR(60) NULL,         -- voters_cert, valid_id, certificate_of_residency
    verification_doc_name VARCHAR(255) NULL,        -- original file name
    verification_status   VARCHAR(20) NOT NULL DEFAULT 'approved', -- pending, approved, rejected
    rejection_reason      VARCHAR(255) NULL,
    verified_at           DATETIME NULL,
    verified_by           INT UNSIGNED NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role       FOREIGN KEY (role_id)       REFERENCES roles(role_id),
    CONSTRAINT fk_users_department FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE SET NULL,
    CONSTRAINT fk_users_area       FOREIGN KEY (area_id)       REFERENCES areas(area_id) ON DELETE SET NULL,
    CONSTRAINT fk_users_verifier   FOREIGN KEY (verified_by)   REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE password_resets (                      -- Forgot / Reset Password
    reset_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at    DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reset_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_reset_token (token_hash)
) ENGINE=InnoDB;

CREATE TABLE password_reset_tokens (                -- used by Laravel's built-in password reset
    email      VARCHAR(255) NOT NULL PRIMARY KEY,
    token      VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB;

-- Staff (admin users) responsible for an area (many-to-many)
CREATE TABLE area_staff (
    area_id     INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (area_id, user_id),
    CONSTRAINT fk_areastaff_area FOREIGN KEY (area_id) REFERENCES areas(area_id) ON DELETE CASCADE,
    CONSTRAINT fk_areastaff_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- 3. COMPLAINTS (SUBMISSION + CLASSIFICATION)
-- =====================================================================

CREATE TABLE complaints (
    complaint_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference_no     VARCHAR(30) NOT NULL UNIQUE,   -- e.g. BRGY-2026-0001 (generated by app)
    complainant_id   INT UNSIGNED NOT NULL,
    report_type_id   TINYINT UNSIGNED NOT NULL,
    category_id      INT UNSIGNED NOT NULL,
    priority_id      TINYINT UNSIGNED NOT NULL,
    area_id          INT UNSIGNED NOT NULL,
    status_id        TINYINT UNSIGNED NOT NULL,     -- current status
    title            VARCHAR(150) NOT NULL,
    description      TEXT NOT NULL,
    location_details VARCHAR(255) NULL,             -- landmark / street
    incident_date    DATETIME NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_comp_complainant FOREIGN KEY (complainant_id) REFERENCES users(user_id),
    CONSTRAINT fk_comp_type        FOREIGN KEY (report_type_id) REFERENCES report_types(report_type_id),
    CONSTRAINT fk_comp_category    FOREIGN KEY (category_id)    REFERENCES complaint_categories(category_id),
    CONSTRAINT fk_comp_priority    FOREIGN KEY (priority_id)    REFERENCES priority_levels(priority_id),
    CONSTRAINT fk_comp_area        FOREIGN KEY (area_id)        REFERENCES areas(area_id),
    CONSTRAINT fk_comp_status      FOREIGN KEY (status_id)      REFERENCES complaint_statuses(status_id),
    INDEX idx_comp_status (status_id),
    INDEX idx_comp_area (area_id),
    INDEX idx_comp_category (category_id),
    INDEX idx_comp_created (created_at)
) ENGINE=InnoDB;

-- =====================================================================
-- 4. ASSIGNMENT, STATUS TRACKING
-- =====================================================================

CREATE TABLE complaint_assignments (
    assignment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    complaint_id  INT UNSIGNED NOT NULL,
    assigned_to   INT UNSIGNED NOT NULL,            -- admin/staff user
    assigned_by   INT UNSIGNED NOT NULL,            -- admin who assigned
    remarks       VARCHAR(255) NULL,
    is_current    BOOLEAN NOT NULL DEFAULT TRUE,    -- FALSE when reassigned
    assigned_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_assign_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    CONSTRAINT fk_assign_to        FOREIGN KEY (assigned_to)  REFERENCES users(user_id),
    CONSTRAINT fk_assign_by        FOREIGN KEY (assigned_by)  REFERENCES users(user_id),
    INDEX idx_assign_to (assigned_to, is_current)
) ENGINE=InnoDB;

CREATE TABLE complaint_status_history (             -- powers Timeline / Status Tracker
    history_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    complaint_id  INT UNSIGNED NOT NULL,
    old_status_id TINYINT UNSIGNED NULL,
    new_status_id TINYINT UNSIGNED NOT NULL,
    changed_by    INT UNSIGNED NOT NULL,
    remarks       VARCHAR(255) NULL,
    changed_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_hist_complaint FOREIGN KEY (complaint_id)  REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    CONSTRAINT fk_hist_old       FOREIGN KEY (old_status_id) REFERENCES complaint_statuses(status_id),
    CONSTRAINT fk_hist_new       FOREIGN KEY (new_status_id) REFERENCES complaint_statuses(status_id),
    CONSTRAINT fk_hist_user      FOREIGN KEY (changed_by)    REFERENCES users(user_id),
    INDEX idx_hist_complaint (complaint_id, changed_at)
) ENGINE=InnoDB;

-- =====================================================================
-- 5. INVESTIGATION & RESOLUTION
-- =====================================================================

CREATE TABLE investigations (
    investigation_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    complaint_id       INT UNSIGNED NOT NULL,
    investigator_id    INT UNSIGNED NOT NULL,       -- admin/staff user
    investigation_date DATETIME NOT NULL,
    findings           TEXT NOT NULL,
    actions_taken      TEXT NULL,
    created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_inv_complaint    FOREIGN KEY (complaint_id)    REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    CONSTRAINT fk_inv_investigator FOREIGN KEY (investigator_id) REFERENCES users(user_id),
    INDEX idx_inv_complaint (complaint_id)
) ENGINE=InnoDB;

CREATE TABLE resolutions (                          -- one resolution per complaint
    resolution_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    complaint_id       INT UNSIGNED NOT NULL UNIQUE,
    resolved_by        INT UNSIGNED NOT NULL,
    resolution_details TEXT NOT NULL,
    resolved_at        DATETIME NOT NULL,
    created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_res_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    CONSTRAINT fk_res_user      FOREIGN KEY (resolved_by)  REFERENCES users(user_id)
) ENGINE=InnoDB;

-- =====================================================================
-- 6. ATTACHMENTS (File Upload Widget: complainant evidence + investigation evidence)
-- =====================================================================

CREATE TABLE attachments (
    attachment_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    complaint_id     INT UNSIGNED NOT NULL,
    investigation_id INT UNSIGNED NULL,             -- NULL = attached by complainant on filing
    uploaded_by      INT UNSIGNED NOT NULL,
    file_name        VARCHAR(255) NOT NULL,
    file_path        VARCHAR(255) NOT NULL,
    file_type        VARCHAR(100) NULL,
    file_size        INT UNSIGNED NULL,             -- bytes
    uploaded_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_att_complaint     FOREIGN KEY (complaint_id)     REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    CONSTRAINT fk_att_investigation FOREIGN KEY (investigation_id) REFERENCES investigations(investigation_id) ON DELETE SET NULL,
    CONSTRAINT fk_att_user          FOREIGN KEY (uploaded_by)      REFERENCES users(user_id)
) ENGINE=InnoDB;

-- =====================================================================
-- 7. NOTIFICATIONS & AUDIT LOGS
-- =====================================================================

CREATE TABLE notifications (                        -- navbar bell
    notification_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    complaint_id    INT UNSIGNED NULL,
    message         VARCHAR(255) NOT NULL,
    is_read         BOOLEAN NOT NULL DEFAULT FALSE,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_user      FOREIGN KEY (user_id)      REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_notif_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    INDEX idx_notif_user (user_id, is_read)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    log_id      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NULL,                  -- NULL if user deleted / system action
    action      VARCHAR(50)  NOT NULL,              -- LOGIN, CREATE, UPDATE, DELETE, ASSIGN...
    table_name  VARCHAR(60)  NULL,
    record_id   INT UNSIGNED NULL,
    description VARCHAR(255) NULL,
    ip_address  VARCHAR(45)  NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_audit_created (created_at),
    INDEX idx_audit_user (user_id)
) ENGINE=InnoDB;

-- =====================================================================
-- 8. SEED DATA
-- =====================================================================

INSERT INTO roles (role_name, description) VALUES
('admin',       'Barangay staff: review, assign, investigate, resolve, manage system'),
('complainant', 'Community member who files complaints and incident reports');

INSERT INTO report_types (type_name) VALUES ('Complaint'), ('Concern'), ('Incident Report');

INSERT INTO priority_levels (priority_name, sort_order) VALUES
('Low', 1), ('Medium', 2), ('High', 3), ('Urgent', 4);

INSERT INTO complaint_statuses (status_name, sort_order, badge_color) VALUES
('Pending',      1, 'yellow'),
('Under Review', 2, 'blue'),
('Assigned',     3, 'indigo'),
('In Progress',  4, 'orange'),
('Resolved',     5, 'green'),
('Closed',       6, 'gray'),
('Rejected',     7, 'red');

INSERT INTO complaint_categories (category_name, description) VALUES
('Noise Disturbance',        'Loud music, karaoke, construction noise'),
('Garbage / Sanitation',     'Improper waste disposal, clogged drainage'),
('Peace and Order',          'Fights, disturbances, threats'),
('Theft / Property',         'Theft, vandalism, trespassing'),
('Road / Infrastructure',    'Potholes, broken streetlights, damaged roads'),
('Neighbor Dispute',         'Boundary, noise, or other disputes between neighbors'),
('Domestic / VAWC',          'Violence against women and children, family concerns'),
('Health and Safety',        'Hazards, outbreaks, unsafe structures'),
('Animal Concerns',          'Stray animals, animal nuisance'),
('Others',                   'Anything not listed');

INSERT INTO departments (department_name, description) VALUES
('Barangay Captain Office',        'Executive office'),
('Peace and Order Committee',      'Handles disputes, security, and incidents'),
('Health and Sanitation Committee','Handles health, garbage, sanitation'),
('Infrastructure Committee',       'Roads, lighting, public works'),
('Lupon Tagapamayapa',             'Barangay conciliation / mediation'),
('Women and Children Desk',        'VAWC and related concerns');

INSERT INTO areas (area_name) VALUES
('Purok 1'), ('Purok 2'), ('Purok 3'), ('Purok 4'), ('Purok 5');

-- Default admin account.
-- IMPORTANT: replace the hash below. Generate one in your app, e.g. PHP:
--   echo password_hash('YourPassword', PASSWORD_DEFAULT);
INSERT INTO users (role_id, department_id, first_name, last_name, email, password_hash)
VALUES (1, 1, 'System', 'Administrator', 'admin@barangay.local', '$2y$12$NX5G5z.xb.6CaydsiZxYauAyN5wC2CMZnrNDbRFU/lEpNsk7sbQuW');
