<?php
// fee_submit.php
include 'db_connect.php';
include 'utils.php'; // optional helper (we'll provide small helper below)

$enrolled_id = intval($_GET['enrolled_id'] ?? 0);
$student = null;
if($enrolled_id){
    $stmt = $conn->prepare("SELECT * FROM student_enrolled WHERE enrolled_id=?");
    $stmt->bind_param("i",$enrolled_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $student = $res->fetch_assoc();
}

if(!$student){
    echo "Student not found"; exit;
}

// fetch courses
$courses_res = $conn->query("SELECT * FROM courses ORDER BY course_name");
$courses = [];
while($r = $courses_res->fetch_assoc()) $courses[] = $r;
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Fee Submit</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
<div class="container">
  <h4>Fee Setup for: <?php echo htmlspecialchars($student['name']); ?> (<?php echo htmlspecialchars($student['enrollment_no']); ?>)</h4>

  <!-- Plan creation -->
  <form id="planForm" method="post" action="create_plan.php">
    <input type="hidden" name="enrolled_id" value="<?php echo $student['enrolled_id']; ?>">
    <div class="row">
      <div class="col-md-4 mb-2">
        <label>Course</label>
        <select name="course_id" id="course_id" class="form-control">
          <option value="">-- Select --</option>
          <?php foreach($courses as $c): ?>
            <option value="<?php echo $c['course_id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
          <?php endforeach; ?>
        </select>
        <small class="text-muted">Or add new course below</small>
      </div>
      <div class="col-md-4 mb-2">
        <label>Or New Course Name</label>
        <input name="new_course_name" class="form-control">
      </div>
      <div class="col-md-2 mb-2">
        <label>Duration (months)</label>
        <select name="duration_months" class="form-control" id="duration">
          <option value="3">3</option>
          <option value="6" selected>6</option>
          <option value="12">12</option>
        </select>
      </div>
      <div class="col-md-2 mb-2">
        <label>Internals</label>
        <input type="number" min="0" name="internals" value="4" class="form-control">
      </div>
      <div class="col-md-2 mb-2">
        <label>Semesters</label>
        <input type="number" min="0" name="semesters" value="2" class="form-control">
      </div>
    </div>
    <div class="mt-2">
      <button class="btn btn-success">Create Plan & Generate Schedule</button>
    </div>
  </form>

  <hr>
  <!-- Show existing plan & schedule if any -->
  <?php
   $ps = $conn->prepare("SELECT p.*, c.course_name FROM enrollment_plans p LEFT JOIN courses c ON c.course_id=p.course_id WHERE p.enrolled_id=?");
   $ps->bind_param("i",$enrolled_id); $ps->execute(); $rp = $ps->get_result();
   if($plan = $rp->fetch_assoc()):
      echo "<h5>Existing Plan: Course: ".htmlspecialchars($plan['course_name'])." | Duration: {$plan['duration_months']} months | Internals: {$plan['internals_per_course']}</h5>";
      // show schedule items
      $sstmt = $conn->prepare("SELECT * FROM fee_schedule WHERE plan_id=? ORDER BY schedule_id");
      $sstmt->bind_param("i",$plan['plan_id']); $sstmt->execute(); $sres = $sstmt->get_result();
      echo "<table class='table table-sm'><thead><tr><th>Type</th><th>Name</th><th>Month</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead><tbody>";
      while($si = $sres->fetch_assoc()){
        echo "<tr>";
        echo "<td>".htmlspecialchars($si['item_type'])."</td>";
        echo "<td>".htmlspecialchars($si['item_name'])."</td>";
        echo "<td>".htmlspecialchars($si['month_name'])."</td>";
        echo "<td>".htmlspecialchars($si['amount'])."</td>";
        echo "<td>".htmlspecialchars($si['status'])."</td>";
        echo "<td><a class='btn btn-sm btn-primary' href='process_fee.php?enrolled_id={$student['enrolled_id']}&schedule_id={$si['schedule_id']}'>Pay</a></td>";
        echo "</tr>";
      }
      echo "</tbody></table>";
   else:
      echo "<div class='alert alert-info'>Koi plan nahi mila. Upar form se plan create karke schedule generate karein.</div>";
   endif;
  ?>
</div>
</body>
</html>
