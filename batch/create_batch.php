<?php
include '../database_connection/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $batch_name = $_POST['batch_name'];
    $timing = $_POST['timing'];
    $students = $_POST['students'];

    // Insert batch
    $stmt = $conn->prepare("INSERT INTO batches (batch_name, timing) VALUES (?, ?)");
    $stmt->bind_param("ss", $batch_name, $timing);
    $stmt->execute();
    $batch_id = $conn->insert_id;

    // Insert students to batch
    foreach ($students as $s) {
        list($table, $student_id) = explode(":", $s);
        $student_id = intval($student_id);

        $stmt2 = $conn->prepare("INSERT INTO student_batches (student_id, student_table, batch_id) VALUES (?, ?, ?)");
        $stmt2->bind_param("isi", $student_id, $table, $batch_id);
        $stmt2->execute();
    }

    header("Location: view_batch.php");
    exit;
}

// Fetch students from both tables
$students = [];
$res1 = $conn->query("SELECT student_id, name, enrollment_id, course, 'students' AS table_name FROM students");
while ($row = $res1->fetch_assoc()) { $students[] = $row; }

$res2 = $conn->query("SELECT id AS student_id, name, enrollment_id, course, 'students26' AS table_name FROM students26");
while ($row = $res2->fetch_assoc()) { $students[] = $row; }

// Fetch distinct courses for filter
$courses_res = $conn->query("SELECT DISTINCT course FROM students UNION SELECT DISTINCT course FROM students26");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Batch</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Same CSS from previous create_batch.php */
        body { font-family: Arial, sans-serif; background: #f8fafc; margin:0; padding:0; }
        .container { max-width:1200px; margin:2rem auto; padding:1rem; }
        .card { background:#fff; padding:1.5rem; border-radius:1rem; margin-bottom:1.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.1);}
        .form-control { width:100%; padding:0.75rem 1rem; margin-bottom:1rem; border-radius:0.5rem; border:1px solid #ccc; }
        .student-list { max-height:400px; overflow-y:auto; border:1px solid #ccc; border-radius:0.75rem; }
        .student-item { display:flex; align-items:center; padding:0.75rem 1rem; border-bottom:1px solid #eee; }
        .student-item:last-child { border-bottom:none; }
        .student-info { flex:1; }
        .btn { padding:0.75rem 1.5rem; border:none; border-radius:0.5rem; background:#4361ee; color:#fff; cursor:pointer; width:100%; font-weight:500;}
        .filters { display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
        @media (max-width:768px){ .filters{ flex-direction:column; } }
    </style>
</head>
<body>

<div class="container">
    <h1>Create New Batch</h1>

    <form method="POST">
        <div class="card">
            <h3>Batch Information</h3>
            <input type="text" name="batch_name" placeholder="Batch Name" class="form-control" required>
            <input type="text" name="timing" placeholder="Timing (e.g. Mon-Fri 9-11)" class="form-control" required>
        </div>

        <div class="card">
            <h3>Add Students</h3>

            <div class="filters">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by name or enrollment..." onkeyup="filterStudents()">
                <select id="courseFilter" class="form-control" onchange="filterStudents()">
                    <option value="">All Courses</option>
                    <?php while($course = $courses_res->fetch_assoc()) { ?>
                        <option value="<?= htmlspecialchars($course['course']) ?>"><?= htmlspecialchars($course['course']) ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="student-list" id="studentList">
                <?php foreach($students as $s) { ?>
                    <div class="student-item" data-name="<?= htmlspecialchars($s['name']) ?>" data-enroll="<?= htmlspecialchars($s['enrollment_id']) ?>" data-course="<?= htmlspecialchars($s['course']) ?>">
                        <input type="checkbox" name="students[]" value="<?= $s['table_name'] ?>:<?= $s['student_id'] ?>" onchange="updateSelectedCount()">
                        <div class="student-info">
                            <strong><?= htmlspecialchars($s['name']) ?></strong> (<?= htmlspecialchars($s['enrollment_id']) ?>) - <?= htmlspecialchars($s['course']) ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <p>Selected Students: <span id="selectedCount">0</span></p>
        </div>

        <button type="submit" class="btn">Create Batch</button>
    </form>
</div>

<script>
function filterStudents(){
    let search = document.getElementById('searchInput').value.toLowerCase();
    let course = document.getElementById('courseFilter').value.toLowerCase();
    let items = document.querySelectorAll('.student-item');
    items.forEach(item=>{
        let name = item.dataset.name.toLowerCase();
        let enroll = item.dataset.enroll.toLowerCase();
        let courseVal = item.dataset.course.toLowerCase();
        if( (name.includes(search) || enroll.includes(search)) && (course=="" || courseVal==course) ){
            item.style.display='flex';
        } else {
            item.style.display='none';
        }
    });
    updateSelectedCount();
}

function updateSelectedCount(){
    let count = document.querySelectorAll('.student-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count;
}

document.addEventListener('DOMContentLoaded', updateSelectedCount);
</script>

</body>
</html>
