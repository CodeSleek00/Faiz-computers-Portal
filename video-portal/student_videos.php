<?php
session_start();
include 'db_connect.php';

// मान लो session से student_id और batch_id आ रहा है (login के समय set किया होगा)
$student_id = $_SESSION['student_id'] ?? 1;  // demo ke liye
$batch_id   = $_SESSION['batch_id'] ?? 1;

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// Base query
$query = "
    SELECT * FROM videos 
    WHERE assigned_to = 'all' 
       OR (assigned_to = 'batch' AND batch_id = ?) 
       OR (assigned_to = 'student' AND student_id = ?)
";

// Agar search diya hai to add karein
if ($search !== "") {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
}

$stmt = $conn->prepare($query);

// Agar search hai to bind karein accordingly
if ($search !== "") {
    $like = "%$search%";
    $stmt->bind_param("iiss", $batch_id, $student_id, $like, $like);
} else {
    $stmt->bind_param("ii", $batch_id, $student_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Videos</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f7f7f7; margin:0; padding:0; }
        .container { width:90%; max-width:1000px; margin:20px auto; }
        h2 { text-align:center; margin-bottom:20px; }
        .search-box { text-align:center; margin-bottom:20px; }
        .search-box input { padding:10px; width:70%; max-width:400px; }
        .video-card { background:#fff; padding:15px; margin-bottom:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
        video { width:100%; border-radius:8px; margin-top:10px; }
        .title { font-weight:bold; font-size:18px; margin-bottom:5px; }
        .desc { color:#555; margin-bottom:10px; }
        .download-btn { display:inline-block; padding:8px 12px; background:#007BFF; color:#fff; text-decoration:none; border-radius:5px; margin-top:10px; }
        .download-btn:hover { background:#0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🎥 My Assigned Videos</h2>

        <!-- Search Box -->
        <div class="search-box">
            <form method="get">
                <input type="text" name="search" placeholder="Search videos..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <?php if ($result->num_rows > 0) { ?>
            <?php while($row = $result->fetch_assoc()) { ?>
                <div class="video-card">
                    <div class="title"><?= htmlspecialchars($row['title']) ?></div>
                    <div class="desc"><?= nl2br(htmlspecialchars($row['description'])) ?></div>
                    <video controls>
                        <source src="uploads/videos/<?= htmlspecialchars($row['filename']) ?>" type="video/mp4">
                        Your browser does not support video playback.
                    </video>
                    <br>
                    <a class="download-btn" href="../uploads/videos/<?= htmlspecialchars($row['filename']) ?>" download>⬇ Download</a>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p style="text-align:center; color:#777;">No videos found.</p>
        <?php } ?>
    </div>
</body>
</html>
