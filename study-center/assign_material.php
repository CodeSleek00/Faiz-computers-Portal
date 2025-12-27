<?php
include '../database_connection/db_connect.php';

// Fetch all batches and students
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");
$students = $conn->query("SELECT student_id, name, enrollment_id FROM students ORDER BY name ASC");
$students26 = $conn->query("SELECT id AS student_id, name FROM students26 ORDER BY name ASC"); // student_id as alias

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $targets = $_POST['targets'] ?? [];
    $file = $_FILES['pdf_file'];

    if ($file['error'] == 0) {
        $filename = time() . '_' . basename($file['name']);
        move_uploaded_file($file['tmp_name'], '../uploads/study_materials/' . $filename);

        // Save to DB
        $stmt = $conn->prepare("INSERT INTO study_materials (title, file_name, uploaded_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $title, $filename);
        $stmt->execute();
        $material_id = $stmt->insert_id;

        foreach ($targets as $t) {
            if (strpos($t, 'batch_') === 0) {
                $bid = intval(str_replace('batch_', '', $t));
                $conn->query("INSERT INTO study_material_targets (material_id, batch_id) VALUES ($material_id, $bid)");
            } elseif (strpos($t, 'student_') === 0) {
                $sid = intval(str_replace('student_', '', $t));
                $conn->query("INSERT INTO study_material_targets (material_id, student_id, student_table) VALUES ($material_id, $sid, 'students')");
            } elseif (strpos($t, 'student26_') === 0) {
                $sid = intval(str_replace('student26_', '', $t));
                $conn->query("INSERT INTO study_material_targets (material_id, student_id, student_table) VALUES ($material_id, $sid, 'students26')");
            }
        }

        header("Location: view_materials_admin.php?msg=uploaded");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Study Material</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* CSS same as previous version */
        :root { --primary-color:#4361ee; --primary-hover:#3a56d4; --text-color:#2b2d42; --light-gray:#f8f9fa; --border-color:#dee2e6; --border-radius:8px; --box-shadow:0 4px 12px rgba(0,0,0,0.1);}
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Poppins',sans-serif;background:var(--light-gray);color:var(--text-color);line-height:1.6;padding:20px;}
        .container{max-width:800px;background:white;margin:0 auto;padding:30px;border-radius:var(--border-radius);box-shadow:var(--box-shadow);}
        h2{text-align:center;margin-bottom:25px;color:var(--text-color);font-size:1.8rem;}
        .form-group{margin-bottom:20px;}
        label{display:block;margin-bottom:8px;font-weight:600;font-size:0.95rem;}
        input[type="text"], input[type="file"], select, textarea{width:100%;padding:12px 15px;border:1px solid var(--border-color);border-radius:var(--border-radius);font-family:'Poppins',sans-serif;font-size:1rem;transition:border-color 0.3s;}
        input:focus, select:focus, textarea:focus{outline:none;border-color:var(--primary-color);box-shadow:0 0 0 3px rgba(67,97,238,0.2);}
        select[multiple]{min-height:150px;padding:10px;}
        optgroup{font-weight:600;margin-top:10px;}
        option{padding:8px 12px;border-bottom:1px solid #eee;}
        .btn{display:inline-block;padding:12px 24px;background:var(--primary-color);color:white;font-weight:600;border:none;border-radius:var(--border-radius);cursor:pointer;text-align:center;width:100%;font-size:1rem;transition:background 0.3s, transform 0.2s;}
        .btn:hover{background:var(--primary-hover);transform:translateY(-2px);}
        .file-input-wrapper{position:relative;overflow:hidden;display:inline-block;width:100%;}
        .file-input-wrapper input[type="file"]{position:absolute;font-size:100px;opacity:0;right:0;top:0;cursor:pointer;}
        .file-input-label{display:flex;align-items:center;justify-content:space-between;padding:12px 15px;background:var(--light-gray);border:1px dashed var(--border-color);border-radius:var(--border-radius);cursor:pointer;}
        .file-input-label span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-right:10px;}
        .file-input-icon{color:var(--primary-color);font-size:1.2rem;}
        @media(max-width:768px){.container{padding:20px;} select[multiple]{min-height:120px;} h2{font-size:1.5rem;}}
        @media(max-width:480px){body{padding:10px;font-size:0.9rem;line-height:1.5;background:white;min-height:100vh;display:flex;flex-direction:column;} .container{padding:15px;box-shadow:none;border-radius:0;flex:1;} h2{font-size:1.3rem;margin-bottom:20px;} .btn{padding:10px 20px;}}
    </style>
</head>
<body>
<div class="container">
    <h2>Upload Study Material</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="pdf_file">Upload PDF:</label>
            <div class="file-input-wrapper">
                <label class="file-input-label" for="pdf_file">
                    <span id="file-name">Choose a PDF file</span>
                    <span class="file-input-icon">📁</span>
                </label>
                <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf" required onchange="updateFileName(this)">
            </div>
        </div>
        <div class="form-group">
            <label for="targets">Assign To:</label>
            <select id="targets" name="targets[]" multiple required>
                <optgroup label="Batches">
                    <?php while ($b = $batches->fetch_assoc()) { ?>
                        <option value="batch_<?= $b['batch_id'] ?>">Batch: <?= htmlspecialchars($b['batch_name']) ?></option>
                    <?php } ?>
                </optgroup>
                <optgroup label="Students">
                    <?php while ($s = $students->fetch_assoc()) { ?>
                        <option value="student_<?= $s['student_id'] ?>">Student: <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['enrollment_id']) ?>)</option>
                    <?php } ?>
                    <?php while ($s26 = $students26->fetch_assoc()) { ?>
                        <option value="student26_<?= $s26['student_id'] ?>">Student26: <?= htmlspecialchars($s26['name']) ?></option>
                    <?php } ?>
                </optgroup>
            </select>
        </div>
        <button type="submit" class="btn">Upload Material</button>
    </form>
</div>

<script>
function updateFileName(input) {
    const fileNameDisplay = document.getElementById('file-name');
    if (input.files.length > 0) {
        fileNameDisplay.textContent = input.files[0].name;
    } else {
        fileNameDisplay.textContent = 'Choose a PDF file';
    }
}
</script>
</body>
</html>
