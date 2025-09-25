<?php
session_start();
include 'db_connect.php';

$student_id = $_SESSION['student_id'] ?? 1;
$batch_id   = $_SESSION['batch_id'] ?? 1;

$video_id = $_GET['id'] ?? 0;

// Fetch video by ID (ensure it's assigned to this student)
$query = "
    SELECT * FROM videos 
    WHERE id = ? AND (
        assigned_to = 'all' 
        OR (assigned_to = 'batch' AND batch_id = ?) 
        OR (assigned_to = 'student' AND student_id = ?)
    )
";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $video_id, $batch_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();
$video = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Video</title>
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 8px;
            --radius-lg: 12px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .container {
            max-width: 1000px;
            width: 90%;
            margin: 40px auto;
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .video-header {
            padding: 24px;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--white) 100%);
        }
        
        .video-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
        }
        
        .video-meta {
            display: flex;
            gap: 16px;
            color: var(--text-light);
            font-size: 0.875rem;
        }
        
        .video-player-container {
            position: relative;
            width: 100%;
            background: #000;
        }
        
        .video-player {
            width: 100%;
            height: auto;
            display: block;
            max-height: 70vh;
        }
        
        .video-content {
            padding: 24px;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--text);
        }
        
        .video-description {
            color: var(--text);
            line-height: 1.7;
            margin-bottom: 24px;
            white-space: pre-line;
        }
        
        .action-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: var(--radius);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            font-family: inherit;
            font-size: 0.95rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .btn-secondary {
            background-color: var(--white);
            color: var(--text);
            border: 1px solid var(--border);
        }
        
        .btn-secondary:hover {
            background-color: #f9fafb;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .btn-icon {
            width: 18px;
            height: 18px;
        }
        
        .error-container {
            text-align: center;
            padding: 60px 24px;
        }
        
        .error-icon {
            font-size: 3rem;
            margin-bottom: 16px;
            color: #ef4444;
        }
        
        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text);
        }
        
        .error-message {
            color: var(--text-light);
            margin-bottom: 24px;
        }
        
        @media (max-width: 768px) {
            .container {
                width: 95%;
                margin: 20px auto;
            }
            
            .video-header {
                padding: 20px;
            }
            
            .video-title {
                font-size: 1.5rem;
            }
            
            .video-content {
                padding: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .video-header {
                padding: 16px;
            }
            
            .video-title {
                font-size: 1.25rem;
            }
            
            .video-meta {
                flex-direction: column;
                gap: 4px;
            }
            
            .video-content {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($video) { ?>
            <div class="video-header">
                <h1 class="video-title"><?= htmlspecialchars($video['title']) ?></h1>
               
            </div>
            
            <div class="video-player-container">
                <video class="video-player" controls controlsList="nodownload">
                    <source src="../uploads/videos/<?= htmlspecialchars($video['filename']) ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
            
            <div class="video-content">
                <h2 class="section-title">Description</h2>
                <div class="video-description"><?= htmlspecialchars($video['description']) ?></div>
                
                <div class="action-buttons">
                    <a class="btn btn-primary" href="../uploads/videos/<?= htmlspecialchars($video['filename']) ?>" download>
                        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download Video
                    </a>
                    <a class="btn btn-secondary" href="student_videos.php">
                        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Videos
                    </a>
                </div>
            </div>
        <?php } else { ?>
            <div class="error-container">
                <div class="error-icon">⚠️</div>
                <h2 class="error-title">Video Not Available</h2>
                <p class="error-message">This video is either not found or not assigned to you.</p>
                <a class="btn btn-secondary" href="student_videos.php">
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Videos
                </a>
            </div>
        <?php } ?>
    </div>
</body>
</html>