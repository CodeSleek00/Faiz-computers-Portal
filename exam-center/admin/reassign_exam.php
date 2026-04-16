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
<link rel="stylesheet" href="../../css/global-theme.css">
<style>
body{background: var(--color-gray-50); padding:20px; display:flex; align-items:center; justify-content:center; min-height:100vh;}
.box{background: var(--color-white); padding:30px; border-radius:12px; max-width:900px; width:100%; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border: 1px solid var(--color-border);}
h2{text-align:center; color: var(--color-blue-primary); margin-bottom:30px; font-size:28px; font-weight:600;}
label{font-weight:600; margin-top:20px; display:block; color: var(--color-text-dark); font-size:16px;}
select{width:100%; padding:12px; margin-top:8px; border:1px solid var(--color-border); border-radius:8px; font-size:16px; background: var(--color-white);}
.list{border:1px solid var(--color-border); padding:15px; margin-top:10px; max-height:300px; overflow:auto; border-radius:8px; background: var(--color-gray-50);}
.student-item{display:flex; align-items:center; margin-bottom:10px; padding:12px; border-radius:8px; background: var(--color-white); border:1px solid var(--color-gray-200); transition: background 0.2s;}
.student-item:hover{background: var(--color-gray-100);}
.student-photo{width:45px; height:45px; border-radius:50%; margin-right:15px; object-fit:cover; border:2px solid var(--color-blue-light);}
.student-info{flex:1;}
.student-name{font-weight:500; color: var(--color-text-dark);}
.student-table{color: var(--color-text-light); font-size:14px;}
.btn{margin-top:25px; background: var(--color-blue-primary); color: var(--color-white); padding:14px; border:none; width:100%; cursor:pointer; border-radius:8px; font-size:16px; font-weight:600; transition: background 0.2s;}
.btn:hover{background: var(--color-blue-dark);}
.msg{margin-bottom:20px; padding:12px; background: var(--color-success); color: var(--color-white); border-radius:8px; border:1px solid var(--color-success);}
.err{margin-bottom:20px; padding:12px; background: var(--color-danger); color: var(--color-white); border-radius:8px; border:1px solid var(--color-danger);}
.search-bar{margin-top:15px;}
.search-bar input{width:100%; padding:12px; border:1px solid var(--color-border); border-radius:8px; font-size:16px;}
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
