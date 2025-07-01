<?php
include '../database_connection/db_connect.php';

$result = $conn->query("SELECT * FROM my_student ORDER BY student_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Students</title>
</head>
<body>
    <h2>Student Records</h2>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Photo</th>
            <th>Name</th>
            <th>Course</th>
            <th>Phone</th>
            <th>Aadhar</th>
            <th>Actions</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['student_id'] ?></td>
            <td><img src="../uploads/<?= $row['photo'] ?>" width="60"></td>
            <td><?= $row['first_name'] . ' ' . $row['last_name'] ?></td>
            <td><?= $row['course'] ?></td>
            <td><?= $row['phone_no'] ?></td>
            <td><?= $row['aadhar_number'] ?></td>
            <td>
                <a href="student_detail.php?id=<?= $row['student_id'] ?>">Show Details</a> |
                <a href="edit_student.php?id=<?= $row['student_id'] ?>">Edit</a> |
                <a href="delete_student.php?id=<?= $row['student_id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
