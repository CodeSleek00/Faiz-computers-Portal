<?php
include '../database_connection/db_connect.php';

// Get all courses for filter dropdown
$course_result = $conn->query("SELECT DISTINCT course FROM my_student ORDER BY course ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Students - Faiz Computer Institute</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #f0f4f8;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
        }

        .controls {
            max-width: 1100px;
            margin: auto;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: space-between;
        }

        .controls input,
        .controls select {
            padding: 10px 14px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 0.95rem;
            width: 100%;
            max-width: 300px;
        }

        .table-container {
            max-width: 1100px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th, td {
            text-align: left;
            padding: 12px 16px;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #eef5ff;
        }

        img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #3498db;
        }

        .actions a {
            text-decoration: none;
            margin-right: 8px;
            font-size: 0.9rem;
            padding: 6px 10px;
            border-radius: 4px;
            font-weight: 500;
        }

        .actions a:nth-child(1) { background-color: #2ecc71; color: white; }
        .actions a:nth-child(2) { background-color: #f1c40f; color: #333; }
        .actions a:nth-child(3) { background-color: #e74c3c; color: white; }

        @media (max-width: 768px) {
            body { padding: 15px; }
            .controls { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>

<h2>Student Records</h2>

<div class="controls">
    <input type="text" id="searchInput" placeholder="Search by name, ID, Aadhar, course, phone...">
    <select id="courseFilter">
        <option value="">All Courses</option>
        <?php while($course = $course_result->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($course['course']) ?>"><?= htmlspecialchars($course['course']) ?></option>
        <?php endwhile; ?>
    </select>
</div>

<div class="table-container" id="studentsTable">
    <!-- Table data will be loaded here -->
</div>

<script>
// Fetch table on page load
document.addEventListener("DOMContentLoaded", () => {
    fetchStudents();
});

// Live search + filter
document.getElementById('searchInput').addEventListener('input', fetchStudents);
document.getElementById('courseFilter').addEventListener('change', fetchStudents);

function fetchStudents() {
    const query = document.getElementById('searchInput').value;
    const course = document.getElementById('courseFilter').value;

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "fetch_students.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        document.getElementById("studentsTable").innerHTML = this.responseText;
    };
    xhr.send("query=" + encodeURIComponent(query) + "&course=" + encodeURIComponent(course));
}
</script>

</body>
</html>
