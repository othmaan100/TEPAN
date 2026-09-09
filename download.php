<?php
require_once __DIR__ . '/includes/functions.php';

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);

$map = [
    'journal'     => ['table' => 'journals',           'name' => 'title'],
    'proceeding'  => ['table' => 'proceedings',        'name' => 'title'],
    'resource'    => ['table' => 'journal_resources',  'name' => 'label'],
];

if (!isset($map[$type]) || $id <= 0) {
    http_response_code(400);
    die('Invalid download request.');
}

$table = $map[$type]['table'];
$nameCol = $map[$type]['name'];

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

db()->prepare("UPDATE $table SET downloads = downloads + 1 WHERE id = ?")->execute([$id]);

$ext = strtolower(pathinfo($item['file_path'], PATHINFO_EXTENSION)) ?: 'pdf';
$mimes = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'zip' => 'application/zip',
    'rtf' => 'application/rtf',
    'odt' => 'application/vnd.oasis.opendocument.text',
];
$mime = $mimes[$ext] ?? 'application/octet-stream';

$downloadName = preg_replace('/[^A-Za-z0-9\-_ ]/', '', (string)$item[$nameCol]);
$downloadName = trim($downloadName) !== '' ? $downloadName : $type;

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $downloadName . '.' . $ext . '"');
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: must-revalidate');
readfile($fullPath);
exit;
