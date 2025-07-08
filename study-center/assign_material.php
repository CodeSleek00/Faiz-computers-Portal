
<?php
include '../database_connection/db_connect.php';

// Fetch all batches and students
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");
$students = $conn->query("SELECT * FROM students ORDER BY name ASC");

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $targets = $_POST['targets'] ?? [];
    $file = $_FILES['pdf_file'];

    if ($file['error'] == 0) {
        $filename = time() . '_' . basename($file['name']);
        move_uploaded_file($file['tmp_name'], '../uploads/study_materials/' . $filename);

        // Save to DB
        $stmt = $conn->prepare("INSERT INTO study_material (title, file_name, uploaded_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $title, $filename);
        $stmt->execute();
        $material_id = $stmt->insert_id;

        foreach ($targets as $t) {
            if (strpos($t, 'batch_') === 0) {
                $bid = intval(str_replace('batch_', '', $t));
                $conn->query("INSERT INTO study_material_targets (material_id, batch_id) VALUES ($material_id, $bid)");
            } elseif (strpos($t, 'student_') === 0) {
                $sid = intval(str_replace('student_', '', $t));
                $conn->query("INSERT INTO study_material_targets (material_id, student_id) VALUES ($material_id, $sid)");
            }
        }

        header("Location: view_material_admin.php?msg=uploaded");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Study Material</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f6fa; padding: 40px; }
        .container {
            max-width: 700px;
            background: white;
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        h2 { text-align: center; margin-bottom: 25px; color: #333; }
        label { display: block; margin-top: 15px; font-weight: 600; }
        input, select, textarea {
            width: 100%; padding: 10px; border: 1px solid #ccc;
            border-radius: 8px; margin-top: 5px; font-family: 'Poppins', sans-serif;
        }
        select[multiple] { height: 150px; }
        button {
            margin-top: 20px;
            padding: 12px;
            background: #007bff;
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
        }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class="container">
    <h2>Upload Study Material</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Title:</label>
        <input type="text" name="title" required>

        <label>Upload PDF:</label>
        <input type="file" name="pdf_file" accept="application/pdf" required>

        <label>Assign To:</label>
        <select name="targets[]" multiple required>
            <optgroup label="Batches">
                <?php while ($b = $batches->fetch_assoc()) { ?>
                    <option value="batch_<?= $b['batch_id'] ?>">Batch: <?= $b['batch_name'] ?></option>
                <?php } ?>
            </optgroup>
            <optgroup label="Students">
                <?php while ($s = $students->fetch_assoc()) { ?>
                    <option value="student_<?= $s['student_id'] ?>">Student: <?= $s['name'] ?> (<?= $s['enrollment_id'] ?>)</option>
                <?php } ?>
            </optgroup>
        </select>

        <button type="submit">Upload</button>
    </form>
</div>
</body>
</html>
