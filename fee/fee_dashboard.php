<?php
session_start();
include "../config.php"; // adjust path if needed
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fee Dashboard - 2026 Batch</title>
<link rel="stylesheet" href="../styles.css">
<style>
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
table, th, td { border: 1px solid #ddd; }
th, td { padding: 10px; text-align: center; }
img { width: 50px; height: 50px; border-radius: 50%; }
button { padding: 5px 10px; cursor: pointer; }
</style>
</head>
<body>

<h2>Students Fee Dashboard - 2026 Batch</h2>

<table>
    <thead>
        <tr>
            <th>Photo</th>
            <th>Name</th>
            <th>Course</th>
            <th>Total Fee</th>
            <th>Paid</th>
            <th>Pending</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
<?php
// Fetch all students from students_2026
$sql = "SELECT * FROM students_2026 ORDER BY id DESC";
$result = $conn->query($sql);

if($result->num_rows > 0){
    while($student = $result->fetch_assoc()){

        $student_id = $student['id'];

        // Calculate total fee
        $fee_sql = "SELECT SUM(amount) AS total_fee,
                           SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) AS paid_fee
                    FROM fee_master
                    WHERE student_id='$student_id'";
        $fee_result = $conn->query($fee_sql);
        $fee_data = $fee_result->fetch_assoc();

        $total_fee = $fee_data['total_fee'] ?? 0;
        $paid_fee = $fee_data['paid_fee'] ?? 0;
        $pending_fee = $total_fee - $paid_fee;
        ?>
        <tr>
            <td><img src="../uploads/<?php echo $student['photo']; ?>" alt="Photo"></td>
            <td><?php echo $student['full_name']; ?></td>
            <td><?php echo $student['course_name']; ?></td>
            <td>₹<?php echo $total_fee; ?></td>
            <td>₹<?php echo $paid_fee; ?></td>
            <td>₹<?php echo $pending_fee; ?></td>
            <td>
                <a href="manage_fee.php?student_id=<?php echo $student_id; ?>">
                    <button>Manage Fee</button>
                </a>
            </td>
        </tr>
<?php
    }
} else {
    echo "<tr><td colspan='7'>No students found.</td></tr>";
}
?>
    </tbody>
</table>

</body>
</html>
