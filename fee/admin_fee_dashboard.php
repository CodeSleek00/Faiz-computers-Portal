<?php
// admin_fee_dashboard.php
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

// Define months
$months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];

// Fetch all students
$sql = "SELECT * FROM students ORDER BY student_id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Fee Dashboard</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
  body { background: #f0f2f5; }
  .table td, .table th { vertical-align: middle; }
  img.student-photo { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; }
</style>
</head>
<body>
<div class="container mt-4">
  <h2>Admin Fee Dashboard</h2>
  <table class="table table-bordered table-striped mt-3">
    <thead class="table-dark">
      <tr>
        <th>Photo</th>
        <th>Enrollment No</th>
        <th>Name</th>
        <th>Course</th>
        <th>Contact</th>
        <th>Total Fee</th>
        <th>Paid Fee</th>
        <th>Show Fee</th>
        <th>Set Fee</th>
        <th>Complete Course</th>
      </tr>
    </thead>
    <tbody>
      <?php while($student = $result->fetch_assoc()): 
        $student_id = $student['student_id'];
        $fee_res = $conn->query("SELECT * FROM student_fees WHERE student_id='$student_id'");
        $fee = $fee_res->fetch_assoc();
        $total_fee = isset($fee['total_fee']) ? $fee['total_fee'] : 0;
        $paid_fee = 0;

        if($fee){
          $paid_fee += isset($fee['admission_fee']) ? $fee['admission_fee'] : 0;
          $paid_fee += isset($fee['internal1']) ? $fee['internal1'] : 0;
          $paid_fee += isset($fee['internal2']) ? $fee['internal2'] : 0;
          $paid_fee += isset($fee['semester1']) ? $fee['semester1'] : 0;
          $paid_fee += isset($fee['semester2']) ? $fee['semester2'] : 0;
          foreach($months as $m){
            $paid_fee += isset($fee['month_'.$m]) ? $fee['month_'.$m] : 0;
          }
        }
      ?>
      <tr>
        <td>
          <?php if(!empty($student['photo']) && file_exists("../uploads/".$student['photo'])): ?>
            <img src="../uploads/<?php echo htmlspecialchars($student['photo']); ?>" class="student-photo" alt="Photo">
          <?php else: ?>
            <img src="https://via.placeholder.com/50" class="student-photo" alt="No Photo">
          <?php endif; ?>
        </td>
        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
        <td><?php echo htmlspecialchars($student['name']); ?></td>
        <td><?php echo htmlspecialchars($student['course']); ?></td>
        <td><?php echo htmlspecialchars($student['contact'] ?? ''); ?></td>
        <td>₹<?php echo number_format($total_fee,2); ?></td>
        <td>₹<?php echo number_format($paid_fee,2); ?></td>
        <td><a href="show_fee.php?student_id=<?php echo $student_id; ?>" class="btn btn-info btn-sm">Show</a></td>
        <td><a href="admin_fee_main.php?student_id=<?php echo $student_id; ?>" class="btn btn-primary btn-sm">Set Fee</a></td>
        <td>
          <?php if(isset($student['course_complete']) && $student['course_complete']): ?>
            <a href="complete_course.php?student_id=<?php echo $student_id; ?>&action=remove" class="btn btn-warning btn-sm">Remove Complete</a>
          <?php else: ?>
            <a href="complete_course.php?student_id=<?php echo $student_id; ?>&action=complete" class="btn btn-success btn-sm">Complete</a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
