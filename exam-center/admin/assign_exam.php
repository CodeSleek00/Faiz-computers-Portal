<?php
include '../../database_connection/db_connect.php';

$exam_id = intval($_GET['exam_id']);

// Fetch students from both tables
$students1 = $conn->query("SELECT student_id, name, enrollment_id, 'students' AS student_table FROM students ORDER BY name ASC");
$students2 = $conn->query("SELECT id AS student_id, name, enrollment_id, 'students26' AS student_table FROM students26 ORDER BY name ASC");

// Merge students into one array
$all_students = [];
while ($row = $students1->fetch_assoc()) $all_students[] = $row;
while ($row = $students2->fetch_assoc()) $all_students[] = $row;

// Fetch batches
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targets = $_POST['targets'] ?? [];

    foreach ($targets as $target) {
        if (strpos($target, 'batch_') !== false) {
            $batch_id = intval(str_replace('batch_', '', $target));
            $conn->query("INSERT INTO exam_assignments (exam_id, batch_id) VALUES ($exam_id, $batch_id)");
        } else {
            // Format: students:123 OR students26:45
            list($table, $student_id) = explode(":", $target);
            $stmt = $conn->prepare("INSERT INTO exam_assignments (exam_id, student_id, student_table) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $exam_id, $student_id, $table);
            $stmt->execute();
        }
    }

    header("Location: exam_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Exam</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --background: #f4f6fb;
            --card: #ffffff;
            --gray: #6b7280;
            --radius: 12px;
            --shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background);
            padding: 40px 20px;
            margin: 0;
            color: #333;
        }

        .form-box {
            max-width: 800px;
            margin: auto;
            background: var(--card);
            padding: 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary);
        }

        h4 {
            margin-top: 30px;
            font-size: 18px;
            color: #333;
        }

        .checkbox-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 12px;
            margin-top: 10px;
        }

        label {
            display: flex;
            align-items: center;
            background: #f9fafb;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            cursor: pointer;
            transition: 0.3s;
        }

        label:hover {
            background: #eef1f7;
        }

        input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.1);
        }

        button {
            margin-top: 30px;
            width: 100%;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #3f3bdc;
        }

        @media (max-width: 480px) {
            h2 {
                font-size: 20px;
            }

            label {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<div class="form-box">
    <h2>📌 Assign Exam</h2>
    <form method="POST">
        <h4>🎓 Assign to Batches</h4>
        <div class="checkbox-list">
            <?php while ($b = $batches->fetch_assoc()) { ?>
                <label>
                    <input type="checkbox" name="targets[]" value="batch_<?= $b['batch_id'] ?>">
                    <?= htmlspecialchars($b['batch_name']) ?>
                </label>
            <?php } ?>
        </div>

        <h4>👤 Assign to Individual Students</h4>
        <div class="checkbox-list">
            <?php foreach ($all_students as $s) { ?>
                <label>
                    <input type="checkbox" name="targets[]" value="<?= $s['student_table'] ?>:<?= $s['student_id'] ?>">
                    <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['enrollment_id']) ?>)
                </label>
            <?php } ?>
        </div>

        <button type="submit">✅ Assign Exam</button>
    </form>
</div>

</body>
</html>
