<?php
include '../database_connection/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Invalid batch ID.");
}

$batch_id = intval($_GET['id']);

// Fetch batch info
$batch_result = $conn->query("SELECT * FROM batches WHERE batch_id = $batch_id");
if ($batch_result->num_rows == 0) {
    die("❌ Batch not found.");
}
$batch = $batch_result->fetch_assoc();

// Fetch students in batch
$students = $conn->query("
    SELECT s.name, s.enrollment_id, s.course, s.photo
    FROM student_batches sb
    JOIN students s ON sb.student_id = s.student_id
    WHERE sb.batch_id = $batch_id
    ORDER BY s.name ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Batch - <?= htmlspecialchars($batch['batch_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #eef2f5;
            padding: 40px 20px;
        }

        .container {
            max-width: 960px;
            margin: auto;
            background: #fff;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
            color: #333;
        }

        .batch-details {
            text-align: center;
            font-size: 15px;
            margin-bottom: 20px;
            color: #666;
        }

        .search-bar {
            margin-bottom: 20px;
            text-align: right;
        }

        .search-bar input {
            padding: 8px 14px;
            width: 250px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-family: 'Poppins', sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th, table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table th {
            background-color: #f6f9fc;
            font-weight: 600;
            color: #333;
        }

        table tr:hover {
            background: #f9fcff;
        }

        .student-photo {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .student-photo:hover {
            transform: scale(1.05);
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            font-weight: 500;
            text-decoration: none;
            color: #007bff;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .empty {
            text-align: center;
            padding: 20px;
            color: #888;
        }

        /* Image Preview Popup */
        .popup-overlay {
            position: fixed;
            display: none;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .popup-box {
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            max-width: 90%;
            max-height: 90%;
            position: relative;
        }

        .popup-box img {
            max-width: 100%;
            max-height: 80vh;
            display: block;
            border-radius: 10px;
        }

        .close-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #ff4d4d;
            color: white;
            border: none;
            font-size: 18px;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            cursor: pointer;
        }

    </style>
    <script>
        function previewImage(src) {
            document.getElementById('popup-img').src = src;
            document.getElementById('popup-overlay').style.display = 'flex';
        }

        function closePopup() {
            document.getElementById('popup-overlay').style.display = 'none';
        }

        function searchStudents() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll("#studentTable tbody tr");
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(input) ? '' : 'none';
            });
        }
    </script>
</head>
<body>

<div class="container">
    <h2>Batch: <?= htmlspecialchars($batch['batch_name']) ?></h2>
    <div class="batch-details">Timing: <?= htmlspecialchars($batch['timing']) ?></div>

    <div class="search-bar">
        <input type="text" id="searchInput" onkeyup="searchStudents()" placeholder="Search by name, ID or course...">
    </div>

    <?php if ($students->num_rows > 0) { ?>
        <table id="studentTable">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Student Name</th>
                    <th>Enrollment ID</th>
                    <th>Course</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $students->fetch_assoc()) { ?>
                    <tr>
                        <td>
                            <?php if (!empty($row['photo']) && file_exists("../uploads/{$row['photo']}")) { ?>
                                <img src="../uploads/<?= $row['photo'] ?>" alt="Photo" class="student-photo" onclick="previewImage('../uploads/<?= $row['photo'] ?>')">
                            <?php } else { ?>
                                <img src="https://via.placeholder.com/50" class="student-photo" alt="No Image">
                            <?php } ?>
                        </td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['enrollment_id']) ?></td>
                        <td><?= htmlspecialchars($row['course']) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="empty">No students assigned to this batch yet.</div>
    <?php } ?>

    <a class="back-link" href="view_batches.php">⬅ Back to All Batches</a>
</div>

<!-- Image Preview Popup -->
<div class="popup-overlay" id="popup-overlay" onclick="closePopup()">
    <div class="popup-box" onclick="event.stopPropagation()">
        <button class="close-btn" onclick="closePopup()">×</button>
        <img id="popup-img" src="" alt="Student Preview">
    </div>
</div>

</body>
</html>
