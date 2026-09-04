<?php
require_once __DIR__ . '/includes/functions.php';

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if (!in_array($type, ['journal', 'proceeding'], true) || $id <= 0) {
    http_response_code(400);
    die('Invalid download request.');
}

$table = $type === 'journal' ? 'journals' : 'proceedings';
$stmt = db()->prepare("SELECT * FROM $table WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    die('File not found.');
}

$fullPath = __DIR__ . '/' . $item['file_path'];
if (!is_file($fullPath)) {
    http_response_code(404);
    die('The requested file is no longer available.');
}

// Track download count
db()->prepare("UPDATE $table SET downloads = downloads + 1 WHERE id = ?")->execute([$id]);

$downloadName = preg_replace('/[^A-Za-z0-9\-_ ]/', '', $item['title']);
$downloadName = trim($downloadName) !== '' ? $downloadName : $type;

header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $downloadName . '.pdf"');
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: must-revalidate');
readfile($fullPath);
exit;
