<?php
// =========================================================
// Database connection (PDO)
// Every page that needs the database includes this file.
// =========================================================
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw errors we can catch
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rows come back as arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                  // real prepared statements (blocks SQL injection)
        ]
    );
} catch (PDOException $e) {
    // Never show database details to visitors
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit(APP_ENV === 'local'
        ? 'Database connection failed: ' . $e->getMessage()
        : 'Sorry, something went wrong. Please try again later.');
}