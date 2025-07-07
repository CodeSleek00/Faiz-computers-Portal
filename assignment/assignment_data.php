<?php
include '../database_connection/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $assignment_id = intval($_POST['assignment_id']);
    $batch_id = !empty($_POST['batch_id']) ? intval($_POST['batch_id']) : null;
    $selected_students = $_POST['students'] ?? [];

    // Determine student list
    $student_ids = [];

    if ($batch_id) {
        // From batch
        $result = $conn->query("SELECT student_id FROM student_batches WHERE batch_id = $batch_id");
        while ($row = $result->fetch_assoc()) {
            $student_ids[] = $row['student_id'];
        }
    }

    if (!empty($selected_students)) {
        // Merge specific students
        foreach ($selected_students as $sid) {
            if (!in_array($sid, $student_ids)) {
                $student_ids[] = intval($sid);
            }
        }
    }

    if (empty($student_ids)) {
        die("❌ No students selected for assignment.");
    }

    // Prevent duplicates: Only insert if not already submitted or assigned
    foreach ($student_ids as $student_id) {
        $check = $conn->query("SELECT * FROM assignment_submissions WHERE assignment_id = $assignment_id AND student_id = $student_id");
        if ($check->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO assignment_submissions (assignment_id, student_id, submitted_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ii", $assignment_id, $student_id);
            $stmt->execute();
        }
    }

    header("Location: view_submissions.php?assigned=1");
    exit;
} else {
    echo "❌ Invalid request.";
}
