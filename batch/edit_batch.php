<?php
include '../database_connection/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Invalid batch ID.");
}

$batch_id = intval($_GET['id']);

// Fetch batch details
$batch_result = $conn->query("SELECT * FROM batches WHERE batch_id = $batch_id");
if ($batch_result->num_rows == 0) {
    die("❌ Batch not found.");
}
$batch = $batch_result->fetch_assoc();

// Fetch all students
$all_students = $conn->query("SELECT * FROM students");

// Fetch assigned student IDs
$assigned_students = [];
$assigned_result = $conn->query("SELECT student_id FROM student_batches WHERE batch_id = $batch_id");
while ($row = $assigned_result->fetch_assoc()) {
    $assigned_students[] = $row['student_id'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_name = $_POST['batch_name'];
    $new_timing = $_POST['timing'];
    $selected_students = isset($_POST['students']) ? $_POST['students'] : [];

    // Update batch info
    $stmt = $conn->prepare("UPDATE batches SET batch_name = ?, timing = ? WHERE batch_id = ?");
    $stmt->bind_param("ssi", $new_name, $new_timing, $batch_id);
    $stmt->execute();

    // Delete all current student links
    $conn->query("DELETE FROM student_batches WHERE batch_id = $batch_id");

    // Re-insert selected students
    foreach ($selected_students as $student_id) {
        $stmt = $conn->prepare("INSERT INTO student_batches (student_id, batch_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $student_id, $batch_id);
        $stmt->execute();
    }

    header("Location: view_batches.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Batch</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7fa;
            padding: 40px;
        }
        .form-container {
            background: white;
            max-width: 650px;
            margin: auto;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 18px rgba(0,0,0,0.08);
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }
        label {
            font-weight: 600;
            margin-top: 15px;
            display: block;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
        }
        select[multiple] {
            height: 180px;
        }
        button {
            width: 100%;
            padding: 12px;
            font-weight: bold;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
            display: block;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Batch: <?= htmlspecialchars($batch['batch_name']) ?></h2>

    <form method="POST">
        <label>Batch Name:</label>
        <input type="text" name="batch_name" value="<?= htmlspecialchars($batch['batch_name']) ?>" required>

        <label>Timing:</label>
        <input type="text" name="timing" value="<?= htmlspecialchars($batch['timing']) ?>" required>

        <label>Select Students:</label>
        <select name="students[]" multiple required>
            <?php while ($student = $all_students->fetch_assoc()) { ?>
                <option value="<?= $student['student_id'] ?>" <?= in_array($student['student_id'], $assigned_students) ? 'selected' : '' ?>>
                    <?= $student['name'] ?> (<?= $student['enrollment_id'] ?>)
                </option>
            <?php } ?>
        </select>

        <button type="submit">Update Batch</button>
    </form>

    <a class="back-link" href="view_batches.php">⬅ Back to Batch List</a>
</div>

</body>
</html>
