<?php
require_once __DIR__ . '/../../includes/functions.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: ' . base_url('admin/login.php'));
    exit;
}

function current_admin(): array
{
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'name' => $_SESSION['admin_name'] ?? 'Administrator',
        'username' => $_SESSION['admin_username'] ?? '',
    ];
}
