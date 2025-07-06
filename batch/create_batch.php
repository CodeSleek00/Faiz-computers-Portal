<?php
include '../database_connection/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $batch_name = $_POST['batch_name'];
    $timing = $_POST['timing'];
    $students = $_POST['students']; // Array of student IDs

    $stmt = $conn->prepare("INSERT INTO batches (batch_name, timing) VALUES (?, ?)");
    $stmt->bind_param("ss", $batch_name, $timing);
    $stmt->execute();
    $batch_id = $conn->insert_id;

    foreach ($students as $student_id) {
        $stmt2 = $conn->prepare("INSERT INTO student_batches (student_id, batch_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $student_id, $batch_id);
        $stmt2->execute();
    }

    header("Location: view_batches.php");
    exit;
}

// Fetch students & courses
$students = $conn->query("SELECT * FROM students ORDER BY name ASC");
$courses = $conn->query("SELECT DISTINCT course FROM students");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Batch</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            padding: 40px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 0 15px rgba(0,0,0,0.08);
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
        }
        label {
            font-weight: 600;
            margin-top: 10px;
            display: block;
        }
        input, select {
            padding: 10px;
            width: 100%;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-family: 'Poppins', sans-serif;
        }
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .student-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 10px;
        }
        .student-row {
            padding: 5px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        .student-row:last-child {
            border-bottom: none;
        }
        button {
            background: #007bff;
            color: white;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            width: 100%;
            cursor: pointer;
            font-weight: 600;
        }
        button:hover {
            background: #0056b3;
        }
    </style>

    <script>
        function filterStudents() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const course = document.getElementById('courseFilter').value.toLowerCase();
            const rows = document.querySelectorAll('.student-row');

            rows.forEach(row => {
                const name = row.dataset.name.toLowerCase();
                const enroll = row.dataset.enroll.toLowerCase();
                const courseVal = row.dataset.course.toLowerCase();

                const matchNameOrEnroll = name.includes(search) || enroll.includes(search);
                const matchCourse = course === "" || courseVal === course;

                if (matchNameOrEnroll && matchCourse) {
                    row.style.display = 'flex';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</head>
<body>

<div class="container">
    <h2>Create New Batch</h2>
    <form method="POST">
        <label>Batch Name:</label>
        <input type="text" name="batch_name" required>

        <label>Timing:</label>
        <input type="text" name="timing" required>

        <div class="filter-bar">
            <input type="text" id="searchInput" onkeyup="filterStudents()" placeholder="Search by name or enrollment ID">
            <select id="courseFilter" onchange="filterStudents()">
                <option value="">All Courses</option>
                <?php while ($course = $courses->fetch_assoc()) { ?>
                    <option value="<?= htmlspecialchars($course['course']) ?>"><?= htmlspecialchars($course['course']) ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="student-list">
            <?php while ($row = $students->fetch_assoc()) { ?>
                <div class="student-row" 
                     data-name="<?= htmlspecialchars($row['name']) ?>" 
                     data-enroll="<?= htmlspecialchars($row['enrollment_id']) ?>" 
                     data-course="<?= htmlspecialchars($row['course']) ?>">
                    <input type="checkbox" name="students[]" value="<?= $row['student_id'] ?>" style="margin-right: 10px;">
                    <?= $row['name'] ?> (<?= $row['enrollment_id'] ?>) - <?= $row['course'] ?>
                </div>
            <?php } ?>
        </div>

        <br>
        <button type="submit">Create Batch</button>
    </form>
</div>

</body>
</html>
