-- Migration: admin email + password reset tokens
-- Apply once to an existing tepan_db:  mysql -u root tepan_db < sql/migration_2026_09_password_reset.sql
USE tepan_db;

ALTER TABLE admins
    ADD COLUMN email VARCHAR(150) DEFAULT NULL AFTER full_name;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token_hash (token_hash),
    INDEX idx_admin (admin_id),
    CONSTRAINT fk_reset_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Give the seed admin a recovery address (change it in the admin Account page)
UPDATE admins SET email = 'editoratepan@gmail.com' WHERE email IS NULL;
