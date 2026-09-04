<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function base_url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function format_date(?string $date, string $format = 'F j, Y'): string
{
    if (!$date) return '';
    $ts = strtotime($date);
    return $ts ? date($format, $ts) : '';
}

function format_filesize(int $bytes): string
{
    if ($bytes <= 0) return '0 KB';
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes, 1024));
    $i = max(0, min($i, count($units) - 1));
    return round($bytes / (1024 ** $i), 1) . ' ' . $units[$i];
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function flash_set(string $key, string $message): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $_SESSION['flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

/** Distinct volumes with year range + issue counts, newest first */
function get_volume_summary(): array
{
    $stmt = db()->query(
        "SELECT volume, COUNT(*) AS issue_count, MIN(pub_year) AS from_year, MAX(pub_year) AS to_year
         FROM journals GROUP BY volume ORDER BY volume DESC"
    );
    return $stmt->fetchAll();
}

/** Distinct publication years with issue counts, newest first */
function get_year_summary(): array
{
    $stmt = db()->query(
        "SELECT pub_year, COUNT(*) AS issue_count FROM journals GROUP BY pub_year ORDER BY pub_year DESC"
    );
    return $stmt->fetchAll();
}

function get_distinct_proceeding_years(): array
{
    $stmt = db()->query(
        "SELECT conference_year, COUNT(*) AS item_count FROM proceedings GROUP BY conference_year ORDER BY conference_year DESC"
    );
    return $stmt->fetchAll();
}
