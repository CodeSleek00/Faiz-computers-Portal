<?php
include '../database_connection/db_connect.php';

/* =========================
   FORM SUBMIT LOGIC
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $batch_name = trim($_POST['batch_name']);
    $timing     = trim($_POST['timing']);
    $students   = $_POST['students'] ?? [];

    if ($batch_name === "" || $timing === "") {
        die("Batch name and timing are required");
    }

    if (count($students) === 0) {
        die("Please select at least one student");
    }

    $conn->begin_transaction();

    try {
        // Insert batch
        $stmt = $conn->prepare(
            "INSERT INTO batches (batch_name, timing) VALUES (?, ?)"
        );
        $stmt->bind_param("ss", $batch_name, $timing);
        $stmt->execute();

        $batch_id = $conn->insert_id;

        // Insert students into batch
        $stmt2 = $conn->prepare(
            "INSERT IGNORE INTO student_batches 
            (student_id, student_table, batch_id)
            VALUES (?, ?, ?)"
        );

        foreach ($students as $data) {
            list($student_id, $student_table) = explode('|', $data);
            $stmt2->bind_param("isi", $student_id, $student_table, $batch_id);
            $stmt2->execute();
        }

        $conn->commit();
        header("Location: view_batch.php?success=1");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}

/* =========================
   FETCH STUDENTS (FIXED IDS)
========================= */
$students = $conn->query("
    SELECT 
        student_id AS student_id,
        name,
        enrollment_id,
        course,
        'students' AS student_table
    FROM students

    UNION ALL

    SELECT
        id AS student_id,
        name,
        enrollment_id,
        course,
        'students26' AS student_table
    FROM students26

    ORDER BY name ASC
");

$courses = $conn->query("
    SELECT DISTINCT course FROM (
        SELECT course FROM students
        UNION ALL
        SELECT course FROM students26
    ) x
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Batch</title>
<link rel="icon" href="image.png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
body{font-family:Inter;background:#f8fafc;margin:0}
.container{max-width:1200px;margin:auto;padding:2rem}
.card{background:#fff;padding:2rem;border-radius:1rem;margin-bottom:1.5rem}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.form-control{padding:.75rem;border:1px solid #ddd;border-radius:.5rem;width:100%}
.student-list{max-height:420px;overflow:auto;border:1px solid #ddd;border-radius:.75rem}
.student-item{display:flex;gap:1rem;padding:1rem;border-bottom:1px solid #eee}
.student-item:hover{background:#eef2ff}
.btn{background:#4361ee;color:#fff;padding:1rem;border:none;border-radius:.5rem;width:100%}
@media(max-width:768px){.form-grid{grid-template-columns:1fr}}
</style>

<script>
function filterStudents(){
    let s=document.getElementById('search').value.toLowerCase();
    let c=document.getElementById('course').value.toLowerCase();

    document.querySelectorAll('.student-item').forEach(i=>{
        let name=i.dataset.name;
        let enroll=i.dataset.enroll;
        let course=i.dataset.course;

        let show=(name.includes(s)||enroll.includes(s)) &&
                 (c==""||course==c);

        i.style.display=show?"flex":"none";
    });
}
</script>
</head>

<body>
<div class="container">

<h2>Create Batch</h2>

<form method="POST">

<div class="card">
    <div class="form-grid">
        <input class="form-control" name="batch_name" placeholder="Batch Name" required>
        <input class="form-control" name="timing" placeholder="Timing" required>
    </div>
</div>

<div class="card">
    <div class="form-grid">
        <input id="search" class="form-control" placeholder="Search student" onkeyup="filterStudents()">
        <select id="course" class="form-control" onchange="filterStudents()">
            <option value="">All Courses</option>
            <?php while($c=$courses->fetch_assoc()){ ?>
                <option value="<?= strtolower($c['course']) ?>">
                    <?= $c['course'] ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <br>

    <div class="student-list">
        <?php while($row=$students->fetch_assoc()){ ?>
            <div class="student-item"
                 data-name="<?= strtolower($row['name']) ?>"
                 data-enroll="<?= strtolower($row['enrollment_id']) ?>"
                 data-course="<?= strtolower($row['course']) ?>">

                <input type="checkbox"
                       name="students[]"
                       value="<?= $row['student_id'].'|'.$row['student_table'] ?>">

                <div>
                    <strong><?= $row['name'] ?></strong><br>
                    <?= $row['enrollment_id'] ?> | <?= $row['course'] ?>
                    <small>(<?= $row['student_table'] ?>)</small>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<button class="btn">Create Batch</button>

</form>
</div>
</body>
</html>
