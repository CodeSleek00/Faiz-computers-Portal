<?php
session_start();
include 'db_connect.php';

// Session values
$student_id = $_SESSION['student_id'] ?? 0;
$batch_id   = $_SESSION['batch_id'] ?? 0;

if (!$student_id) {
    die("Unauthorized access. Please login first.");
}

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// Base query
$query = "
    SELECT * FROM videos 
    WHERE (assigned_to = 'all'
       OR (assigned_to = 'batch' AND batch_id = ?)
       OR (assigned_to = 'student' AND student_id = ?))
";

// If search applied
$params = [$batch_id, $student_id];
$types  = "ii";

if ($search !== "") {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$query .= " ORDER BY id DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Videos</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
    h2 { text-align: center; margin-bottom: 20px; }
    form { text-align: center; margin-bottom: 20px; }
    input[type="text"] { padding: 8px; width: 250px; }
    button { padding: 8px 12px; cursor: pointer; }
    .video-card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin: 15px auto;
        width: 80%;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .video-card h3 { margin: 0 0 10px; color: #333; }
    .video-card p { color: #555; }
    iframe { width: 100%; height: 315px; border-radius: 8px; }
  </style>
</head>
<body>
  <h2>Student Video Portal</h2>

  <!-- Search Form -->
  <form method="get" action="">
      <input type="text" name="search" placeholder="Search videos..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit">Search</button>
  </form>

  <?php if ($result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
          <div class="video-card">
              <h3><?= htmlspecialchars($row['title']) ?></h3>
              <p><?= htmlspecialchars($row['description']) ?></p>
              <?php if (!empty($row['file_path'])): ?>
                  <video width="100%" height="315" controls>
                      <source src="../uploads/videos/<?= htmlspecialchars($row['file_path']) ?>" type="video/mp4">
                      Your browser does not support the video tag.
                  </video>
              <?php elseif (!empty($row['youtube_link'])): ?>
                  <iframe src="<?= htmlspecialchars($row['youtube_link']) ?>" frameborder="0" allowfullscreen></iframe>
              <?php endif; ?>
          </div>
      <?php endwhile; ?>
  <?php else: ?>
      <p style="text-align:center; color:red;">No videos found.</p>
  <?php endif; ?>
</body>
</html>
