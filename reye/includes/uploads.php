<?php
// =========================================================
// Safe image uploads (product photos)
// - only real JPG, PNG or WebP images, max 4 MB
// - saved with a random name, so nobody can guess or overwrite files
// =========================================================

const UPLOAD_MAX_BYTES = 4 * 1024 * 1024;
const UPLOAD_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

// Returns the saved path (e.g. "uploads/products/ab12.jpg"), null if no file was chosen,
// or throws an exception with a friendly message if the file is not allowed.
function save_product_image(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The photo did not upload. Try a smaller file.');
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        throw new RuntimeException('Photos must be under 4 MB.');
    }

    // Check what the file really is (not just its name)
    $type = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(UPLOAD_TYPES[$type]) || !getimagesize($file['tmp_name'])) {
        throw new RuntimeException('Photos must be JPG, PNG or WebP images.');
    }

    $dir = __DIR__ . '/../uploads/products';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $name = bin2hex(random_bytes(12)) . '.' . UPLOAD_TYPES[$type];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('Could not save the photo. Check that the uploads folder is writable.');
    }
    return 'uploads/products/' . $name;
}

// Remove an old photo from the server
function delete_product_image(?string $path): void
{
    if ($path && strpos($path, 'uploads/products/') === 0 && strpos($path, '..') === false) {
        $full = __DIR__ . '/../' . $path;
        if (is_file($full)) {
            unlink($full);
        }
    }
}
