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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #1e40af;
            --light-blue: #3b82f6;
            --accent-blue: #60a5fa;
            --white: #ffffff;
            --light-gray: #f8fafc;
            --medium-gray: #e2e8f0;
            --dark-gray: #64748b;
            --text-dark: #1e293b;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-gray);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--light-blue) 100%);
            color: white;
            padding: 20px 0;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
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
            font-weight: 700;
            font-size: 1.5rem;
        }

        .logo-icon {
            font-size: 1.8rem;
        }

        .back-btn {
            background-color: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        /* Main Content */
        .main-content {
            padding: 40px 0;
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary-blue);
            font-weight: 600;
            font-size: 2rem;
        }

        /* Search Section */
        .search-section {
            background-color: var(--white);
            border-radius: var(--radius);
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-input {
            flex: 1;
            padding: 12px 20px;
            border: 1px solid var(--medium-gray);
            border-radius: 50px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--light-blue);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .search-btn {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background-color: var(--light-blue);
            transform: translateY(-2px);
        }

        /* Video Grid */
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .video-card {
            background-color: var(--white);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .video-thumbnail {
            height: 180px;
            background: linear-gradient(135deg, var(--light-blue) 0%, var(--accent-blue) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .video-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .video-title {
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--text-dark);
            line-height: 1.4;
        }

        .video-description {
            color: var(--dark-gray);
            font-size: 0.9rem;
            margin-bottom: 15px;
            flex: 1;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .view-btn {
            background-color: var(--primary-blue);
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 6px;
            text-align: center;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
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
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--medium-gray);
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .video-grid {
                grid-template-columns: 1fr;
            }
            
            .page-title {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }
            
            .search-section {
                padding: 20px;
            }
            
            .video-thumbnail {
                height: 150px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <span class="logo-icon">🎥</span>
                    <span>Video Library</span>
                </div>
                <button class="back-btn" onclick="history.back()">
                    <span>←</span> Back
                </button>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="main-content">
            <h1 class="page-title">My Assigned Videos</h1>
            
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
                           
                           <img src="../uploads/thumbnails/<?= htmlspecialchars($row['thumbnail']) ?>" 
         alt="Thumbnail" style="width:100%; max-height:200px; object-fit:cover; border-radius:6px;">
    <div class="title"><?= htmlspecialchars($row['title']) ?></div>
    <div class="desc"><?= nl2br(htmlspecialchars($row['description'])) ?></div>
    <a class="view-btn" href="view_video.php?id=<?= $row['id'] ?>">▶ Watch Video</a>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="empty-state">
                    <div class="empty-icon">📹</div>
                    <p>No videos found.</p>
                    <p>Try adjusting your search or check back later for new content.</p>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>