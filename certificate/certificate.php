<?php
require_once __DIR__ . '/config.php';

$id = trim((string)($_GET['id'] ?? ''));
$stmt = $pdo->prepare("SELECT * FROM certificates WHERE certificate_id = ? LIMIT 1");
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c) {
    http_response_code(404);
    exit('Certificate not found.');
}

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$verifyUrl = $siteUrl . '/verify.php?id=' . rawurlencode($c['certificate_id']);
$qrUrl = 'https://quickchart.io/qr?size=260&margin=1&text=' . rawurlencode($verifyUrl);

$photoUrl = '';
if (!empty($c['photo'])) {
    $photoUrl = $siteUrl . '/' . ltrim($c['photo'], '/');
}

$displayDate = date('d/m/Y', strtotime($c['issue_date']));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=h($c['certificate_id'])?> - Certificate</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#e5e7eb;font-family:Georgia,"Times New Roman",serif}
.toolbar{position:fixed;z-index:10;top:14px;right:14px;display:flex;gap:8px;font-family:Arial,sans-serif}
.toolbar button{border:0;border-radius:9px;padding:11px 15px;background:#4338ca;color:#fff;font-weight:700;cursor:pointer}
.page{
  width:848px;height:1224px;position:relative;margin:25px auto;
  background:url("assets/template.png") center/100% 100% no-repeat;
  overflow:hidden;
}
/* White masks remove the sample values printed in the supplied template. */
.mask{position:absolute;background:rgba(255,255,255,.97)}
.name-mask{left:120px;top:675px;width:610px;height:62px}
.course-mask{left:35px;top:803px;width:780px;height:48px}
.enroll-mask{left:110px;top:875px;width:630px;height:49px}
.date-mask{left:205px;top:984px;width:440px;height:54px}
.photo-mask{left:333px;top:443px;width:170px;height:194px}
.qr-mask{left:619px;top:1018px;width:150px;height:145px}
.dynamic-photo{
 position:absolute;left:337px;top:447px;width:162px;height:187px;
 object-fit:cover;border:2px solid #111;background:#fff;
}
.name{
 position:absolute;left:55px;top:681px;width:738px;text-align:center;
 font-size:42px;font-weight:700;text-transform:uppercase;line-height:1.1;
}
.course{
 position:absolute;left:45px;top:810px;width:758px;text-align:center;
 font-size:25px;font-weight:700;text-transform:uppercase;line-height:1.1;color:#0d086d;
}
.enroll{
 position:absolute;left:55px;top:882px;width:738px;text-align:center;
 font-size:27px;font-weight:700;line-height:1.1;
}
.date{
 position:absolute;left:205px;top:992px;width:440px;text-align:center;
 font-size:25px;font-weight:700;line-height:1.1;
}
.qr{
 position:absolute;left:628px;top:1026px;width:132px;height:132px;
 object-fit:contain;background:#fff;
}
.verify-id{
 position:absolute;left:40px;top:1165px;width:550px;text-align:left;
 font-family:Arial,sans-serif;font-size:10px;color:#666;
}
@media print{
 @page{size:848px 1224px;margin:0}
 body{background:#fff}.toolbar{display:none}.page{margin:0;box-shadow:none}
}
@media(max-width:900px){
 .page{transform-origin:top left;transform:scale(calc((100vw - 20px)/848));margin:10px;width:848px}
 body{height:calc(1224px * ((100vw - 20px)/848))}
}
</style>
</head>
<body>
<div class="toolbar">
<button onclick="window.print()">Print / Save PDF</button>
</div>
<div class="page">
<div class="mask name-mask"></div>
<div class="mask course-mask"></div>
<div class="mask enroll-mask"></div>
<div class="mask date-mask"></div>
<div class="mask photo-mask"></div>
<div class="mask qr-mask"></div>

<?php if($photoUrl): ?>
<img class="dynamic-photo" src="<?=h($photoUrl)?>" alt="Student Photo">
<?php else: ?>
<div class="dynamic-photo"></div>
<?php endif; ?>

<div class="name"><?=h($c['student_name'])?></div>
<div class="course"><?=h($c['course_name'])?></div>
<div class="enroll">ENROLLMENT NO. : <?=h($c['enrollment_no'])?></div>
<div class="date">Date of Issue:- <?=h($displayDate)?></div>
<img class="qr" src="<?=h($qrUrl)?>" alt="Certificate verification QR">
<div class="verify-id">Certificate ID: <?=h($c['certificate_id'])?></div>
</div>
</body></html>
