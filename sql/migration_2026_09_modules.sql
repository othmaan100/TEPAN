-- Migration: editorial board, policy pages, author resources, announcements,
--            mailing list, conference editions + registration, manuscript
--            submission & peer review.
-- Apply once:  mysql -u root tepan_db < sql/migration_2026_09_modules.sql
USE tepan_db;

-- ===== Editable content pages (policies + guidelines) =====
CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    body MEDIUMTEXT,
    nav_group VARCHAR(40) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===== Downloadable author resources (manuscript template, copyright form...) =====
CREATE TABLE IF NOT EXISTS journal_resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(200) NOT NULL,
    description VARCHAR(500) DEFAULT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    downloads INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===== Editorial board =====
CREATE TABLE IF NOT EXISTS board_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    role ENUM('editor_in_chief','associate_editor','board_member') NOT NULL DEFAULT 'board_member',
    position VARCHAR(150) DEFAULT NULL,
    affiliation VARCHAR(255) DEFAULT NULL,
    country VARCHAR(100) DEFAULT NULL,
    bio TEXT,
    photo VARCHAR(255) DEFAULT NULL,
    orcid VARCHAR(50) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===== Announcements =====
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(160) NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    body MEDIUMTEXT,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    published_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pub (is_published, published_at)
) ENGINE=InnoDB;

-- ===== Mailing list subscribers (double opt-in) =====
CREATE TABLE IF NOT EXISTS subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    name VARCHAR(150) DEFAULT NULL,
    token_hash CHAR(64) NOT NULL,
    confirmed TINYINT(1) NOT NULL DEFAULT 0,
    confirmed_at DATETIME DEFAULT NULL,
    unsubscribed TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token_hash),
    INDEX idx_state (confirmed, unsubscribed)
) ENGINE=InnoDB;

-- ===== Conference editions =====
CREATE TABLE IF NOT EXISTS conference_editions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    edition_label VARCHAR(80) DEFAULT NULL,
    theme VARCHAR(300) DEFAULT NULL,
    year INT NOT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    venue VARCHAR(200) DEFAULT NULL,
    city VARCHAR(120) DEFAULT NULL,
    description MEDIUMTEXT,
    cfp_body MEDIUMTEXT,
    cfp_deadline DATE DEFAULT NULL,
    cfp_open TINYINT(1) NOT NULL DEFAULT 0,
    registration_open TINYINT(1) NOT NULL DEFAULT 0,
    registration_info MEDIUMTEXT,
    banner_image VARCHAR(255) DEFAULT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_year (year)
) ENGINE=InnoDB;

ALTER TABLE proceedings
    ADD COLUMN conference_edition_id INT DEFAULT NULL AFTER id,
    ADD INDEX idx_edition (conference_edition_id),
    ADD CONSTRAINT fk_proc_edition FOREIGN KEY (conference_edition_id)
        REFERENCES conference_editions(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS conference_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    edition_id INT NOT NULL,
    reference VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(40) DEFAULT NULL,
    affiliation VARCHAR(255) DEFAULT NULL,
    attendance_type ENUM('presenter','attendee') NOT NULL DEFAULT 'attendee',
    paper_title VARCHAR(300) DEFAULT NULL,
    abstract TEXT,
    notes TEXT,
    payment_status ENUM('unpaid','paid','waived') NOT NULL DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_edition (edition_id),
    CONSTRAINT fk_reg_edition FOREIGN KEY (edition_id)
        REFERENCES conference_editions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Manuscript submissions & peer review =====
CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(30) NOT NULL UNIQUE,
    title VARCHAR(300) NOT NULL,
    abstract TEXT NOT NULL,
    keywords VARCHAR(300) DEFAULT NULL,
    author_name VARCHAR(150) NOT NULL,
    author_email VARCHAR(150) NOT NULL,
    co_authors VARCHAR(500) DEFAULT NULL,
    affiliation VARCHAR(255) DEFAULT NULL,
    cover_letter TEXT,
    manuscript_file VARCHAR(255) NOT NULL,
    manuscript_size INT DEFAULT 0,
    supplementary_file VARCHAR(255) DEFAULT NULL,
    status ENUM('submitted','under_review','revisions_requested','accepted','rejected','withdrawn')
        NOT NULL DEFAULT 'submitted',
    editor_notes TEXT,
    decision ENUM('','accept','minor_revisions','major_revisions','reject') NOT NULL DEFAULT '',
    decision_letter TEXT,
    decided_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS submission_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    reviewer_name VARCHAR(150) NOT NULL,
    reviewer_email VARCHAR(150) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    status ENUM('invited','declined','completed') NOT NULL DEFAULT 'invited',
    due_at DATE DEFAULT NULL,
    recommendation ENUM('','accept','minor_revisions','major_revisions','reject') NOT NULL DEFAULT '',
    comments_to_author TEXT,
    comments_to_editor TEXT,
    invited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL,
    INDEX idx_token (token_hash),
    INDEX idx_submission (submission_id),
    CONSTRAINT fk_review_submission FOREIGN KEY (submission_id)
        REFERENCES submissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS submission_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    event VARCHAR(150) NOT NULL,
    detail VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_submission (submission_id),
    CONSTRAINT fk_event_submission FOREIGN KEY (submission_id)
        REFERENCES submissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;
