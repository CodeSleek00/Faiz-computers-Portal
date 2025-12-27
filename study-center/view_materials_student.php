<?php
include '../database_connection/db_connect.php';
session_start();

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['enrollment_id'], $_SESSION['student_table'])) {
    die("Please login.");
}

$enrollment   = $_SESSION['enrollment_id'];
$studentTable = $_SESSION['student_table']; // students OR students26

/* ================= FETCH STUDENT ================= */
$stmt = $conn->prepare("SELECT student_id, name FROM $studentTable WHERE enrollment_id = ?");
$stmt->bind_param("s", $enrollment);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    die("Student not found.");
}

$student_id   = $student['student_id'];
$student_name = $student['name'];

/* ================= FETCH STUDY MATERIAL ================= */
$query = "
    SELECT DISTINCT m.*
    FROM study_materials m
    JOIN study_material_targets t ON m.id = t.material_id
    WHERE 
        (t.student_id = ? AND t.student_table = ?)
        OR 
        t.batch_id IN (
            SELECT batch_id 
            FROM student_batches 
            WHERE student_id = ? AND student_table = ?
        )
    ORDER BY m.uploaded_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("isis", $student_id, $studentTable, $student_id, $studentTable);
$stmt->execute();
$data = $stmt->get_result();
?>


<!DOCTYPE html>
<html>
<head>
    <title>My Study Materials</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="image.png">
  <link rel="apple-touch-icon" href="image.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --light-bg: #f4f6fa;
            --card-bg: #ffffff;
            --text-color: #333;
            --gray: #6c757d;
            --radius: 10px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
        }

        .header {
            background: var(--card-bg);
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .header a.back-btn {
            text-decoration: none;
            color: var(--primary);
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .header h2 {
            margin: 0;
            font-size: 22px;
        }

        .material {
            background: var(--card-bg);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: var(--radius);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .material-title {
            font-weight: 600;
            font-size: 18px;
        }

        .download {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--primary);
            color: white;
            padding: 10px 16px;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            width: fit-content;
            transition: background 0.3s;
        }

        .download:hover {
            background-color: #3f3bd9;
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            color: var(--gray);
        }

        @media (max-width: 768px) {
            .header {
                text-align: center;
                align-items: center;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <!-- Header -->
    <div class="header">
        <a href="../test.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
        <h2>Hi, <?= htmlspecialchars($student_name) ?> 👋</h2>
        <p style="color: var(--gray); font-size: 14px;">Here are your assigned study materials:</p>
    </div>

    <!-- Materials -->
    <?php if ($data->num_rows > 0): ?>
        <?php while ($row = $data->fetch_assoc()): ?>
            <div class="material">
                <div class="material-title">📘 <?= htmlspecialchars($row['title']) ?></div>
                <a class="download" href="download.php?file=<?= urlencode($row['file_name']) ?>">
                    <i class="fas fa-download"></i> Download PDF
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-data">
            <i class="fas fa-folder-open" style="font-size: 40px; margin-bottom: 10px;"></i>
            <h3>No Study Materials Found</h3>
            <p>You haven't been assigned any study material yet.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
