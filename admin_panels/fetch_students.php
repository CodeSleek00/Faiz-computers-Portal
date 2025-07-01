<?php
include '../database_connection/db_connect.php';

$query = $_POST['query'] ?? '';
$course = $_POST['course'] ?? '';

$search = "%$query%";

$sql = "SELECT * FROM my_student WHERE 
    (student_id LIKE ? OR 
     first_name LIKE ? OR 
     last_name LIKE ? OR 
     aadhar_number LIKE ? OR 
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
        <th>Aadhar</th>
        <th>Actions</th>
    </tr>
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['student_id'] ?></td>
                <td><img src="../uploads/<?= $row['photo'] ?>" alt="Student"></td>
                <td><?= $row['first_name'] . ' ' . $row['last_name'] ?></td>
                <td><?= $row['course'] ?></td>
                <td><?= $row['phone_no'] ?></td>
                <td><?= $row['aadhar_number'] ?></td>
                <td class="actions">
                    <a href="student_detail.php?id=<?= $row['student_id'] ?>">View</a>
                    <a href="edit_student.php?id=<?= $row['student_id'] ?>">Edit</a>
                    <a href="delete_student.php?id=<?= $row['student_id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="7">No matching students found.</td></tr>
    <?php endif; ?>
</table>
