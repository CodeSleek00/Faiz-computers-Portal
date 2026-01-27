<?php
// admin_students.php
session_start();
include("../database_connection/db_connect.php");

/* ================= FETCH STUDENTS WITH FULL DETAILS ================= */
$sql = "
SELECT 
    s.id,
    s.photo,
    s.name,
    s.contact,
    s.enrollment_id,
    s.course,
    a.aadhar,
    a.apaar,
    a.email,
    a.religion,
    a.caste,
    a.address,
    a.permanent_address,
    a.dob,
    a.father_name,
    a.mother_name,
    a.parent_contact,
    a.course_name,
    a.duration,
    a.admission_date
FROM students26 s
LEFT JOIN admission a 
ON s.enrollment_id = a.enrollment_id
ORDER BY s.id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Student Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: #f4f6f9;
    color: #333;
}

.container {
    padding: 20px;
    max-width: 100%;
    overflow: hidden;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.header h1 {
    color: #2563eb;
    font-size: 24px;
    font-weight: 600;
}

.controls {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}

.search-box {
    position: relative;
    min-width: 250px;
}

.search-box input {
    width: 100%;
    padding: 10px 15px 10px 40px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    transition: all 0.3s;
}

.search-box input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
}

.filter-btn {
    background: #fff;
    border: 1px solid #d1d5db;
    padding: 10px 15px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    color: #4b5563;
    transition: all 0.3s;
}

.filter-btn:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    padding: 20px;
    margin-bottom: 20px;
    overflow: hidden;
}

/* Table Styling */
.table-container {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    max-height: calc(100vh - 200px);
    position: relative;
}

/* Custom scrollbar */
.table-wrapper::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}

.table-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.table-wrapper::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.table-wrapper::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

table {
    border-collapse: collapse;
    width: 100%;
    min-width: 1400px;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
    vertical-align: top;
    white-space: normal;
    word-break: break-word;
    position: relative;
}

/* Fixed first column (Photo) */
th:first-child,
td:first-child {
    position: sticky;
    left: 0;
    background: white;
    z-index: 5;
    border-right: 2px solid #e5e7eb;
    min-width: 80px;
}

/* Fixed second column (Enrollment ID) */
th:nth-child(2),
td:nth-child(2) {
    position: sticky;
    left: 80px; /* Photo column width */
    background: white;
    z-index: 5;
    border-right: 2px solid #e5e7eb;
    min-width: 120px;
}

/* Fixed last column (Action) */
th:last-child,
td:last-child {
    position: sticky;
    right: 0;
    background: white;
    z-index: 5;
    border-left: 2px solid #e5e7eb;
    min-width: 100px;
}

/* Header background for fixed columns */
th:first-child,
th:nth-child(2),
th:last-child {
    background: #2563eb;
    color: white;
    z-index: 10;
}

th {
    background: #2563eb;
    color: white;
    font-weight: 500;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 4;
}

tr:hover {
    background-color: #f8fafc;
}

tr:hover td:first-child,
tr:hover td:nth-child(2),
tr:hover td:last-child {
    background-color: #f8fafc;
}

img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e5e7eb;
}

.btn {
    background: #2563eb;
    color: white;
    padding: 8px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    display: inline-block;
    border: none;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s;
    text-align: center;
    margin: 2px;
}

.btn:hover {
    background: #1e40af;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.small {
    font-size: 12px;
    color: #374151;
    max-width: 150px;
    line-height: 1.4;
}

.btn-view {
    background: #16a34a;
}

.btn-view:hover {
    background: #15803d;
}

.btn-delete {
    background: #dc2626;
}

.btn-delete:hover {
    background: #b91c1c;
}

/* Mobile responsive */
@media (max-width: 1200px) {
    .container {
        padding: 15px;
    }
    
    .header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .controls {
        width: 100%;
    }
    
    .search-box {
        min-width: 100%;
    }
    
    th, td {
        padding: 10px 12px;
        font-size: 14px;
    }
}

@media (max-width: 768px) {
    .container {
        padding: 10px;
    }
    
    .header h1 {
        font-size: 20px;
    }
    
    th, td {
        padding: 8px 10px;
        font-size: 13px;
    }
    
    img {
        width: 40px;
        height: 40px;
    }
    
    .btn {
        padding: 6px 10px;
        font-size: 12px;
    }
    
    table {
        min-width: 1200px;
    }
}

/* Loading animation */
.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6b7280;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    color: #d1d5db;
}

.empty-state p {
    font-size: 16px;
}

/* Column visibility toggle */
.column-toggle {
    display: none;
    position: absolute;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    z-index: 100;
    min-width: 200px;
    max-height: 300px;
    overflow-y: auto;
    right: 0;
    top: 40px;
}

.column-toggle.show {
    display: block;
}

.column-toggle label {
    display: block;
    padding: 8px 0;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
}

.column-toggle label:last-child {
    border-bottom: none;
}

.column-toggle input[type="checkbox"] {
    margin-right: 10px;
}

/* Info badge for scroll hint */
.scroll-hint {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #2563eb;
    color: white;
    padding: 10px 15px;
    border-radius: 50px;
    font-size: 14px;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 1000;
    animation: fadeInUp 0.5s ease;
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

/* Print styles */
@media print {
    .header, .controls, .btn {
        display: none;
    }
    
    .table-wrapper {
        overflow: visible;
    }
    
    table {
        min-width: 100%;
    }
    
    th:first-child,
    td:first-child,
    th:nth-child(2),
    td:nth-child(2),
    th:last-child,
    td:last-child {
        position: static;
        border: 1px solid #ddd;
    }
}
</style>
</head>

<body>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-users"></i> Student Dashboard (Full Details)</h1>
        
        <div class="controls">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search students by name, enrollment ID, contact...">
            </div>
            
            <button class="filter-btn" id="filterBtn">
                <i class="fas fa-filter"></i> Filter Columns
            </button>
            
            <button class="btn" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-container">
            <div class="table-wrapper" id="tableWrapper">
                <table id="studentsTable">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Enrollment ID</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Aadhar</th>
                            <th>Apaar</th>
                            <th>Religion</th>
                            <th>Caste</th>
                            <th>DOB</th>
                            <th>Father</th>
                            <th>Mother</th>
                            <th>Parent Contact</th>
                            <th>Address</th>
                            <th>Permanent Address</th>
                            <th>Course</th>
                            <th>Duration (Months)</th>
                            <th>Admission Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <img src="../uploads/<?= htmlspecialchars($row['photo']) ?>" 
                                         alt="<?= htmlspecialchars($row['name']) ?>'s photo"
                                         onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($row['name']) ?>&background=2563eb&color=fff'">
                                </td>

                                <td><?= htmlspecialchars($row['enrollment_id']) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['contact']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['aadhar']) ?></td>
                                <td><?= htmlspecialchars($row['apaar']) ?></td>
                                <td><?= htmlspecialchars($row['religion']) ?></td>
                                <td><?= htmlspecialchars($row['caste']) ?></td>
                                <td><?= htmlspecialchars($row['dob']) ?></td>
                                <td><?= htmlspecialchars($row['father_name']) ?></td>
                                <td><?= htmlspecialchars($row['mother_name']) ?></td>
                                <td><?= htmlspecialchars($row['parent_contact']) ?></td>

                                <td class="small"><?= nl2br(htmlspecialchars($row['address'])) ?></td>
                                <td class="small"><?= nl2br(htmlspecialchars($row['permanent_address'])) ?></td>

                                <td><?= htmlspecialchars($row['course_name']) ?></td>
                                <td><?= htmlspecialchars($row['duration']) ?></td>
                                <td><?= htmlspecialchars($row['admission_date']) ?></td>

                                <td>
                                    <a class="btn" href="edit_student.php?id=<?= $row['id'] ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a class="btn btn-view" 
                                       href="../admission/admission_success.php?eid=<?= urlencode($row['enrollment_id']) ?>"
                                       target="_blank">
                                        <i class="fas fa-eye"></i> View Form
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="19">
                                    <div class="empty-state">
                                        <i class="fas fa-user-slash"></i>
                                        <p>No students found. Please add some students first.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Column Toggle Menu -->
<div class="column-toggle" id="columnToggle">
    <h4 style="margin-bottom: 10px; color: #2563eb;">Show/Hide Columns</h4>
    <label><input type="checkbox" checked data-column="0"> Photo</label>
    <label><input type="checkbox" checked data-column="1"> Enrollment ID</label>
    <label><input type="checkbox" checked data-column="2"> Name</label>
    <label><input type="checkbox" checked data-column="3"> Contact</label>
    <label><input type="checkbox" checked data-column="4"> Email</label>
    <label><input type="checkbox" checked data-column="5"> Aadhar</label>
    <label><input type="checkbox" checked data-column="6"> Apaar</label>
    <label><input type="checkbox" checked data-column="7"> Religion</label>
    <label><input type="checkbox" checked data-column="8"> Caste</label>
    <label><input type="checkbox" checked data-column="9"> DOB</label>
    <label><input type="checkbox" checked data-column="10"> Father</label>
    <label><input type="checkbox" checked data-column="11"> Mother</label>
    <label><input type="checkbox" checked data-column="12"> Parent Contact</label>
    <label><input type="checkbox" checked data-column="13"> Address</label>
    <label><input type="checkbox" checked data-column="14"> Permanent Address</label>
    <label><input type="checkbox" checked data-column="15"> Course</label>
    <label><input type="checkbox" checked data-column="16"> Duration</label>
    <label><input type="checkbox" checked data-column="17"> Admission Date</label>
    <label><input type="checkbox" checked data-column="18"> Action</label>
</div>

<!-- Scroll Hint -->
<div class="scroll-hint" id="scrollHint">
    <i class="fas fa-arrow-left-right"></i> Scroll horizontally to view more columns
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');
    const filterBtn = document.getElementById('filterBtn');
    const columnToggle = document.getElementById('columnToggle');
    const scrollHint = document.getElementById('scrollHint');
    const tableWrapper = document.getElementById('tableWrapper');
    const rows = tableBody.getElementsByTagName('tr');
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        for(let row of rows) {
            const cells = row.getElementsByTagName('td');
            let found = false;
            
            for(let cell of cells) {
                if(cell.textContent.toLowerCase().includes(searchTerm)) {
                    found = true;
                    break;
                }
            }
            
            row.style.display = found ? '' : 'none';
        }
    });
    
    // Column toggle functionality
    filterBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        columnToggle.classList.toggle('show');
    });
    
    // Close column toggle when clicking outside
    document.addEventListener('click', function() {
        columnToggle.classList.remove('show');
    });
    
    // Prevent column toggle from closing when clicking inside
    columnToggle.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Handle column visibility
    const checkboxes = columnToggle.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const columnIndex = parseInt(this.getAttribute('data-column'));
            const ths = document.querySelectorAll('th');
            const tds = document.querySelectorAll('td');
            
            // Toggle header
            if(ths[columnIndex]) {
                ths[columnIndex].style.display = this.checked ? '' : 'none';
            }
            
            // Toggle cells
            for(let i = 0; i < tds.length; i++) {
                if(i % 19 === columnIndex) { // 19 columns total
                    tds[i].style.display = this.checked ? '' : 'none';
                }
            }
        });
    });
    
    // Hide scroll hint after 5 seconds
    setTimeout(() => {
        scrollHint.style.opacity = '0';
        setTimeout(() => {
            scrollHint.style.display = 'none';
        }, 500);
    }, 5000);
    
    // Show scroll hint on horizontal scroll
    let scrollHintTimeout;
    tableWrapper.addEventListener('scroll', function() {
        if(this.scrollLeft > 50) {
            scrollHint.style.display = 'none';
        }
        
        clearTimeout(scrollHintTimeout);
        scrollHintTimeout = setTimeout(() => {
            if(this.scrollLeft === 0) {
                scrollHint.style.display = 'flex';
                scrollHint.style.opacity = '1';
            }
        }, 10000);
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+F to focus search
        if(e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            searchInput.focus();
        }
        
        // Escape to close column toggle
        if(e.key === 'Escape') {
            columnToggle.classList.remove('show');
        }
    });
    
    // Handle image errors
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('error', function() {
            this.src = 'https://ui-avatars.com/api/?name=Student&background=2563eb&color=fff';
        });
    });
});
</script>

</body>
</html>