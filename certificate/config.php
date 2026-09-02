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
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS certificates (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            certificate_id VARCHAR(40) NOT NULL,
            student_name VARCHAR(150) NOT NULL,
            enrollment_no VARCHAR(100) NOT NULL,
            course_name VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            photo VARCHAR(255) DEFAULT NULL,
            issue_date DATE NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'COURSE COMPLETED',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_certificate_id (certificate_id),
            KEY idx_enrollment (enrollment_no)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    $columnCheck = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.columns
         WHERE table_schema = DATABASE() AND table_name = 'certificates' AND column_name = 'description'"
    );
    $columnCheck->execute();
    if (!(int)$columnCheck->fetchColumn()) {
        $pdo->exec('ALTER TABLE certificates ADD COLUMN description TEXT DEFAULT NULL AFTER course_name');
    }
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Please check config.php.');
}
