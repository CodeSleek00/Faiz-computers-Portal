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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            color: white;
            padding: 25px 30px;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .stats-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 20px 30px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #4b6cb7;
        }
        
        .stats-info {
            font-size: 18px;
            font-weight: 600;
        }
        
        .stats-info span {
            color: #4b6cb7;
        }
        
        .search-container {
            padding: 25px 30px;
            border-bottom: 1px solid #eee;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
        }
        
        .search-input {
            flex: 1;
            padding: 14px 18px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #4b6cb7;
            box-shadow: 0 0 0 3px rgba(75, 108, 183, 0.1);
        }
        
        .search-button {
            background-color: #4b6cb7;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .search-button:hover {
            background-color: #3a5795;
        }
        
        .clear-button {
            background-color: #f1f3f5;
            color: #666;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0 20px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .clear-button:hover {
            background-color: #e9ecef;
        }
        
        .table-container {
            padding: 0 30px 30px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        
        thead {
            background-color: #f8f9fa;
        }
        
        th {
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #e9ecef;
            font-size: 16px;
        }
        
        td {
            padding: 16px 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        tr:hover {
            background-color: #f8fafc;
        }
        
        .student-photo {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #e9ecef;
        }
        
        .student-name {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }
        
        .course-badge {
            display: inline-block;
            background-color: #e7f4ff;
            color: #0066cc;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .submit-btn {
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .submit-btn:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.2);
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 18px;
        }
        
        .no-results-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ddd;
        }
        
        .footer {
            padding: 20px 30px;
            text-align: center;
            color: #666;
            border-top: 1px solid #eee;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .stats-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            th, td {
                padding: 12px 10px;
                font-size: 14px;
            }
            
            .header {
                padding: 20px;
            }
            
            .search-container, .table-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Student Fee Management</h1>
            <p>Manage student monthly fee submissions</p>
        </div>
        
        <div class="stats-card">
            <div class="stats-info">
                Total Students: <span><?php echo $totalStudents; ?></span>
            </div>
            <?php if (!empty($search)): ?>
            <div class="stats-info">
                Filtered Students: <span><?php echo $filteredStudents; ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="search-container">
            <form method="GET" action="" class="search-form">
                <input 
                    type="text" 
                    name="search" 
                    class="search-input" 
                    placeholder="Search by name, course, or enrollment ID..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                <button type="submit" class="search-button">Search</button>
                <?php if (!empty($search)): ?>
                <a href="?" class="clear-button">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="table-container">
            <?php if ($filteredStudents > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($s = $students->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <img 
                                src="../uploads/<?= htmlspecialchars($s['photo']) ?>" 
                                alt="Student Photo" 
                                class="student-photo"
                                onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($s['name']) ?>&background=4b6cb7&color=fff&size=50'"
                            >
                        </td>
                        <td>
                            <div class="student-name"><?= htmlspecialchars($s['name']) ?></div>
                            <div style="font-size: 14px; color: #666; margin-top: 3px;">
                                ID: <?= htmlspecialchars($s['enrollment_id']) ?>
                            </div>
                        </td>
                        <td><span class="course-badge"><?= htmlspecialchars($s['course_name']) ?></span></td>
                        <td>
                            <a href="student_fee_select.php?enroll=<?= $s['enrollment_id'] ?>" class="submit-btn">
                                Submit Fee
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-results">
                <div class="no-results-icon">📭</div>
                <p>No students found<?php echo !empty($search) ? ' for "' . htmlspecialchars($search) . '"' : ''; ?>.</p>
                <?php if (!empty($search)): ?>
                <p style="margin-top: 10px;">Try a different search term or <a href="?" style="color: #4b6cb7;">clear the search</a>.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p>Student Fee Management System &copy; <?php echo date('Y'); ?></p>
        </div>
    </div>
    
    <script>
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight search term in table rows
            const searchTerm = "<?php echo addslashes($search); ?>";
            if (searchTerm) {
                const tableCells = document.querySelectorAll('td');
                tableCells.forEach(cell => {
                    const originalContent = cell.textContent;
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    const highlighted = originalContent.replace(regex, '<mark style="background-color: #fff9c4; padding: 2px 0; border-radius: 2px;">$1</mark>');
                    if (highlighted !== originalContent) {
                        cell.innerHTML = highlighted;
                    }
                });
            }
            
            // Add animation to table rows
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(10px)';
                
                setTimeout(() => {
                    row.style.transition = 'opacity 0.3s, transform 0.3s';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 50);
            });
        });
    </script>
</body>
</html>