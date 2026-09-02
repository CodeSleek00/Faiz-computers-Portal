<?php
require_once __DIR__ . '/auth.php';
require_admin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = trim((string)($_POST['student_name'] ?? ''));
    $enrollment = trim((string)($_POST['enrollment_no'] ?? ''));
    $course = trim((string)($_POST['course_name'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
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
        } elseif (!empty($_POST['existing_photo'])) {
            $existingPhoto = basename((string)$_POST['existing_photo']);
            $existingPhotoFile = __DIR__ . '/../uploads/' . $existingPhoto;
            if (is_file($existingPhotoFile)) {
                $photoPath = '../uploads/' . $existingPhoto;
            }
        }

        if ($error === '') {
            $certificateId = 'CSF-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $stmt = $pdo->prepare(
                "INSERT INTO certificates
                (certificate_id,student_name,enrollment_no,course_name,description,photo,issue_date,status)
                VALUES (?,?,?,?,?,?,?,'COURSE COMPLETED')"
            );
            $stmt->execute([$certificateId,$studentName,$enrollment,$course,$description,$photoPath,$issueDate]);
            header('Location: certificate.php?id=' . urlencode($certificateId));
            exit;
        }
    }
}

$students = $pdo->query(
    "SELECT s.id, s.name, s.enrollment_id, s.course, s.photo,
            a.course_name
     FROM students26 s
     LEFT JOIN admission a ON a.enrollment_id = s.enrollment_id
     ORDER BY s.id DESC"
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
input,textarea{width:100%;padding:12px;border:1px solid #d1d5db;border-radius:9px;font:inherit;resize:vertical}
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
.select-link{display:inline-block;padding:8px 11px;border:0;border-radius:7px;background:#0f766e;color:#fff;font-size:13px;font-weight:700;white-space:nowrap;cursor:pointer}
@media(max-width:650px){.grid{grid-template-columns:1fr}.full{grid-column:auto}}
</style>
</head>
<body><div class="wrap"><div class="card">
<h1>Generate Certificate</h1>
<p><small>Enter the student's details. A unique Certificate ID and QR verification link will be created automatically.</small></p>
<?php if($error): ?><div class="err"><?=htmlspecialchars($error)?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
<div class="grid">
<div class="full"><label>Student Name *</label><input id="student_name" name="student_name" required value="<?=htmlspecialchars($_POST['student_name'] ?? '')?>"></div>
<div><label>Enrollment No. *</label><input id="enrollment_no" name="enrollment_no" required value="<?=htmlspecialchars($_POST['enrollment_no'] ?? '')?>"></div>
<div><label>Issue Date *</label><input type="date" name="issue_date" required value="<?=htmlspecialchars($_POST['issue_date'] ?? date('Y-m-d'))?>"></div>
<div class="full"><label>Course Name *</label><input id="course_name" name="course_name" required value="<?=htmlspecialchars($_POST['course_name'] ?? 'DIPLOMA IN OFFICE AUTOMATION & PUBLISHING')?>"></div>
<div class="full"><label>Description</label><textarea id="description" name="description" maxlength="180" rows="3" placeholder="Write a short description for the certificate..."><?=htmlspecialchars($_POST['description'] ?? '')?></textarea><small>This description will be printed below the course name.</small></div>
<div class="full"><label>Student Photo</label><input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp"><small>Recommended: passport-size portrait, JPG/PNG/WEBP, max 4 MB.</small></div>
<input type="hidden" id="existing_photo" name="existing_photo" value="">
</div>
<div class="actions"><a class="back" href="admin.php">Back</a><button type="submit">Generate Certificate</button></div>
</form>
</div>

<section class="student-list" aria-labelledby="student-list-title">
<h2 id="student-list-title">All Students</h2>
<p><?=count($students)?> student<?=count($students) === 1 ? '' : 's'?> available from students26</p>
<?php if ($students): ?>
<div class="table-wrap">
<table>
<thead><tr><th>Student</th><th>Enrollment</th><th>Course</th><th>Photo</th><th>Action</th></tr></thead>
<tbody>
<?php foreach ($students as $student): ?>
<tr>
<td class="student-name"><?=htmlspecialchars($student['name'])?></td>
<td class="muted"><?=htmlspecialchars($student['enrollment_id'])?></td>
<td><?=htmlspecialchars($student['course_name'] ?: $student['course'])?></td>
<td><?=empty($student['photo']) ? 'Not uploaded' : 'Available'?></td>
<td>
<button type="button" class="select-link" data-name="<?=htmlspecialchars($student['name'], ENT_QUOTES)?>" data-enrollment="<?=htmlspecialchars($student['enrollment_id'], ENT_QUOTES)?>" data-course="<?=htmlspecialchars($student['course'], ENT_QUOTES)?>" data-course-name="<?=htmlspecialchars($student['course_name'] ?: $student['course'], ENT_QUOTES)?>" data-photo="<?=htmlspecialchars($student['photo'] ?? '', ENT_QUOTES)?>">Use Details</button>
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
</div>
<script>
document.querySelectorAll('.select-link').forEach(function (button) {
    button.addEventListener('click', function () {
        document.getElementById('student_name').value = button.dataset.name || '';
        document.getElementById('enrollment_no').value = button.dataset.enrollment || '';
        document.getElementById('course_name').value = button.dataset.courseName || button.dataset.course || '';
        document.getElementById('description').value = '';
        document.getElementById('existing_photo').value = button.dataset.photo || '';
        document.getElementById('student_name').scrollIntoView({behavior: 'smooth', block: 'center'});
        document.getElementById('student_name').focus();
    });
});
</script>
</body></html>
