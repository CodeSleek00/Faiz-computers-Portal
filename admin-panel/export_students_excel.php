<?php
include '../database_connection/db_connect.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=students_list.xls");
header("Pragma: no-cache");
header("Expires: 0");

$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $sql = "SELECT * FROM students WHERE 
            name LIKE ? OR 
            contact_number LIKE ? OR 
            course LIKE ? OR
            enrollment_id LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%$search%";
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM students");
}

echo "Name\tContact Number\tEnrollment ID\tCourse\n";
while ($row = $result->fetch_assoc()) {
    echo "{$row['name']}\t{$row['contact_number']}\t{$row['enrollment_id']}\t{$row['course']}\n";
}
?>
