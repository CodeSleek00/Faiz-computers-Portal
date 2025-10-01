<?php
include '../database_connection/db_connect.php';

if (!$conn) {
    die("Database connection not found");
}

// Fetch all students for dropdown
$students_result = $conn->query("SELECT student_id, name FROM students ORDER BY student_id ASC");

// Handle form submission
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];

    // Fetch existing fee record
    $res = $conn->query("SELECT * FROM student_fees WHERE student_id='$student_id'");
    $fee_record = $res->fetch_assoc();

    // If record exists, update; else insert
    if ($fee_record) {
        $stmt = $conn->prepare("UPDATE student_fees SET
            internal1=?, internal2=?, semester1=?, semester2=?,
            month_jan=?, month_feb=?, month_mar=?, month_apr=?,
            month_may=?, month_jun=?, month_jul=?, month_aug=?,
            month_sep=?, month_oct=?, month_nov=?, month_dec=?,
            last_updated=NOW()
            WHERE student_id=?");

        $stmt->bind_param("dddddddddddddddds",
            $_POST['internal1'], $_POST['internal2'], $_POST['semester1'], $_POST['semester2'],
            $_POST['month_jan'], $_POST['month_feb'], $_POST['month_mar'], $_POST['month_apr'],
            $_POST['month_may'], $_POST['month_jun'], $_POST['month_jul'], $_POST['month_aug'],
            $_POST['month_sep'], $_POST['month_oct'], $_POST['month_nov'], $_POST['month_dec'],
            $student_id
        );
        $stmt->execute();
    } else {
        // Insert new
        $stmt = $conn->prepare("INSERT INTO student_fees (student_id, student_name, total_fee,
            internal1, internal2, semester1, semester2,
            month_jan, month_feb, month_mar, month_apr,
            month_may, month_jun, month_jul, month_aug,
            month_sep, month_oct, month_nov, month_dec)
            SELECT student_id, name, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            FROM students WHERE student_id=?");

        $stmt->bind_param("ddddddddddddddddds",
            $_POST['internal1'], $_POST['internal2'], $_POST['semester1'], $_POST['semester2'],
            $_POST['month_jan'], $_POST['month_feb'], $_POST['month_mar'], $_POST['month_apr'],
            $_POST['month_may'], $_POST['month_jun'], $_POST['month_jul'], $_POST['month_aug'],
            $_POST['month_sep'], $_POST['month_oct'], $_POST['month_nov'], $_POST['month_dec'],
            $student_id
        );
        $stmt->execute();
    }

    $msg = "Fee submitted successfully!";
}

// Fetch selected student fee to show current status
$selected_fee = [];
if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];
    $res = $conn->query("SELECT * FROM student_fees WHERE student_id='$student_id'");
    $selected_fee = $res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fee Submission</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
input[type=number] { width: 100px; }
</style>
</head>
<body class="bg-light">
<div class="container my-5">
    <h2>Fee Submission</h2>
    <?php if($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>

    <form method="get" class="mb-4">
        <label>Select Student:</label>
        <select name="student_id" class="form-select" onchange="this.form.submit()">
            <option value="">--Select--</option>
            <?php while($stu = $students_result->fetch_assoc()): ?>
                <option value="<?php echo $stu['student_id']; ?>" <?php if(isset($_GET['student_id']) && $_GET['student_id']==$stu['student_id']) echo 'selected'; ?>>
                    <?php echo $stu['student_id']." - ".$stu['name']; ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if($selected_fee || isset($_GET['student_id'])): ?>
        <form method="post">
            <input type="hidden" name="student_id" value="<?php echo $_GET['student_id']; ?>">

            <h5>Exam Fee</h5>
            <div class="row mb-3">
                <div class="col"><label>Internal 1</label><input type="number" step="0.01" name="internal1" class="form-control" value="<?php echo $selected_fee['internal1']??0; ?>"></div>
                <div class="col"><label>Internal 2</label><input type="number" step="0.01" name="internal2" class="form-control" value="<?php echo $selected_fee['internal2']??0; ?>"></div>
                <div class="col"><label>Semester 1</label><input type="number" step="0.01" name="semester1" class="form-control" value="<?php echo $selected_fee['semester1']??0; ?>"></div>
                <div class="col"><label>Semester 2</label><input type="number" step="0.01" name="semester2" class="form-control" value="<?php echo $selected_fee['semester2']??0; ?>"></div>
            </div>

            <h5>Monthly Fee</h5>
            <div class="row mb-3">
                <?php 
                $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
                foreach($months as $m): ?>
                    <div class="col mb-2">
                        <label><?php echo ucfirst($m); ?></label>
                        <input type="number" step="0.01" name="month_<?php echo $m; ?>" class="form-control" value="<?php echo $selected_fee['month_'.$m]??0; ?>">
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn-success">Submit Fee</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
