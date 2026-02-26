<?php
include '../database_connection/db_connect.php';

$successMsg = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'uploaded') {
    $successMsg = '✅ Video uploaded successfully!';
}

$videos = $conn->query("
    SELECT 
        v.id,
        v.title,
        v.description,
        v.file_name,
        v.mime_type,
        v.file_size,
        v.uploaded_at,
        (SELECT COUNT(*) FROM video_assignments a WHERE a.video_id = v.id) AS assigned_count
    FROM videos v
    ORDER BY v.uploaded_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Videos</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Poppins', sans-serif; background: #eef2f5; padding: 40px; }
    .container { max-width: 1100px; margin: auto; background: white; padding: 30px; border-radius: 14px; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
    h2 { text-align: center; margin-bottom: 30px; color: #333; }
    .message { background: #d4edda; color: #155724; padding: 10px 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: left; }
    th { background: #f1f1f1; color: #333; }
    table tr:hover { background: #f9f9f9; }
    .btn { padding: 6px 12px; text-decoration: none; border-radius: 8px; color: white; font-size: 14px; margin-right: 8px; display: inline-block; }
    .view { background: #2563eb; }
    .assign { background: #0ea5e9; }
    .no-data { text-align: center; padding: 40px 0; color: #888; }
</style>
</head>
<body>
<div class="container">
    <h2>🎬 Video Uploads</h2>

    <?php if (!empty($successMsg)) { ?>
        <div class="message"><?= htmlspecialchars($successMsg) ?></div>
    <?php } ?>

    <?php if ($videos && $videos->num_rows > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>File</th>
                    <th>Assigned</th>
                    <th>Uploaded On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $videos->fetch_assoc()) { ?>
                    <?php
                        $viewPath = $row['file_name'];
                        $candidate = __DIR__ . '/../uploads/videos/' . $viewPath;
                        if (!file_exists($candidate)) {
                            $fallback = __DIR__ . '/../uploads/videos/hostinger_uploads/' . $viewPath;
                            if (file_exists($fallback)) {
                                $viewPath = 'hostinger_uploads/' . $viewPath;
                            }
                        }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['file_name']) ?></td>
                        <td><?= (int) $row['assigned_count'] ?></td>
                        <td><?= date('d M Y, h:i A', strtotime($row['uploaded_at'])) ?></td>
                        <td>
                            <a class="btn view" href="../uploads/videos/<?= urlencode($viewPath) ?>" target="_blank">View</a>
                            <a class="btn assign" href="video_reassign.php?video_id=<?= (int) $row['id'] ?>">Assign</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="no-data">No videos uploaded yet.</div>
    <?php } ?>
</div>
</body>
</html>
