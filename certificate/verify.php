<?php
require_once __DIR__ . '/config.php';

$id = trim((string)($_GET['id'] ?? ''));
$stmt = $pdo->prepare("SELECT * FROM certificates WHERE certificate_id = ? LIMIT 1");
$stmt->execute([$id]);
$c = $stmt->fetch();

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Certificate Verification - Code Sleek Foundation</title>
<style>
*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;background:#f5f7fb;color:#111827}
.wrap{width:min(720px,92%);margin:45px auto}.card{background:#fff;border-radius:22px;padding:32px;box-shadow:0 10px 35px #0001;text-align:center}
.brand{font-weight:900;letter-spacing:.5px;color:#4338ca;font-size:22px}.ok{display:inline-block;margin:22px 0 8px;background:#dcfce7;color:#166534;padding:9px 15px;border-radius:999px;font-weight:800}
h1{font-size:30px;margin:5px 0 25px}.photo{width:130px;height:155px;object-fit:cover;border:2px solid #111;margin:8px auto 20px}
.details{max-width:520px;margin:auto;text-align:left;border:1px solid #e5e7eb;border-radius:15px;overflow:hidden}
.row{display:flex;justify-content:space-between;gap:20px;padding:13px 16px;border-bottom:1px solid #eee}.row:last-child{border-bottom:0}.label{font-weight:700;color:#6b7280}.value{text-align:right;font-weight:700}
.completed{color:#166534}.invalid{color:#991b1b}
a{display:inline-block;margin-top:22px;padding:11px 16px;border-radius:9px;background:#4338ca;color:#fff;text-decoration:none;font-weight:700}
.note{color:#6b7280;font-size:13px;margin-top:20px}
</style>
<link rel="stylesheet" href="assets/certificate.css">
</head>
<body><main class="verify-shell"><div class="card panel verify-card">
<div class="brand"><span class="brand-mark">CSF</span><span>Code Sleek Foundation<small>Credential verification</small></span></div>
<?php if($c): ?>
<div class="verify-mark">✓</div>
<h1>COURSE COMPLETED</h1>
<?php if(!empty($c['photo'])): ?><img class="photo" src="<?=h($siteUrl . '/' . ltrim($c['photo'],'/'))?>" alt="Student Photo"><?php endif; ?>
<div class="details">
<div class="detail-row"><span class="detail-label">Student Name</span><span class="detail-value"><?=h($c['student_name'])?></span></div>
<div class="detail-row"><span class="detail-label">Enrollment No.</span><span class="detail-value"><?=h($c['enrollment_no'])?></span></div>
<div class="detail-row"><span class="detail-label">Course</span><span class="detail-value"><?=h($c['course_name'])?></span></div>
<div class="detail-row"><span class="detail-label">Date of Issue</span><span class="detail-value"><?=h(date('d/m/Y',strtotime($c['issue_date'])))?></span></div>
<div class="detail-row"><span class="detail-label">Certificate ID</span><span class="detail-value"><?=h($c['certificate_id'])?></span></div>
<div class="detail-row"><span class="detail-label">Status</span><span class="detail-value completed"><?=h($c['status'])?></span></div>
</div>
<a class="btn btn-primary" target="_blank" href="certificate.php?id=<?=urlencode($c['certificate_id'])?>">View Certificate</a>
<div class="note">This certificate was verified from the official Code Sleek Foundation certificate database.</div>
<?php else: ?>
<div class="verify-mark invalid">!</div>
<h1>Invalid Certificate</h1>
<p class="note">The certificate number supplied in this QR code does not exist in our verification database.</p>
<?php endif; ?>
</div></main></body></html>
