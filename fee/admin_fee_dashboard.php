<?php
include 'db_connect.php';

// Students fetch
$result = $conn->query("SELECT * FROM students ORDER BY enrollment_id ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Fee Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin:20px; }
        table { border-collapse: collapse; width:100%; margin-top:20px; }
        th, td { border:1px solid #ccc; padding:10px; text-align:center; }
        th { background:#f4f4f4; }
        a.btn { padding:6px 10px; background:blue; color:#fff; text-decoration:none; border-radius:4px; }
        a.btn-danger { background:red; }
    </style>
</head>
<body>
<h2>Admin Fee Dashboard</h2>
<table>
    <tr>
        <th>Photo</th>
        <th>Enrollment No</th>
        <th>Name</th>
        <th>Course</th>
        <th>Total Fee</th>
        <th>Paid Fee</th>
        <th>Show Fee</th>
        <th>Set Fee</th>
        <th>Complete Course</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
        <?php
        $student_id = $row['student_id'];
        $feeData = $conn->query("SELECT SUM(amount) as paid FROM student_fees WHERE student_id=$student_id");
        $feeRow = $feeData->fetch_assoc();
        $paid = $feeRow['paid'] ?? 0;
        ?>
        <tr>
            <td><img src="../uploads/<?php echo $row['photo']; ?>" width="60"></td>
            <td><?php echo $row['enrollment_id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['course']; ?></td>
            <td><?php echo $row['total_fee'] ?? "Not Set"; ?></td>
            <td><?php echo $paid; ?></td>
            <td><a class="btn" href="show_fee.php?student_id=<?php echo $student_id; ?>">Show Fee</a></td>
            <td><a class="btn" href="admin_fee_main.php?student_id=<?php echo $student_id; ?>">Set Fee</a></td>
            <td><a class="btn-danger" href="complete_course.php?student_id=<?php echo $student_id; ?>" onclick="return confirm('Are you sure to complete this course?')">Complete</a></td>
        </tr>
    <?php endwhile; ?>
</table>
</body>
</html>
