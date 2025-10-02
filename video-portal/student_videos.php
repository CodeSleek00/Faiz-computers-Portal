<?php
session_start();
include '../database_connection/db_connect.php';

$student_id = $_SESSION['student_id'] ?? 0;
if (!$student_id) {
    header("Location: login.php");
    exit;
}

// Get student's batch
$batch = $conn->query("SELECT batch_id FROM student_batches WHERE student_id = $student_id LIMIT 1")->fetch_assoc();
$batch_id = $batch['batch_id'] ?? 0;

// Fetch assigned videos
$sql = "SELECT DISTINCT v.id, v.title, v.file_name 
        FROM videos v
        LEFT JOIN video_targets vt ON v.id = vt.video_id
        WHERE vt.student_id = $student_id OR vt.batch_id = $batch_id
        ORDER BY v.id DESC";

$videos = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Videos - Student Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --secondary: #3f37c9;
            --text-dark: #2d3748;
            --text-light: #718096;
            --white: #ffffff;
            --gray-light: #f7fafc;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--gray-light);
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        
        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .back-btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }
        
        .page-title {
            margin-left: 20px;
            font-size: 24px;
            font-weight: 700;
            color: var(--text-dark);
        }
        
        .welcome-section {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        
        .welcome-text {
            font-size: 18px;
            color: var(--text-dark);
            font-weight: 500;
        }
        
        .highlight {
            color: var(--primary);
            font-weight: 600;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: var(--primary);
        }
        
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .video-card {
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--border);
        }
        
        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .video-thumbnail {
            height: 160px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 15px;
        }
        
        .video-title-thumbnail {
            color: white;
            font-weight: 600;
            font-size: 18px;
            text-align: center;
            line-height: 1.4;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .video-duration {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .video-info {
            padding: 16px;
        }
        
        .video-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-dark);
            font-size: 16px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 48px;
        }
        
        .video-meta {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: var(--text-light);
            margin-bottom: 15px;
        }
        
        .play-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            padding: 10px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .play-btn:hover {
            background: var(--secondary);
        }
        
        .no-videos {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px 20px;
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
        }
        
        .no-videos i {
            font-size: 48px;
            color: var(--text-light);
            margin-bottom: 15px;
        }
        
        .no-videos p {
            font-size: 18px;
            color: var(--text-light);
        }
        
        @media (max-width: 768px) {
            .video-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .page-title {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
            <h1 class="page-title">My Learning Videos</h1>
        </div>
        
        <div class="welcome-section">
            <p class="welcome-text">Welcome to your video library, <span class="highlight">Student</span>. Here you can access all your assigned learning materials.</p>
        </div>
        
        <h2 class="section-title">
            <i class="fas fa-play-circle"></i>
            Assigned Videos
        </h2>
        
        <div class="video-grid">
            <?php if ($videos->num_rows > 0): ?>
                <?php while($v = $videos->fetch_assoc()): ?>
                    <div class="video-card">
                        <div class="video-thumbnail">
                            <div class="video-title-thumbnail"><?= htmlspecialchars($v['title']) ?></div>
                            <span class="video-duration">25:30</span>
                        </div>
                        <div class="video-info">
                            <h3 class="video-title"><?= htmlspecialchars($v['title']) ?></h3>
                            <div class="video-meta">
                                <span><i class="far fa-calendar-alt"></i> Added: 2 days ago</span>
                                <span><i class="far fa-eye"></i> 145 views</span>
                            </div>
                            <a href="play_video.php?id=<?= $v['id'] ?>" class="play-btn">
                                <i class="fas fa-play"></i>
                                Play Video
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-videos">
                    <i class="fas fa-video-slash"></i>
                    <p>No videos assigned yet.</p>
                    <p style="font-size: 14px; margin-top: 10px;">Your instructor will assign videos for you to watch soon.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>