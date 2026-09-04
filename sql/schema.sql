-- TEPAN (Technology Education Practitioners Association of Nigeria) website schema
CREATE DATABASE IF NOT EXISTS tepan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tepan_db;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS journals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    volume INT NOT NULL,
    issue INT NOT NULL DEFAULT 1,
    pub_year INT NOT NULL,
    publication_date DATE NOT NULL,
    editor VARCHAR(150) DEFAULT NULL,
    description TEXT,
    cover_image VARCHAR(255) DEFAULT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT DEFAULT 0,
    is_current TINYINT(1) NOT NULL DEFAULT 0,
    downloads INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_volume (volume),
    INDEX idx_year (pub_year),
    INDEX idx_current (is_current)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS proceedings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    conference_name VARCHAR(255) NOT NULL,
    conference_year INT NOT NULL,
    location VARCHAR(150) DEFAULT NULL,
    description TEXT,
    cover_image VARCHAR(255) DEFAULT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT DEFAULT 0,
    downloads INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_conf_year (conference_year)
) ENGINE=InnoDB;
