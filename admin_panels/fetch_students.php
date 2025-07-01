<?php
include '../database_connection/db_connect.php';

$query = $_POST['query'] ?? '';
$course = $_POST['course'] ?? '';

$search = "%$query%";

$sql = "SELECT * FROM my_student WHERE 
    (student_id LIKE ? OR 
     first_name LIKE ? OR 
     last_name LIKE ? OR 
     phone_no LIKE ? OR 
     course LIKE ?)";

$params = [$search, $search, $search, $search, $search, $search];

if ($course !== '') {
    $sql .= " AND course = ?";
    $params[] = $course;
}

$sql .= " ORDER BY student_id DESC";

$stmt = $conn->prepare($sql);
$types = str_repeat('s', count($params));
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<table>
  <tr>
    <th>ID</th>
    <th>Photo</th>
    <th>Name</th>
    <th>Course</th>
    <th>Phone</th>
    <th>Address</th>
    <th>Actions</th>
  </tr>
  <?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['student_id'] ?></td>
        <td><img src="../uploads/<?= $row['photo'] ?>" class="photo" alt="Student Photo"></td>
        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
        <td><?= htmlspecialchars($row['course']) ?></td>
        <td><?= htmlspecialchars($row['phone_no']) ?></td>
        <td><?= htmlspecialchars($row['address']) ?></td>
        <td class="actions">
          <a href="student_detail.php?id=<?= $row['student_id'] ?>" class="view">View</a>
          <a href="edit_student.php?id=<?= $row['student_id'] ?>" class="edit">Edit</a>
          <a href="delete_student.php?id=<?= $row['student_id'] ?>" class="delete" onclick="return confirm('Are you sure?')">Delete</a>
        </td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
    <tr><td colspan="7">No students found.</td></tr>
  <?php endif; ?>
</table>
