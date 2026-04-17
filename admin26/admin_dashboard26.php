<?php
session_start();
include("../database_connection/db_connect.php");

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
}
.container{
    padding:25px;
}
.card{
    background:#fff;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
    padding:20px;
}
h1{
    margin-bottom:20px;
}

/* ✅ SCROLL FIX */
.top-scroll{
    overflow-x:auto;
    height:12px;
}
.top-scroll div{
    height:1px;
}

.table-wrapper{
    overflow:auto;
    max-height:600px;
}

/* TABLE */
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

/* ✅ STICKY HEADER */
th{
    position:sticky;
    top:0;
    background:#2563eb;
    color:#fff;
    z-index:2;
    white-space:nowrap;
}

img{
    width:50px;
    height:50px;
    border-radius:50%;
    object-fit:cover;
}

/* BUTTON */
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

.btn-view{
    background:#16a34a;
    margin-top:6px;
}
.btn-view:hover{
    background:#15803d;
}

.small{
    font-size:12px;
    color:#374151;
}

/* SEARCH */
.search-bar{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:18px;
}
.search-bar input{
    flex:1 1 300px;
    padding:10px 14px;
    border:1px solid #d1d5db;
    border-radius:8px;
}
.search-bar button{
    background:#2563eb;
    color:#fff;
    border:none;
    border-radius:8px;
    padding:10px 18px;
    cursor:pointer;
}
.search-bar button:hover{
    background:#1e40af;
}
</style>
</head>

<body>

<div class="container">
<div class="card">

<h1>Student Dashboard (Full Details)</h1>

<div class="search-bar">
    <input id="searchInput" type="text" placeholder="Search by name, enrollment, contact...">
    <button id="searchButton">Search</button>
</div>

<!-- ✅ TOP SCROLLBAR -->
<div class="top-scroll" id="topScroll">
    <div id="topScrollInner"></div>
</div>

<div class="table-wrapper" id="tableWrapper">
<table id="mainTable">
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
    <th>Duration</th>
    <th>Admission Date</th>
    <th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td>
<img src="../uploads/<?= htmlspecialchars($row['photo']) ?>"
     onerror="this.src='https://via.placeholder.com/50'">
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
</div>

<script>
const searchInput = document.getElementById('searchInput');
const searchButton = document.getElementById('searchButton');
const rows = document.querySelectorAll("#mainTable tr");

function filterStudents(){
    const query = searchInput.value.toLowerCase();

    rows.forEach((row, index)=>{
        if(index === 0) return;
        row.style.display = row.innerText.toLowerCase().includes(query) ? "" : "none";
    });
}

searchButton.onclick = filterStudents;
searchInput.addEventListener("keydown", e=>{
    if(e.key==="Enter"){
        e.preventDefault();
        filterStudents();
    }
});

/* ✅ SYNC TOP & BOTTOM SCROLL */
const topScroll = document.getElementById("topScroll");
const tableWrapper = document.getElementById("tableWrapper");
const topInner = document.getElementById("topScrollInner");
const table = document.getElementById("mainTable");

function syncScroll(){
    topInner.style.width = table.scrollWidth + "px";
}

syncScroll();
window.onresize = syncScroll;

topScroll.onscroll = ()=> {
    tableWrapper.scrollLeft = topScroll.scrollLeft;
};

tableWrapper.onscroll = ()=> {
    topScroll.scrollLeft = tableWrapper.scrollLeft;
};
</script>

</body>
</html>