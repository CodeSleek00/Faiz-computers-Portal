<?php
include '../database_connection/db_connect.php';

if (isset($_POST['upload'])) {
    $title = $_POST['title'];
    $assign_type = $_POST['assign_type'];
    $file = $_FILES['pdf'];

    $filename = uniqid() . '_' . basename($file['name']);
    $target_path = "uploads/" . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        $conn->query("INSERT INTO study_materials (title, file_name) VALUES ('$title', '$filename')");
        $material_id = $conn->insert_id;

        if ($assign_type === 'all') {
            $students = $conn->query("SELECT student_id FROM students");
            while ($s = $students->fetch_assoc()) {
                $conn->query("INSERT INTO study_material_targets (material_id, student_id) VALUES ($material_id, {$s['student_id']})");
            }
        } elseif ($assign_type === 'batch') {
            $batch_id = $_POST['batch_id'];
            $conn->query("INSERT INTO study_material_targets (material_id, batch_id) VALUES ($material_id, $batch_id)");
        } elseif ($assign_type === 'student') {
            $student_id = $_POST['student_id'];
            $conn->query("INSERT INTO study_material_targets (material_id, student_id) VALUES ($material_id, $student_id)");
        }

        header("Location: view_materials_admin.php?success=1");
    } else {
        echo "❌ File upload failed.";
    }
}
?>
