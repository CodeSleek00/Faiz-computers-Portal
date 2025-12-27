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
            "INSERT IGNORE INTO student_batches (student_id, student_table, batch_id)
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
   FETCH STUDENTS (students + students26)
========================= */
$students = $conn->query("
    SELECT student_id, name, enrollment_id, course, 'students' AS student_table
    FROM students

    UNION ALL

    SELECT student_id, name, enrollment_id, course, 'students26' AS student_table
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
    <link rel="icon" type="image/png" href="image.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
:root {
    --primary:#4361ee;
    --primary-light:#e0e7ff;
    --secondary:#3f37c9;
    --text:#1e293b;
    --text-light:#64748b;
    --border:#e2e8f0;
    --bg:#f8fafc;
    --card:#ffffff;
}

body {
    font-family:Inter,sans-serif;
    background:var(--bg);
    margin:0;
}

.container {
    max-width:1200px;
    margin:auto;
    padding:2rem;
}

.header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:2rem;
}

.card {
    background:var(--card);
    border-radius:1rem;
    padding:2rem;
    margin-bottom:2rem;
    box-shadow:0 4px 10px rgba(0,0,0,.05);
}

.form-grid {
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:1.5rem;
}

.form-control {
    width:100%;
    padding:.75rem 1rem;
    border:1px solid var(--border);
    border-radius:.5rem;
}

.student-list {
    max-height:420px;
    overflow-y:auto;
    border:1px solid var(--border);
    border-radius:.75rem;
}

.student-item {
    display:flex;
    align-items:center;
    padding:1rem;
    border-bottom:1px solid var(--border);
}

.student-item:hover {
    background:var(--primary-light);
}

.student-checkbox {
    margin-right:1rem;
    width:18px;
    height:18px;
    accent-color:var(--primary);
}

.btn {
    background:var(--primary);
    color:#fff;
    padding:1rem;
    border:none;
    width:100%;
    border-radius:.5rem;
    font-size:1rem;
    cursor:pointer;
}

@media(max-width:768px){
    .form-grid{grid-template-columns:1fr;}
}
</style>

<script>
function filterStudents(){
    let s = document.getElementById('search').value.toLowerCase();
    let c = document.getElementById('course').value.toLowerCase();

    document.querySelectorAll('.student-item').forEach(item=>{
        let name = item.dataset.name;
        let enroll = item.dataset.enroll;
        let course = item.dataset.course;

        let show =
            (name.includes(s) || enroll.includes(s)) &&
            (c === "" || course === c);

        item.style.display = show ? "flex" : "none";
    });
}
</script>
</head>

<body>
<div class="container">

<div class="header">
    <h2>Create New Batch</h2>
    <a href="view_batch.php">← Back</a>
</div>

<form method="POST">

<div class="card">
    <div class="form-grid">
        <input type="text" name="batch_name" class="form-control" placeholder="Batch Name" required>
        <input type="text" name="timing" class="form-control" placeholder="Timing" required>
    </div>
</div>

<div class="card">
    <div class="form-grid">
        <input type="text" id="search" class="form-control" placeholder="Search name / enrollment" onkeyup="filterStudents()">

        <select id="course" class="form-control" onchange="filterStudents()">
            <option value="">All Courses</option>
            <?php while($c = $courses->fetch_assoc()){ ?>
                <option value="<?= strtolower($c['course']) ?>"><?= $c['course'] ?></option>
            <?php } ?>
        </select>
    </div>

    <br>

    <div class="student-list">
        <?php while($row = $students->fetch_assoc()){ ?>
            <div class="student-item"
                 data-name="<?= strtolower($row['name']) ?>"
                 data-enroll="<?= strtolower($row['enrollment_id']) ?>"
                 data-course="<?= strtolower($row['course']) ?>">

                <input type="checkbox"
                       class="student-checkbox"
                       name="students[]"
                       value="<?= $row['student_id'].'|'.$row['student_table'] ?>">

                <div>
                    <strong><?= $row['name'] ?></strong><br>
                    <?= $row['enrollment_id'] ?> | <?= $row['course'] ?> |
                    <small><?= $row['student_table'] ?></small>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<button class="btn">
    <i class="fas fa-plus-circle"></i> Create Batch
</button>

</form>
</div>
</body>
</html>
