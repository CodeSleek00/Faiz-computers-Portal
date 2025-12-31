<?php
include '../database_connection/db_connect.php';
session_start();

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['enrollment_id'], $_SESSION['student_table'])) {
    die("Please login.");
}

$enrollment   = $_SESSION['enrollment_id'];
$studentTable = $_SESSION['student_table']; // students OR students26

/* ================= DETERMINE ID COLUMN ================= */
$idColumn = ($studentTable === 'students') ? 'student_id' : 'id';

/* ================= FETCH STUDENT ================= */
$stmt = $conn->prepare("SELECT $idColumn AS student_id, name FROM $studentTable WHERE enrollment_id = ?");
$stmt->bind_param("s", $enrollment);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    die("Student not found.");
}

$student_id   = $student['student_id'];
$student_name = $student['name'];

/* ================= FETCH STUDY MATERIALS ================= */
$query = "
    SELECT DISTINCT m.*
    FROM study_materials m
    JOIN study_material_targets t ON m.id = t.material_id
    WHERE 
        (t.student_id = ? AND t.student_table = ?)
        OR 
        t.batch_id IN (
            SELECT batch_id 
            FROM student_batches 
            WHERE student_id = ? AND student_table = ?
        )
    ORDER BY m.uploaded_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("isis", $student_id, $studentTable, $student_id, $studentTable);
$stmt->execute();
$data = $stmt->get_result();

// Count materials by type
$total_materials = $data->num_rows;
$data->data_seek(0); // Reset pointer
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Materials | Student Dashboard</title>
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --success: #10b981;
            --warning: #f59e0b;
            --dark: #1f2937;
            --gray: #6b7280;
            --light-gray: #f3f4f6;
            --border: #e5e7eb;
            --white: #ffffff;
            --radius: 8px;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--light-gray);
            color: var(--dark);
            line-height: 1.5;
            padding: 16px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            margin-bottom: 24px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: var(--white);
            color: var(--primary);
            text-decoration: none;
            border-radius: var(--radius);
            font-size: 13px;
            font-weight: 500;
            border: 1px solid var(--border);
            transition: all 0.2s;
        }

        .back-btn:hover {
            background: var(--light-gray);
            border-color: var(--primary);
        }

        .welcome-section h1 {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .welcome-section p {
            color: var(--gray);
            font-size: 13px;
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--white);
            padding: 16px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: var(--white);
            background: var(--primary);
            flex-shrink: 0;
        }

        .stat-icon.recent { background: var(--success); }

        .stat-info {
            flex: 1;
        }

        .stat-number {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 2px;
        }

        .stat-label {
            font-size: 12px;
            color: var(--gray);
        }

        /* Materials Section */
        .materials-section {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .section-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header h2 {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-header h2 i {
            color: var(--primary);
        }

        .materials-count {
            font-size: 12px;
            color: var(--gray);
            background: var(--light-gray);
            padding: 4px 10px;
            border-radius: 20px;
        }

        .materials-list {
            padding: 20px;
        }

        .material-item {
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .material-item:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
        }

        .material-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .material-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            flex: 1;
        }

        .material-meta {
            font-size: 11px;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: 12px;
            white-space: nowrap;
        }

        .material-body {
            margin-bottom: 16px;
        }

        .material-desc {
            color: var(--gray);
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 12px;
        }

        .material-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--gray);
        }

        .file-info i {
            color: var(--primary);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: var(--radius);
            font-size: 13px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn:hover {
            background: var(--primary-light);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn i {
            font-size: 12px;
        }

        /* No Materials */
        .empty-state {
            padding: 40px 20px;
            text-align: center;
        }

        .empty-state i {
            font-size: 40px;
            color: var(--border);
            margin-bottom: 16px;
        }

        .empty-state h3 {
            font-size: 16px;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .empty-state p {
            color: var(--gray);
            font-size: 13px;
            margin-bottom: 20px;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .material-header {
                flex-direction: column;
                gap: 8px;
                align-items: flex-start;
            }
            
            .material-meta {
                margin-left: 0;
            }
            
            .material-actions {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }
            
            .file-info {
                justify-content: center;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .header-top {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {
            .stat-card {
                flex-direction: column;
                text-align: center;
                gap: 8px;
            }
            
            .empty-state {
                padding: 30px 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <a href="../test.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </a>
            </div>
            <div class="welcome-section">
                <h1>Hi, <?= htmlspecialchars($student_name) ?></h1>
                <p>Enrollment ID: <?= $enrollment ?> • Access your study materials</p>
            </div>
        </div>

       
        <!-- Materials Section -->
        <div class="materials-section">
            <div class="section-header">
                <h2><i class="fas fa-file-alt"></i> Study Materials</h2>
                <div class="materials-count"><?= $total_materials ?> materials</div>
            </div>

            <div class="materials-list">
                <?php if ($data->num_rows > 0): ?>
                    <?php 
                    $counter = 0;
                    while ($row = $data->fetch_assoc()): 
                        $counter++;
                        $upload_date = date('M d, Y', strtotime($row['uploaded_at']));
                        $file_size = !empty($row['file_size']) ? formatFileSize($row['file_size']) : 'N/A';
                    ?>
                        <div class="material-item">
                            <div class="material-header">
                                <div class="material-title">
                                    <?= htmlspecialchars($row['title']) ?>
                                </div>
                                <div class="material-meta">
                                    <i class="far fa-calendar"></i>
                                    <?= $upload_date ?>
                                </div>
                            </div>

                            <div class="material-body">
                                <?php if (!empty($row['description'])): ?>
                                    <div class="material-desc">
                                        <?= nl2br(htmlspecialchars(substr($row['description'], 0, 200))) ?>
                                        <?= strlen($row['description']) > 200 ? '...' : '' ?>
                                    </div>
                                <?php endif; ?>

                                <div class="material-actions">
                                    <div class="file-info">
                                        <i class="fas fa-file-pdf"></i>
                                        <span>PDF • <?= $file_size ?></span>
                                    </div>
                                    <a href="download.php?file=<?= urlencode($row['file_name']) ?>" class="btn">
                                        <i class="fas fa-download"></i>
                                        Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h3>No Study Materials</h3>
                        <p>You haven't been assigned any study material yet.</p>
                        <a href="../test.php" class="btn" style="width: auto; max-width: 200px;">
                           
                            Return to Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
    // Function to format file size
    function formatFileSize($bytes) {
        if ($bytes == 0) return '0 Bytes';
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
    ?>
</body>
</html>