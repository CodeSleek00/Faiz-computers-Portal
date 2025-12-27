<?php
include '../database_connection/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $assignment_id = intval($_POST['assignment_id']);
    $batch_id = !empty($_POST['batch_id']) ? intval($_POST['batch_id']) : null;
    $selected_students = $_POST['students'] ?? [];

    // Determine student list
    $student_ids = [];

    if ($batch_id) {
        // From batch (SAFE)
        $stmt_batch = $conn->prepare("SELECT student_id FROM student_batches WHERE batch_id = ?");
        $stmt_batch->bind_param("i", $batch_id);
        $stmt_batch->execute();
        $result = $stmt_batch->get_result();
        while ($row = $result->fetch_assoc()) {
            $student_ids[] = (int) $row['student_id'];
        }
        $stmt_batch->close();
    }

    if (!empty($selected_students)) {
        // Merge specific students
        foreach ($selected_students as $sid) {
            $sid = (int) $sid;
            if (!in_array($sid, $student_ids)) {
                $student_ids[] = $sid;
            }
        }
    }

    if (empty($student_ids)) {
        die("❌ No students selected for assignment.");
    }

    // Prevent duplicates: Only insert if not already submitted or assigned (SAFE)
    $stmt_check = $conn->prepare("SELECT submission_id FROM assignment_submissions WHERE assignment_id = ? AND student_id = ? LIMIT 1");
    $stmt_insert = $conn->prepare("INSERT INTO assignment_submissions (assignment_id, student_id, submitted_at) VALUES (?, ?, NOW())");
    
    foreach ($student_ids as $student_id) {
        $stmt_check->bind_param("ii", $assignment_id, $student_id);
        $stmt_check->execute();
        $check_result = $stmt_check->get_result();
        
        if ($check_result->num_rows == 0) {
            $stmt_insert->bind_param("ii", $assignment_id, $student_id);
            $stmt_insert->execute();
        }
    }
    
    $stmt_check->close();
    $stmt_insert->close();

    header("Location: view_submissions.php?assigned=1");
    exit;
} else {
    echo "❌ Invalid request.";
}
