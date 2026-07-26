<?php
// ============================================
// DATABASE CONFIGURATION
// ============================================

// Support Railway MYSQL_URL or individual env vars
$mysqlUrl = getenv('MYSQL_URL');
if ($mysqlUrl) {
    $url = parse_url($mysqlUrl);
    define('DB_HOST', $url['host'] ?? 'localhost');
    define('DB_PORT', $url['port'] ?? '3306');
    define('DB_NAME', ltrim($url['path'] ?? '/railway', '/'));
    define('DB_USER', $url['user'] ?? 'root');
    define('DB_PASS', $url['pass'] ?? '');
} else {
    define('DB_HOST', getenv('MYSQLHOST') ?: (getenv('DB_HOST') ?: 'localhost'));
    define('DB_PORT', getenv('MYSQLPORT') ?: (getenv('DB_PORT') ?: '3306'));
    define('DB_NAME', getenv('MYSQLDATABASE') ?: (getenv('DB_NAME') ?: 'surat_kampus'));
    define('DB_USER', getenv('MYSQLUSER') ?: (getenv('DB_USER') ?: 'root'));
    define('DB_PASS', getenv('MYSQLPASSWORD') ?: (getenv('DB_PASS') ?: ''));
}

// App Configuration
define('APP_NAME', 'Sistem Pengajuan Surat Kampus');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8000');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_FILES', 3);

// Database Connection
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
