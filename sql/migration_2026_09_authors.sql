-- Migration: add author attribution to journals
-- Apply once to an existing tepan_db:  mysql -u root tepan_db < sql/migration_2026_09_authors.sql
USE tepan_db;

ALTER TABLE journals
    ADD COLUMN authors VARCHAR(500) DEFAULT NULL AFTER editor,
    ADD INDEX idx_authors (authors);
