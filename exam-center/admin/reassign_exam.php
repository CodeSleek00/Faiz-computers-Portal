<?php
require '../../database_connection/db_connect.php';

/* ================= VALIDATE EXAM ID ================= */
$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
if ($exam_id <= 0) {
    die("Invalid Exam ID");
}

/* ================= FETCH ALL STUDENTS (MERGED) ================= */
/*
 We normalize data:
 enrollment_id = COMMON KEY
*/
$students = $conn->query("
    SELECT enrollment_id, name FROM students
    UNION
    SELECT enrollment_id, name FROM students26
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
        // Remove old assignments
        $stmt = $conn->prepare("DELETE FROM exam_assignments WHERE exam_id = ?");
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();

        /* ========= ASSIGN TO STUDENTS ========= */
        if ($assign_type === 'student' && !empty($_POST['enrollment_ids'])) {

            $stmt = $conn->prepare("
                INSERT INTO exam_assignments (exam_id, enrollment_id)
                VALUES (?, ?)
            ");

            foreach ($_POST['enrollment_ids'] as $enroll) {
                $stmt->bind_param("is", $exam_id, $enroll);
                $stmt->execute();
                $count++;
            }
        }

        /* ========= ASSIGN TO BATCHES ========= */
        elseif ($assign_type === 'batch' && !empty($_POST['batch_ids'])) {

            $stmt = $conn->prepare("
                INSERT INTO exam_assignments (exam_id, batch_id)
                VALUES (?, ?)
            ");

            foreach ($_POST['batch_ids'] as $batch_id) {
                $stmt->bind_param("ii", $exam_id, $batch_id);
                $stmt->execute();
                $count++;
            }
        }

        /* ========= ASSIGN TO ALL ========= */
        elseif ($assign_type === 'all') {

            $conn->query("
                INSERT INTO exam_assignments (exam_id, enrollment_id)
                SELECT $exam_id, enrollment_id FROM students
                UNION
                SELECT $exam_id, enrollment_id FROM students26
            ");

            $count = $conn->affected_rows;
        }

        $message = "✅ Exam successfully assigned to $count record(s)";

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
        body{font-family:Poppins;background:#f1f5f9;padding:20px}
        .box{background:#fff;padding:25px;border-radius:10px;max-width:800px;margin:auto}
        h2{text-align:center;color:#4f46e5}
        label{font-weight:600;margin-top:15px;display:block}
        select,input{width:100%;padding:10px;margin-top:5px}
        .list{border:1px solid #ddd;padding:10px;margin-top:10px;max-height:250px;overflow:auto}
        .btn{margin-top:20px;background:#4f46e5;color:#fff;padding:12px;border:none;width:100%;cursor:pointer}
        .msg{margin-bottom:15px;padding:10px;background:#dcfce7;color:#166534}
        .err{background:#fee2e2;color:#b91c1c}
    </style>
</head>
<body>

<div class="box">
<h2>Re-Assign Exam</h2>

<?php if($message): ?>
<div class="<?= str_contains($message,'Error')?'err':'msg' ?>">
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

<div id="students" style="display:none">
<label>Select Students</label>
<div class="list">
<?php while($s=$students->fetch_assoc()): ?>
<div>
<input type="checkbox" name="enrollment_ids[]" value="<?= $s['enrollment_id'] ?>">
<?= htmlspecialchars($s['name']) ?> (<?= $s['enrollment_id'] ?>)
</div>
<?php endwhile; ?>
</div>
</div>

<div id="batches" style="display:none">
<label>Select Batches</label>
<div class="list">
<?php while($b=$batches->fetch_assoc()): ?>
<div>
<input type="checkbox" name="batch_ids[]" value="<?= $b['batch_id'] ?>">
<?= htmlspecialchars($b['batch_name']) ?>
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
</script>

</body>
</html>
<?php $conn->close(); ?>
