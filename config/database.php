<?php
/**
 * Database connection for the TEPAN website.
 * Default values match a stock XAMPP install (root / no password).
 * Change these if your MySQL credentials differ.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'tepan_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SITE_NAME', 'Technology Education Practitioners Association of Nigeria');
define('SITE_SHORT', 'TEPAN');
define('BASE_URL', '/TEPA');

// The journal series' International Standard Serial Numbers (identify the
// publication as a whole, not individual issues). Leave blank to hide.
define('JOURNAL_ISSN', '2645-2839');    // print
define('JOURNAL_EISSN', '2645-2847');   // electronic
define('CONTACT_EMAIL', 'editoratepan@gmail.com');
define('CONTACT_PHONES', ['07036120387', '08036899034', '08036290334']);
define('CONTACT_LOCATION', 'Abuja, Nigeria');

// --- Outgoing email ---
define('MAIL_FROM', 'no-reply@tepan.org.ng');
define('MAIL_ENABLED', true);            // set FALSE to skip PHP mail() entirely
// Stock XAMPP has no mail server. While TRUE, action links (subscribe confirm,
// reviewer invite, etc.) are also shown on screen / in the admin so flows can be
// completed locally. SET FALSE on a real server with working mail.
define('MAIL_DEV_SHOW_LINKS', true);

// --- Admin password reset ---
// How long a reset link stays valid.
define('PASSWORD_RESET_TTL_MINUTES', 60);
// From-address used for the reset email sent via PHP mail().
define('PASSWORD_RESET_FROM', 'no-reply@tepan.org.ng');
// Stock XAMPP has no outgoing mail server, so mail() usually fails silently.
// While TRUE, the reset link is shown on screen after a request so an admin can
// still recover locally. SET THIS TO FALSE on any public/production server —
// otherwise anyone who knows an admin email can seize the account.
define('PASSWORD_RESET_DEV_SHOW_LINK', true);

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
            // Align MySQL's clock (NOW(), CURRENT_TIMESTAMP) with PHP's, so
            // publish-date comparisons and stored timestamps stay consistent.
            $pdo->exec("SET time_zone = '" . (new DateTime())->format('P') . "'");
        } catch (PDOException $e) {
            http_response_code(500);
            die('Database connection failed. Please verify MySQL is running and config/database.php credentials are correct.');
        }
    }
    return $pdo;
}
