<?php
include '../database_connection/db_connect.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $question_text = $_POST['question_text'];
    $marks = $_POST['marks'];
    $target_type = $_POST['target_type'];

    // Handle image upload
    $image_name = null;
    if (!empty($_FILES['question_image']['name'])) {
        $image_name = time() . "_" . basename($_FILES['question_image']['name']);
        $target_path = "../uploads/assignments/" . $image_name;

        // Ensure the upload folder exists
        if (!is_dir("../uploads/assignments")) {
            mkdir("../uploads/assignments", 0777, true);
        }

        move_uploaded_file($_FILES['question_image']['tmp_name'], $target_path);
    }

    // Insert into assignments table
    $stmt = $conn->prepare("INSERT INTO assignments (title, question_text, question_image, marks) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $title, $question_text, $image_name, $marks);
    $stmt->execute();
    $assignment_id = $conn->insert_id;

    // Assignment targets
    if ($target_type == 'all') {
        $students = $conn->query("SELECT student_id FROM students");
        while ($row = $students->fetch_assoc()) {
            $sid = $row['student_id'];
            $conn->query("INSERT INTO assignment_targets (assignment_id, student_id) VALUES ($assignment_id, $sid)");
        }
    } elseif ($target_type == 'batch') {
        $batch_id = $_POST['batch_id'];
        $conn->query("INSERT INTO assignment_targets (assignment_id, batch_id) VALUES ($assignment_id, $batch_id)");
    } elseif ($target_type == 'student') {
        $selected_students = $_POST['student_ids'];
        foreach ($selected_students as $sid) {
            $conn->query("INSERT INTO assignment_targets (assignment_id, student_id) VALUES ($assignment_id, $sid)");
        }
    }

    header("Location: admin_assignments.php?success=1");
    exit;
} else {
    die("❌ Invalid request method.");
}
