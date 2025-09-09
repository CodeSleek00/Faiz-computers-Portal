<?php
// admin_fee_dashboard.php
// Requires: db_connect.php which provides $pdo (PDO) connection
// Place this file in your admin folder and include db_connect.php at top

include '../database_connection/db_connect.php';
// Basic protections (optional): you may add session/auth checks here

// Handle POST actions: update total_fee, mark complete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_total') {
        $student_id = intval($_POST['student_id']);
        $total_fee = floatval($_POST['total_fee']);
        $stmt = $pdo->prepare("UPDATE student_fees SET total_fee = ? WHERE student_id = ?");
        $stmt->execute([$total_fee, $student_id]);
        header('Location: admin_fee_dashboard.php'); exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'mark_complete') {
        $student_id = intval($_POST['student_id']);
        // Delete fee row and optionally mark student status completed
        $pdo->beginTransaction();
        try {
            $pdo->prepare("DELETE FROM student_fees WHERE student_id = ?")->execute([$student_id]);
            // If you have a students.status column and want to set it:
            // $pdo->prepare("UPDATE students SET status='completed' WHERE student_id=?")->execute([$student_id]);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
        header('Location: admin_fee_dashboard.php'); exit;
    }
}

// Fetch all students with fees (active and completed depending on your needs)
$sql = "SELECT s.student_id AS sid, s.name AS student_name, s.course AS course, f.*
        FROM students s
        LEFT JOIN student_fees f ON f.student_id = s.student_id
        ORDER BY s.name ASC";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Helper to calculate sums per row
function calc_sums($row) {
    $months = ['month_jan','month_feb','month_mar','month_apr','month_may','month_jun','month_jul','month_aug','month_sep','month_oct','month_nov','month_dec'];
    $monthlySum = 0.0;
    foreach ($months as $m) {
        if (isset($row[$m])) $monthlySum += floatval($row[$m]);
    }
    $examSum = floatval($row['internal1'] ?? 0) + floatval($row['internal2'] ?? 0) + floatval($row['semester1'] ?? 0) + floatval($row['semester2'] ?? 0);
    $grand = $monthlySum + $examSum;
    return ['monthly'=>$monthlySum,'exam'=>$examSum,'grand'=>$grand, 'months'=>$months];
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Fee Dashboard</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:Arial,Helvetica,sans-serif;max-width:1200px;margin:18px auto;padding:10px}
    table{width:100%;border-collapse:collapse}
    th,td{padding:8px;border:1px solid #ddd;text-align:left}
    th{background:#f4f4f4}
    .small{font-size:13px;color:#555}
    .btn{padding:6px 10px;border:0;background:#0b76ef;color:#fff;border-radius:4px;cursor:pointer}
    .btn.red{background:#e74c3c}
    .btn.gray{background:#777}
    .inline-form{display:inline-block;margin:0}
    .modal{position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:none;align-items:center;justify-content:center}
    .modal .card{background:#fff;padding:16px;border-radius:6px;max-width:700px;width:95%;box-shadow:0 6px 24px rgba(0,0,0,0.2)}
    .close{float:right;cursor:pointer;padding:4px 8px}
    .right{float:right}
    input[type="number"]{padding:6px;width:120px}
    .note{font-size:13px;color:#444;margin-top:8px}
  </style>
</head>
<body>
  <h2>Admin Fee Dashboard</h2>
  <p class="note">Yahan students list hai — aap <strong>Total Fee</strong> set kar sakte hain, aur <strong>Show Fee</strong> se monthly + exam payments dekh sakte hain. "Complete Course" karne par fee entry delete ho jayegi.</p>

  <table>
    <thead>
      <tr>
        <th>Student Name</th>
        <th>Student ID</th>
        <th>Course</th>
        <th>Total Fee (you set)</th>
        <th>Paid Till Now</th>
        <th>Details</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $r):
        $sums = calc_sums($r);
        $paid = number_format($sums['grand'],2);
        $total_fee_val = isset($r['total_fee']) ? floatval($r['total_fee']) : 0.0;
    ?>
      <tr>
        <td><?= htmlspecialchars($r['student_name'] ?? '—') ?></td>
        <td><?= htmlspecialchars($r['sid'] ?? '—') ?></td>
        <td><?= htmlspecialchars($r['course'] ?? '—') ?></td>
        <td>
          <form class="inline-form" method="post" action="admin_fee_dashboard.php">
            <input type="hidden" name="action" value="update_total">
            <input type="hidden" name="student_id" value="<?= intval($r['sid']) ?>">
            <input type="number" name="total_fee" step="0.01" value="<?= number_format($total_fee_val,2,'.','') ?>">
            <button class="btn" type="submit">Save</button>
          </form>
        </td>
        <td>₹ <?= $paid ?></td>
        <td><button class="btn gray" onclick="openModal('modal_<?= htmlspecialchars($r['sid']) ?>')">Show Fee</button></td>
        <td>
          <form method="post" action="admin_fee_dashboard.php" onsubmit="return confirm('Mark course complete? This will remove fee entries for this student. Are you sure?');" style="display:inline-block">
            <input type="hidden" name="action" value="mark_complete">
            <input type="hidden" name="student_id" value="<?= intval($r['sid']) ?>">
            <button class="btn red" type="submit">Complete Course</button>
          </form>
        </td>
      </tr>

      <!-- Modal content for this student -->
      <div id="modal_<?= htmlspecialchars($r['sid']) ?>" class="modal">
        <div class="card">
          <div style="overflow:hidden">
            <strong>Fee details — <?= htmlspecialchars($r['student_name'] ?? '') ?> (<?= htmlspecialchars($r['sid']) ?>)</strong>
            <span class="close right" onclick="closeModal('modal_<?= htmlspecialchars($r['sid']) ?>')">✖</span>
          </div>
          <div style="margin-top:8px">
            <table style="width:100%">
              <tr><th>Component</th><th>Amount (₹)</th></tr>
              <?php foreach ($sums['months'] as $col): ?>
                <tr><td><?= ucfirst(str_replace('month_','', $col)) ?></td><td style="text-align:right"><?= number_format(floatval($r[$col] ?? 0),2) ?></td></tr>
              <?php endforeach; ?>
              <tr><td>Internal 1</td><td style="text-align:right"><?= number_format(floatval($r['internal1'] ?? 0),2) ?></td></tr>
              <tr><td>Internal 2</td><td style="text-align:right"><?= number_format(floatval($r['internal2'] ?? 0),2) ?></td></tr>
              <tr><td>Semester 1</td><td style="text-align:right"><?= number_format(floatval($r['semester1'] ?? 0),2) ?></td></tr>
              <tr><td>Semester 2</td><td style="text-align:right"><?= number_format(floatval($r['semester2'] ?? 0),2) ?></td></tr>
              <tr><th>Monthly Sum</th><th style="text-align:right">₹ <?= number_format($sums['monthly'],2) ?></th></tr>
              <tr><th>Exam Sum</th><th style="text-align:right">₹ <?= number_format($sums['exam'],2) ?></th></tr>
              <tr><th>Grand Total Paid</th><th style="text-align:right">₹ <?= number_format($sums['grand'],2) ?></th></tr>
            </table>

            <div style="margin-top:12px">
              <a href="receipt.php?student_id=<?= intval($r['sid']) ?>" target="_blank" class="btn">Open Receipt</a>
            </div>
          </div>
        </div>
      </div>

    <?php endforeach; ?>
    </tbody>
  </table>

  <script>
    function openModal(id){
      var el = document.getElementById(id);
      if(el) el.style.display = 'flex';
    }
    function closeModal(id){
      var el = document.getElementById(id);
      if(el) el.style.display = 'none';
    }
    // close on ESC
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ document.querySelectorAll('.modal').forEach(m=>m.style.display='none'); } });
  </script>
</body>
</html>