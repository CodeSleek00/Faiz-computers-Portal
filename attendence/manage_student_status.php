<?php
include '../database_connection/db_connect.php';

// Check if status column exists
$status_exists_students = $conn->query("SHOW COLUMNS FROM students LIKE 'status'")->num_rows > 0;
$status_exists_students26 = $conn->query("SHOW COLUMNS FROM students26 LIKE 'status'")->num_rows > 0;

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $student_id = $_POST['student_id'];
    $table_name = $_POST['table_name'];
    $status = $_POST['status'];

    $status_exists = ($table_name == 'students') ? $status_exists_students : $status_exists_students26;

    if ($status_exists) {
        $sql = "UPDATE $table_name SET status = ? WHERE " . ($table_name == 'students' ? 'student_id' : 'id') . " = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $student_id);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "<script>alert('Status column not found. Please run add_status_column.php first.');</script>";
    }
}

// Fetch all students
$query = "
SELECT 
    student_id AS id,
    'students' AS table_name,
    name,
    enrollment_id,
    course,
    COALESCE(status, 'continue') AS status
FROM students

UNION ALL

SELECT 
    id AS id,
    'students26' AS table_name,
    name,
    enrollment_id,
    course,
    COALESCE(status, 'continue') AS status
FROM students26
ORDER BY name
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Student Status</title>
<link rel="stylesheet" href="../css/global-theme.css">
<style>
body { padding: 20px; }
.card { background: #fff; padding: 20px; border-radius: 8px; max-width: 1200px; margin: auto; }
table { width: 100%; border-collapse: collapse; margin-top: 15px; }
th, td { border: 1px solid #D1D5DB; padding: 10px; text-align: center; }
select { padding: 5px; }
button { padding: 5px 10px; background: #2563EB; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
</style>
</head>
<body>

<div class="card">
<h2>Manage Student Status</h2>
<p>Classify students: <strong>continue</strong> (ongoing), <strong>completed</strong> (finished), <strong>hold</strong> (on hold), <strong>drop</strong> (dropped out)</p>

<table>
<tr>
<th>Photo</th>
<th>Name</th>
<th>Enrollment ID</th>
<th>Course</th>
<th>Status</th>
<th>Action</th>
</tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
<td><img src="../uploads/<?php echo htmlspecialchars($row['photo']); ?>" width="50" height="50" style="border-radius:50%; object-fit:cover;" alt="Photo"></td>
<td><?php echo htmlspecialchars($row['name']); ?></td>
<td><?php echo htmlspecialchars($row['enrollment_id']); ?></td>
<td><?php echo htmlspecialchars($row['course']); ?></td>
<td>
<form method="POST" style="display: inline;">
<input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
<input type="hidden" name="table_name" value="<?php echo $row['table_name']; ?>">
<select name="status">
<option value="continue" <?php if ($row['status'] == 'continue') echo 'selected'; ?>>Continue</option>
<option value="completed" <?php if ($row['status'] == 'completed') echo 'selected'; ?>>Completed</option>
<option value="hold" <?php if ($row['status'] == 'hold') echo 'selected'; ?>>Hold</option>
<option value="drop" <?php if ($row['status'] == 'drop') echo 'selected'; ?>>Drop</option>
</select>
</td>
<td>
<button type="submit" name="update_status">Update</button>
</form>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>

</body>
</html>