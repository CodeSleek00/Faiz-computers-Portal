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
<title>Admin Student Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
body{
    font-family: 'Poppins', sans-serif;
    background: #f4f6f9;
    margin: 0;
    padding: 0;
}
.container{
    padding: 20px;
}
.card{
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,.08);
    padding: 20px;
    overflow: hidden;
}
h1{
    margin-bottom: 20px;
    color: #1e293b;
    font-size: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}
h1 i {
    color: #2563eb;
}

/* Main table container with fixed header */
.table-container {
    max-height: 70vh;
    overflow: auto;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    position: relative;
}

/* Fixed header styling */
.table-wrapper {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #2563eb;
}

/* Table styling */
table {
    border-collapse: collapse;
    width: 100%;
    min-width: 100%;
}

/* Header row stays fixed */
thead {
    position: sticky;
    top: 0;
    z-index: 20;
}

th {
    background: #2563eb;
    color: #fff;
    font-weight: 500;
    white-space: nowrap;
    padding: 16px 18px;
    text-align: left;
    border-bottom: 2px solid #1e40af;
    position: sticky;
    top: 0;
    z-index: 30;
    box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);
}

/* Column headers with sorting indicators */
th.sortable {
    cursor: pointer;
    transition: background 0.3s;
}

th.sortable:hover {
    background: #1d4ed8;
}

th.sortable i {
    margin-left: 8px;
    font-size: 12px;
    opacity: 0.7;
}

/* Table body styling */
tbody tr {
    transition: background 0.2s;
    border-bottom: 1px solid #e5e7eb;
}

tbody tr:hover {
    background: #f8fafc;
}

td {
    padding: 14px 18px;
    text-align: left;
    vertical-align: top;
    white-space: normal;
    word-break: break-word;
    max-width: 200px;
}

/* Alternate row coloring */
tbody tr:nth-child(even) {
    background: #f9fafb;
}

tbody tr:nth-child(even):hover {
    background: #f1f5f9;
}

/* Image styling */
img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
}

/* Button styling */
.btn {
    background: #2563eb;
    color: #fff;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    font-weight: 500;
    min-width: 80px;
}

.btn:hover {
    background: #1e40af;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.btn i {
    font-size: 12px;
}

.btn-view {
    background: #16a34a;
    margin-top: 8px;
}

.btn-view:hover {
    background: #15803d;
}

.btn-edit {
    background: #f59e0b;
}

.btn-edit:hover {
    background: #d97706;
}

/* Action buttons container */
.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 120px;
}

/* Table footer with stats */
.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding: 12px 20px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.stats {
    font-size: 14px;
    color: #64748b;
    display: flex;
    gap: 20px;
    align-items: center;
}

.stats i {
    color: #2563eb;
    margin-right: 5px;
}

/* Scrollbar styling */
.table-container::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

.table-container::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
    transition: background 0.3s;
}

.table-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 40px;
    color: #64748b;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    color: #cbd5e1;
}

/* Column width adjustments */
th:nth-child(1), td:nth-child(1) { width: 80px; } /* Photo */
th:nth-child(2), td:nth-child(2) { min-width: 120px; } /* Enrollment ID */
th:nth-child(3), td:nth-child(3) { min-width: 150px; } /* Name */
th:nth-child(4), td:nth-child(4) { min-width: 120px; } /* Contact */
th:nth-child(5), td:nth-child(5) { min-width: 200px; } /* Email */
th:nth-child(20), td:nth-child(20) { min-width: 140px; } /* Actions */

/* Responsive adjustments */
@media (max-width: 1400px) {
    .table-container {
        max-height: 65vh;
    }
    
    th, td {
        padding: 12px 14px;
    }
}

@media (max-width: 1200px) {
    .container {
        padding: 15px;
    }
    
    .card {
        padding: 15px;
    }
}

/* Print styles */
@media print {
    .table-container {
        max-height: none;
        overflow: visible;
    }
    
    .btn {
        display: none;
    }
}
</style>
</head>

<body>

<div class="container">
    <div class="card">
        <h1><i class="fas fa-users"></i> Student Dashboard (Full Details)</h1>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="sortable">Photo</th>
                        <th class="sortable">Enrollment ID <i class="fas fa-sort"></i></th>
                        <th class="sortable">Name <i class="fas fa-sort"></i></th>
                        <th class="sortable">Contact <i class="fas fa-sort"></i></th>
                        <th class="sortable">Email <i class="fas fa-sort"></i></th>
                        <th class="sortable">Aadhar <i class="fas fa-sort"></i></th>
                        <th class="sortable">Apaar <i class="fas fa-sort"></i></th>
                        <th class="sortable">Religion <i class="fas fa-sort"></i></th>
                        <th class="sortable">Caste <i class="fas fa-sort"></i></th>
                        <th class="sortable">DOB <i class="fas fa-sort"></i></th>
                        <th class="sortable">Father's Name <i class="fas fa-sort"></i></th>
                        <th class="sortable">Mother's Name <i class="fas fa-sort"></i></th>
                        <th class="sortable">Parent Contact <i class="fas fa-sort"></i></th>
                        <th class="sortable">Address</th>
                        <th class="sortable">Permanent Address</th>
                        <th class="sortable">Course <i class="fas fa-sort"></i></th>
                        <th class="sortable">Duration (Months) <i class="fas fa-sort"></i></th>
                        <th class="sortable">Admission Date <i class="fas fa-sort"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $studentCount = 0;
                    if ($result->num_rows > 0): 
                        while($row = $result->fetch_assoc()): 
                            $studentCount++;
                    ?>
                    <tr>
                        <td>
                            <img src="../uploads/<?= htmlspecialchars($row['photo']) ?>" 
                                 alt="<?= htmlspecialchars($row['name']) ?>"
                                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($row['name']) ?>&background=2563eb&color=fff&size=50'">
                        </td>

                        <td><?= htmlspecialchars($row['enrollment_id']) ?></td>
                        <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
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
                        <td><span class="badge"><?= htmlspecialchars($row['duration']) ?> months</span></td>
                        <td><?= htmlspecialchars($row['admission_date']) ?></td>

                        <td>
                            <div class="action-buttons">
                                <a class="btn btn-edit" href="edit_student.php?id=<?= $row['id'] ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a class="btn btn-view" 
                                   href="../admission/admission_success.php?eid=<?= urlencode($row['enrollment_id']) ?>"
                                   target="_blank">
                                    <i class="fas fa-eye"></i> View Form
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                    <tr>
                        <td colspan="19">
                            <div class="empty-state">
                                <i class="fas fa-user-slash"></i>
                                <h3>No Students Found</h3>
                                <p>No student records available in the database.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="stats">
                <span><i class="fas fa-users"></i> Total Students: <?= $studentCount ?></span>
                <span><i class="fas fa-calendar-alt"></i> Last Updated: <?= date('Y-m-d H:i:s') ?></span>
            </div>
            <div>
                <button onclick="window.print()" class="btn" style="background: #6b7280;">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Basic column sorting (you can enhance this with AJAX for server-side sorting)
document.querySelectorAll('th.sortable').forEach(th => {
    th.addEventListener('click', function() {
        const table = th.closest('table');
        const tbody = table.querySelector('tbody');
        const index = Array.from(th.parentNode.children).indexOf(th);
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        const isAsc = !th.classList.contains('asc');
        
        // Remove sort classes from all headers
        table.querySelectorAll('th.sortable').forEach(h => {
            h.classList.remove('asc', 'desc');
        });
        
        // Set current sort direction
        th.classList.toggle('asc', isAsc);
        th.classList.toggle('desc', !isAsc);
        
        // Sort rows
        rows.sort((a, b) => {
            const aText = a.children[index].textContent.trim();
            const bText = b.children[index].textContent.trim();
            
            if (!isAsc) {
                return bText.localeCompare(aText);
            }
            return aText.localeCompare(bText);
        });
        
        // Reappend sorted rows
        rows.forEach(row => tbody.appendChild(row));
    });
});

// Highlight row on click
document.querySelectorAll('tbody tr').forEach(row => {
    row.addEventListener('click', function(e) {
        if (e.target.tagName === 'A') return; // Don't highlight if clicking links
        this.classList.toggle('selected');
    });
});

// Initialize tooltips for buttons
document.querySelectorAll('.btn').forEach(btn => {
    btn.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px) scale(1.02)';
    });
    btn.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});
</script>

</body>
</html>