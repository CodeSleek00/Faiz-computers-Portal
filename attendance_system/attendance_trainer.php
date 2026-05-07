
<?php
include '../db_connect.php';

$students = mysqli_query($conn, "SELECT * FROM students");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Trainer</title>

    <style>
        body{
            font-family: Arial;
            background:#111827;
            color:white;
            padding:30px;
        }

        table{
            width:100%;
            border-collapse:collapse;
            background:#1f2937;
        }

        th,td{
            padding:15px;
            border:1px solid #374151;
        }

        a{
            text-decoration:none;
            background:#2563eb;
            color:white;
            padding:10px 15px;
            border-radius:8px;
        }
    </style>
</head>
<body>

<h1>Attendance Trainer</h1>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Enrollment</th>
        <th>Action</th>
    </tr>

    <?php while($row = mysqli_fetch_assoc($students)){ ?>

    <tr>
        <td><?= $row['id']; ?></td>
        <td><?= $row['name']; ?></td>
        <td><?= $row['enrollment_id']; ?></td>

        <td>
            <a href="face_register.php?id=<?= $row['id']; ?>&table=students">
                Register Face
            </a>
        </td>
    </tr>

    <?php } ?>

</table>

</body>
</html>