<?php
require '../../database_connection/db_connect.php';

/* ================= VALIDATE EXAM ID ================= */
$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
if ($exam_id <= 0) {
    die("Invalid Exam ID");
}

/* ================= FETCH ALL STUDENTS ================= */
$students = $conn->query("
    SELECT student_id AS sid, name, photo, 'students' AS student_table FROM students
    UNION ALL
    SELECT id AS sid, name, photo, 'students26' AS student_table FROM students26
    ORDER BY name ASC
");

/* ================= FETCH BATCHES ================= */
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");

/* ================= FORM SUBMIT ================= */
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $assign_type = $_POST['assign_type'] ?? '';
    $count = 0;

    try {

       

        /* ================= ASSIGN TO STUDENTS ================= */
        if ($assign_type === 'student' && !empty($_POST['students_assign'])) {

            $stmt = $conn->prepare("
                INSERT INTO exam_assignments (exam_id, student_id, student_table)
                VALUES (?, ?, ?)
            ");

            foreach ($_POST['students_assign'] as $val) {
                list($sid, $table) = explode('|', $val);
                $stmt->bind_param("iis", $exam_id, $sid, $table);
                $stmt->execute();
                $count++;
            }
        }

        /* ================= ASSIGN TO BATCHES ================= */
        elseif ($assign_type === 'batch' && !empty($_POST['batch_ids'])) {

            $stmt = $conn->prepare("
                INSERT INTO exam_assignments (exam_id, student_id, student_table)
                VALUES (?, ?, ?)
            ");

            foreach ($_POST['batch_ids'] as $batch_id) {

                $batch_students = $conn->query("
                    SELECT student_id AS sid, 'students' AS student_table
                    FROM students WHERE batch_id=$batch_id

                    UNION ALL

                    SELECT id AS sid, 'students26' AS student_table
                    FROM students26 WHERE batch_id=$batch_id
                ");

                while ($s = $batch_students->fetch_assoc()) {
                    $stmt->bind_param("iis", $exam_id, $s['sid'], $s['student_table']);
                    $stmt->execute();
                    $count++;
                }
            }
        }

        /* ================= ASSIGN TO ALL ================= */
        elseif ($assign_type === 'all') {

            $conn->query("
                INSERT INTO exam_assignments (exam_id, student_id, student_table)
                SELECT $exam_id, student_id, 'students' FROM students
                UNION ALL
                SELECT $exam_id, id, 'students26' FROM students26
            ");

            $count = $conn->affected_rows;
        }

        $message = "✅ Exam successfully assigned to $count student(s)";

    } catch (Exception $e) {
        $message = "❌ Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Re-Assign Exam</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body{font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);padding:20px;margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center}
.box{background:#fff;padding:30px;border-radius:15px;max-width:900px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.1);border:1px solid #e5e7eb}
h2{text-align:center;color:#4f46e5;margin-bottom:30px;font-size:28px;font-weight:600}
label{font-weight:600;margin-top:20px;display:block;color:#374151;font-size:16px}
select{width:100%;padding:12px;margin-top:8px;border:1px solid #d1d5db;border-radius:8px;font-size:16px;background:#fff}
.list{border:1px solid #ddd;padding:15px;margin-top:10px;max-height:300px;overflow:auto;border-radius:8px;background:#f9fafb}
.student-item{display:flex;align-items:center;margin-bottom:10px;padding:10px;border-radius:8px;background:#fff;border:1px solid #e5e7eb;transition:background 0.2s}
.student-item:hover{background:#f3f4f6}
.student-photo{width:40px;height:40px;border-radius:50%;margin-right:15px;object-fit:cover;border:2px solid #e5e7eb}
.student-info{flex:1}
.student-name{font-weight:500;color:#111827}
.student-table{color:#6b7280;font-size:14px}
.btn{margin-top:25px;background:linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);color:#fff;padding:14px;border:none;width:100%;cursor:pointer;border-radius:8px;font-size:16px;font-weight:600;transition:transform 0.2s}
.btn:hover{transform:translateY(-2px)}
.msg{margin-bottom:20px;padding:12px;background:#dcfce7;color:#166534;border-radius:8px;border:1px solid #bbf7d0}
.err{margin-bottom:20px;padding:12px;background:#fee2e2;color:#b91c1c;border-radius:8px;border:1px solid #fecaca}
.search-bar{margin-top:15px}
.search-bar input{width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;font-size:16px}
</style>
</head>

<body>

<div class="box">
<h2>Re-Assign Exam</h2>

<?php if($message): ?>
<div class="<?= str_contains($message,'Error') ? 'err':'msg' ?>">
<?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<form method="post">

<label>Assignment Type</label>
<select name="assign_type" onchange="toggle(this.value)" required>
    <option value="">-- Select --</option>
    <option value="student">Specific Students</option>
    <option value="batch">Batch</option>
    <option value="all">All Students</option>
</select>

<!-- STUDENTS -->
<div id="students" style="display:none">
<label>Select Students</label>
<div class="search-bar">
<input type="text" id="studentSearch" placeholder="Search students..." onkeyup="filterStudents()">
</div>
<div class="list" id="studentList">
<?php while($s=$students->fetch_assoc()): ?>
<div class="student-item">
<input type="checkbox" name="students_assign[]" value="<?= $s['sid'] ?>|<?= $s['student_table'] ?>" style="margin-right:15px">
<?php if(!empty($s['photo'])): ?>
<img src="../../uploads/<?= htmlspecialchars($s['photo']) ?>" alt="Photo" class="student-photo">
<?php else: ?>
<img src="https://via.placeholder.com/40" alt="No Photo" class="student-photo">
<?php endif; ?>
<div class="student-info">
<div class="student-name"><?= htmlspecialchars($s['name']) ?></div>
<div class="student-table">(<?= $s['student_table'] ?>)</div>
</div>
</div>
<?php endwhile; ?>
</div>
</div>

<!-- BATCHES -->
<div id="batches" style="display:none">
<label>Select Batches</label>
<div class="list">
<?php while($b=$batches->fetch_assoc()): ?>
<div class="student-item" style="padding:15px;">
<input type="checkbox" name="batch_ids[]" value="<?= $b['batch_id'] ?>" style="margin-right:15px">
<div class="student-info">
<div class="student-name"><?= htmlspecialchars($b['batch_name']) ?></div>
</div>
</div>
<?php endwhile; ?>
</div>
</div>

<button class="btn">Re-Assign Exam</button>

</form>
</div>

<script>
function toggle(type){
    document.getElementById('students').style.display = (type==='student')?'block':'none';
    document.getElementById('batches').style.display = (type==='batch')?'block':'none';
}

function filterStudents() {
    const search = document.getElementById('studentSearch').value.toLowerCase();
    const items = document.querySelectorAll('.student-item');
    items.forEach(item => {
        const name = item.querySelector('.student-name').textContent.toLowerCase();
        item.style.display = name.includes(search) ? '' : 'none';
    });
}
</script>

</body>
</html>

<?php $conn->close(); ?>
