<?php
/**
 * Offline admin password reset — the guaranteed recovery path when email is
 * unavailable and no one can sign in.
 *
 * Usage (from the project root):
 *   C:\xampp\php\php.exe bin\reset_admin_password.php <username> <new-password>
 *
 * Example:
 *   C:\xampp\php\php.exe bin\reset_admin_password.php admin "NewSecret#2026"
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script can only be run from the command line.\n");
}

require_once __DIR__ . '/../config/database.php';

$username = $argv[1] ?? null;
$password = $argv[2] ?? null;

if (!$username || $password === null) {
    fwrite(STDERR, "Usage: php bin/reset_admin_password.php <username> <new-password>\n");
    exit(1);
}

if (strlen($password) < 8) {
    fwrite(STDERR, "Password must be at least 8 characters.\n");
    exit(1);
}

$stmt = db()->prepare("SELECT id, username FROM admins WHERE username = ? LIMIT 1");
$stmt->execute([$username]);
$admin = $stmt->fetch();

if (!$admin) {
    fwrite(STDERR, "No admin found with username \"$username\".\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
db()->prepare("UPDATE admins SET password = ? WHERE id = ?")->execute([$hash, $admin['id']]);
db()->prepare("DELETE FROM password_resets WHERE admin_id = ?")->execute([$admin['id']]);

echo "Password updated for admin \"{$admin['username']}\" (id {$admin['id']}).\n";
