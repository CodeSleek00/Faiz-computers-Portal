<?php
include '../database_connection/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid student ID");
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Student not found");
}

$row = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Student</title>
    <link rel="icon" type="image/png" href="image.png">
  <link rel="apple-touch-icon" href="image.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            margin: 0;
            background-color: #f4f6f8;
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .container {
            background-color: #fff;
            max-width: 700px;
            width: 100%;
            padding: 30px;
            margin: 20px auto;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.06);
        }

        h2 {
            text-align: center;
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 25px;
        }

        .student-photo {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            display: block;
            margin: 0 auto 20px auto;
            border: 4px solid #e0e0e0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .detail-item {
            background-color: #f9fafc;
            border-left: 4px solid #4A6CF7;
            padding: 15px 18px;
            margin-bottom: 16px;
            border-radius: 8px;
        }

        .detail-item strong {
            font-weight: 600;
            color: #555;
            display: block;
            margin-bottom: 6px;
        }

        .back-link {
            display: inline-block;
            margin-top: 25px;
            color: #fff;
            background-color: #4A6CF7;
            padding: 10px 16px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: 0.3s ease;
            text-align: center;
        }

        .back-link:hover {
            background-color: #3554c2;
        }

        .no-photo {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888;
            font-size: 14px;
            margin: 0 auto 20px auto;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin: 15px;
            }

            h2 {
                font-size: 1.6rem;
            }

            .detail-item {
                padding: 12px;
            }

            .student-photo, .no-photo {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Student Details</h2>

        <?php if (!empty($row['photo'])): ?>
            <img src="../uploads/<?= htmlspecialchars($row['photo']) ?>" alt="Student Photo" class="student-photo">
        <?php else: ?>
            <div class="no-photo">No Photo</div>
        <?php endif; ?>

        <div class="detail-item">
            <strong>Name</strong>
            <?= htmlspecialchars($row['name']) ?>
        </div>

        <div class="detail-item">
            <strong>Contact</strong>
            <?= htmlspecialchars($row['contact_number']) ?>
        </div>

        <div class="detail-item">
            <strong>Course</strong>
            <?= htmlspecialchars($row['course']) ?>
        </div>

        <div class="detail-item">
            <strong>Address</strong>
            <?= htmlspecialchars($row['address']) ?>
        </div>

        <div class="detail-item">
            <strong>Enrollment ID</strong>
            <?= htmlspecialchars($row['enrollment_id']) ?>
        </div>

        <div style="text-align: center;">
            <a href="manage_student.php" class="back-link">← Back to Students</a>
        </div>
    </div>
</body>
</html>
