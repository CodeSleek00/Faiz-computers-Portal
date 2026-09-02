<?php
session_start();
require_once __DIR__ . '/config.php';

function require_admin(): void {
    if (empty($_SESSION['csf_admin'])) {
        header('Location: admin.php');
        exit;
    }
}
