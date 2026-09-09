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

/* ===== Shared helpers (slugs, tokens, mail, references) ===== */

function absolute_base_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . BASE_URL;
}

function abs_url(string $path = ''): string
{
    return absolute_base_url() . '/' . ltrim($path, '/');
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    if (function_exists('iconv')) {
        $conv = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        if ($conv !== false) $text = $conv;
    }
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim((string)$text, '-');
    return $text !== '' ? $text : 'item';
}

/** A slug for $table that is not already taken (optionally ignoring one row id). */
function unique_slug(string $table, string $base, ?int $ignoreId = null): string
{
    $allowed = ['pages', 'announcements', 'conference_editions'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException('Bad table for unique_slug');
    }
    $slug = slugify($base);
    $candidate = $slug;
    $n = 2;
    while (true) {
        $sql = "SELECT COUNT(*) FROM $table WHERE slug = ?";
        $params = [$candidate];
        if ($ignoreId !== null) { $sql .= " AND id <> ?"; $params[] = $ignoreId; }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        if ((int)$stmt->fetchColumn() === 0) return $candidate;
        $candidate = $slug . '-' . $n++;
    }
}

/** Returns [rawToken, sha256Hash]. */
function token_pair(): array
{
    $raw = bin2hex(random_bytes(32));
    return [$raw, hash('sha256', $raw)];
}

/** Sequential reference like TEPAN-2026-0007, unique within $table.$column for the year. */
function next_reference(string $table, string $column, string $prefix): string
{
    $allowed = ['submissions' => 'reference', 'conference_registrations' => 'reference'];
    if (($allowed[$table] ?? null) !== $column) {
        throw new InvalidArgumentException('Bad table/column for next_reference');
    }
    $year = date('Y');
    $like = $prefix . '-' . $year . '-%';
    for ($attempt = 0; $attempt < 25; $attempt++) {
        $stmt = db()->prepare("SELECT COUNT(*) FROM $table WHERE $column LIKE ?");
        $stmt->execute([$like]);
        $seq = (int)$stmt->fetchColumn() + 1 + $attempt;
        $ref = sprintf('%s-%s-%04d', $prefix, $year, $seq);
        $check = db()->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
        $check->execute([$ref]);
        if ((int)$check->fetchColumn() === 0) return $ref;
    }
    return sprintf('%s-%s-%s', $prefix, $year, substr(bin2hex(random_bytes(3)), 0, 6));
}

/** Best-effort plain-text email. Returns true only if mail() accepted it. */
function send_mail(string $to, string $subject, string $body): bool
{
    if (!MAIL_ENABLED) return false;
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) return false;
    $headers = 'From: ' . MAIL_FROM . "\r\n"
        . 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
    return @mail($to, '[' . SITE_SHORT . '] ' . $subject, $body, $headers);
}

/** Published pages in a header nav group, ordered. */
function nav_pages(string $group): array
{
    $stmt = db()->prepare(
        "SELECT slug, title FROM pages WHERE nav_group = ? AND is_published = 1 ORDER BY sort_order, title"
    );
    $stmt->execute([$group]);
    return $stmt->fetchAll();
}

function latest_announcements(int $limit = 5): array
{
    $limit = max(1, min($limit, 50));
    $stmt = db()->query(
        "SELECT * FROM announcements
         WHERE is_published = 1 AND (published_at IS NULL OR published_at <= NOW())
         ORDER BY COALESCE(published_at, created_at) DESC
         LIMIT $limit"
    );
    return $stmt->fetchAll();
}

function confirmed_subscriber_emails(): array
{
    $stmt = db()->query("SELECT email, name FROM subscribers WHERE confirmed = 1 AND unsubscribed = 0");
    return $stmt->fetchAll();
}

/** Create a single-use reset token for an admin; returns the raw token to email. */
function password_reset_create(int $adminId): string
{
    // Clear any earlier unused tokens for this admin.
    db()->prepare("DELETE FROM password_resets WHERE admin_id = ? AND used = 0")->execute([$adminId]);

    $raw = bin2hex(random_bytes(32));
    $hash = hash('sha256', $raw);
    $expires = (new DateTime())->modify('+' . (int)PASSWORD_RESET_TTL_MINUTES . ' minutes')->format('Y-m-d H:i:s');

    db()->prepare("INSERT INTO password_resets (admin_id, token_hash, expires_at) VALUES (?, ?, ?)")
        ->execute([$adminId, $hash, $expires]);

    return $raw;
}

/** Return the matching admin row for a valid, unused, unexpired token, plus the reset id. */
function password_reset_lookup(string $rawToken): ?array
{
    $rawToken = trim($rawToken);
    if ($rawToken === '' || !ctype_xdigit($rawToken)) return null;

    $hash = hash('sha256', $rawToken);
    $stmt = db()->prepare(
        "SELECT pr.id AS reset_id, a.*
         FROM password_resets pr
         JOIN admins a ON a.id = pr.admin_id
         WHERE pr.token_hash = ? AND pr.used = 0 AND pr.expires_at > NOW()
         LIMIT 1"
    );
    $stmt->execute([$hash]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/** Apply a new password for the admin behind a reset token and burn the token. */
function password_reset_complete(int $resetId, int $adminId, string $newPassword): void
{
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    db()->prepare("UPDATE admins SET password = ? WHERE id = ?")->execute([$hash, $adminId]);
    db()->prepare("UPDATE password_resets SET used = 1 WHERE id = ?")->execute([$resetId]);
    // Invalidate every other outstanding token for this admin.
    db()->prepare("DELETE FROM password_resets WHERE admin_id = ? AND used = 0")->execute([$adminId]);
}

/** Best-effort reset email. Returns true only if PHP mail() accepted the message. */
function password_reset_send_email(string $toEmail, string $toName, string $link): bool
{
    $subject = SITE_SHORT . ' admin password reset';
    $body = "Hello " . ($toName !== '' ? $toName : 'Administrator') . ",\n\n"
        . "A password reset was requested for your " . SITE_SHORT . " admin account.\n"
        . "Open the link below to choose a new password. It expires in "
        . (int)PASSWORD_RESET_TTL_MINUTES . " minutes.\n\n"
        . $link . "\n\n"
        . "If you did not request this, you can ignore this email and your password stays unchanged.\n";
    $headers = 'From: ' . PASSWORD_RESET_FROM . "\r\n"
        . 'Content-Type: text/plain; charset=UTF-8' . "\r\n";

    return @mail($toEmail, $subject, $body, $headers);
}

/** Distinct author/contributor credits with journal counts, alphabetical */
function get_author_summary(): array
{
    $stmt = db()->query(
        "SELECT authors, COUNT(*) AS journal_count, MAX(pub_year) AS latest_year
         FROM journals
         WHERE authors IS NOT NULL AND TRIM(authors) <> ''
         GROUP BY authors
         ORDER BY authors ASC"
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
