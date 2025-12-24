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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }
        
        /* Color Scheme: 70% White, 20% Blue, 10% Accent */
        :root {
            --white-bg: #ffffff;
            --white-card: #ffffff;
            --blue-primary: #1e40af; /* Primary blue */
            --blue-light: #3b82f6; /* Light blue */
            --accent-color: #10b981; /* Emerald green accent */
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
        }
        
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        /* Header Section */
        .header {
            background-color: var(--white-bg);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }
        
        .header h1 {
            color: var(--text-primary);
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .header p {
            color: var(--text-secondary);
            font-size: 16px;
            font-weight: 400;
        }
        
        /* Stats Section - 20% Blue */
        .stats-section {
            background-color: var(--blue-primary);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            color: white;
        }
        
        .stats-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stats-item {
            text-align: center;
        }
        
        .stats-number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .stats-label {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 400;
        }
        
        /* Search Section - 70% White */
        .search-section {
            background-color: var(--white-card);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }
        
        .search-form {
            display: flex;
            gap: 12px;
            max-width: 600px;
        }
        
        .search-input {
            flex: 1;
            padding: 14px 20px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 15px;
            font-weight: 400;
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--blue-light);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .search-button {
            background-color: var(--blue-primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0 28px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .search-button:hover {
            background-color: var(--blue-light);
        }
        
        .clear-button {
            background-color: transparent;
            color: var(--blue-primary);
            border: 1px solid var(--blue-primary);
            border-radius: 8px;
            padding: 0 20px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .clear-button:hover {
            background-color: rgba(30, 64, 175, 0.05);
        }
        
        /* Table Section - 70% White */
        .table-section {
            background-color: var(--white-card);
            border-radius: 12px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background-color: #f8fafc;
            border-bottom: 2px solid var(--border-color);
        }
        
        th {
            padding: 20px 24px;
            text-align: left;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover {
            background-color: #f8fafc;
        }
        
        /* Student Photo */
        .student-photo {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid var(--border-color);
        }
        
        /* Student Info */
        .student-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .student-name {
            font-weight: 500;
            color: var(--text-primary);
            font-size: 16px;
        }
        
        .student-id {
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        /* Course Badge */
        .course-badge {
            display: inline-block;
            background-color: #f0f9ff;
            color: var(--blue-primary);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        
        /* Action Button - 10% Accent */
        .action-button {
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .action-button:hover {
            background-color: #0da271;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }
        
        /* No Results */
        .no-results {
            text-align: center;
            padding: 60px 24px;
        }
        
        .no-results-icon {
            font-size: 48px;
            margin-bottom: 16px;
            color: var(--border-color);
        }
        
        .no-results h3 {
            font-size: 18px;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .no-results p {
            color: var(--text-secondary);
            font-size: 15px;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 24px;
            color: var(--text-secondary);
            font-size: 14px;
            margin-top: 24px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 0 16px;
                margin: 20px auto;
            }
            
            .header {
                padding: 24px;
            }
            
            .stats-container {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }
            
            .stats-item {
                text-align: left;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            th, td {
                padding: 16px;
                font-size: 14px;
            }
            
            .action-button {
                padding: 8px 16px;
                font-size: 13px;
            }
        }
        
        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header (70% White) -->
        <div class="header fade-in">
            <h1>Student Fee Management</h1>
            <p>Manage and process student fee submissions efficiently</p>
        </div>
        
        <!-- Stats Section (20% Blue) -->
        <div class="stats-section fade-in">
            <div class="stats-container">
                <div class="stats-item">
                    <div class="stats-number"><?php echo $totalStudents; ?></div>
                    <div class="stats-label">Total Students</div>
                </div>
                
                <?php if (!empty($search)): ?>
                <div class="stats-item">
                    <div class="stats-number"><?php echo $filteredStudents; ?></div>
                    <div class="stats-label">Search Results</div>
                </div>
                
                <div class="stats-item">
                    <div class="stats-number" style="color: var(--accent-color);">
                        <?php echo round(($filteredStudents / $totalStudents) * 100, 1); ?>%
                    </div>
                    <div class="stats-label">Match Rate</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Search Section (70% White) -->
        <div class="search-section fade-in">
            <form method="GET" action="" class="search-form">
                <input 
                    type="text" 
                    name="search" 
                    class="search-input" 
                    placeholder="Search students by name, course, or enrollment ID..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                <button type="submit" class="search-button">Search</button>
                <?php if (!empty($search)): ?>
                <a href="?" class="clear-button">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Table Section (70% White) -->
        <div class="table-section fade-in">
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
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <img 
                                        src="../uploads/<?= htmlspecialchars($s['photo']) ?>" 
                                        alt="Student Photo" 
                                        class="student-photo"
                                        onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($s['name']) ?>&background=1e40af&color=fff&size=96&font-size=0.4&bold=true'"
                                    >
                                    <div class="student-info">
                                        <div class="student-name"><?= htmlspecialchars($s['name']) ?></div>
                                        <div class="student-id">ID: <?= htmlspecialchars($s['enrollment_id']) ?></div>
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
                <div class="no-results">
                    <div class="no-results-icon">📋</div>
                    <h3>No students found</h3>
                    <p>
                        <?php if (!empty($search)): ?>
                        No students match "<?php echo htmlspecialchars($search); ?>"
                        <?php else: ?>
                        No students found in the database
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($search)): ?>
                    <p style="margin-top: 16px;">
                        <a href="?" style="color: var(--blue-primary); text-decoration: none; font-weight: 500;">
                            ← View all students
                        </a>
                    </p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Student Management System. All rights reserved.</p>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add fade-in animation to table rows
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(10px)';
                
                setTimeout(() => {
                    row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 50);
            });
            
            // Highlight search terms in table
            const searchTerm = "<?php echo addslashes($search); ?>";
            if (searchTerm.trim() !== '') {
                const cells = document.querySelectorAll('td .student-name, td .course-badge');
                cells.forEach(cell => {
                    const original = cell.textContent;
                    const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                    const highlighted = original.replace(regex, '<span style="background-color: #fef3c7; padding: 1px 3px; border-radius: 2px;">$1</span>');
                    if (highlighted !== original) {
                        cell.innerHTML = highlighted;
                    }
                });
            }
            
            // Focus search input on page load
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>