<?php
include 'db_connect.php';

// Dashboard stats
$total_videos = $conn->query("SELECT COUNT(*) as total FROM videos")->fetch_assoc()['total'];
$total_students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$total_batches = $conn->query("SELECT COUNT(*) as total FROM batches")->fetch_assoc()['total'];

// Fetch all videos
$videos = $conn->query("SELECT * FROM videos ORDER BY uploaded_at DESC");

// Fetch batches and students for reassignment modal
$batches = $conn->query("SELECT * FROM batches");
$students = $conn->query("SELECT * FROM students");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Video Dashboard</title>
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
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
            --success: #10b981;
            --danger: #ef4444;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
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
            line-height: 1.5;
            min-height: 100vh;
        }

        /* Header */
        header {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 16px 0;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 1.25rem;
            color: var(--primary);
        }

        .logo-icon {
            font-size: 1.5rem;
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* Stats Section */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            text-align: center;
            transition: transform 0.2s ease;
        }

        .stat-box:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 12px;
            color: var(--primary);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Actions Section */
        .actions {
            display: flex;
            gap: 16px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .action-btn {
            background: var(--primary);
            color: var(--white);
            text-decoration: none;
            padding: 12px 24px;
            border-radius: var(--radius);
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
        }

        .action-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .action-btn.secondary {
            background: var(--white);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .action-btn.secondary:hover {
            background: var(--bg);
        }

        /* Table Styles */
        .table-container {
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th {
            background: var(--primary-light);
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
            color: var(--text);
            font-size: 0.9rem;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 16px 12px;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: var(--bg);
        }

        /* Table Content Styles */
        .thumbnail {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--radius);
        }

        .video-title {
            font-weight: 600;
            margin-bottom: 4px;
            color: var(--text);
        }

        .video-preview {
            max-width: 200px;
            border-radius: var(--radius);
            display: block;
        }

        .assigned-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-all {
            background: var(--primary-light);
            color: var(--primary-dark);
        }

        .badge-batch {
            background: #f0f9ff;
            color: #0369a1;
        }

        .badge-student {
            background: #f0fdf4;
            color: #166534;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 6px 12px;
            border-radius: var(--radius);
            border: none;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-family: 'Inter', sans-serif;
        }

        .btn-download {
            background: var(--success);
            color: white;
        }

        .btn-download:hover {
            background: #0da271;
        }

        .btn-delete {
            background: var(--danger);
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .btn-reassign {
            background: var(--primary);
            color: white;
        }

        .btn-reassign:hover {
            background: var(--primary-dark);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal-content {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 24px;
            max-width: 500px;
            width: 100%;
            box-shadow: var(--shadow-lg);
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text);
        }

        .close {
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
            background: none;
            border: none;
        }

        .close:hover {
            color: var(--text);
        }

        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: var(--text);
        }

        input, select, textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .hidden {
            display: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .action-btn {
                justify-content: center;
            }
            
            .container {
                padding: 20px 16px;
            }
            
            th, td {
                padding: 12px 8px;
            }
        }

        @media (max-width: 480px) {
            .stat-box {
                padding: 20px;
            }
            
            .stat-number {
                font-size: 1.75rem;
            }
            
            .modal-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <div class="logo">
            <span class="logo-icon">📊</span>
            <span>Admin Dashboard</span>
        </div>
    </div>
</header>

<div class="container">

    <!-- Stats -->
    <div class="stats">
        <div class="stat-box">
            <div class="stat-icon">🎬</div>
            <div class="stat-number"><?= $total_videos ?></div>
            <div class="stat-label">Total Videos</div>
        </div>
        <div class="stat-box">
            <div class="stat-icon">👨‍🎓</div>
            <div class="stat-number"><?= $total_students ?></div>
            <div class="stat-label">Total Students</div>
        </div>
        <div class="stat-box">
            <div class="stat-icon">📚</div>
            <div class="stat-number"><?= $total_batches ?></div>
            <div class="stat-label">Total Batches</div>
        </div>
    </div>

    <!-- Actions -->
    <div class="actions">
        <a href="admin_videos.php" class="action-btn">
            <span>+</span> Add New Video
        </a>
        <a href="assign_existing_video.php" class="action-btn secondary">
            <span>🔄</span> Reassign Videos
        </a>
    </div>

    <!-- Videos Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Thumbnail</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Assigned To</th>
                    <th>Video Preview</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($v = $videos->fetch_assoc()) { 
                    // Determine badge class based on assignment type
                    $badgeClass = '';
                    if($v['assigned_to'] == 'all') $badgeClass = 'badge-all';
                    elseif($v['assigned_to'] == 'batch') $badgeClass = 'badge-batch';
                    elseif($v['assigned_to'] == 'student') $badgeClass = 'badge-student';
                ?>
                <tr>
                    <td>
                        <?php if(!empty($v['thumbnail'])) { ?>
                            <img class="thumbnail" src="../uploads/thumbnails/<?= htmlspecialchars($v['thumbnail']) ?>" alt="Thumbnail">
                        <?php } else { ?>
                            <div style="width:80px; height:60px; background:var(--primary-light); border-radius:var(--radius); 
                                        display:flex; align-items:center; justify-content:center; color:var(--primary);">
                                🎥
                            </div>
                        <?php } ?>
                    </td>
                    <td>
                        <div class="video-title"><?= htmlspecialchars($v['title']) ?></div>
                    </td>
                    <td>
                        <div style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">
                            <?= htmlspecialchars($v['description']) ?>
                        </div>
                    </td>
                    <td>
                        <span class="assigned-badge <?= $badgeClass ?>">
                            <?php
                                if($v['assigned_to'] == 'all') echo "All Students";
                                elseif($v['assigned_to'] == 'batch') echo "Batch ID: ".$v['batch_id'];
                                elseif($v['assigned_to'] == 'student') echo "Student ID: ".$v['student_id'];
                            ?>
                        </span>
                    </td>
                    <td>
                        <video class="video-preview" controls>
                            <source src="../uploads/videos/<?= htmlspecialchars($v['filename']) ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="../uploads/videos/<?= htmlspecialchars($v['filename']) ?>" download class="btn btn-download">
                                <span>↓</span> Download
                            </a>
                            <a href="delete_video.php?id=<?= $v['id'] ?>" onclick="return confirm('Are you sure you want to delete this video?')" class="btn btn-delete">
                                <span>🗑</span> Delete
                            </a>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Reassign Modal -->
<div id="reassignModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Reassign Video</h3>
            <button class="close" onclick="closeReassignModal()">&times;</button>
        </div>
        <form id="reassignForm" method="post" action="reassign_video.php">
            <input type="hidden" name="video_id" id="video_id">
            
            <div class="form-group">
                <label for="assigned_to_select">Assign To:</label>
                <select name="assigned_to" id="assigned_to_select" onchange="toggleFields()">
                    <option value="all">All Students</option>
                    <option value="batch">Specific Batch</option>
                    <option value="student">Specific Student</option>
                </select>
            </div>

            <div id="batch_select" class="form-group hidden">
                <label for="batch_id">Select Batch</label>
                <select name="batch_id" id="batch_id">
                    <option value="">-- Select Batch --</option>
                    <?php
                    $batches->data_seek(0);
                    while($b = $batches->fetch_assoc()) {
                        echo "<option value='{$b['batch_id']}'>{$b['batch_name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div id="student_select" class="form-group hidden">
                <label for="student_id">Select Student</label>
                <select name="student_id" id="student_id">
                    <option value="">-- Select Student --</option>
                    <?php
                    $students->data_seek(0);
                    while($s = $students->fetch_assoc()) {
                        echo "<option value='{$s['student_id']}'>{$s['name']} (ID: {$s['student_id']})</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="action-btn" style="width:100%; justify-content:center; margin-top:10px;">
                Save Changes
            </button>
        </form>
    </div>
</div>

<script>
    // Function to open reassign modal
    function openReassignModal(videoId) {
        document.getElementById('video_id').value = videoId;
        document.getElementById('reassignModal').style.display = 'flex';
    }

    // Function to close reassign modal
    function closeReassignModal() {
        document.getElementById('reassignModal').style.display = 'none';
    }

    // Function to toggle batch/student selection fields
    function toggleFields() {
        const assignedTo = document.getElementById('assigned_to_select').value;
        const batchSelect = document.getElementById('batch_select');
        const studentSelect = document.getElementById('student_select');
        
        // Reset both to hidden
        batchSelect.classList.add('hidden');
        studentSelect.classList.add('hidden');
        
        // Show appropriate field
        if (assignedTo === 'batch') {
            batchSelect.classList.remove('hidden');
        } else if (assignedTo === 'student') {
            studentSelect.classList.remove('hidden');
        }
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('reassignModal');
        if (event.target === modal) {
            closeReassignModal();
        }
    }
</script>

</body>
</html>