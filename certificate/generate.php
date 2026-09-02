<?php
require_once __DIR__ . '/auth.php';
require_admin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = trim((string)($_POST['student_name'] ?? ''));
    $enrollment = trim((string)($_POST['enrollment_no'] ?? ''));
    $course = trim((string)($_POST['course_name'] ?? ''));
    $issueDate = (string)($_POST['issue_date'] ?? date('Y-m-d'));

    if ($studentName === '' || $enrollment === '' || $course === '') {
        $error = 'Please fill all required fields.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $issueDate)) {
        $error = 'Invalid issue date.';
    } else {
        $photoPath = null;

        if (!empty($_FILES['photo']['name'])) {
            if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                $error = 'Photo upload failed.';
            } else {
                $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
                $mime = mime_content_type($_FILES['photo']['tmp_name']);
                if (!isset($allowed[$mime])) {
                    $error = 'Photo must be JPG, PNG or WEBP.';
                } elseif ($_FILES['photo']['size'] > 4 * 1024 * 1024) {
                    $error = 'Photo must be smaller than 4 MB.';
                } else {
                    $dir = __DIR__ . '/uploads';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $filename = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $dir . '/' . $filename)) {
                        $photoPath = 'uploads/' . $filename;
                    } else {
                        $error = 'Could not save photo.';
                    }
                }
            }
        }

        if ($error === '') {
            $certificateId = 'CSF-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $stmt = $pdo->prepare(
                "INSERT INTO certificates
                (certificate_id,student_name,enrollment_no,course_name,photo,issue_date,status)
                VALUES (?,?,?,?,?,?,'COURSE COMPLETED')"
            );
            $stmt->execute([$certificateId,$studentName,$enrollment,$course,$photoPath,$issueDate]);
            header('Location: certificate.php?id=' . urlencode($certificateId));
            exit;
        }
    }
}

$students = $pdo->query(
    "SELECT certificate_id, student_name, enrollment_no, course_name, issue_date, status
     FROM certificates
     ORDER BY id DESC"
)->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Generate Certificate</title>
<style>
*{box-sizing:border-box}body{font-family:Arial,sans-serif;background:#f4f5fb;margin:0}
.wrap{width:min(760px,92%);margin:35px auto}.card{background:#fff;border-radius:18px;padding:28px;box-shadow:0 8px 30px #0001}
h1{margin-top:0}.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.full{grid-column:1/-1}label{display:block;font-weight:700;margin-bottom:7px}
input{width:100%;padding:12px;border:1px solid #d1d5db;border-radius:9px}
button,.back{display:inline-block;padding:12px 18px;border:0;border-radius:9px;background:#4338ca;color:#fff;font-weight:700;cursor:pointer;text-decoration:none}
.back{background:#e5e7eb;color:#111827;margin-right:8px}.err{background:#fee2e2;color:#991b1b;padding:10px;border-radius:9px;margin-bottom:16px}
small{color:#6b7280}.actions{margin-top:20px}
.student-list{margin-top:24px;background:#fff;border-radius:18px;padding:24px;box-shadow:0 8px 30px #0001}
.student-list h2{margin:0 0 5px}.student-list p{margin:0 0 18px;color:#6b7280}
.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:720px}
th,td{padding:12px 10px;text-align:left;border-bottom:1px solid #e5e7eb;vertical-align:middle}
th{font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;background:#f8fafc}
td{font-size:14px}.student-name{font-weight:700;color:#111827}.muted{color:#6b7280;font-size:13px}
.status{display:inline-block;padding:5px 8px;border-radius:999px;background:#dcfce7;color:#166534;font-size:12px;font-weight:700;white-space:nowrap}
.view-link{display:inline-block;padding:8px 11px;border-radius:7px;background:#4338ca;color:#fff;font-size:13px;font-weight:700;white-space:nowrap}
.verify-link{display:inline-block;margin-left:6px;color:#4338ca;font-size:13px;font-weight:700;white-space:nowrap}
@media(max-width:650px){.grid{grid-template-columns:1fr}.full{grid-column:auto}}
</style>
</head>
<body><div class="wrap"><div class="card">
<h1>Generate Certificate</h1>
<p><small>Enter the student's details. A unique Certificate ID and QR verification link will be created automatically.</small></p>
<?php if($error): ?><div class="err"><?=htmlspecialchars($error)?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
<div class="grid">
<div class="full"><label>Student Name *</label><input name="student_name" required value="<?=htmlspecialchars($_POST['student_name'] ?? '')?>"></div>
<div><label>Enrollment No. *</label><input name="enrollment_no" required value="<?=htmlspecialchars($_POST['enrollment_no'] ?? '')?>"></div>
<div><label>Issue Date *</label><input type="date" name="issue_date" required value="<?=htmlspecialchars($_POST['issue_date'] ?? date('Y-m-d'))?>"></div>
<div class="full"><label>Course Name *</label><input name="course_name" required value="<?=htmlspecialchars($_POST['course_name'] ?? 'DIPLOMA IN OFFICE AUTOMATION & PUBLISHING')?>"></div>
<div class="full"><label>Student Photo</label><input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp"><small>Recommended: passport-size portrait, JPG/PNG/WEBP, max 4 MB.</small></div>
</div>
<div class="actions"><a class="back" href="admin.php">Back</a><button type="submit">Generate Certificate</button></div>
</form>
</div>

<section class="student-list" aria-labelledby="student-list-title">
<h2 id="student-list-title">All Students</h2>
<p><?=count($students)?> certificate<?=count($students) === 1 ? '' : 's'?> available</p>
<?php if ($students): ?>
<div class="table-wrap">
<table>
<thead><tr><th>Student</th><th>Enrollment</th><th>Course</th><th>Issue Date</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($students as $student): ?>
<tr>
<td class="student-name"><?=htmlspecialchars($student['student_name'])?></td>
<td class="muted"><?=htmlspecialchars($student['enrollment_no'])?></td>
<td><?=htmlspecialchars($student['course_name'])?></td>
<td><?=htmlspecialchars(date('d/m/Y', strtotime($student['issue_date'])))?></td>
<td><span class="status"><?=htmlspecialchars($student['status'])?></span></td>
<td>
<a class="view-link" target="_blank" href="certificate.php?id=<?=urlencode($student['certificate_id'])?>">View Details</a>
<a class="verify-link" target="_blank" href="verify.php?id=<?=urlencode($student['certificate_id'])?>">Verify</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php else: ?>
<p>No students have certificates yet.</p>
<?php endif; ?>
</section>
</div></body></html>
