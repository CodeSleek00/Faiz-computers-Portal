<?php 
session_start();
require_once '../database_connection/db_connect.php';

/* =====================================================
   1. LOGIN CHECK
===================================================== */
if (!isset($_SESSION['enrollment_id'], $_SESSION['student_table'], $_SESSION['student_id'])) {
    header("Location: ../login-system/login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];
$table         = $_SESSION['student_table']; // students OR students26
$student_id    = (int) $_SESSION['student_id'];

/* =====================================================
   2. FETCH STUDENT DATA (SAFE & DYNAMIC)
===================================================== */
$stmt = $conn->prepare("
    SELECT 
        name,
        enrollment_id,
        photo
    FROM $table
    WHERE enrollment_id = ?
    LIMIT 1
");
$stmt->bind_param("s", $enrollment_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    session_destroy();
    header("Location: ../login-system/login.php?error=student_not_found");
    exit;
}

$student = $res->fetch_assoc();

/* =====================================================
   3. FETCH ASSIGNMENTS (SAFE & DYNAMIC)
===================================================== */
$stmt = $conn->prepare("
    SELECT 
        a.*, 
        s.submission_id, 
        s.marks_awarded, 
        s.submitted_at
    FROM assignments a
    INNER JOIN assignment_targets t 
        ON a.assignment_id = t.assignment_id
    LEFT JOIN assignment_submissions s 
        ON s.assignment_id = a.assignment_id 
        AND s.student_id = ?
    WHERE 
        t.student_id = ?
        OR t.batch_id IN (
            SELECT batch_id 
            FROM student_batches 
            WHERE student_id = ?
        )
    GROUP BY a.assignment_id
    ORDER BY a.created_at DESC
");
$stmt->bind_param("iii", $student_id, $student_id, $student_id);
$stmt->execute();
$assignments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <title>Assignments | Faiz Computer Institute</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@300;400;500;600;700&family=SF+Pro+Text:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #007AFF;
            --primary-blue-light: #409CFF;
            --primary-blue-dark: #0056CC;
            --accent-blue: #5AC8FA;
            --success: #34C759;
            --warning: #FF9500;
            --danger: #FF3B30;
            --dark: #1D1D1F;
            --dark-gray: #8E8E93;
            --medium-gray: #C7C7CC;
            --light-gray: #F2F2F7;
            --card-gray: #F5F5F7;
            --white: #FFFFFF;
            --sidebar-bg: #FFFFFF;
            --card-radius: 14px;
            --card-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            --card-shadow-hover: 0 6px 24px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'SF Pro Text', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #f2f2f7 100%);
            color: var(--dark);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            min-height: 100vh;
            overflow-x: hidden;
            padding: 20px;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Dashboard Header - Enhanced */
        .dashboard-header {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: var(--card-radius);
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--glass-border);
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: rgba(0, 122, 255, 0.1);
            color: var(--primary-blue);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
        }

        .back-btn:hover {
            background: rgba(0, 122, 255, 0.2);
            transform: translateX(-4px);
        }

        .live-clock {
            background: rgba(0, 122, 255, 0.1);
            border-radius: 12px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(0, 122, 255, 0.2);
        }

        .clock-icon {
            color: var(--primary-blue);
            font-size: 14px;
        }

        .clock-time {
            font-family: 'SF Pro Display', monospace;
            font-weight: 600;
            font-size: 14px;
            color: var(--dark);
        }

        .welcome-section h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--dark);
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--accent-blue) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-section p {
            color: var(--dark-gray);
            font-size: 14px;
            font-weight: 400;
        }

        /* Stats Overview */
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: var(--card-radius);
            padding: 20px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            text-align: center;
        }

        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--card-shadow-hover);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary-blue), var(--accent-blue));
            border-radius: 4px 0 0 4px;
        }

        .stat-icon {
            font-size: 24px;
            color: var(--primary-blue);
            margin-bottom: 12px;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 800;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 6px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-label {
            font-size: 12px;
            font-weight: 500;
            color: var(--dark-gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Assignments Grid */
        .assignments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
        }

        .assignment-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: var(--card-radius);
            padding: 24px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: 1px solid var(--glass-border);
            position: relative;
            overflow: hidden;
        }

        .assignment-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--card-shadow-hover);
        }

        .assignment-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary-blue), var(--accent-blue));
            border-radius: 4px 0 0 4px;
        }

        .assignment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .assignment-title {
            font-size: 17px;
            font-weight: 600;
            color: var(--dark);
            flex: 1;
        }

        .assignment-marks {
            background: rgba(0, 122, 255, 0.1);
            color: var(--primary-blue);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            min-width: 60px;
            text-align: center;
            border: 1px solid rgba(0, 122, 255, 0.2);
        }

        .assignment-body {
            margin-bottom: 20px;
        }

        .assignment-question {
            color: var(--dark-gray);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 16px;
            max-height: 100px;
            overflow: hidden;
            position: relative;
        }

        .assignment-question::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: linear-gradient(to bottom, transparent, var(--glass-bg));
        }

        .assignment-image {
            width: 100%;
            border-radius: 10px;
            margin: 16px 0;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .assignment-image:hover {
            transform: scale(1.02);
        }

        .assignment-status {
            margin-bottom: 20px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 8px;
        }

        .status-submitted {
            background: rgba(52, 199, 89, 0.15);
            color: var(--success);
            border: 2px solid rgba(52, 199, 89, 0.3);
        }

        .status-not-submitted {
            background: rgba(255, 59, 48, 0.15);
            color: var(--danger);
            border: 2px solid rgba(255, 59, 48, 0.3);
        }

        .status-graded {
            background: rgba(0, 122, 255, 0.15);
            color: var(--primary-blue);
            border: 2px solid rgba(0, 122, 255, 0.3);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-light) 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 122, 255, 0.3);
        }

        /* No Assignments */
        .no-assignments {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--card-radius);
            padding: 60px 40px;
            text-align: center;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--glass-border);
            grid-column: 1 / -1;
        }

        .no-assignments i {
            font-size: 64px;
            color: var(--medium-gray);
            margin-bottom: 24px;
            opacity: 0.5;
        }

        .no-assignments h3 {
            font-size: 22px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 12px;
        }

        .no-assignments p {
            color: var(--dark-gray);
            font-size: 15px;
            max-width: 400px;
            margin: 0 auto 24px;
        }

        /* Animation Classes */
        .animate-in {
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-8px);
            }
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .assignments-grid {
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            }
        }

        @media (max-width: 992px) {
            .assignments-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
            
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 16px;
            }
            
            .dashboard-header {
                padding: 20px;
            }
            
            .header-top {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }
            
            .back-btn {
                order: 1;
            }
            
            .live-clock {
                order: 2;
                justify-content: center;
            }
            
            .welcome-section {
                order: 3;
                text-align: center;
            }
            
            .welcome-section h1 {
                font-size: 22px;
            }
            
            .assignments-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .assignment-card {
                padding: 20px;
            }
            
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            
            .stat-card {
                padding: 16px;
            }
            
            .stat-number {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            .stats-overview {
                grid-template-columns: 1fr;
            }
            
            .no-assignments {
                padding: 40px 20px;
            }
            
            .no-assignments i {
                font-size: 48px;
            }
            
            .assignment-header {
                flex-direction: column;
                gap: 12px;
            }
            
            .assignment-marks {
                align-self: flex-start;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-blue);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-blue-dark);
        }

        /* Focus States */
        *:focus {
            outline: 2px solid var(--primary-blue);
            outline-offset: 2px;
        }

        *:focus:not(:focus-visible) {
            outline: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Dashboard Header -->
        <div class="dashboard-header animate-in" style="animation-delay: 0.1s">
            <div class="header-top">
                <a href="../test.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Dashboard</span>
                </a>
                
                <div class="live-clock">
                    <i class="fas fa-clock clock-icon"></i>
                    <div class="clock-time" id="liveClock">--:--:--</div>
                </div>
            </div>
            
            <div class="welcome-section">
                <h1>Hi, <?= htmlspecialchars($student['name']) ?> 👋</h1>
                <p>Manage and submit your assignments. Keep track of your progress.</p>
            </div>
        </div>

        <?php
        // Count assignments
        $total_assignments = $assignments->num_rows;
        $submitted_count = 0;
        $graded_count = 0;
        
        // Count submitted and graded assignments
        $assignments->data_seek(0); // Reset pointer
        while($a = $assignments->fetch_assoc()) {
            if (!empty($a['submission_id'])) {
                $submitted_count++;
                if (!is_null($a['marks_awarded'])) {
                    $graded_count++;
                }
            }
        }
        $assignments->data_seek(0); // Reset pointer again for display
        ?>

        <!-- Stats Overview -->
        <div class="stats-overview">
            <div class="stat-card animate-in" style="animation-delay: 0.2s">
                <i class="fas fa-tasks stat-icon float-animation"></i>
                <div class="stat-number"><?= $total_assignments ?></div>
                <div class="stat-label">Total Assignments</div>
            </div>
            
            <div class="stat-card animate-in" style="animation-delay: 0.3s">
                <i class="fas fa-check-circle stat-icon float-animation"></i>
                <div class="stat-number"><?= $submitted_count ?></div>
                <div class="stat-label">Submitted</div>
            </div>
            
            <div class="stat-card animate-in" style="animation-delay: 0.4s">
                <i class="fas fa-star stat-icon float-animation"></i>
                <div class="stat-number"><?= $graded_count ?></div>
                <div class="stat-label">Graded</div>
            </div>
            
            <div class="stat-card animate-in" style="animation-delay: 0.5s">
                <i class="fas fa-hourglass-half stat-icon float-animation"></i>
                <div class="stat-number"><?= $total_assignments - $submitted_count ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>

        <!-- Assignments Grid -->
        <?php if ($assignments->num_rows > 0): ?>
            <div class="assignments-grid">
                <?php 
                $animation_delay = 0.1;
                while ($a = $assignments->fetch_assoc()): 
                    $animation_delay += 0.1;
                ?>
                    <div class="assignment-card animate-in" style="animation-delay: <?= $animation_delay ?>s">
                        <div class="assignment-header">
                            <div class="assignment-title"><?= htmlspecialchars($a['title']) ?></div>
                            <div class="assignment-marks"><?= $a['marks'] ?> pts</div>
                        </div>

                        <div class="assignment-body">
                            <?php if (!empty($a['question_text'])): ?>
                                <div class="assignment-question">
                                    <?= nl2br(htmlspecialchars($a['question_text'])) ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($a['question_image'])): ?>
                                <img src="../uploads/assignments/<?= $a['question_image'] ?>" 
                                     class="assignment-image" 
                                     alt="Assignment Image">
                            <?php endif; ?>

                            <div class="assignment-status">
                                <?php if (!empty($a['submission_id'])): ?>
                                    <div class="status-badge status-submitted">
                                        <i class="fas fa-check-circle"></i>
                                        Submitted
                                    </div>
                                    
                                    <?php if (!is_null($a['marks_awarded'])): ?>
                                        <div class="status-badge status-graded">
                                            <i class="fas fa-star"></i>
                                            Graded: <?= $a['marks_awarded'] ?>/<?= $a['marks'] ?>
                                        </div>
                                        <div style="font-size: 12px; color: var(--dark-gray); margin-top: 8px;">
                                            Submitted on <?= date('M d, Y h:i A', strtotime($a['submitted_at'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <div style="font-size: 12px; color: var(--warning); margin-top: 8px;">
                                            <i class="fas fa-clock"></i> Awaiting Grading
                                        </div>
                                    <?php endif; ?>
                                    
                                <?php else: ?>
                                    <div class="status-badge status-not-submitted">
                                        <i class="fas fa-exclamation-circle"></i>
                                        Not Submitted
                                    </div>
                                    <a href="submit_assignment.php?assignment_id=<?= $a['assignment_id'] ?>" class="btn">
                                        <i class="fas fa-paper-plane"></i>
                                        <span>Submit Assignment</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-assignments animate-in">
                <i class="fas fa-book-open float-animation"></i>
                <h3>No Assignments Found</h3>
                <p>You don't have any assignments at this time. Check back later!</p>
                <a href="../test.php" class="btn" style="width: auto; padding: 12px 24px;">
                    <i class="fas fa-home"></i>
                    Return to Dashboard
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Live Clock Functionality
        function updateLiveClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const clockElement = document.getElementById('liveClock');
            if (clockElement) {
                clockElement.textContent = timeString;
                
                // Add pulse animation
                clockElement.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    clockElement.style.transform = 'scale(1)';
                }, 300);
            }
        }

        // Initialize everything
        document.addEventListener('DOMContentLoaded', function() {
            // Start live clock
            updateLiveClock();
            setInterval(updateLiveClock, 1000);

            // Enhanced hover effects
            const interactiveElements = document.querySelectorAll('.stat-card, .assignment-card, .btn');
            interactiveElements.forEach(element => {
                element.addEventListener('mouseenter', function() {
                    this.style.transform = this.classList.contains('assignment-card') ? 
                        'translateY(-8px)' : 'translateY(-4px)';
                });
                element.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Add touch feedback for mobile
            interactiveElements.forEach(element => {
                element.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.98)';
                });
                element.addEventListener('touchend', function() {
                    this.style.transform = 'scale(1)';
                });
            });

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 20,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Add animation delays
            const animateElements = document.querySelectorAll('.animate-in');
            animateElements.forEach((el, index) => {
                el.style.animationDelay = `${0.1 + (index * 0.05)}s`;
            });

            // Intersection Observer for animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
            
            document.querySelectorAll('.animate-in').forEach(el => {
                el.style.opacity = 0;
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });

        // Handle page visibility changes
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                updateLiveClock();
            }
        });
    </script>
</body>
</html>