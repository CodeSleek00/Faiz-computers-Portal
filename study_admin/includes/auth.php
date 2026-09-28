<?php
require_once __DIR__ . '/config.php';

/*
 * Your existing admin login should normally already protect the admin area.
 * These common session names are supported.
 *
 * If your project uses a different session variable, add it here.
 */
$loggedIn =
    !empty($_SESSION['admin_id']) ||
    !empty($_SESSION['admin_logged_in']) ||
    !empty($_SESSION['is_admin_logged_in']) ||
    !empty($_SESSION['admin']);

if (!$loggedIn) {
    /*
     * If this module is placed inside an already protected admin folder,
     * you can simply return here instead of redirecting.
     *
     * Change this URL if your login page has a different location.
     */
    $login = '../login.php';
    if (file_exists(dirname(__DIR__) . '/../login.php')) {
        header('Location: ' . $login);
        exit;
    }

    /*
     * Development fallback: show a clear message instead of silently
     * exposing the module.
     */
    http_response_code(403);
    die(
        '<div style="font-family:Arial;padding:30px">' .
        '<h2>Admin login required</h2>' .
        '<p>Set your existing admin session in <code>includes/auth.php</code>.</p>' .
        '</div>'
    );
}
