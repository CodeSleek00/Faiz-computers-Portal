<?php
// Database values can be overridden with environment variables on production.
$dbHost = getenv('CERTIFICATE_DB_HOST') ?: 'localhost';
$dbName = getenv('CERTIFICATE_DB_NAME') ?: 'u298112699_FAIZ_COMPUTER';
$dbUser = getenv('CERTIFICATE_DB_USER') ?: 'u298112699_FAIZ2912';
$dbPass = getenv('CERTIFICATE_DB_PASSWORD') ?: 'Faiz2912';

// Build absolute links correctly on localhost, subfolders, and the live domain.
$configuredSiteUrl = rtrim((string)(getenv('CERTIFICATE_SITE_URL') ?: ''), '/');
if ($configuredSiteUrl !== '') {
    $siteUrl = $configuredSiteUrl;
} else {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDirectory = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/.');
    $siteUrl = $scheme . '://' . $host . ($scriptDirectory ? '/' . ltrim($scriptDirectory, '/') : '');
}

$adminPassword = getenv('CERTIFICATE_ADMIN_PASSWORD') ?: 'admin123';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Please check config.php.');
}
