<?php
include '../../database_connection/db_connect.php';

if (isset($_GET['exam_id'])) {
    $exam_id = intval($_GET['exam_id']);

    // Update exam table
    $stmt = $conn->prepare("UPDATE exams SET result_declared = 1 WHERE exam_id = ?");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: exam_dashboard.php?success=1");
    } else {
        echo "Failed to declare result. Maybe already declared.";
    }
} else {
    echo "Exam ID not provided.";
}
?>
