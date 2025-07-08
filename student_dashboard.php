<?php
include 'database_connection/db_connect.php';
session_start();
$eid = $_SESSION['enrollment_id'] ?? null;
if (!$eid) { header("Location: login.php"); exit; }
$st = $conn->query("SELECT * FROM students WHERE enrollment_id='$eid'")->fetch_assoc();
$sid = $st['student_id'];

$exams = $conn->query("SELECT e.*, 
  (SELECT COUNT(*) FROM exam_submissions WHERE exam_id=e.exam_id AND student_id=$sid) as taken
  FROM exams e 
  JOIN exam_assignments ea ON e.exam_id=ea.exam_id
  WHERE ea.student_id=$sid
");
$asgn = $conn->query("SELECT * FROM assignments a 
  LEFT JOIN assignment_submissions s ON a.assignment_id=s.assignment_id 
  AND s.student_id=$sid
");
$mats = $conn->query("SELECT * FROM study_materials");
?>

<!DOCTYPE html>
<html><head>
  <meta charset="utf-8"><title>Student Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body{font-family:'Poppins',sans-serif;background:#eef1f7;margin:0;padding:0}
    .topbar{background:#fff;padding:15px 30px;box-shadow:0 4px 12px rgba(0,0,0,0.05);display:flex;justify-content:space-between;align-items:center}
    .container{padding:30px;}
    .cards { display:flex; flex-wrap:wrap; gap:20px;}
    .card{background:#fff;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.05);flex:1;min-width:250px;position:relative;}
    .card.ready{border-left:5px solid #28a745}
    .card.pending{border-left:5px solid #f6c23e}
    .card a{display:inline-block;margin-top:15px;color:#4e73df;text-decoration:none}
    @media(max-width:800px){.cards{flex-direction:column}}
    table{width:100%;background:#fff;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.05);margin-top:30px;border-collapse:collapse}
    th,td{padding:15px;border-bottom:1px solid #eee;text-align:left}
  </style>
</head><body>
  <div class="topbar">
    <div>Hello, <?= htmlspecialchars($st['name']) ?></div>
    <a href="logout.php">Logout</a>
  </div>
  <div class="container">
    <h2>Dashboard</h2>
    <div class="cards">
      <?php while($e=$exams->fetch_assoc()): 
        $cls = $e['taken']?'pending':'ready';
      ?>
      <div class="card <?=$cls?>">
        <h3><?= htmlspecialchars($e['exam_name']) ?></h3>
        <p>Duration: <?= $e['duration'] ?> mins</p>
        <a href="<?= $e['taken']?'view_results_student.php?exam_id='.$e['exam_id']:'take_exam.php?exam_id='.$e['exam_id'] ?>">
          <?= $e['taken']?'View Result':'Start Exam' ?>
        </a>
      </div>
      <?php endwhile ?>

      <?php while($a=$asgn->fetch_assoc()): 
        $done = $a['submission_id']?'submitted':'pending'; ?>
      <div class="card <?=$done?>">
        <h3><?= htmlspecialchars($a['title']) ?></h3>
        <p>Marks: <?= $a['marks'] ?></p>
        <a href="<?= $done?'view_assignment.php?id='.$a['assignment_id']:'submit_assignment.php?id='.$a['assignment_id'] ?>">
          <?= $done?'View Submission':'Submit Assignment' ?>
        </a>
      </div>
      <?php endwhile ?>

      <?php while($m=$mats->fetch_assoc()): ?>
      <div class="card ready">
        <h3><?= htmlspecialchars($m['title']) ?></h3>
        <a href="../study-center/download.php?file=<?= urlencode($m['file_name']) ?>">Download Material</a>
      </div>
      <?php endwhile ?>
    </div>

    <!-- Optional Study Center table etc -->
  </div>
</body></html>
