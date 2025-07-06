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

$student_result = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Batch</title>
    <style>
        body { font-family: Poppins, sans-serif; padding: 30px; background: #f5f6fa; }
        .form-box { max-width: 600px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        input, select { width: 100%; padding: 10px; margin: 10px 0; border-radius: 8px; border: 1px solid #ccc; }
        button { padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 8px; cursor: pointer; }
    </style>
</head>
<body>

<div class="form-box">
    <h2>Create New Batch</h2>
    <form method="POST">
        <label>Batch Name:</label>
        <input type="text" name="batch_name" required>

        <label>Timing:</label>
        <input type="text" name="timing" required>

        <label>Select Students:</label>
        <select name="students[]" multiple size="8" required>
            <?php while ($row = $student_result->fetch_assoc()) { ?>
                <option value="<?= $row['student_id'] ?>"><?= $row['name'] ?> (<?= $row['enrollment_id'] ?>)</option>
            <?php } ?>
        </select>

        <button type="submit">Create Batch</button>
    </form>
</div>

</body>
</html>
