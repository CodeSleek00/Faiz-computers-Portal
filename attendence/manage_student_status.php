<?php
include '../database_connection/db_connect.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $student_id = $_POST['student_id'];
    $table_name = $_POST['table_name'];
    $status = $_POST['status'];

    $sql = "UPDATE $table_name SET status = ? WHERE " . ($table_name == 'students' ? 'student_id' : 'id') . " = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $student_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all students
$query = "
SELECT 
    student_id AS id,
    'students' AS table_name,
    name,
    enrollment_id,
    course,
    status
FROM students

UNION ALL

SELECT 
    id AS id,
    'students26' AS table_name,
    name,
    enrollment_id,
    course,
    status
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
<p>Classify students: <strong>continue</strong> (ongoing), <strong>completed</strong> (finished), <strong>hold</strong> (on hold)</p>

<table>
<tr>
<th>Name</th>
<th>Enrollment ID</th>
<th>Course</th>
<th>Status</th>
<th>Action</th>
</tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
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