<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';
session_start();

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: admin.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $password = (string)($_POST['password'] ?? '');
    if (hash_equals($adminPassword, $password)) {
        session_regenerate_id(true);
        $_SESSION['csf_admin'] = true;
        header('Location: admin.php');
        exit;
    }
    $error = 'Incorrect admin password.';
}

if (empty($_SESSION['csf_admin'])):
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>CSF Certificate Admin Login</title>
<style>
body{font-family:Arial,sans-serif;background:#f4f5fb;display:grid;place-items:center;min-height:100vh;margin:0}
.card{background:#fff;width:min(420px,90%);padding:30px;border-radius:18px;box-shadow:0 10px 35px #0001}
h1{margin-top:0;color:#1d1d1d}.logo{font-weight:800;color:#4338ca;font-size:22px;margin-bottom:20px}
input{width:100%;box-sizing:border-box;padding:13px;border:1px solid #ddd;border-radius:10px;margin:8px 0 15px}
button{width:100%;padding:13px;border:0;border-radius:10px;background:#4338ca;color:#fff;font-weight:700;cursor:pointer}
.err{background:#fee2e2;color:#991b1b;padding:10px;border-radius:9px;margin-bottom:15px}
</style>
</head>
<body><div class="card">
<div class="logo">CODE SLEEK FOUNDATION</div>
<h1>Certificate Admin</h1>
<?php if($error): ?><div class="err"><?=htmlspecialchars($error)?></div><?php endif; ?>
<form method="post">
<label>Admin Password</label>
<input type="password" name="password" required autofocus>
<button name="login" value="1">Login</button>
</form>
</div></body></html>
<?php
exit;
endif;

require_once __DIR__ . '/auth.php';
require_admin();

$stmt = $pdo->query("SELECT * FROM certificates ORDER BY id DESC LIMIT 100");
$certificates = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Certificate Dashboard</title>
<style>
*{box-sizing:border-box}body{font-family:Arial,sans-serif;margin:0;background:#f5f7fb;color:#111827}
header{background:#111827;color:#fff;padding:18px 5%;display:flex;justify-content:space-between;align-items:center}
header strong{font-size:20px}a{text-decoration:none}.btn{display:inline-block;padding:10px 15px;border-radius:9px;font-weight:700}
.primary{background:#4338ca;color:white}.light{background:#fff;color:#111827}.danger{color:#fecaca}
.wrap{width:min(1200px,92%);margin:30px auto}.top{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:20px}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:15px;overflow:hidden;box-shadow:0 5px 20px #0000000d}
th,td{padding:13px;text-align:left;border-bottom:1px solid #eee}th{background:#f8fafc;font-size:13px}
.badge{background:#dcfce7;color:#166534;padding:5px 8px;border-radius:20px;font-size:12px;font-weight:700}
.actions a{margin-right:6px}.muted{color:#6b7280}
@media(max-width:700px){table{font-size:12px}.hide-sm{display:none}}
</style>
</head>
<body>
<header><strong>Code Sleek Foundation</strong><a class="danger" href="?logout=1">Logout</a></header>
<div class="wrap">
<div class="top">
<div><h1>Certificate Dashboard</h1><div class="muted">Create and verify course-completion certificates.</div></div>
<a class="btn primary" href="generate.php">+ Generate Certificate</a>
</div>
<table>
<thead><tr><th>Certificate ID</th><th>Student</th><th>Enrollment</th><th class="hide-sm">Course</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach($certificates as $c): ?>
<tr>
<td><strong><?=htmlspecialchars($c['certificate_id'])?></strong></td>
<td><?=htmlspecialchars($c['student_name'])?></td>
<td><?=htmlspecialchars($c['enrollment_no'])?></td>
<td class="hide-sm"><?=htmlspecialchars($c['course_name'])?></td>
<td><span class="badge"><?=htmlspecialchars($c['status'])?></span></td>
<td class="actions">
<a class="btn light" target="_blank" href="certificate.php?id=<?=urlencode($c['certificate_id'])?>">View</a>
<a class="btn light" target="_blank" href="verify.php?id=<?=urlencode($c['certificate_id'])?>">Verify</a>
</td>
</tr>
<?php endforeach; ?>
<?php if(!$certificates): ?><tr><td colspan="6">No certificates created yet.</td></tr><?php endif; ?>
</tbody>
</table>
</div>
</body></html>
