<?php
// admin_fee_main.php
include '../database_connection/db_connect.php'; // mysqli connection

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
        $stmt = $conn->prepare("UPDATE student_fees SET total_fee = ? WHERE student_id = ?");
        $stmt->bind_param("di", $total_fee, $student_id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO student_fees (student_id, student_name, course, total_fee) 
                                SELECT student_id, name, course, ? FROM students WHERE student_id = ?");
        $stmt->bind_param("di", $total_fee, $student_id);
        $stmt->execute();
    }

    header("Location: admin_fee_main.php?msg=Total+fee+updated");
    exit;
}

// Search filter
$search = "";
$where = "";
if (!empty($_GET['q'])) {
    $search = trim($_GET['q']);
    $like = "%" . $conn->real_escape_string($search) . "%";
    $where = "WHERE s.name LIKE '$like' OR s.course LIKE '$like' OR s.student_id LIKE '$like'";
}

// Fetch students ordered by enrollment number (student_id)
$query = "
    SELECT s.student_id, s.name, s.course, 
           IFNULL(sf.total_fee, 0) AS total_fee
    FROM students s
    LEFT JOIN student_fees sf ON s.student_id = sf.student_id
    $where
    ORDER BY s.student_id ASC
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
        .msg { text-align: center; color: green; font-weight: bold; }
        .search-box { text-align: center; margin-bottom: 20px; }
        .search-box input { padding: 8px; width: 250px; }
        .search-box button { padding: 8px 12px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px; }
        .search-box button:hover { background: #218838; }
        table { border-collapse: collapse; width: 95%; margin: 20px auto; background: white; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #333; color: white; }
        form { margin: 0; }
        input[type=number] { padding: 5px; width: 100px; }
        button.save-btn { padding: 6px 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button.save-btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h2>Set / Update Student Total Fee</h2>

    <?php if (isset($_GET['msg'])): ?>
        <p class="msg"><?php echo htmlspecialchars($_GET['msg']); ?></p>
    <?php endif; ?>

    <div class="search-box">
        <form method="get" action="admin_fee_main.php">
            <input type="text" name="q" placeholder="Search by Name, Enrollment No. or Course" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <table>
        <tr>
            <th>Enrollment No.</th>
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
                        <button type="submit" class="save-btn">Save</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
