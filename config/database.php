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
        } catch (PDOException $e) {
            http_response_code(500);
            die('Database connection failed. Please verify MySQL is running and config/database.php credentials are correct.');
        }
    }
    return $pdo;
}
