<?php
/**
 * Upload helpers shared by the admin journal/proceedings forms.
 * Returns ['ok' => bool, 'path' => relative path|null, 'size' => int, 'error' => string|null]
 */

function handle_pdf_upload(string $fieldName, string $subdir, bool $required = false): array
{
    $result = ['ok' => true, 'path' => null, 'size' => 0, 'error' => null];

    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            $result['ok'] = false;
            $result['error'] = 'A PDF file is required.';
        }
        return $result;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'path' => null, 'size' => 0, 'error' => 'Upload failed (error code ' . $file['error'] . ').'];
    }

    $maxBytes = 25 * 1024 * 1024; // 25MB
    if ($file['size'] > $maxBytes) {
        return ['ok' => false, 'path' => null, 'size' => 0, 'error' => 'File is too large. Maximum size is 25MB.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($ext !== 'pdf' || $mime !== 'application/pdf') {
        return ['ok' => false, 'path' => null, 'size' => 0, 'error' => 'Only PDF files are allowed.'];
    }

    $dir = __DIR__ . '/../uploads/' . trim($subdir, '/') . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $safeName = uniqid($subdir . '_', true) . '.pdf';
    $destPath = $dir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['ok' => false, 'path' => null, 'size' => 0, 'error' => 'Could not save the uploaded file.'];
    }

    return ['ok' => true, 'path' => 'uploads/' . trim($subdir, '/') . '/' . $safeName, 'size' => $file['size'], 'error' => null];
}

function handle_image_upload(string $fieldName, string $subdir = 'covers'): array
{
    $result = ['ok' => true, 'path' => null, 'error' => null];

    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return $result; // optional field
    }

    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'path' => null, 'error' => 'Cover image upload failed.'];
    }

    $maxBytes = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxBytes) {
        return ['ok' => false, 'path' => null, 'error' => 'Cover image too large (max 5MB).'];
    }

    $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowed[$ext]) || $allowed[$ext] !== $mime) {
        return ['ok' => false, 'path' => null, 'error' => 'Cover image must be a JPG, PNG, or WEBP file.'];
    }

    $dir = __DIR__ . '/../uploads/' . trim($subdir, '/') . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $safeName = uniqid('cover_', true) . '.' . $ext;
    $destPath = $dir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['ok' => false, 'path' => null, 'error' => 'Could not save the cover image.'];
    }

    return ['ok' => true, 'path' => 'uploads/' . trim($subdir, '/') . '/' . $safeName, 'error' => null];
}

function delete_uploaded_file(?string $relativePath): void
{
    if (!$relativePath) return;
    $full = __DIR__ . '/../' . $relativePath;
    if (is_file($full)) @unlink($full);
}
