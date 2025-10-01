<?php
// complete_course.php
include '../database_connection/db_connect.php'; // mysqli connection file

if (!$conn) {
    die("Database connection not found");
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Pehle student ka naam fetch karte hain confirmation ke liye
    $stmt = $conn->prepare("SELECT s.name, s.course FROM student_fees sf 
                            JOIN students s ON sf.student_id = s.student_id 
                            WHERE sf.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if ($student) {
        // Delete fee record
        $delete = $conn->prepare("DELETE FROM student_fees WHERE id = ?");
        $delete->bind_param("i", $id);
        if ($delete->execute()) {
            // Success redirect
            header("Location: admin_fee_dashboard.php?msg=Course+completed+for+" . urlencode($student['name']));
            exit;
        } else {
            die("Error deleting record: " . $conn->error);
        }
    } else {
        die("Student fee record not found.");
    }
} else {
    die("Invalid request.");
}
