<?php
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

$msg = $_GET['msg'] ?? '';

$students = $conn->query("
    SELECT s.student_id, s.name, s.course, sf.total_fee, sf.payment_date, sf.course_complete
    FROM students s
    LEFT JOIN student_fees sf ON s.student_id = sf.student_id
    WHERE sf.course_complete = 0 OR sf.course_complete IS NULL
    ORDER BY s.student_id DESC
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Fee Dashboard</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
img.photo { width:50px; height:50px; object-fit:cover; border-radius:50%; }
</style>
</head>
<body>
<div class="container mt-4">
  <?php if($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
  <?php endif; ?>

  <h2>Student Fee Dashboard</h2>
  <table class="table table-bordered table-hover mt-3">
    <thead class="table-light">
      <tr>
        <th>Photo</th>
        <th>Enrollment</th>
        <th>Name</th>
        <th>Course</th>
        <th>Total Fee</th>
        <th>Paid Fee</th>
        <th>Show Fee</th>
        <th>Set Fee</th>
        <th>Complete Course</th>
        <th>WhatsApp</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $students->fetch_assoc()): 
        $paid = ($row['admission_fee']+$row['internal1']+$row['internal2']+$row['semester1']+$row['semester2']);
        foreach(['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'] as $m) $paid += $row['month_'.$m];
      ?>
      <tr>
        <td>
          <?php if(!empty($row['photo']) && file_exists("../uploads/".$row['photo'])): ?>
            <img src="../uploads/<?php echo $row['photo']; ?>" class="photo">
          <?php else: ?>
            <img src="https://via.placeholder.com/50" class="photo">
          <?php endif; ?>
        </td>
        <td><?php echo $row['student_id']; ?></td>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo htmlspecialchars($row['course']); ?></td>
        <td><?php echo number_format($row['total_fee'],2); ?></td>
        <td><?php echo number_format($paid,2); ?></td>
        <td><a href="show_fee.php?student_id=<?php echo $row['student_id']; ?>" class="btn btn-info btn-sm">Show Fee</a></td>
        <td><a href="admin_fee_main.php?student_id=<?php echo $row['student_id']; ?>" class="btn btn-primary btn-sm">Set Fee</a></td>
        <td>
          <?php if(!$row['course_complete']): ?>
            <a href="complete_course.php?student_id=<?php echo $row['student_id']; ?>" class="btn btn-success btn-sm">Complete</a>
          <?php else: ?>
            <span class="badge bg-success">Completed</span>
          <?php endif; ?>
        </td>
        <td>
          <a href="https://wa.me/<?php echo $row['contact_number']; ?>?text=<?php echo urlencode('Hello '.$row['name'].', your fee details can be checked here.'); ?>" target="_blank" class="btn btn-success btn-sm">WhatsApp</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
