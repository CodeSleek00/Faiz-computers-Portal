<?php
// admin_fee_dashboard.php
// Robust version: works with either PDO ($pdo) or MySQLi ($conn or $mysqli).
// Make sure your project has a db_connect.php that defines either $pdo (PDO) OR $conn/$mysqli (mysqli).

// Try including db_connect from a few likely places (no warnings).
$tried = [];
$paths = [
    __DIR__ . '/db_connect.php',
    __DIR__ . '/../db_connect.php',
    __DIR__ . '/../../db_connect.php',
    __DIR__ . '/../db/db_connect.php',
    __DIR__ . '/../../../db/db_connect.php',
    __DIR__ . '/config/db_connect.php',
    'db_connect.php'
];
foreach ($paths as $p) {
    if (file_exists($p)) { @include_once $p; break; }
}
// Also attempt a silent include of common name (in case above didn't match)
@include_once 'db_connect.php';

// Detect connection type
$use_pdo = (isset($pdo) && $pdo instanceof PDO);
$use_mysqli = (isset($conn) && $conn instanceof mysqli) || (isset($mysqli) && $mysqli instanceof mysqli);

if (!$use_pdo && isset($mysqli) && $mysqli instanceof mysqli) {
    // normalize
    $conn = $mysqli;
    $use_mysqli = true;
}
if (!$use_pdo && isset($conn) && $conn instanceof mysqli) {
    $use_mysqli = true;
}

// If still nothing, show clear message and stop
if (!$use_pdo && !$use_mysqli) {
    echo "<h3>Database connection not found</h3>";
    echo "<p>Please ensure <code>db_connect.php</code> exists and defines either <code>\$pdo</code> (PDO) or <code>\$conn</code>/<code>\$mysqli</code> (mysqli).</p>";
    exit;
}

// Helper functions to fetch data in a unified way
function db_fetch_all($sql) {
    global $use_pdo, $use_mysqli, $pdo, $conn;
    if ($use_pdo) {
        $stmt = $pdo->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    } else {
        $res = $conn->query($sql);
        if (!$res) return [];
        $out = [];
        while ($row = $res->fetch_assoc()) $out[] = $row;
        return $out;
    }
}

function db_prepare_execute($sql, $params = []) {
    global $use_pdo, $use_mysqli, $pdo, $conn;
    if ($use_pdo) {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } else {
        // For MySQLi, we'll bind dynamically (limited but works for numeric/string types)
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        if (!empty($params)) {
            $types = '';
            $bindValues = [];
            foreach ($params as $p) {
                if (is_int($p)) $types .= 'i';
                elseif (is_float($p) || is_double($p)) $types .= 'd';
                else $types .= 's';
                $bindValues[] = $p;
            }
            // bind_param requires variables by reference
            $refs = array();
            foreach ($bindValues as $k => $v) $refs[$k] = &$bindValues[$k];
            array_unshift($refs, $types);
            call_user_func_array([$stmt, 'bind_param'], $refs);
        }
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
}

// POST handling (must be before any HTML output)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // update total_fee
    if (isset($_POST['action']) && $_POST['action'] === 'update_total') {
        $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : 0;
        $total_fee = isset($_POST['total_fee']) ? $_POST['total_fee'] : 0;
        // use prepared update
        $sql = "UPDATE student_fees SET total_fee = ? WHERE student_id = ?";
        db_prepare_execute($sql, [$total_fee, $student_id]);
        header('Location: ' . basename(__FILE__));
        exit;
    }

    // mark complete => delete fee entry (and optional student status update)
    if (isset($_POST['action']) && $_POST['action'] === 'mark_complete') {
        $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : 0;
        // delete from student_fees
        db_prepare_execute("DELETE FROM student_fees WHERE student_id = ?", [$student_id]);
        // optional: update students table status if column exists (safe attempt)
        // try-catch for PDO or check return for mysqli is suppressed here
        // Redirect back
        header('Location: ' . basename(__FILE__));
        exit;
    }
}

// Fetch rows
$sql = "SELECT s.student_id AS sid, s.name AS student_name, s.course AS course, f.*
        FROM students s
        LEFT JOIN student_fees f ON f.student_id = s.student_id
        ORDER BY s.name ASC";
$rows = db_fetch_all($sql);

// helper sums
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
        // student id for forms (keep as-is)
        $sid = isset($r['sid']) ? $r['sid'] : '';
    ?>
      <tr>
        <td><?= htmlspecialchars($r['student_name'] ?? '—') ?></td>
        <td><?= htmlspecialchars($sid) ?></td>
        <td><?= htmlspecialchars($r['course'] ?? '—') ?></td>
        <td>
          <form class="inline-form" method="post" action="<?= htmlspecialchars(basename(__FILE__)) ?>">
            <input type="hidden" name="action" value="update_total">
            <input type="hidden" name="student_id" value="<?= htmlspecialchars($sid) ?>">
            <input type="number" name="total_fee" step="0.01" value="<?= number_format($total_fee_val,2,'.','') ?>">
            <button class="btn" type="submit">Save</button>
          </form>
        </td>
        <td>₹ <?= $paid ?></td>
        <td><button class="btn gray" onclick="openModal('modal_<?= htmlspecialchars($sid) ?>')">Show Fee</button></td>
        <td>
          <form method="post" action="<?= htmlspecialchars(basename(__FILE__)) ?>" onsubmit="return confirm('Mark course complete? This will remove fee entries for this student. Are you sure?');" style="display:inline-block">
            <input type="hidden" name="action" value="mark_complete">
            <input type="hidden" name="student_id" value="<?= htmlspecialchars($sid) ?>">
            <button class="btn red" type="submit">Complete Course</button>
          </form>
        </td>
      </tr>

      <!-- Modal content for this student -->
      <div id="modal_<?= htmlspecialchars($sid) ?>" class="modal">
        <div class="card">
          <div style="overflow:hidden">
            <strong>Fee details — <?= htmlspecialchars($r['student_name'] ?? '') ?> (<?= htmlspecialchars($sid) ?>)</strong>
            <span class="close right" onclick="closeModal('modal_<?= htmlspecialchars($sid) ?>')">✖</span>
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
              <a href="receipt.php?student_id=<?= urlencode($sid) ?>" target="_blank" class="btn">Open Receipt</a>
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
