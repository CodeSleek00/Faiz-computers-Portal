<?php
include '../database_connection/db_connect.php';

// Get courses for filter dropdown
$course_result = $conn->query("SELECT DISTINCT course FROM my_student ORDER BY course ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Records</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      margin: 0;
      padding: 40px 20px;
      background-color: #f9fafb;
      color: #333;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    h2 {
      text-align: center;
      margin-bottom: 30px;
      font-size: 2rem;
      font-weight: 600;
      color: #222;
    }

    .controls {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .controls input,
    .controls select {
      padding: 10px 14px;
      font-size: 0.95rem;
      border-radius: 8px;
      border: 1px solid #ccc;
      width: 100%;
      max-width: 300px;
      background-color: #fff;
    }

    .table-wrapper {
      background: #ffffff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 900px;
    }

    th, td {
      text-align: left;
      padding: 12px 14px;
    }

    th {
      background-color: #f0f4f8;
      font-weight: 600;
      color: #444;
    }

    tr:nth-child(even) {
      background-color: #fafafa;
    }

    tr:hover {
      background-color: #f0f8ff;
    }

    .photo {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #ddd;
    }

    .actions a {
      padding: 6px 12px;
      margin-right: 6px;
      font-size: 0.85rem;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 500;
    }

    .actions a.view { background-color: #e1f7e6; color: #2e7d32; }
    .actions a.edit { background-color: #fff7d6; color: #b18800; }
    .actions a.delete { background-color: #fdecea; color: #c62828; }

    @media (max-width: 768px) {
      .controls {
        flex-direction: column;
        align-items: stretch;
      }

      .controls input,
      .controls select {
        max-width: 100%;
      }
    }
  </style>
</head>
<body>
<div class="container">
  <h2>Student Records</h2>

  <div class="controls">
    <input type="text" id="searchInput" placeholder="Search by name, ID, Aadhar, etc.">
    <select id="courseFilter">
      <option value="">All Courses</option>
      <?php while($row = $course_result->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($row['course']) ?>"><?= htmlspecialchars($row['course']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>

  <div class="table-wrapper" id="studentsTable">
    <!-- Table will be loaded here -->
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  fetchStudents();
});

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
