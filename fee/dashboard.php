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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-light: #eff6ff;
            --primary-dark: #1d4ed8;
            --success: #10b981;
            --warning: #f59e0b;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
            --radius-sm: 0.375rem;
            --radius: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7eb 100%);
            color: var(--gray-900);
            min-height: 100vh;
            line-height: 1.5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        /* Header */
        .header {
            margin-bottom: 3rem;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.75rem;
            letter-spacing: -0.025em;
        }
        
        .header p {
            color: var(--gray-600);
            font-size: 1.125rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Main Card */
        .main-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            margin-bottom: 2rem;
            border: 1px solid var(--gray-200);
        }
        
        /* Search Section */
        .search-section {
            padding: 2.5rem;
            background: linear-gradient(135deg, var(--primary-light) 0%, #f0f9ff 100%);
            border-bottom: 1px solid var(--gray-200);
        }
        
        .search-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }
        
        .search-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            pointer-events: none;
        }
        
        .search-form {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .search-input {
            flex: 1;
            padding: 1rem 1rem 1rem 3.5rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 500;
            color: var(--gray-900);
            background: white;
            transition: all 0.2s ease;
            height: 56px;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .search-input::placeholder {
            color: var(--gray-400);
            font-weight: 400;
        }
        
        .search-button {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            padding: 0 2.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            height: 56px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            white-space: nowrap;
        }
        
        .search-button:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .clear-button {
            background: white;
            color: var(--gray-600);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            padding: 0 2rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            height: 56px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }
        
        .clear-button:hover {
            background: var(--gray-50);
            border-color: var(--gray-300);
        }
        
        /* Stats */
        .stats-container {
            padding: 2rem 2.5rem;
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
        }
        
        .stats {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .stat-card {
            background: white;
            border-radius: var(--radius-md);
            padding: 1.5rem;
            min-width: 200px;
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
            transition: all 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .stat-label {
            color: var(--gray-600);
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .stat-icon {
            width: 3rem;
            height: 3rem;
            background: var(--primary-light);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }
        
        /* Table */
        .table-section {
            overflow: hidden;
        }
        
        .table-header {
            padding: 1.5rem 2.5rem;
            background: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .table-count {
            color: var(--gray-600);
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .table-container {
            overflow-x: auto;
            padding: 0.5rem;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        .data-table thead {
            background: var(--gray-50);
        }
        
        .data-table th {
            padding: 1.25rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid var(--gray-200);
        }
        
        .data-table td {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            vertical-align: middle;
            transition: background-color 0.2s ease;
        }
        
        .data-table tbody tr {
            transition: all 0.2s ease;
        }
        
        .data-table tbody tr:hover {
            background: var(--primary-light);
            transform: scale(1.002);
        }
        
        .student-info {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }
        
        .student-avatar {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: var(--radius);
            overflow: hidden;
            border: 3px solid white;
            box-shadow: var(--shadow);
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--primary-light) 0%, #e0f2fe 100%);
        }
        
        .student-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .student-details h3 {
            font-weight: 600;
            margin-bottom: 0.375rem;
            color: var(--gray-900);
        }
        
        .student-details p {
            color: var(--gray-600);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .student-id {
            font-family: 'Monaco', 'Consolas', monospace;
            background: var(--gray-100);
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            color: var(--gray-700);
        }
        
        .course-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--success) 0%, #34d399 100%);
            color: white;
            padding: 0.625rem 1.25rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            box-shadow: var(--shadow-sm);
        }
        
        .action-button {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--radius);
            padding: 0.875rem 1.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow);
        }
        
        .action-button:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        /* Empty State */
        .empty-state {
            padding: 6rem 2rem;
            text-align: center;
        }
        
        .empty-icon {
            width: 6rem;
            height: 6rem;
            background: linear-gradient(135deg, var(--primary-light) 0%, #e0f2fe 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: var(--primary);
            font-size: 2.5rem;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--gray-900);
        }
        
        .empty-state p {
            color: var(--gray-600);
            max-width: 400px;
            margin: 0 auto 2rem;
            line-height: 1.6;
        }
        
        .search-term {
            background: var(--warning);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-sm);
            font-weight: 600;
        }
        
        .back-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }
        
        .back-link:hover {
            gap: 0.75rem;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 2rem;
            color: var(--gray-500);
            font-size: 0.875rem;
            margin-top: 3rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .search-section {
                padding: 1.5rem;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .search-input,
            .search-button,
            .clear-button {
                width: 100%;
                justify-content: center;
            }
            
            .stats {
                flex-direction: column;
            }
            
            .stat-card {
                min-width: auto;
            }
            
            .table-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .data-table th,
            .data-table td {
                padding: 1rem;
            }
        }
        
        /* Animations */
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
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .fade-in {
            animation: fadeIn 0.4s ease-out;
        }
        
        .slide-in {
            animation: slideIn 0.3s ease-out;
        }
    </style>
</head>
<body>
    <div class="container fade-in">
        <!-- Header -->
        <div class="header">
            <h1>Student Fee Management</h1>
            <p>Streamline fee submissions with an intuitive interface and powerful search</p>
        </div>
        
        <!-- Main Card -->
        <div class="main-card">
            <!-- Search Section -->
            <div class="search-section">
                <div class="search-container">
                    <form method="GET" action="" class="search-form">
                        <div style="position: relative; flex: 1;">
                            <svg class="search-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input 
                                type="text" 
                                name="search" 
                                class="search-input" 
                                placeholder="Search students by name, course, or enrollment ID..." 
                                value="<?php echo htmlspecialchars($search); ?>"
                                autocomplete="off"
                            >
                        </div>
                        <button type="submit" class="search-button">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Search
                        </button>
                        <?php if (!empty($search)): ?>
                        <a href="?" class="clear-button">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Clear
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <!-- Stats Section -->
            <div class="stats-container">
                <div class="stats">
                    <div class="stat-card fade-in" style="animation-delay: 0.1s">
                        <div class="stat-value">
                            <?php echo $totalStudents; ?>
                            <div class="stat-icon">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13 0a4 4 0 110 5.292"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-label">Total Students</div>
                    </div>
                    
                    <?php if (!empty($search)): ?>
                    <div class="stat-card fade-in" style="animation-delay: 0.2s">
                        <div class="stat-value">
                            <?php echo $filteredStudents; ?>
                            <div class="stat-icon">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-label">Search Results</div>
                    </div>
                    
                    <div class="stat-card fade-in" style="animation-delay: 0.3s">
                        <div class="stat-value">
                            <?php echo round(($filteredStudents / $totalStudents) * 100, 1); ?>%
                            <div class="stat-icon">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-label">Match Rate</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Table Section -->
            <div class="table-section">
                <div class="table-header">
                    <div class="table-title">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Student Records
                    </div>
                    <div class="table-count">
                        <?php echo $filteredStudents; ?> student<?php echo $filteredStudents != 1 ? 's' : ''; ?>
                        <?php if (!empty($search)): ?>
                        <span style="color: var(--primary); margin-left: 0.5rem;">
                            • Search active
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="table-container">
                    <?php if ($filteredStudents > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($s = $students->fetch_assoc()): ?>
                            <tr class="slide-in">
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">
                                            <img 
                                                src="../uploads/<?= htmlspecialchars($s['photo']) ?>" 
                                                alt="<?= htmlspecialchars($s['name']) ?>"
                                                onerror="this.style.display='none'; this.parentElement.innerHTML='<div style=\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-weight:600;color:#3b82f6;font-size:1.25rem\'>' + '<?= strtoupper(substr($s['name'], 0, 1)) ?>' + '</div>'"
                                            >
                                        </div>
                                        <div class="student-details">
                                            <h3><?= htmlspecialchars($s['name']) ?></h3>
                                            <p>
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                                </svg>
                                                <span class="student-id"><?= htmlspecialchars($s['enrollment_id']) ?></span>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="course-badge">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                        <?= htmlspecialchars($s['course_name']) ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="student_fee_select.php?enroll=<?= $s['enrollment_id'] ?>" class="action-button">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Submit Fee
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3>No students found</h3>
                        <p>
                            <?php if (!empty($search)): ?>
                            No students match your search for <span class="search-term">"<?php echo htmlspecialchars($search); ?>"</span>
                            <?php else: ?>
                            No student records are currently available in the system
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($search)): ?>
                        <a href="?" class="back-link">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            View all students
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Student Management System • Streamlining education administration</p>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Focus search input
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
            
            // Highlight search terms
            const searchTerm = "<?php echo addslashes($search); ?>";
            if (searchTerm.trim() !== '') {
                const elements = document.querySelectorAll('.student-details h3, .course-badge, .student-id');
                elements.forEach(element => {
                    const original = element.textContent || element.innerText;
                    const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                    const highlighted = original.replace(regex, '<mark style="background: linear-gradient(120deg, #fef3c7 0%, #fde68a 100%); color: #92400e; padding: 2px 6px; border-radius: 4px; font-weight: 600;">$1</mark>');
                    if (highlighted !== original) {
                        element.innerHTML = highlighted;
                    }
                });
            }
            
            // Add hover effects to table rows
            const tableRows = document.querySelectorAll('.data-table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.002)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
            
            // Smooth animations for table rows
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            tableRows.forEach(row => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(10px)';
                row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                observer.observe(row);
            });
            
            // Add subtle animation to search button on click
            const searchButton = document.querySelector('.search-button');
            if (searchButton) {
                searchButton.addEventListener('click', function(e) {
                    if (!searchInput.value.trim()) {
                        e.preventDefault();
                        searchInput.focus();
                        searchInput.style.borderColor = '#ef4444';
                        searchInput.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
                        setTimeout(() => {
                            searchInput.style.borderColor = '';
                            searchInput.style.boxShadow = '';
                        }, 1000);
                    }
                });
            }
        });
    </script>
</body>
</html>