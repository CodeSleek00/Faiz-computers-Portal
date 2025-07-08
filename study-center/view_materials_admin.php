<?php
include '../database_connection/db_connect.php';

// Success message handler
$successMsg = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'uploaded') $successMsg = '✅ Material uploaded successfully!';
    if ($_GET['msg'] === 'deleted') $successMsg = '🗑 Material deleted successfully!';
}

// Fetch all materials
$materials = $conn->query("SELECT * FROM study_materials ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Study Materials</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #eef2f5;
            padding: 40px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        h2 { text-align: center; margin-bottom: 30px; color: #333; }
        .message {
            background: #d4edda;
            color: #155724;
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #f1f1f1;
            color: #333;
        }
        .btn {
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            margin-right: 10px;
        }
        .download { background: #28a745; }
        .delete { background: #dc3545; }
        .btn:hover { opacity: 0.9; }
        .no-data {
            text-align: center;
            padding: 40px 0;
            color: #888;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📚 Study Materials Dashboard</h2>

    <?php if (!empty($successMsg)) { ?>
        <div class="message"><?= $successMsg ?></div>
    <?php } ?>

    <?php if ($materials->num_rows > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>File</th>
                    <th>Uploaded On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $materials->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['file_name']) ?></td>
                        <td><?= date('d M Y, h:i A', strtotime($row['uploaded_at'])) ?></td>
                        <td>
                            <a class="btn download" href="download.php?file=<?= urlencode($row['file_name']) ?>">Download</a>
                            <a class="btn delete" href="delete_material.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this material?')">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="no-data">No study material uploaded yet.</div>
    <?php } ?>
</div>
</body>
</html>
