<?php
include("db_connect.php");

// Get search parameter if exists
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query with search filter
$query = "
    SELECT DISTINCT enrollment_id, name, photo, course_name
    FROM student_monthly_fee
";

if (!empty($search)) {
    $query .= " WHERE name LIKE '%" . $conn->real_escape_string($search) . "%' 
                OR course_name LIKE '%" . $conn->real_escape_string($search) . "%'
                OR enrollment_id LIKE '%" . $conn->real_escape_string($search) . "%'";
}

$query .= " ORDER BY name ASC";

$students = $conn->query($query);

// Get total count of students
$countQuery = "SELECT COUNT(DISTINCT enrollment_id) as total FROM student_monthly_fee";
$countResult = $conn->query($countQuery);
$totalStudents = $countResult->fetch_assoc()['total'];

// For filtered count
if (!empty($search)) {
    $filteredQuery = "SELECT COUNT(DISTINCT enrollment_id) as filtered FROM student_monthly_fee 
                      WHERE name LIKE '%" . $conn->real_escape_string($search) . "%' 
                      OR course_name LIKE '%" . $conn->real_escape_string($search) . "%'
                      OR enrollment_id LIKE '%" . $conn->real_escape_string($search) . "%'";
    $filteredResult = $conn->query($filteredQuery);
    $filteredStudents = $filteredResult->fetch_assoc()['filtered'];
} else {
    $filteredStudents = $totalStudents;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Fee Management</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #dbeafe;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
        }
        
        body {
            background: var(--gray-50);
            color: var(--gray-900);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 4px;
        }
        
        .header p {
            color: var(--gray-600);
            font-size: 14px;
        }
        
        /* Search */
        .search-container {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            max-width: 600px;
        }
        
        .search-form {
            display: flex;
            gap: 12px;
        }
        
        .search-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .search-button {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0 24px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
        }
        
        .clear-button {
            background: transparent;
            color: var(--gray-600);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 0 20px;
            font-size: 15px;
            cursor: pointer;
        }
        
        /* Stats */
        .stats {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            padding: 20px;
            min-width: 200px;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 4px;
        }
        
        .stat-label {
            color: var(--gray-600);
            font-size: 14px;
        }
        
        /* Table */
        .table-container {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            padding: 16px 24px;
            text-align: left;
            font-weight: 500;
            color: var(--gray-600);
            font-size: 14px;
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
        }
        
        td {
            padding: 20px 24px;
            border-bottom: 1px solid var(--gray-200);
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover {
            background: var(--gray-50);
        }
        
        .student-cell {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .student-avatar {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background: var(--primary-light);
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .student-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .student-info h4 {
            font-weight: 500;
            margin-bottom: 4px;
        }
        
        .student-info p {
            color: var(--gray-600);
            font-size: 13px;
        }
        
        .course-badge {
            display: inline-block;
            padding: 6px 12px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .action-button {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        /* Empty State */
        .empty-state {
            padding: 60px 24px;
            text-align: center;
            color: var(--gray-600);
        }
        
        .empty-state h3 {
            font-size: 18px;
            margin-bottom: 8px;
            color: var(--gray-900);
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-200);
            color: var(--gray-600);
            font-size: 14px;
            text-align: center;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 16px;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .stats {
                flex-direction: column;
            }
            
            .stat-item {
                min-width: auto;
            }
            
            th, td {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Student Fee Management</h1>
            <p>Submit and manage student fees</p>
        </div>
        
        <!-- Search -->
        <div class="search-container">
            <form method="GET" action="" class="search-form">
                <input 
                    type="text" 
                    name="search" 
                    class="search-input" 
                    placeholder="Search students..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                <button type="submit" class="search-button">Search</button>
                <?php if (!empty($search)): ?>
                <a href="?" class="clear-button">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Stats -->
        <div class="stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo $totalStudents; ?></div>
                <div class="stat-label">Total Students</div>
            </div>
            
            <?php if (!empty($search)): ?>
            <div class="stat-item">
                <div class="stat-number"><?php echo $filteredStudents; ?></div>
                <div class="stat-label">Search Results</div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Table -->
        <div class="table-container">
            <?php if ($filteredStudents > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($s = $students->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="student-cell">
                                <div class="student-avatar">
                                    <img 
                                        src="../uploads/<?= htmlspecialchars($s['photo']) ?>" 
                                        alt="<?= htmlspecialchars($s['name']) ?>"
                                        onerror="this.style.display='none'; this.parentElement.style.backgroundColor='#dbeafe'; this.parentElement.innerHTML='<span style=\'color:#2563eb;font-weight:500\'>' + '<?= strtoupper(substr($s['name'], 0, 1)) ?>' + '</span>'"
                                    >
                                </div>
                                <div class="student-info">
                                    <h4><?= htmlspecialchars($s['name']) ?></h4>
                                    <p>ID: <?= htmlspecialchars($s['enrollment_id']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="course-badge"><?= htmlspecialchars($s['course_name']) ?></span>
                        </td>
                        <td style="text-align: right;">
                            <a href="student_fee_select.php?enroll=<?= $s['enrollment_id'] ?>" class="action-button">
                                Submit Fee
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <h3>No students found</h3>
                <p>
                    <?php if (!empty($search)): ?>
                    No results for "<?php echo htmlspecialchars($search); ?>"
                    <?php else: ?>
                    No student records available
                    <?php endif; ?>
                </p>
                <?php if (!empty($search)): ?>
                <p style="margin-top: 16px;">
                    <a href="?" style="color: var(--primary); text-decoration: none;">
                        ← View all students
                    </a>
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Student Management System</p>
        </div>
    </div>
</body>
</html>