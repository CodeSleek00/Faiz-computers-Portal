<?php
// admin_fee_main.php
include '../database_connection/db_connect.php';// mysqli connection file


if (!$conn) {
    die("Database connection not found");
}

// Save total fee update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'], $_POST['total_fee'])) {
    $student_id = (int) $_POST['student_id'];
    $total_fee  = (float) $_POST['total_fee'];

    // Update or insert fee record
    $check = $conn->prepare("SELECT id FROM student_fees WHERE student_id = ?");
    $check->bind_param("i", $student_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        // Update existing
        $stmt = $conn->prepare("UPDATE student_fees SET total_fee = ? WHERE student_id = ?");
        $stmt->bind_param("di", $total_fee, $student_id);
        $stmt->execute();
    } else {
        // Insert new
        $stmt = $conn->prepare("INSERT INTO student_fees (student_id, student_name, course, total_fee) 
                                SELECT student_id, name, course, ? FROM students WHERE student_id = ?");
        $stmt->bind_param("di", $total_fee, $student_id);
        $stmt->execute();
    }

    header("Location: admin_fee_main.php?msg=Total+fee+updated");
    exit;
}

// Fetch students with/without fee entry
$query = "
    SELECT s.student_id, s.name, s.course, 
           IFNULL(sf.total_fee, 0) AS total_fee
    FROM students s
    LEFT JOIN student_fees sf ON s.student_id = sf.student_id
    ORDER BY s.name ASC
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Student Fee - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        h2 { text-align: center; }
        table { border-collapse: collapse; width: 90%; margin: 20px auto; background: white; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #333; color: white; }
        form { margin: 0; }
        input[type=number] { padding: 5px; width: 100px; }
        button { padding: 6px 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .msg { text-align: center; color: green; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Set / Update Student Total Fee</h2>

    <?php if (isset($_GET['msg'])): ?>
        <p class="msg"><?php echo htmlspecialchars($_GET['msg']); ?></p>
    <?php endif; ?>

    <table>
        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Course</th>
            <th>Total Fee</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['course']); ?></td>
                <td>
                    <form method="post" action="admin_fee_main.php">
                        <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                        <input type="number" step="0.01" name="total_fee" value="<?php echo $row['total_fee']; ?>">
                </td>
                <td>
                        <button type="submit">Save</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
