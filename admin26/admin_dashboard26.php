<?php
session_start();
include("../database_connection/db_connect.php");

// FETCH STUDENTS WITH FULL DETAILS
$sql = "
SELECT 
    s.id,
    s.photo,
    s.name,
    s.contact,
    s.enrollment_id,
    a.email,
    a.aadhar,
    a.apaar,
    a.religion,
    a.caste,
    a.dob,
    a.father_name,
    a.mother_name,
    a.parent_contact,
    a.address,
    a.permanent_address,
    a.course_name,
    a.duration,
    a.admission_date
FROM students26 s
LEFT JOIN admission a ON s.enrollment_id = a.enrollment_id
ORDER BY s.id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Management | Admin Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
    --secondary: #64748b;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --background: #f8fafc;
    --surface: #ffffff;
    --border: #e2e8f0;
    --text: #1e293b;
    --text-light: #64748b;
    --shadow: 0 1px 3px rgba(0,0,0,0.1);
    --radius: 8px;
    --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    background: var(--background);
    color: var(--text);
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
}

.container {
    padding: 1.5rem;
    max-width: 100%;
}

/* HEADER */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.header h1 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.header h1 i {
    color: var(--primary);
}

.controls {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    flex-wrap: wrap;
}

.search-container {
    position: relative;
    min-width: 280px;
}

.search-container input {
    width: 100%;
    padding: 0.625rem 1rem 0.625rem 2.75rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    font-size: 0.875rem;
    transition: var(--transition);
    color: var(--text);
}

.search-container input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.search-container i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-light);
    font-size: 0.875rem;
}

.btn {
    padding: 0.625rem 1rem;
    border: none;
    border-radius: var(--radius);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition);
    background: var(--surface);
    color: var(--text);
    border: 1px solid var(--border);
}

.btn:hover {
    background: #f8fafc;
    border-color: var(--primary);
    transform: translateY(-1px);
    box-shadow: var(--shadow);
}

.btn:active {
    transform: translateY(0);
}

.btn:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}

.btn-primary {
    background: var(--primary);
    color: white;
    border: 1px solid var(--primary);
}

.btn-primary:hover {
    background: var(--primary-dark);
    border-color: var(--primary-dark);
}

/* TABLE CONTAINER */
.table-container {
    background: var(--surface);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    max-height: calc(100vh - 200px);
}

.table-wrapper::-webkit-scrollbar {
    height: 6px;
    width: 6px;
}

.table-wrapper::-webkit-scrollbar-track {
    background: var(--background);
}

.table-wrapper::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.table-wrapper::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1400px;
}

th {
    background: var(--primary);
    color: white;
    font-weight: 600;
    font-size: 0.8125rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.875rem 1rem;
    text-align: left;
    position: sticky;
    top: 0;
    z-index: 10;
    white-space: nowrap;
}

th:first-child {
    border-radius: var(--radius) 0 0 0;
}

th:last-child {
    border-radius: 0 var(--radius) 0 0;
}

td {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
    font-size: 0.875rem;
    vertical-align: middle;
    background: var(--surface);
}

tbody tr {
    transition: var(--transition);
}

tbody tr:hover {
    background: #f8fafc;
}

/* FIXED COLUMNS */
th:first-child,
td:first-child {
    position: sticky;
    left: 0;
    background: inherit;
    z-index: 5;
}

th:last-child,
td:last-child {
    position: sticky;
    right: 0;
    background: inherit;
    z-index: 5;
    box-shadow: -1px 0 0 var(--border);
}

th:first-child,
th:last-child {
    z-index: 11;
}

/* CONTENT STYLES */
.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border);
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    background: #f1f5f9;
    color: var(--text-light);
}

.text-truncate {
    max-width: 150px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.text-sm {
    font-size: 0.8125rem;
    color: var(--text-light);
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    min-width: 140px;
}

.action-btn {
    padding: 0.5rem;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: var(--text-light);
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-btn:hover {
    background: #f1f5f9;
    color: var(--text);
}

.action-btn:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}

.action-btn.edit:hover {
    background: #dbeafe;
    color: var(--primary);
}

.action-btn.view:hover {
    background: #dcfce7;
    color: var(--success);
}

/* EMPTY STATE */
.empty-state {
    padding: 4rem 2rem;
    text-align: center;
    color: var(--text-light);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* LOADING SKELETON */
.skeleton {
    animation: pulse 2s infinite;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
}

@keyframes pulse {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* DROPDOWN */
.dropdown {
    position: relative;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    background: var(--surface);
    border-radius: var(--radius);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    padding: 0.5rem;
    min-width: 200px;
    z-index: 1000;
    display: none;
}

.dropdown-menu.show {
    display: block;
    animation: slideDown 0.2s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    border-radius: 6px;
    cursor: pointer;
    color: var(--text);
    transition: var(--transition);
}

.dropdown-item:hover {
    background: #f1f5f9;
}

.dropdown-item input[type="checkbox"] {
    margin-right: 0.5rem;
}

/* ACCESSIBILITY */
.visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}

@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* RESPONSIVE */
@media (max-width: 1024px) {
    .container {
        padding: 1rem;
    }
    
    .header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .controls {
        width: 100%;
    }
    
    .search-container {
        min-width: 100%;
    }
}

@media (max-width: 768px) {
    .header h1 {
        font-size: 1.25rem;
    }
    
    .btn span {
        display: none;
    }
    
    .btn i {
        margin: 0;
    }
    
    .action-buttons {
        flex-direction: column;
        min-width: auto;
    }
}

@media print {
    .header,
    .controls,
    .action-buttons {
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
    th:last-child,
    td:last-child {
        position: static;
    }
}
</style>
</head>

<body>
<div class="container">
    <!-- HEADER -->
    <header class="header">
        <h1>
            <i class="fas fa-users"></i>
            Student Management
        </h1>
        
        <div class="controls">
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input 
                    type="search" 
                    id="searchInput" 
                    placeholder="Search students..."
                    aria-label="Search students"
                >
            </div>
            
            <div class="dropdown">
                <button class="btn" id="filterBtn" aria-expanded="false" aria-haspopup="true">
                    <i class="fas fa-sliders-h"></i>
                    <span>Columns</span>
                </button>
                
                <div class="dropdown-menu" id="columnMenu" role="menu">
                    <div class="dropdown-item">
                        <input type="checkbox" id="col-photo" data-column="0" checked>
                        <label for="col-photo">Photo</label>
                    </div>
                    <div class="dropdown-item">
                        <input type="checkbox" id="col-enrollment" data-column="1" checked>
                        <label for="col-enrollment">Enrollment ID</label>
                    </div>
                    <div class="dropdown-item">
                        <input type="checkbox" id="col-name" data-column="2" checked>
                        <label for="col-name">Name</label>
                    </div>
                    <div class="dropdown-item">
                        <input type="checkbox" id="col-contact" data-column="3" checked>
                        <label for="col-contact">Contact</label>
                    </div>
                    <div class="dropdown-item">
                        <input type="checkbox" id="col-email" data-column="4" checked>
                        <label for="col-email">Email</label>
                    </div>
                    <div class="dropdown-item">
                        <input type="checkbox" id="col-aadhar" data-column="5">
                        <label for="col-aadhar">Aadhar</label>
                    </div>
                    <div class="dropdown-item">
                        <input type="checkbox" id="col-apaar" data-column="6">
                        <label for="col-apaar">Apaar</label>
                    </div>
                    <div class="dropdown-item">
                        <input type="checkbox" id="col-course" data-column="16" checked>
                        <label for="col-course">Course</label>
                    </div>
                    <div class="dropdown-item">
                        <input type="checkbox" id="col-admission" data-column="18">
                        <label for="col-admission">Admission Date</label>
                    </div>
                </div>
            </div>
            
            <button class="btn btn-primary" onclick="window.print()" aria-label="Print table">
                <i class="fas fa-print"></i>
                <span>Print</span>
            </button>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="table-container">
        <div class="table-wrapper" id="tableWrapper">
            <table id="studentsTable" role="grid" aria-label="Students list">
                <thead>
                    <tr>
                        <th scope="col">Photo</th>
                        <th scope="col">Enrollment ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Contact</th>
                        <th scope="col">Email</th>
                        <th scope="col">Aadhar</th>
                        <th scope="col">Apaar</th>
                        <th scope="col">Religion</th>
                        <th scope="col">Caste</th>
                        <th scope="col">DOB</th>
                        <th scope="col">Father</th>
                        <th scope="col">Mother</th>
                        <th scope="col">Parent Contact</th>
                        <th scope="col">Address</th>
                        <th scope="col">Permanent Address</th>
                        <th scope="col">Course</th>
                        <th scope="col">Duration</th>
                        <th scope="col">Admission Date</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr role="row">
                            <td>
                                <img 
                                    src="../uploads/<?= htmlspecialchars($row['photo']) ?>" 
                                    alt="<?= htmlspecialchars($row['name']) ?>" 
                                    class="avatar"
                                    onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($row['name']) ?>&background=2563eb&color=fff'"
                                >
                            </td>
                            <td>
                                <span class="badge"><?= htmlspecialchars($row['enrollment_id']) ?></span>
                            </td>
                            <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                            <td><?= htmlspecialchars($row['contact']) ?></td>
                            <td class="text-truncate"><?= htmlspecialchars($row['email']) ?></td>
                            <td class="text-sm"><?= htmlspecialchars($row['aadhar']) ?></td>
                            <td class="text-sm"><?= htmlspecialchars($row['apaar']) ?></td>
                            <td class="text-sm"><?= htmlspecialchars($row['religion']) ?></td>
                            <td class="text-sm"><?= htmlspecialchars($row['caste']) ?></td>
                            <td class="text-sm"><?= htmlspecialchars($row['dob']) ?></td>
                            <td class="text-sm"><?= htmlspecialchars($row['father_name']) ?></td>
                            <td class="text-sm"><?= htmlspecialchars($row['mother_name']) ?></td>
                            <td class="text-sm"><?= htmlspecialchars($row['parent_contact']) ?></td>
                            <td class="text-sm text-truncate"><?= nl2br(htmlspecialchars($row['address'])) ?></td>
                            <td class="text-sm text-truncate"><?= nl2br(htmlspecialchars($row['permanent_address'])) ?></td>
                            <td><strong><?= htmlspecialchars($row['course_name']) ?></strong></td>
                            <td class="text-sm"><?= htmlspecialchars($row['duration']) ?> months</td>
                            <td class="text-sm"><?= htmlspecialchars($row['admission_date']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button 
                                        class="action-btn edit"
                                        onclick="window.location.href='edit_student.php?id=<?= $row['id'] ?>'"
                                        aria-label="Edit <?= htmlspecialchars($row['name']) ?>"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        class="action-btn view"
                                        onclick="window.open('../admission/admission_success.php?eid=<?= urlencode($row['enrollment_id']) ?>', '_blank')"
                                        aria-label="View form for <?= htmlspecialchars($row['name']) ?>"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="19">
                                <div class="empty-state">
                                    <i class="fas fa-user-slash"></i>
                                    <p>No students found</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ELEMENTS
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');
    const filterBtn = document.getElementById('filterBtn');
    const columnMenu = document.getElementById('columnMenu');
    const checkboxes = columnMenu.querySelectorAll('input[type="checkbox"]');
    const rows = tableBody.querySelectorAll('tr');
    
    // SEARCH FUNCTIONALITY
    searchInput.addEventListener('input', debounce(function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        rows.forEach(row => {
            if (row.querySelector('.empty-state')) return;
            
            const cells = Array.from(row.querySelectorAll('td'));
            const hasMatch = cells.some(cell => 
                cell.textContent.toLowerCase().includes(searchTerm)
            );
            
            row.style.display = hasMatch ? '' : 'none';
        });
    }, 300));
    
    // COLUMN TOGGLE
    filterBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const isExpanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', !isExpanded);
        columnMenu.classList.toggle('show');
    });
    
    // CLOSE DROPDOWN
    document.addEventListener('click', () => {
        filterBtn.setAttribute('aria-expanded', 'false');
        columnMenu.classList.remove('show');
    });
    
    columnMenu.addEventListener('click', (e) => e.stopPropagation());
    
    // TOGGLE COLUMNS
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const columnIndex = parseInt(this.dataset.column);
            const ths = document.querySelectorAll('th');
            const tds = document.querySelectorAll('td');
            
            // Toggle header
            if (ths[columnIndex]) {
                ths[columnIndex].style.display = this.checked ? '' : 'none';
            }
            
            // Toggle cells
            tds.forEach((td, index) => {
                if (index % 19 === columnIndex) {
                    td.style.display = this.checked ? '' : 'none';
                }
            });
        });
    });
    
    // KEYBOARD NAVIGATION
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + F for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            searchInput.focus();
        }
        
        // Escape closes dropdown
        if (e.key === 'Escape') {
            filterBtn.setAttribute('aria-expanded', 'false');
            columnMenu.classList.remove('show');
        }
    });
    
    // IMAGE ERROR HANDLING
    document.querySelectorAll('.avatar').forEach(img => {
        img.addEventListener('error', function() {
            const name = this.alt || 'Student';
            this.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=2563eb&color=fff`;
        });
    });
    
    // ACCESSIBILITY: FOCUS TRAPPING IN DROPDOWN
    columnMenu.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
            const focusableElements = columnMenu.querySelectorAll('input, label, button');
            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];
            
            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    filterBtn.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    filterBtn.focus();
                }
            }
        }
    });
    
    // UTILITY: DEBOUNCE
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // REDUCED MOTION PREFERENCE
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.querySelectorAll('*').forEach(el => {
            el.style.animationDuration = '0.001ms';
            el.style.transitionDuration = '0.001ms';
        });
    }
});
</script>
</body>
</html>