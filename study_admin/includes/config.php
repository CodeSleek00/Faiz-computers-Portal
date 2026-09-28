<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/*
 * Existing project database connection.
 * Expected file: ../database_connection/db_connect.php
 *
 * That file should create a mysqli connection in $conn.
 */
$dbFile = dirname(__DIR__, 1) . '/../database_connection/db_connect.php';

if (file_exists($dbFile)) {
    require_once $dbFile;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    die(
        '<div style="font-family:Arial;padding:30px">' .
        '<h2>Database connection not found</h2>' .
        '<p>Edit <code>includes/config.php</code> and set the correct path to your existing db_connect.php.</p>' .
        '</div>'
    );
}

$conn->set_charset('utf8mb4');

define('STUDY_ADMIN_BASE', dirname(__DIR__));
define('STUDY_UPLOAD_BASE', STUDY_ADMIN_BASE . '/uploads');

function study_url(string $path = ''): string {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/study_admin/index.php'), '/\\');
    return $base . ($path ? '/' . ltrim($path, '/') : '');
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void {
    $_SESSION['study_flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array {
    $f = $_SESSION['study_flash'] ?? null;
    unset($_SESSION['study_flash']);
    return $f;
}

function post_string(string $key, string $default = ''): string {
    return trim((string)($_POST[$key] ?? $default));
}

function post_int(string $key, int $default = 0): int {
    return (int)($_POST[$key] ?? $default);
}

function slugify(string $text): string {
    $text = trim(mb_strtolower($text, 'UTF-8'));
    $text = preg_replace('/[^\pL\pN]+/u', '-', $text);
    $text = trim((string)$text, '-');
    return $text !== '' ? $text : 'item-' . time();
}

function ensure_upload_dir(string $type): string {
    $allowed = ['videos','notes','pdf','documents','practical','attachments','thumbnails'];
    if (!in_array($type, $allowed, true)) {
        throw new RuntimeException('Invalid upload directory.');
    }
    $dir = STUDY_UPLOAD_BASE . '/' . $type;
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        throw new RuntimeException('Could not create upload directory.');
    }
    return $dir;
}

function save_upload(array $file, string $type, array $allowedExt, int $maxBytes): string {
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        throw new RuntimeException('No file selected.');
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed. Error code: ' . (int)$file['error']);
    }
    if ((int)$file['size'] > $maxBytes) {
        throw new RuntimeException('File is larger than the allowed limit.');
    }

    $original = (string)$file['name'];
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        throw new RuntimeException('File type is not allowed.');
    }

    $blocked = ['php','php3','php4','php5','phtml','phar','cgi','pl','py','sh','exe','com','bat'];
    if (in_array($ext, $blocked, true)) {
        throw new RuntimeException('Executable files are not allowed.');
    }

    $dir = ensure_upload_dir($type);
    $name = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $destination = $dir . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not save uploaded file.');
    }

    return 'uploads/' . $type . '/' . $name;
}

function delete_relative_file(?string $relative): void {
    if (!$relative) return;
    $relative = ltrim($relative, '/\\');
    $root = realpath(STUDY_ADMIN_BASE);
    $path = realpath(STUDY_ADMIN_BASE . '/' . $relative);
    if ($root && $path && strpos($path, $root . DIRECTORY_SEPARATOR) === 0 && is_file($path)) {
        @unlink($path);
    }
}

function current_admin_name(): string {
    return (string)(
        $_SESSION['admin_name']
        ?? $_SESSION['admin_username']
        ?? 'Administrator'
    );
}
