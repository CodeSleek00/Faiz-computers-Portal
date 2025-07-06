<?php
include '../database_connection/db_connect.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM batches";
if (!empty($search)) {
    $query .= " WHERE batch_name LIKE '%" . $conn->real_escape_string($search) . "%'";
}
$batches = $conn->query($query);
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

        .search-container {
            max-width: 600px;
            margin: 0 auto 30px;
        }

        .search-box {
            width: 100%;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            font-size: 16px;
            outline: none;
            transition: box-shadow 0.3s ease;
        }

        .search-box:focus {
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
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

        .edit-btn {
            background: #007bff;
            color: white;
        }

        .edit-btn:hover {
            background: #0056b3;
        }

        .view-btn {
            background: #28a745;
            color: white;
        }

        .view-btn:hover {
            background: #1e7e34;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background: #bd2130;
        }

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

<div class="search-container">
    <form method="GET" action="">
        <input type="text" class="search-box" name="search" placeholder="Search batches by name..." value="<?= htmlspecialchars($search) ?>">
    </form>
</div>

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