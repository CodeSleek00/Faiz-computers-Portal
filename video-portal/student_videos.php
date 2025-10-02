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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px;
            width: 100%;
        }
        
        .header {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
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
            font-size: 14px;
            flex-shrink: 0;
        }
        
        .back-btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }
        
        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-dark);
            flex: 1;
            min-width: 200px;
        }
        
        .welcome-section {
            background: var(--white);
            padding: 18px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            width: 100%;
        }
        
        .welcome-text {
            font-size: 16px;
            color: var(--text-dark);
            font-weight: 500;
        }
        
        .highlight {
            color: var(--primary);
            font-weight: 600;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 18px;
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
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 18px;
            width: 100%;
        }
        
        .video-card {
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .video-thumbnail {
            height: 150px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 15px;
            flex-shrink: 0;
        }
        
        .video-title-thumbnail {
            color: white;
            font-weight: 600;
            font-size: 16px;
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
            bottom: 8px;
            right: 8px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 11px;
        }
        
        .video-info {
            padding: 15px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        
        .video-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-dark);
            font-size: 15px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 42px;
        }
        
        .video-meta {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-light);
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 5px;
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
            font-size: 14px;
            margin-top: auto;
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
            width: 100%;
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
        
        /* Mobile-specific styles */
        @media (max-width: 768px) {
            .container {
                padding: 12px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
                margin-bottom: 20px;
            }
            
            .page-title {
                font-size: 20px;
                min-width: unset;
                width: 100%;
            }
            
            .back-btn {
                width: 100%;
                justify-content: center;
            }
            
            .welcome-section {
                padding: 15px;
                margin-bottom: 20px;
            }
            
            .welcome-text {
                font-size: 15px;
            }
            
            .section-title {
                font-size: 17px;
                margin-bottom: 15px;
            }
            
            .video-grid {
                grid-template-columns: repeat(auto-fill, minmax(100%, 1fr));
                gap: 15px;
            }
            
            .video-thumbnail {
                height: 140px;
                padding: 12px;
            }
            
            .video-title-thumbnail {
                font-size: 15px;
            }
            
            .video-info {
                padding: 12px;
            }
            
            .video-title {
                font-size: 14px;
                min-height: 40px;
            }
            
            .video-meta {
                font-size: 11px;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }
            
            .header {
                margin-bottom: 15px;
            }
            
            .page-title {
                font-size: 18px;
            }
            
            .welcome-section {
                padding: 12px;
                margin-bottom: 15px;
            }
            
            .welcome-text {
                font-size: 14px;
            }
            
            .section-title {
                font-size: 16px;
            }
            
            .video-thumbnail {
                height: 130px;
            }
            
            .video-title-thumbnail {
                font-size: 14px;
            }
            
            .no-videos {
                padding: 30px 15px;
            }
            
            .no-videos i {
                font-size: 40px;
            }
            
            .no-videos p {
                font-size: 16px;
            }
        }
        
        /* Small mobile devices */
        @media (max-width: 360px) {
            .video-thumbnail {
                height: 120px;
            }
            
            .video-title-thumbnail {
                font-size: 13px;
            }
            
            .video-info {
                padding: 10px;
            }
            
            .video-title {
                font-size: 13px;
            }
            
            .video-meta {
                font-size: 10px;
            }
            
            .play-btn {
                font-size: 13px;
                padding: 8px;
            }
        }
        
        /* Large screens */
        @media (min-width: 1400px) {
            .container {
                max-width: 1300px;
            }
            
            .video-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="../test.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>

            </a>
            <h1 class="page-title">My Learning Videos</h1>
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
                        </div>
                        <div class="video-info">
                            <h3 class="video-title"><?= htmlspecialchars($v['title']) ?></h3>
                           
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