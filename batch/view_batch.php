<?php
include '../database_connection/db_connect.php';

// Batch Search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM batches";
if (!empty($search)) {
    $query .= " WHERE batch_name LIKE '%" . $conn->real_escape_string($search) . "%'";
}
$batches = $conn->query($query);

// Student Batch Redirect
$student_search_error = '';
if (isset($_GET['student_query'])) {
    $student_query = $conn->real_escape_string(trim($_GET['student_query']));
    
    $find_student = $conn->query("
        SELECT b.batch_id
        FROM student_batches sb
        JOIN students s ON sb.student_id = s.student_id
        JOIN batches b ON sb.batch_id = b.batch_id
        WHERE s.name LIKE '%$student_query%' OR s.enrollment_id LIKE '%$student_query%'
        LIMIT 1
    ");

    if ($find_student->num_rows > 0) {
        $batch = $find_student->fetch_assoc();
        header("Location: view_batches.php?id=" . $batch['batch_id']);
        exit;
    } else {
        $student_search_error = "No batches found for this student";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batches Management | Learning System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --dark: #1a1a2e;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --border-radius: 12px;
            --box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            position: relative;
            display: inline-block;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        .search-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }

        .search-tabs {
            display: flex;
            border-bottom: 1px solid var(--gray-light);
            margin-bottom: 1.5rem;
        }

        .tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            font-weight: 500;
            color: var(--gray);
            border-bottom: 3px solid transparent;
            transition: var(--transition);
        }

        .tab.active {
            color: var(--primary);
            border-bottom: 3px solid var(--primary);
        }

        .search-box {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .search-box input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background-color: var(--light);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .error-message {
            color: var(--danger);
            background-color: rgba(247, 37, 133, 0.1);
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .error-message i {
            font-size: 1.2rem;
        }

        .batches-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .batch-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .batch-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }

        .batch-header {
            background: var(--primary-light);
            padding: 1.5rem;
            border-bottom: 1px solid rgba(67, 97, 238, 0.1);
        }

        .batch-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }

        .batch-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--gray);
            font-size: 0.875rem;
        }

        .batch-meta i {
            font-size: 0.9rem;
        }

        .batch-body {
            padding: 1.5rem;
        }

        .batch-info {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .info-item i {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 6px;
            font-size: 0.8rem;
        }

        .batch-actions {
            display: flex;
            gap: 0.75rem;
            border-top: 1px solid var(--gray-light);
            padding-top: 1.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.65rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            border: none;
            flex: 1;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary-light);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #d1146a;
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--gray-light);
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .empty-state p {
            color: var(--gray);
            max-width: 500px;
            margin: 0 auto 1.5rem;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .batches-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="page-title">Batches Management</h1>
            <a href="add_batch.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Batch
            </a>
        </div>

        <div class="search-section">
            <div class="search-tabs">
                <div class="tab active">Search Batches</div>
                <div class="tab">Find Student</div>
            </div>

            <form method="GET" action="">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search batches by name..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </form>

            <form method="GET" action="" class="search-box">
                <i class="fas fa-user-graduate"></i>
                <input type="text" name="student_query" placeholder="Find student by name or enrollment ID...">
            </form>

            <?php if (!empty($student_search_error)) { ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $student_search_error ?>
                </div>
            <?php } ?>
        </div>

        <?php if ($batches->num_rows > 0) { ?>
            <div class="batches-grid">
                <?php while ($batch = $batches->fetch_assoc()) { ?>
                    <div class="batch-card">
                        <div class="batch-header">
                            <h3 class="batch-title"><?= htmlspecialchars($batch['batch_name']) ?></h3>
                            <div class="batch-meta">
                                <span><i class="far fa-calendar-alt"></i> Created: <?= date('M d, Y', strtotime($batch['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="batch-body">
                            <div class="batch-info">
                                <div class="info-item">
                                    <i class="far fa-clock"></i>
                                    <span><?= htmlspecialchars($batch['timing']) ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                    <span>Instructor: <?= htmlspecialchars($batch['instructor'] ?? 'Not assigned') ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-users"></i>
                                    <span>Capacity: <?= htmlspecialchars($batch['capacity'] ?? '20') ?> students</span>
                                </div>
                            </div>
                            <div class="batch-actions">
                                <a href="view_batches.php?id=<?= $batch['batch_id'] ?>" class="btn btn-primary">
                                    <i class="far fa-eye"></i> View
                                </a>
                                <a href="edit_batch.php?id=<?= $batch['batch_id'] ?>" class="btn btn-outline">
                                    <i class="far fa-edit"></i> Edit
                                </a>
                                <a href="delete_batch.php?id=<?= $batch['batch_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this batch?')">
                                    <i class="far fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No Batches Found</h3>
                <p><?= !empty($search) ? "No batches match your search criteria." : "You haven't created any batches yet." ?></p>
                <a href="add_batch.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Your First Batch
                </a>
            </div>
        <?php } ?>
    </div>

    <script>
        // Simple tab switching functionality
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
            });
        });
    </script>
</body>
</html>