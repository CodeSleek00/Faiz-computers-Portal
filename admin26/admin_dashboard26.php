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

<style>
body{
    font-family:Poppins,sans-serif;
    background:#f4f6f9;
    margin:0;
    padding:0;
    height:100vh;
    overflow:hidden;
    display:flex;
    flex-direction:column;
}
.container{
    padding:25px;
    flex:1;
    display:flex;
    flex-direction:column;
}
.card{
    background:#fff;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
    padding:20px;
    flex:1;
    display:flex;
    flex-direction:column;
}
h1{
    margin-bottom:20px;
    flex-shrink:0;
}
.table-wrapper{
    overflow-x:auto;
    flex:1;
    overflow-y:hidden;
}

/* Main table area - fixed height */
.table-main-area {
    height: calc(100vh - 200px);
    overflow: hidden;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    flex:1;
}

table{
    border-collapse:collapse;
    width:max-content;
    min-width:100%;
}

th, td{
    padding:12px 18px;
    text-align:left;
    border-bottom:1px solid #e5e7eb;
    vertical-align:top;
    white-space:normal;
    word-break:break-word;
}

th{
    background:#2563eb;
    color:#fff;
    font-weight:500;
    white-space:nowrap;
    position:sticky;
    top:0;
    z-index:10;
}

/* Footer section */
.table-footer {
    margin-top:15px;
    background:#fff;
    border-radius:8px;
    padding:15px;
    box-shadow:0 4px 6px rgba(0,0,0,0.05);
    border:1px solid #e5e7eb;
    flex-shrink:0;
    max-height:150px;
    display:flex;
    flex-direction:column;
}

.footer-title {
    font-weight:600;
    color:#2563eb;
    margin-bottom:10px;
    font-size:14px;
    display:flex;
    align-items:center;
    gap:8px;
}

.footer-title i {
    font-size:16px;
}

/* Footer scrollable content */
.footer-scroll {
    flex:1;
    overflow-y:auto;
    overflow-x:hidden;
    border:1px solid #e2e8f0;
    border-radius:6px;
    padding:12px;
    background:#f9fafb;
    max-height:100px;
}

/* Footer scrollbar styling */
.footer-scroll::-webkit-scrollbar {
    width:8px;
}

.footer-scroll::-webkit-scrollbar-track {
    background:#f1f5f9;
    border-radius:4px;
}

.footer-scroll::-webkit-scrollbar-thumb {
    background:#cbd5e1;
    border-radius:4px;
}

.footer-scroll::-webkit-scrollbar-thumb:hover {
    background:#94a3b8;
}

/* Footer content */
.footer-content {
    font-size:13px;
    color:#4b5563;
    line-height:1.6;
}

.footer-stats {
    display:flex;
    gap:20px;
    margin-bottom:8px;
    flex-wrap:wrap;
}

.stat-item {
    display:flex;
    align-items:center;
    gap:6px;
}

.stat-item i {
    color:#2563eb;
    font-size:14px;
}

/* Table body scrollbar */
.table-main-area::-webkit-scrollbar {
    width:10px;
}

.table-main-area::-webkit-scrollbar-track {
    background:#f1f5f9;
    border-radius:4px;
}

.table-main-area::-webkit-scrollbar-thumb {
    background:#cbd5e1;
    border-radius:4px;
}

.table-main-area::-webkit-scrollbar-thumb:hover {
    background:#94a3b8;
}

img{
    width:50px;
    height:50px;
    border-radius:50%;
    object-fit:cover;
}
.btn{
    background:#2563eb;
    color:#fff;
    padding:6px 12px;
    border-radius:6px;
    text-decoration:none;
    font-size:13px;
    display:inline-block;
}
.btn:hover{
    background:#1e40af;
}
.small{
    font-size:12px;
    color:#374151;
}
.btn-view{
    background:#16a34a;
    margin-top:6px;
}
.btn-view:hover{
    background:#15803d;
}

</style>
</head>

<body>

<div class="container">
    <div class="card">
        <h1>Student Dashboard (Full Details)</h1>
        
        <!-- Main table area -->
        <div class="table-main-area">
            <div class="table-wrapper">
                <table>
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
                        <th>Father's Name</th>
                        <th>Mother's Name</th>
                        <th>Parent Contact</th>
                        <th>Address</th>
                        <th>Permanent Address</th>
                        <th>Course</th>
                        <th>Duration (Months)</th>
                        <th>Admission Date</th>
                        <th>Action</th>
                    </tr>

                    <?php 
                    $studentCount = 0;
                    $currentDate = date('Y-m-d');
                    while($row = $result->fetch_assoc()): 
                        $studentCount++;
                    ?>
                    <tr>
                        <td>
                            <img src="../uploads/<?= htmlspecialchars($row['photo']) ?>">
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
                        <a class="btn" href="edit_student.php?id=<?= $row['id'] ?>">Edit</a><br>

                        <a class="btn btn-view"
                        href="../admission/admission_success.php?eid=<?= urlencode($row['enrollment_id']) ?>"
                        target="_blank">
                        View Form
                        </a>
                    </td>

                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
        
        <!-- Footer with separate scrollbar -->
        <div class="table-footer">
            <div class="footer-title">
                <span>📊 Dashboard Summary</span>
            </div>
            
            <div class="footer-scroll">
                <div class="footer-content">
                    <div class="footer-stats">
                        <div class="stat-item">
                            <span>👥</span>
                            <span>Total Students: <?= $studentCount ?></span>
                        </div>
                        <div class="stat-item">
                            <span>📅</span>
                            <span>Date: <?= $currentDate ?></span>
                        </div>
                        <div class="stat-item">
                            <span>🕒</span>
                            <span>Last Updated: <?= date('H:i:s') ?></span>
                        </div>
                    </div>
                    
                    <div style="margin-top:10px;">
                        <strong>Quick Actions:</strong>
                        <ul style="margin:5px 0 0 20px; padding:0;">
                            <li>Click on any student row to select</li>
                            <li>Use Edit button to modify student details</li>
                            <li>View Form opens admission details in new tab</li>
                            <li>Scroll horizontally to see all columns</li>
                        </ul>
                    </div>
                    
                    <div style="margin-top:10px; color:#6b7280; font-size:12px;">
                        <em>Note: This footer stays fixed at the bottom with its own scrollbar. You can scroll this section independently.</em>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Add icon font for the footer
const link = document.createElement('link');
link.rel = 'stylesheet';
link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
document.head.appendChild(link);

// Make rows clickable for selection
document.querySelectorAll('tbody tr').forEach(row => {
    row.addEventListener('click', function(e) {
        // Don't select if clicking on links/buttons
        if (e.target.tagName === 'A' || e.target.closest('a')) {
            return;
        }
        
        // Toggle selection
        this.classList.toggle('selected');
        
        // Update selection count in footer
        updateSelectionCount();
    });
});

function updateSelectionCount() {
    const selectedCount = document.querySelectorAll('tbody tr.selected').length;
    let statsElement = document.querySelector('.footer-stats');
    
    // Find or create selection count element
    let selectionStat = statsElement.querySelector('.selection-count');
    if (!selectionStat) {
        selectionStat = document.createElement('div');
        selectionStat.className = 'stat-item selection-count';
        selectionStat.innerHTML = '<span>✅</span><span>Selected: 0</span>';
        statsElement.appendChild(selectionStat);
    }
    
    selectionStat.innerHTML = `<span>✅</span><span>Selected: ${selectedCount}</span>`;
}

// Add some basic styling for selected rows
const style = document.createElement('style');
style.textContent = `
    tbody tr.selected {
        background-color: #e0f2fe !important;
        border-left: 3px solid #2563eb;
    }
    tbody tr.selected:hover {
        background-color: #bae6fd !important;
    }
`;
document.head.appendChild(style);

// Initialize selection count
setTimeout(updateSelectionCount, 100);
</script>

</body>
</html>