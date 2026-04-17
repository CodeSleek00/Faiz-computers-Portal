<?php
include 'database_connection/db_connect.php';

$sql = "CREATE TABLE IF NOT EXISTS student_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    student_id INT NOT NULL,
    student_table VARCHAR(20) NOT NULL,
    question_id INT NOT NULL,
    selected_option VARCHAR(1) NOT NULL,
    is_correct TINYINT(1) NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_answer (exam_id, student_id, student_table, question_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table student_answers created successfully or already exists.";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>