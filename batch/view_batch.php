<?php
include '../database_connection/db_connect.php';

// Batch Search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM batches";
if (!empty($search)) {
    $query .= " WHERE batch_name LIKE '%" . $conn->real_escape_string($search) . "%'";
}
$batches = $conn->query($query);

// Student Batch Redirect
$student_search_error = '';
if (isset($_GET['student_query'])) {
    $student_query = $conn->real_escape_string(trim($_GET['student_query']));
    
    $find_student = $conn->query("
        SELECT b.batch_id
        FROM student_batches sb
        JOIN students s ON sb.student_id = s.student_id
        JOIN batches b ON sb.batch_id = b.batch_id
        WHERE s.name LIKE '%$student_query%' OR s.enrollment_id LIKE '%$student_query%'
        LIMIT 1
    ");

    if ($find_student->num_rows > 0) {
        $batch = $find_student->fetch_assoc();
        header("Location: view_batches.php?id=" . $batch['batch_id']);
        exit;
    } else {
        $student_search_error = "Student not assigned to any batch.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Batches</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #eef2f5;
            padding: 40px 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .search-container {
            max-width: 600px;
            margin: 0 auto 25px;
        }

        .search-box {
            width: 100%;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            font-size: 16px;
            outline: none;
        }

        .student-search {
            margin-top: 10px;
        }

        .student-search input {
            width: calc(100% - 20px);
            padding: 12px 18px;
            border-radius: 8px;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            font-size: 15px;
        }

        .error {
            color: red;
            text-align: center;
            margin-top: 8px;
        }

        .batch-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .batch-info {
            font-size: 18px;
            font-weight: 600;
            color: #444;
        }

        .batch-timing {
            font-size: 14px;
            color: #888;
        }

        .action-buttons a {
            text-decoration: none;
            padding: 10px 16px;
            margin-left: 10px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: 0.2s ease;
        }

        .edit-btn { background: #007bff; color: white; }
        .edit-btn:hover { background: #0056b3; }

        .view-btn { background: #28a745; color: white; }
        .view-btn:hover { background: #1e7e34; }

        .delete-btn { background: #dc3545; color: white; }
        .delete-btn:hover { background: #bd2130; }

        .no-results {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }

        @media screen and (max-width: 600px) {
            .batch-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .action-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>

<h2>All Batches</h2>

<!-- Search by Batch Name -->
<div class="search-container">
    <form method="GET" action="">
        <input type="text" class="search-box" name="search" placeholder="Search batches by name..." value="<?= htmlspecialchars($search) ?>">
    </form>

    <!-- Search Student by Name or Enrollment ID -->
    <form method="GET" action="" class="student-search">
        <input type="text" name="student_query" placeholder="Search student to find their batch..." required>
    </form>

    <?php if (!empty($student_search_error)) { ?>
        <div class="error"><?= $student_search_error ?></div>
    <?php } ?>
</div>

<!-- Display All Batches -->
<?php 
if ($batches->num_rows > 0) {
    while ($batch = $batches->fetch_assoc()) { ?>
        <div class="batch-card">
            <div>
                <div class="batch-info"><?= htmlspecialchars($batch['batch_name']) ?></div>
                <div class="batch-timing"><?= htmlspecialchars($batch['timing']) ?></div>
            </div>
            <div class="action-buttons">
                <a class="edit-btn" href="edit_batch.php?id=<?= $batch['batch_id'] ?>">✏️ Edit</a>
                <a class="view-btn" href="view_batches.php?id=<?= $batch['batch_id'] ?>">👁 View</a>
                <a class="delete-btn" href="delete_batch.php?id=<?= $batch['batch_id'] ?>" onclick="return confirm('Are you sure to delete this batch?')">🗑 Delete</a>
            </div>
        </div>
    <?php } 
} else { ?>
    <div class="no-results">No batches found<?= !empty($search) ? " matching your search" : "" ?></div>
<?php } ?>

</body>
</html>
