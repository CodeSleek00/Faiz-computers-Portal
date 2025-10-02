<?php
session_start();
include 'db_connect.php';

// Session se student info
$student_id = $_SESSION['student_id'] ?? 1;
$batch_id   = $_SESSION['batch_id'] ?? 1;

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// Base query with proper parentheses
$query = "
    SELECT * FROM videos 
    WHERE (assigned_to = 'all' 
       OR (assigned_to = 'batch' AND batch_id = ?) 
       OR (assigned_to = 'student' AND student_id = ?))
";

if ($search !== "") {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
}

// Prepare statement
$stmt = $conn->prepare($query);

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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Videos</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
    --primary-light: #dbeafe;
    --text: #1f2937;
    --text-light: #6b7280;
    --bg: #f8fafc;
    --white: #ffffff;
    --border: #e5e7eb;
    --shadow: 0 1px 3px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
    --radius: 8px;
    --radius-lg: 12px;
}
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }
.container { max-width:1200px; margin:0 auto; padding:0 20px; }
header { background:var(--white); border-bottom:1px solid var(--border); padding:16px 0; position:sticky; top:0; z-index:100; }
.header-content { display:flex; justify-content:space-between; align-items:center; }
.logo { font-weight:600; font-size:1.25rem; color:var(--primary); }
.back-btn { background:transparent; border:1px solid var(--border); color:var(--text); padding:8px 16px; border-radius:var(--radius); cursor:pointer; display:flex; align-items:center; gap:6px; transition:all 0.2s ease; }
.back-btn:hover { background:var(--primary-light); border-color:var(--primary); color:var(--primary); }
.main-content { padding:40px 0; }
.page-title { font-size:1.875rem; font-weight:600; margin-bottom:8px; text-align:center; }
.page-subtitle { text-align:center; color:var(--text-light); margin-bottom:32px; font-size:1rem; }
.search-section { background:var(--white); border-radius:var(--radius); padding:24px; margin-bottom:32px; box-shadow:var(--shadow); border:1px solid var(--border); }
.search-form { display:flex; gap:12px; }
.search-input { flex:1; padding:12px 16px; border:1px solid var(--border); border-radius:var(--radius); font-size:1rem; }
.search-input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,0.1); }
.search-btn { background:var(--primary); color:var(--white); border:none; padding:12px 24px; border-radius:var(--radius); cursor:pointer; }
.search-btn:hover { background:var(--primary-dark); }
.video-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:24px; }
.video-card { background:var(--white); border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow); border:1px solid var(--border); display:flex; flex-direction:column; height:100%; transition:all 0.3s ease; }
.video-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-lg); }
.thumbnail-container { position:relative; height:180px; overflow:hidden; }
.thumbnail { width:100%; height:100%; object-fit:cover; transition:transform 0.3s ease; }
.video-card:hover .thumbnail { transform:scale(1.05); }
.play-overlay { position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.3); display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity 0.3s ease; }
.video-card:hover .play-overlay { opacity:1; }
.play-icon { color:white; font-size:3rem; filter:drop-shadow(0 2px 4px rgba(0,0,0,0.3)); }
.video-content { padding:20px; flex:1; display:flex; flex-direction:column; }
.video-title { font-weight:600; font-size:1.125rem; margin-bottom:8px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.video-description { color:var(--text-light); font-size:0.875rem; margin-bottom:16px; flex:1; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
.view-btn { background:var(--primary); color:var(--white); text-decoration:none; padding:10px 16px; border-radius:var(--radius); text-align:center; font-weight:500; display:flex; align-items:center; justify-content:center; gap:8px; margin-top:auto; transition:all 0.2s ease; }
.view-btn:hover { background:var(--primary-dark); }
.empty-state { text-align:center; padding:60px 20px; color:var(--text-light); }
.empty-icon { font-size:4rem; margin-bottom:16px; color:var(--border); }
.empty-title { font-size:1.25rem; font-weight:600; margin-bottom:8px; color:var(--text); }
.empty-message { font-size:1rem; margin-bottom:24px; }
@media (max-width:768px){.header-content{flex-direction:column;gap:16px;text-align:center;}.search-form{flex-direction:column;}.video-grid{grid-template-columns:1fr;}.page-title{font-size:1.5rem;}.main-content{padding:24px 0;}}
@media (max-width:480px){.container{padding:0 16px;}.search-section{padding:20px;}.thumbnail-container{height:160px;}.video-content{padding:16px;}}
</style>
</head>
<body>
<header>
<div class="container">
<div class="header-content">
<div class="logo">Video Library</div>
<button class="back-btn" onclick="history.back()">← Back</button>
</div>
</div>
</header>

<div class="container">
<div class="main-content">
<h1 class="page-title">My Videos</h1>
<p class="page-subtitle">Browse your assigned video content</p>

<div class="search-section">
<form method="get" class="search-form">
<input type="text" name="search" placeholder="Search videos by title or description..." 
value="<?= htmlspecialchars($search) ?>" class="search-input">
<button type="submit" class="search-btn">Search</button>
</form>
</div>

<?php if ($result->num_rows > 0) { ?>
<div class="video-grid">
<?php while($row = $result->fetch_assoc()) { ?>
<div class="video-card">
<div class="thumbnail-container">
<?php if (!empty($row['thumbnail'])) { ?>
<img src="../uploads/thumbnails/<?= htmlspecialchars($row['thumbnail']) ?>" alt="Thumbnail" class="thumbnail">
<?php } else { ?>
<div style="width:100%; height:100%; background:linear-gradient(135deg,var(--primary)0%,var(--primary-light)100%); display:flex; align-items:center; justify-content:center; color:white; font-size:3rem;">🎥</div>
<?php } ?>
<div class="play-overlay"><span class="play-icon">▶</span></div>
</div>
<div class="video-content">
<h3 class="video-title"><?= htmlspecialchars($row['title']) ?></h3>
<p class="video-description"><?= htmlspecialchars($row['description']) ?></p>
<a class="view-btn" href="view_video.php?id=<?= $row['id'] ?>">▶ Watch Video</a>
</div>
</div>
<?php } ?>
</div>
<?php } else { ?>
<div class="empty-state">
<div class="empty-icon">📹</div>
<h2 class="empty-title">No videos found</h2>
<p class="empty-message"><?= $search !== "" ? "Try adjusting your search terms" : "Check back later for new content" ?></p>
<?php if ($search !== "") { ?>
<a href="?" class="view-btn" style="display:inline-flex; width:auto; padding:10px 20px;">Clear Search</a>
<?php } ?>
</div>
<?php } ?>
</div>
</div>
</body>
</html>
