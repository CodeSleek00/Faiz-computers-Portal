<?php
include 'db_connect.php';

// Dashboard stats
$total_videos = $conn->query("SELECT COUNT(*) as total FROM videos")->fetch_assoc()['total'];
$total_students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$total_batches = $conn->query("SELECT COUNT(*) as total FROM batches")->fetch_assoc()['total'];

// Fetch all videos
$videos = $conn->query("SELECT * FROM videos ORDER BY uploaded_at DESC");

// Fetch batches and students for reassign modal
$batches = $conn->query("SELECT batch_id, batch_name FROM batches");
$students = $conn->query("SELECT student_id, name FROM students");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Video Dashboard</title>
 <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
<style>
    body { font-family: Arial,sans-serif; margin:0; padding:0; background:#f7f7f7; }
    header { background:#2563eb; color:#fff; padding:20px; }
    header h1 { margin:0; font-size:24px; }
    .container { padding:20px; max-width:1200px; margin:auto; }
    .stats { display:flex; gap:20px; margin-bottom:20px; flex-wrap:wrap; }
    .stat-box { flex:1; background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); text-align:center; }
    .stat-box h2 { margin:10px 0; font-size:20px; color:#2563eb; }
    .actions { margin-bottom:20px; }
    .actions button { background:#2563eb; color:#fff; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; }
    .actions button:hover { background:#3b82f6; }
    table { width:100%; border-collapse: collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
    th, td { padding:12px; border-bottom:1px solid #ddd; text-align:left; }
    th { background:#3b82f6; color:#fff; }
    img.thumbnail { width:80px; border-radius:4px; }
    video { max-width:200px; border-radius:4px; }
    button.action-btn { padding:6px 12px; margin-right:5px; border:none; border-radius:4px; cursor:pointer; }
    button.action-btn.download { background:#10b981; color:#fff; }
    button.action-btn.delete { background:#ef4444; color:#fff; }
    button.action-btn.reassign { background:#2563eb; color:#fff; }

    /* Modal */
    .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; }
    .modal-content { background:#fff; padding:20px; border-radius:8px; max-width:500px; width:90%; }
    .close { float:right; cursor:pointer; font-size:18px; color:#333; }
    input, select, textarea { width:100%; padding:8px; margin:6px 0; border:1px solid #ccc; border-radius:4px; }
</style>
</head>
<body>

<header>
    <h1>Admin Video Dashboard</h1>
</header>

<div class="container">

    <!-- Stats -->
    <div class="stats">
        <div class="stat-box">
            <h2><?= $total_videos ?></h2>
            <p>Total Videos</p>
        </div>
        <div class="stat-box">
            <h2><?= $total_students ?></h2>
            <p>Total Students</p>
        </div>
        <div class="stat-box">
            <h2><?= $total_batches ?></h2>
            <p>Total Batches</p>
        </div>
    </div>

    <!-- Actions -->
    <div class="actions">
        <button onclick="window.location.href='upload_video_page.php'">➕ Add New Video</button>
      <button onclick="window.location.href='reassign_video.php'">Reassign</button> 
    </div>

    <!-- Videos Table -->
    <table>
        <tr>
            <th>Thumbnail</th>
            <th>Title</th>
            <th>Description</th>
            <th>Assigned To</th>
            <th>Video</th>
            <th>Actions</th>
        </tr>
        <?php while($v = $videos->fetch_assoc()) { ?>
        <tr>
            <td><img class="thumbnail" src="../uploads/thumbnails/<?= htmlspecialchars($v['thumbnail']) ?>" alt="thumb"></td>
            <td><?= htmlspecialchars($v['title']) ?></td>
            <td><?= nl2br(htmlspecialchars($v['description'])) ?></td>
            <td>
                <?php
                    if($v['assigned_to']=='all') echo "All Students";
                    elseif($v['assigned_to']=='batch') echo "Batch ID: ".$v['batch_id'];
                    elseif($v['assigned_to']=='student') echo "Student ID: ".$v['student_id'];
                ?>
            </td>
            <td>
                <video controls>
                    <source src="../uploads/videos/<?= htmlspecialchars($v['filename']) ?>" type="video/mp4">
                </video>
            </td>
            <td>
                <a href="../uploads/videos/<?= htmlspecialchars($v['filename']) ?>" download><button class="action-btn download">Download</button></a>
                <a href="delete_video.php?id=<?= $v['id'] ?>" onclick="return confirm('Are you sure?')"><button class="action-btn delete">Delete</button></a>
                
            </td>
        </tr>
        <?php } ?>
    </table>

</div>

<!-- Reassign Modal -->
<div id="reassignModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeReassignModal()">&times;</span>
        <h3>Reassign Video</h3>
        <form id="reassignForm" method="post" action="reassign_video.php">
            <input type="hidden" name="video_id" id="video_id">
            <label>Assign To:</label>
            <select name="assigned_to" id="assigned_to_select" onchange="toggleFields()">
                <option value="all">All Students</option>
                <option value="batch">Specific Batch</option>
                <option value="student">Specific Student</option>
            </select>

            <div id="batch_select" class="hidden">
                <label>Select Batch</label>
                <select name="batch_id">
                    <option value="">--Select Batch--</option>
                    <?php
                    $batches->data_seek(0);
                    while($b = $batches->fetch_assoc()) {
                        echo "<option value='{$b['batch_id']}'>{$b['batch_name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div id="student_select" class="hidden">
                <label>Select Student</label>
                <select name="student_id">
                    <option value="">--Select Student--</option>
                    <?php
                    $students->data_seek(0);
                    while($s = $students->fetch_assoc()) {
                        echo "<option value='{$s['student_id']}'>{$s['name']} (ID: {$s['student_id']})</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openReassignModal(id){
    document.getElementById('video_id').value = id;
    document.getElementById('reassignModal').style.display = 'flex';
}
function closeReassignModal(){
    document.getElementById('reassignModal').style.display = 'none';
}
function toggleFields(){
    let assign = document.getElementById('assigned_to_select').value;
    document.getElementById('batch_select').style.display = (assign=='batch')?'block':'none';
    document.getElementById('student_select').style.display = (assign=='student')?'block':'none';
}
window.onclick = function(event) {
    if (event.target == document.getElementById('reassignModal')) {
        closeReassignModal();
    }
}
</script>

</body>
</html>
