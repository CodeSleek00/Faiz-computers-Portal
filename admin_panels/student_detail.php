<?php
include '../database_connection/db_connect.php';
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM my_student WHERE student_id = $id");
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Details</title>
</head>
<body>
    <h2>Student Full Details</h2>
    <img src="../uploads/<?= $row['photo'] ?>" width="120"><br><br>

    <p><strong>Name:</strong> <?= $row['first_name'] . ' ' . $row['last_name'] ?></p>
    <p><strong>Father's Name:</strong> <?= $row['fathers_name'] ?></p>
    <p><strong>Mother's Name:</strong> <?= $row['mothers_name'] ?></p>
    <p><strong>Course:</strong> <?= $row['course'] ?></p>
    <p><strong>Address:</strong> <?= $row['address'] ?></p>
    <p><strong>Phone:</strong> <?= $row['phone_no'] ?></p>
    <p><strong>Aadhar:</strong> <?= $row['aadhar_number'] ?></p>
    <p><strong>Birthday:</strong> <?= $row['birthday'] ?></p>
    <p><strong>ABC ID:</strong> <?= $row['abc_id'] ?></p>

    <br>
    <a href="edit_student.php?id=<?= $row['student_id'] ?>">✏️ Edit This Student</a>
</body>
</html>
