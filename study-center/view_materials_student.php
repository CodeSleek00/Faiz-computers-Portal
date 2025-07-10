<?php
include '../database_connection/db_connect.php';
session_start();

$enrollment = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment) die("Please login.");

$student = $conn->query("SELECT student_id, name FROM students WHERE enrollment_id = '$enrollment'")->fetch_assoc();
$student_id = $student['student_id'];
$student_name = $student['name'];

$query = "
    SELECT DISTINCT m.*
    FROM study_materials m
    JOIN study_material_targets t ON m.id = t.material_id
    WHERE t.student_id = $student_id
       OR t.batch_id IN (SELECT batch_id FROM student_batches WHERE student_id = $student_id)
    ORDER BY m.uploaded_at DESC
";
$data = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Study Materials</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        <a href="../student_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
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
