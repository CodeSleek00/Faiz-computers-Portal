<?php
session_start();
include 'db_connect.php';

// मान लो session से student_id और batch_id आ रहा है
$student_id = $_SESSION['student_id'] ?? 1;
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
if ($search !== "") {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
}
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
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #2563eb;
            --light-blue: #3b82f6;
            --white: #ffffff;
            --light-gray: #f8fafc;
            --medium-gray: #e2e8f0;
            --dark-gray: #64748b;
            --text-dark: #1e293b;
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--white);
            color: var(--text-dark);
            line-height: 1.5;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background: var(--white);
            border-bottom: 1px solid var(--medium-gray);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 1.3rem;
            color: var(--primary-blue);
        }

        .back-btn {
            background: none;
            border: 1px solid var(--medium-gray);
            color: var(--dark-gray);
            padding: 8px 16px;
            border-radius: var(--border-radius);
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .back-btn:hover {
            border-color: var(--primary-blue);
            color: var(--primary-blue);
        }

        /* Main Content */
        .main-content {
            padding: 40px 0;
        }

        .page-title {
            font-weight: 600;
            font-size: 1.8rem;
            margin-bottom: 30px;
            color: var(--text-dark);
        }

        /* Search */
        .search-section {
            margin-bottom: 30px;
        }

        .search-form {
            display: flex;
            gap: 10px;
            max-width: 500px;
        }

        .search-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid var(--medium-gray);
            border-radius: var(--border-radius);
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-blue);
        }

        .search-btn {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: var(--border-radius);
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .search-btn:hover {
            background-color: var(--light-blue);
        }

        /* Video Grid */
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .video-card {
            background: var(--white);
            border: 1px solid var(--medium-gray);
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .video-card:hover {
            border-color: var(--primary-blue);
            transform: translateY(-2px);
        }

        .video-thumbnail {
            width: 100%;
            height: 160px;
            object-fit: cover;
            display: block;
        }

        .video-content {
            padding: 16px;
        }

        .video-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 8px;
            color: var(--text-dark);
            line-height: 1.4;
        }

        .video-description {
            color: var(--dark-gray);
            font-size: 0.9rem;
            margin-bottom: 16px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .view-btn {
            background-color: var(--primary-blue);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background-color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            width: 100%;
            justify-content: center;
        }

        .view-btn:hover {
            background-color: var(--light-blue);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--dark-gray);
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 1rem;
            margin-bottom: 8px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .video-grid {
                grid-template-columns: 1fr;
            }
            
            .page-title {
                font-size: 1.5rem;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }
            
            .main-content {
                padding: 30px 0;
            }
            
            .video-thumbnail {
                height: 140px;
            }
        }

        /* Video count */
        .video-count {
            color: var(--dark-gray);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <span>📹</span>
                    <span>My Videos</span>
                </div>
                <a href="javascript:history.back()" class="back-btn">
                    <span>←</span> Back
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="main-content">
            <h1 class="page-title">My Videos</h1>
            
            <div class="search-section">
                <form method="get" class="search-form">
                    <input type="text" name="search" placeholder="Search videos..." 
                           value="<?= htmlspecialchars($search) ?>" class="search-input">
                    <button type="submit" class="search-btn">Search</button>
                </form>
                <?php if ($result->num_rows > 0): ?>
                    <div class="video-count"><?= $result->num_rows ?> video(s) found</div>
                <?php endif; ?>
            </div>

            <?php if ($result->num_rows > 0) { ?>
                <div class="video-grid">
                    <?php while($row = $result->fetch_assoc()) { ?>
                        <div class="video-card">
                            <img src="../uploads/thumbnails/<?= htmlspecialchars($row['thumbnail']) ?>" 
                                 alt="Thumbnail" class="video-thumbnail">
                            <div class="video-content">
                                <div class="video-title"><?= htmlspecialchars($row['title']) ?></div>
                                <div class="video-description"><?= nl2br(htmlspecialchars($row['description'])) ?></div>
                                <a class="view-btn" href="view_video.php?id=<?= $row['id'] ?>">
                                    <span>▶</span> Watch Video
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="empty-state">
                    <div class="empty-icon">📹</div>
                    <p>No videos found</p>
                    <p><?= $search ? 'Try adjusting your search' : 'Check back later for new content' ?></p>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>