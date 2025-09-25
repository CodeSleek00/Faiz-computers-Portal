<?php
// admin_dashboard.php
include 'db_connect.php';

$q = "";
if(isset($_GET['q'])) {
    $q = $conn->real_escape_string($_GET['q']);
    $res = $conn->query("SELECT * FROM student_enrolled WHERE name LIKE '%$q%' OR enrollment_no LIKE '%$q%' ORDER BY created_at DESC");
} else {
    $res = $conn->query("SELECT * FROM student_enrolled ORDER BY created_at DESC");
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard - Enrolled Students</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
.thumb { width:60px; height:60px; object-fit:cover; border-radius:6px; }
</style>
</head>
<body class="p-4">
<div class="container">
  <h3>Enrolled Students</h3>
  <div class="mb-3 d-flex">
    <form class="me-auto" method="get">
      <input class="form-control" name="q" placeholder="Search name or enrollment no" value="<?php echo htmlspecialchars($q); ?>">
    </form>
    <a href="add_enrolled_student.php" class="btn btn-primary ms-2">Add Student</a>
  </div>

  <table class="table table-bordered">
    <thead>
      <tr><th>Photo</th><th>Name</th><th>Enrollment No</th><th>Phone</th><th>Course</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php while($row = $res->fetch_assoc()): ?>
      <tr>
        <td><img src="<?php echo htmlspecialchars($row['photo'] ?: 'default.png'); ?>" class="thumb"></td>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo htmlspecialchars($row['enrollment_no']); ?></td>
        <td><?php echo htmlspecialchars($row['phone']); ?></td>
        <td><?php echo htmlspecialchars($row['course']); ?></td>
        <td>
          <a href="fee_submit.php?enrolled_id=<?php echo $row['enrolled_id']; ?>" class="btn btn-sm btn-success">Fee Submit</a>
          <a href="view_student.php?enrolled_id=<?php echo $row['enrolled_id']; ?>" class="btn btn-sm btn-info">View</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
