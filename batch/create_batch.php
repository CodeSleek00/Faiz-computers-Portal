<?php
include '../database_connection/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $batch_name = $_POST['batch_name'];
    $timing = $_POST['timing'];
    $students = $_POST['students'];

    $stmt = $conn->prepare("INSERT INTO batches (batch_name, timing) VALUES (?, ?)");
    $stmt->bind_param("ss", $batch_name, $timing);
    $stmt->execute();
    $batch_id = $conn->insert_id;

    foreach ($students as $student_id) {
        $stmt2 = $conn->prepare("INSERT INTO student_batches (student_id, batch_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $student_id, $batch_id);
        $stmt2->execute();
    }

    header("Location: view_batch.php");
    exit;
}

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
            background: #f2f4f8;
            margin: 0;
            padding: 40px 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.06);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            color: #333;
        }

        label {
            display: block;
            font-weight: 600;
            margin: 20px 0 8px;
        }

        input, select {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin: 25px 0 15px;
        }

        .filters input,
        .filters select {
            flex: 1;
            min-width: 200px;
        }

        .student-list {
            max-height: 320px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 12px;
            background: #fafafa;
        }

        .student-item {
            display: flex;
            align-items: center;
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
        }

        .student-item:hover {
            background: #f0f4ff;
        }

        .student-item:last-child {
            border-bottom: none;
        }

        .student-item input[type="checkbox"] {
            margin-right: 12px;
            transform: scale(1.2);
            cursor: pointer;
        }

        button {
            width: 100%;
            margin-top: 30px;
            padding: 15px;
            font-size: 16px;
            background-color: #007bff;
            border: none;
            color: #fff;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        @media screen and (max-width: 768px) {
            .filters {
                flex-direction: column;
            }
        }
    </style>

    <script>
        function filterStudents() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const course = document.getElementById('courseFilter').value.toLowerCase();
            const items = document.querySelectorAll('.student-item');

            items.forEach(item => {
                const name = item.dataset.name.toLowerCase();
                const enroll = item.dataset.enroll.toLowerCase();
                const courseVal = item.dataset.course.toLowerCase();

                const matchText = name.includes(search) || enroll.includes(search);
                const matchCourse = course === "" || courseVal === course;

                if (matchText && matchCourse) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
</head>
<body>

<div class="container">
    <h2>Create New Student Batch</h2>
    <form method="POST">
        <label>Batch Name:</label>
        <input type="text" name="batch_name" required>

        <label>Timing:</label>
        <input type="text" name="timing" placeholder="e.g. Mon-Fri 9:00 AM - 11:00 AM" required>

        <div class="filters">
            <input type="text" id="searchInput" placeholder="Search by name or enrollment ID" onkeyup="filterStudents()">
            <select id="courseFilter" onchange="filterStudents()">
                <option value="">Filter by course</option>
                <?php while ($course = $courses->fetch_assoc()) { ?>
                    <option value="<?= htmlspecialchars($course['course']) ?>"><?= htmlspecialchars($course['course']) ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="student-list">
            <?php while ($row = $students->fetch_assoc()) { ?>
                <div class="student-item" 
                     data-name="<?= htmlspecialchars($row['name']) ?>" 
                     data-enroll="<?= htmlspecialchars($row['enrollment_id']) ?>" 
                     data-course="<?= htmlspecialchars($row['course']) ?>">
                    <input type="checkbox" name="students[]" value="<?= $row['student_id'] ?>">
                    <?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['enrollment_id']) ?>) — <?= htmlspecialchars($row['course']) ?>
                </div>
            <?php } ?>
        </div>

        <button type="submit">Create Batch</button>
    </form>
</div>

</body>
</html>
