<?php
include 'database_connection/db_connect.php';

// Add status column to students table if not exists
$sql1 = "ALTER TABLE students ADD COLUMN IF NOT EXISTS status ENUM('continue', 'completed', 'hold') DEFAULT 'continue'";
if ($conn->query($sql1) === TRUE) {
    echo "Status column added to students table successfully.<br>";
} else {
    echo "Error adding status to students: " . $conn->error . "<br>";
}

// Add status column to students26 table if not exists
$sql2 = "ALTER TABLE students26 ADD COLUMN IF NOT EXISTS status ENUM('continue', 'completed', 'hold') DEFAULT 'continue'";
if ($conn->query($sql2) === TRUE) {
    echo "Status column added to students26 table successfully.<br>";
} else {
    echo "Error adding status to students26: " . $conn->error . "<br>";
}

$conn->close();
?>