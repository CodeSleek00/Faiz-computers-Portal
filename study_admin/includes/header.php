<?php
require_once __DIR__ . '/config.php';
$flash = get_flash();
$pageTitle = $pageTitle ?? 'Study Material';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> - Study Material Admin</title>
<style>
:root{--orange:#fe9001;--navy:#002a4d;--bg:#f5f7fa;--white:#fff;--border:#e5e7eb;--text:#172033;--muted:#64748b;--danger:#dc2626;--success:#15803d}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font-family:Arial,Helvetica,sans-serif}
a{text-decoration:none;color:inherit}
.layout{display:flex;min-height:100vh}
.sidebar{width:245px;background:var(--navy);color:#fff;padding:20px;position:fixed;top:0;bottom:0;left:0;overflow:auto}
.brand{font-size:20px;font-weight:700;margin-bottom:25px}
.brand small{display:block;font-size:11px;opacity:.7;margin-top:5px}
.nav a{display:block;padding:11px 13px;border-radius:8px;margin:4px 0;color:#eaf1f7}
.nav a:hover,.nav a.active{background:rgba(255,255,255,.12)}
.main{margin-left:245px;width:calc(100% - 245px);padding:25px}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:20px}
h1{font-size:25px;margin:0 0 5px}
h2{font-size:19px;margin:0 0 15px}
.muted{color:var(--muted)}
.card{background:var(--white);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,.03)}
.grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px}
.stat{padding:18px;border-radius:12px;background:#fff;border:1px solid var(--border)}
.stat strong{font-size:28px;display:block;margin-top:5px}
.btn{display:inline-block;border:0;border-radius:8px;padding:9px 13px;background:var(--navy);color:#fff;cursor:pointer;font-size:14px}
.btn.orange{background:var(--orange);color:#111}
.btn.danger{background:var(--danger)}
.btn.success{background:var(--success)}
.btn.light{background:#eef2f7;color:#1f2937}
.btn.small{padding:6px 9px;font-size:12px}
form.inline{display:inline}
label{display:block;font-weight:600;font-size:13px;margin:0 0 6px}
input,select,textarea{width:100%;border:1px solid #d6dce5;border-radius:8px;padding:10px;font-size:14px;background:#fff}
textarea{min-height:130px;resize:vertical}
.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
.form-grid .full{grid-column:1/-1}
.table-wrap{overflow:auto}
table{width:100%;border-collapse:collapse;background:#fff}
th,td{padding:11px;border-bottom:1px solid var(--border);text-align:left;font-size:13px;vertical-align:top}
th{background:#f8fafc;color:#334155}
.badge{display:inline-block;border-radius:999px;padding:4px 8px;font-size:11px;background:#eef2f7}
.badge.active{background:#dcfce7;color:#166534}
.badge.inactive{background:#fee2e2;color:#991b1b}
.alert{padding:12px 14px;border-radius:8px;margin-bottom:15px}
.alert.success{background:#dcfce7;color:#166534}
.alert.error{background:#fee2e2;color:#991b1b}
.actions{display:flex;gap:6px;flex-wrap:wrap}
.searchbar{display:flex;gap:8px;margin-bottom:15px}
.searchbar input{max-width:350px}
@media(max-width:900px){.sidebar{position:static;width:100%;min-height:auto}.layout{display:block}.main{margin-left:0;width:100%}.grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.main{padding:14px}.grid,.form-grid{grid-template-columns:1fr}.form-grid .full{grid-column:auto}}
</style>
</head>
<body>
<div class="layout">
<aside class="sidebar">
    <div class="brand">Faiz Computer Institute<small>Study Material Admin</small></div>
    <nav class="nav">
        <a href="index.php">Dashboard</a>
        <a href="courses.php">Courses</a>
        <a href="topics.php">Topics</a>
        <a href="contents.php">Content</a>
        <a href="assignments.php">Assignments</a>
    </nav>
</aside>

<main class="main">
<div class="topbar">
    <div>
        <h1><?= e($pageTitle) ?></h1>
        <div class="muted">Study videos, notes, PDFs and assignments</div>
    </div>
    <div class="muted"><?= e(current_admin_name()) ?></div>
</div>

<?php if ($flash): ?>
<div class="alert <?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <?= e($flash['message']) ?>
</div>
<?php endif; ?>
