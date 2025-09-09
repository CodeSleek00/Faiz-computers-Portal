<?php
// admin_fee_dashboard.php
include '../database_connectioon/db_connect.php'; // ye file $conn define karegi

if (!$conn) {
    die("Database connection not found");
}

// Students aur unki fees fetch karna
$sql = "SELECT sf.id, sf.student_id, s.name AS student_name, s.course, 
               sf.total_fee, 
               (sf.internal1 + sf.internal2 + sf.semester1 + sf.semester2 + 
                sf.month_jan + sf.month_feb + sf.month_mar + sf.month_apr + 
                sf.month_may + sf.month_jun + sf.month_jul + sf.month_aug + 
                sf.month_sep + sf.month_oct + sf.month_nov + sf.month_dec
               ) AS paid_fee
        FROM student_fees sf
        JOIN students s ON sf.student_id = s.student_id";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Fee Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container my-5">
  <h2 class="mb-4">Student Fee Dashboard</h2>
  <table class="table table-bordered table-hover bg-white">
    <thead class="table-dark">
      <tr>
        <th>Student ID</th>
        <th>Name</th>
        <th>Course</th>
        <th>Total Fee</th>
        <th>Paid Fee</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['student_id']); ?></td>
            <td><?php echo htmlspecialchars($row['student_name']); ?></td>
            <td><?php echo htmlspecialchars($row['course']); ?></td>
            <td>₹<?php echo number_format($row['total_fee'], 2); ?></td>
            <td>₹<?php echo number_format($row['paid_fee'], 2); ?></td>
            <td>
              <a href="show_fee.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Show Fee</a>
              <a href="complete_course.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger"
                 onclick="return confirm('Are you sure you want to mark this course as completed and delete fee record?')">
                 Complete Course
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center">No records found</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
