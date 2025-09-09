<?php
// admin_fee_dashboard.php
include '../database_connection/db_connect.php'; // ye file $conn define karegi

if (!$conn) {
    die("Database connection not found");
}

// Students aur unki fees fetch karna
$sql = "SELECT sf.id, sf.student_id, s.name AS student_name, s.course, s.photo,
               sf.total_fee, 
               (sf.internal1 + sf.internal2 + sf.semester1 + sf.semester2 + 
                sf.month_jan + sf.month_feb + sf.month_mar + sf.month_apr + 
                sf.month_may + sf.month_jun + sf.month_jul + sf.month_aug + 
                sf.month_sep + sf.month_oct + sf.month_nov + sf.month_dec
               ) AS paid_fee
        FROM student_fees sf
        JOIN students s ON sf.student_id = s.student_id
        ORDER BY sf.student_id ASC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Fee Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    .student-photo {
      width: 50px;
      height: 50px;
      object-fit: cover;
      border-radius: 50%;
    }
    .btn-space {
      margin-bottom: 2px;
    }
  </style>
</head>
<body class="bg-light">
<div class="container my-5">
  <h2 class="mb-4">Student Fee Dashboard</h2>

  <!-- Search by Student ID or Name -->
  <form method="get" class="mb-3">
    <div class="row g-2">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control" placeholder="Search by ID or Name" 
               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary">Search</button>
      </div>
    </div>
  </form>

  <table class="table table-bordered table-hover bg-white align-middle">
    <thead class="table-dark text-center">
      <tr>
        <th>Photo</th>
        <th>Student ID</th>
        <th>Name</th>
        <th>Course</th>
        <th>Total Fee</th>
        <th>Paid Fee</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $has_result = false;
      if ($result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
          // Search filter
          if (isset($_GET['search']) && $_GET['search'] !== '') {
              $search = strtolower($_GET['search']);
              if (strpos(strtolower($row['student_id']), $search) === false &&
                  strpos(strtolower($row['student_name']), $search) === false) {
                  continue; // Skip non-matching
              }
          }
          $has_result = true;
      ?>
          <tr>
            <td class="text-center">
              <?php if (!empty($row['photo']) && file_exists("../uploads/" . $row['photo'])): ?>
                <img src="../uploads/<?php echo htmlspecialchars($row['photo']); ?>" 
                     alt="Photo" class="student-photo">
              <?php else: ?>
                <img src="https://via.placeholder.com/50" alt="No Photo" class="student-photo">
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($row['student_id']); ?></td>
            <td><?php echo htmlspecialchars($row['student_name']); ?></td>
            <td><?php echo htmlspecialchars($row['course']); ?></td>
            <td class="text-end">₹<?php echo number_format($row['total_fee'], 2); ?></td>
            <td class="text-end">₹<?php echo number_format($row['paid_fee'], 2); ?></td>
            <td class="text-center">
              <a href="show_fee.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary btn-space">Show Fee</a>
              <a href="admin_fee_main.php?student_id=<?php echo $row['student_id']; ?>" class="btn btn-sm btn-success">Submit Fee</a>
              <a href="complete_course.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger btn-space"
                 onclick="return confirm('Are you sure you want to mark this course as completed and delete fee record?')">
                 Complete Course
              </a>
            </td>
          </tr>
      <?php
        endwhile;
      endif;

      if (!$has_result):
      ?>
        <tr><td colspan="7" class="text-center">No records found</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
