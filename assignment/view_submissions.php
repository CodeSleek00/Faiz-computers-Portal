<?php
include '../database_connection/db_connect.php';

// Fetch all assignments
$assignment_result = $conn->query("SELECT * FROM assignments ORDER BY created_at DESC");

// Initialize filter variables
$filter_assignment = $_GET['assignment_id'] ?? null;
$filter_student = $_GET['student_name'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_from_date = $_GET['from_date'] ?? '';
$filter_to_date = $_GET['to_date'] ?? '';

// Build filter conditions
$filter_conditions = [];
$params = [];

if (!empty($filter_assignment)) {
    $filter_assignment = intval($filter_assignment);
    $filter_conditions[] = "s.assignment_id = ?";
    $params[] = $filter_assignment;
}

if (!empty($filter_student)) {
    $filter_conditions[] = "(st.name LIKE ? OR st.enrollment_id LIKE ?)";
    $params[] = "%$filter_student%";
    $params[] = "%$filter_student%";
}

if ($filter_status === 'graded') {
    $filter_conditions[] = "s.marks_awarded IS NOT NULL";
} elseif ($filter_status === 'ungraded') {
    $filter_conditions[] = "s.marks_awarded IS NULL";
}

if (!empty($filter_from_date)) {
    $filter_conditions[] = "s.submitted_at >= ?";
    $params[] = $filter_from_date . ' 00:00:00';
}

if (!empty($filter_to_date)) {
    $filter_conditions[] = "s.submitted_at <= ?";
    $params[] = $filter_to_date . ' 23:59:59';
}

$filter_condition = empty($filter_conditions) ? '' : 'WHERE ' . implode(' AND ', $filter_conditions);

// Prepare and execute query with filters
$submission_query = "
    SELECT s.*, st.name AS student_name, st.enrollment_id, a.title AS assignment_title
    FROM assignment_submissions s
    JOIN students st ON s.student_id = st.student_id
    JOIN assignments a ON s.assignment_id = a.assignment_id
    $filter_condition
    ORDER BY s.submitted_at DESC
";

$stmt = $conn->prepare($submission_query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$submissions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submissions Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #e0e7ff;
            --primary-dark: #3730a3;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --text: #1f2937;
            --text-light: #6b7280;
            --border: #e5e7eb;
            --bg: #f9fafb;
            --card-bg: #ffffff;
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
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title i {
            color: var(--primary);
        }

        .export-btn {
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s;
        }

        .export-btn:hover {
            background: var(--primary-dark);
        }

        .filters-section {
            padding: 1.5rem 2rem;
            background: var(--primary-light);
            border-bottom: 1px solid var(--border);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }

        .filter-group {
            margin-bottom: 0;
        }

        .filter-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text);
        }

        .filter-control {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: 0.875rem;
            background: var(--card-bg);
        }

        .filter-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 0.75rem;
            align-items: flex-end;
        }

        .filter-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-family: inherit;
            font-weight: 500;
            cursor: pointer;
            height: 38px;
        }

        .reset-btn {
            background: var(--card-bg);
            color: var(--text);
            border: 1px solid var(--border);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-family: inherit;
            font-weight: 500;
            cursor: pointer;
            height: 38px;
        }

        .table-container {
            overflow-x: auto;
            padding: 0 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            font-size: 0.875rem;
        }

        thead {
            background: var(--bg);
            border-bottom: 2px solid var(--border);
        }

        th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }

        tr:hover {
            background: var(--bg);
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-ungraded {
            background: var(--primary-light);
            color: var(--primary);
        }

        .status-graded {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .action-link {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            transition: background 0.2s;
        }

        .action-link:hover {
            background: var(--primary-light);
        }

        .file-link {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            color: var(--text);
            text-decoration: none;
        }

        .file-link:hover {
            color: var(--primary);
        }

        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--border);
        }

        .pagination-info {
            font-size: 0.875rem;
            color: var(--text-light);
        }

        .pagination-controls {
            display: flex;
            gap: 0.5rem;
        }

        .pagination-btn {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            background: var(--card-bg);
            cursor: pointer;
            font-family: inherit;
        }

        .pagination-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--border);
        }

        .empty-state p {
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .table-container {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="page-title">
                <i class="fas fa-inbox"></i> Submissions Management
            </h1>
            <a href="#" class="export-btn">
                <i class="fas fa-download"></i> Export
            </a>
        </div>

        <div class="filters-section">
            <form method="GET" id="filterForm">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="assignment_id">Assignment</label>
                        <select id="assignment_id" name="assignment_id" class="filter-control">
                            <option value="">All Assignments</option>
                            <?php 
                            $assignment_result->data_seek(0); // Reset pointer
                            while ($a = $assignment_result->fetch_assoc()) { ?>
                                <option value="<?= $a['assignment_id'] ?>" <?= ($a['assignment_id'] == $filter_assignment) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($a['title']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="student_name">Student Name/ID</label>
                        <input type="text" id="student_name" name="student_name" class="filter-control" 
                               placeholder="Search by name or ID" value="<?= htmlspecialchars($filter_student) ?>">
                    </div>

                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="filter-control">
                            <option value="">All Statuses</option>
                            <option value="graded" <?= ($filter_status === 'graded') ? 'selected' : '' ?>>Graded</option>
                            <option value="ungraded" <?= ($filter_status === 'ungraded') ? 'selected' : '' ?>>Ungraded</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="from_date">From Date</label>
                        <input type="date" id="from_date" name="from_date" class="filter-control" 
                               value="<?= htmlspecialchars($filter_from_date) ?>">
                    </div>

                    <div class="filter-group">
                        <label for="to_date">To Date</label>
                        <input type="date" id="to_date" name="to_date" class="filter-control" 
                               value="<?= htmlspecialchars($filter_to_date) ?>">
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="filter-btn">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button type="button" class="reset-btn" onclick="resetFilters()">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-container">
            <?php if ($submissions->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Assignment</th>
                            <th>Student</th>
                            <th>Enrollment ID</th>
                            <th>Submission</th>
                            <th>Status</th>
                            <th>Marks</th>
                            <th>Submitted At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($s = $submissions->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['assignment_title']) ?></td>
                                <td><?= htmlspecialchars($s['student_name']) ?></td>
                                <td><?= htmlspecialchars($s['enrollment_id']) ?></td>
                                <td>
                                    <?php if ($s['submitted_text']): ?>
                                        <div><?= substr(htmlspecialchars($s['submitted_text']), 0, 30) . (strlen($s['submitted_text']) > 30 ? '...' : '') ?></div>
                                    <?php endif; ?>
                                    <?php if ($s['submitted_file']): ?>
                                        <a href="../uploads/submissions/<?= $s['submitted_file'] ?>" target="_blank" class="file-link">
                                            <i class="fas fa-paperclip"></i> View File
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?= is_null($s['marks_awarded']) ? 'status-ungraded' : 'status-graded' ?>">
                                        <?= is_null($s['marks_awarded']) ? 'Pending' : 'Graded' ?>
                                    </span>
                                </td>
                                <td><?= is_null($s['marks_awarded']) ? '-' : $s['marks_awarded'] ?></td>
                                <td><?= date("d M Y, h:i A", strtotime($s['submitted_at'])) ?></td>
                                <td>
                                    <a href="grade_submission.php?id=<?= $s['submission_id'] ?>" class="action-link">
                                        <i class="fas fa-edit"></i> Grade
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No submissions found matching your criteria</p>
                    <button type="button" class="reset-btn" onclick="resetFilters()">
                        <i class="fas fa-undo"></i> Reset Filters
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <div class="pagination">
            <div class="pagination-info">
                Showing 1-10 of 50 submissions
            </div>
            <div class="pagination-controls">
                <button class="pagination-btn" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="pagination-btn active">1</button>
                <button class="pagination-btn">2</button>
                <button class="pagination-btn">3</button>
                <button class="pagination-btn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        function resetFilters() {
            document.getElementById('assignment_id').value = '';
            document.getElementById('student_name').value = '';
            document.getElementById('status').value = '';
            document.getElementById('from_date').value = '';
            document.getElementById('to_date').value = '';
            document.getElementById('filterForm').submit();
        }

        // Date validation
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            const fromDate = document.getElementById('from_date').value;
            const toDate = document.getElementById('to_date').value;
            
            if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
                alert('From date cannot be after To date');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>