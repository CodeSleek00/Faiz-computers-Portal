<?php
include 'db_connect.php';

// Fetch all videos
$videos = $conn->query("SELECT v.*, 
    (SELECT COUNT(*) FROM students s WHERE v.assigned_to='student' AND s.student_id=v.student_id) AS student_count,
    (SELECT COUNT(*) FROM students s WHERE v.assigned_to='batch' AND s.batch_id=v.batch_id) AS batch_count
    FROM videos v ORDER BY uploaded_at DESC");

// Fetch batches and students for reassign modal
$batches = $conn->query("SELECT batch_id, batch_name FROM batches");
$students = $conn->query("SELECT student_id, name FROM students");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Video Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background:#f7f7f7; }
        h1 { color:#2563eb; margin-bottom:20px; }
        table { width:100%; border-collapse: collapse; background:#fff; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding:12px; border-bottom:1px solid #ddd; text-align:left; }
        th { background:#2563eb; color:#fff; }
        tr:hover { background:#f0f8ff; }
        img.thumb { width:80px; height:45px; object-fit:cover; border-radius:4px; }
        a.button { padding:6px 12px; background:#3b82f6; color:#fff; text-decoration:none; border-radius:4px; margin-right:5px; }
        a.button:hover { background:#2563eb; }
        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; }
        .modal-content { background:#fff; padding:20px; border-radius:8px; max-width:500px; width:90%; }
        .close { float:right; cursor:pointer; font-size:18px; font-weight:bold; }
        select, input[type=text] { width:100%; padding:8px; margin:6px 0; }
        button.submit-btn { background:#2563eb; color:#fff; padding:8px 15px; border:none; border-radius:5px; cursor:pointer; }
        button.submit-btn:hover { background:#3b82f6; }
    </style>
    <script>
        function openModal(videoId) {
            document.getElementById('reassignModal').style.display = 'flex';
            document.getElementById('video_id').value = videoId;
        }
        function closeModal() {
            document.getElementById('reassignModal').style.display = 'none';
        }
    </script>
</head>
<body>
    <h1>Admin Video Dashboard</h1>
    <table>
        <tr>
            <th>Thumbnail</th>
            <th>Title</th>
            <th>Description</th>
            <th>Assigned To</th>
            <th>Count</th>
            <th>Actions</th>
        </tr>
        <?php while($v = $videos->fetch_assoc()) { ?>
        <tr>
            <td>
                <?php if($v['thumbnail']) { ?>
                    <img class="thumb" src="../uploads/thumbnails/<?= $v['thumbnail'] ?>" alt="Thumb">
                <?php } else { echo "-"; } ?>
            </td>
            <td><?= htmlspecialchars($v['title']) ?></td>
            <td><?= htmlspecialchars($v['description']) ?></td>
            <td><?= ucfirst($v['assigned_to']) ?></td>
            <td>
                <?php
                if($v['assigned_to']=='all') echo "All Students";
                elseif($v['assigned_to']=='batch') echo $v['batch_count'] . " student(s)";
                elseif($v['assigned_to']=='student') echo $v['student_count'] . " student(s)";
                ?>
            </td>
            <td>
                <a class="button" href="../uploads/videos/<?= $v['filename'] ?>" download>Download</a>
                <a class="button" href="delete_video.php?id=<?= $v['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                <a class="button" href="javascript:void(0);" onclick="openModal(<?= $v['id'] ?>)">Reassign</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <!-- Reassign Modal -->
    <div class="modal" id="reassignModal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">×</span>
            <h3>Reassign Video</h3>
            <form action="reassign_video.php" method="post">
                <input type="hidden" name="video_id" id="video_id">
                
                <label>Assign To</label>
                <select name="assigned_to" id="assigned_to_modal" required onchange="toggleFields()">
                    <option value="all">All Students</option>
                    <option value="batch">Specific Batch</option>
                    <option value="student">Specific Student</option>
                </select>

                <div id="batch_select_modal" style="display:none;">
                    <label>Select Batch</label>
                    <select name="batch_id">
                        <option value="">-- Select Batch --</option>
                        <?php
                        $batches->data_seek(0); // reset pointer
                        while($b = $batches->fetch_assoc()) {
                            echo "<option value='{$b['batch_id']}'>{$b['batch_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div id="student_select_modal" style="display:none;">
                    <label>Select Student</label>
                    <select name="student_id">
                        <option value="">-- Select Student --</option>
                        <?php
                        $students->data_seek(0);
                        while($s = $students->fetch_assoc()) {
                            echo "<option value='{$s['student_id']}'>{$s['name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Reassign</button>
            </form>
        </div>
    </div>

<script>
function toggleFields() {
    let val = document.getElementById('assigned_to_modal').value;
    document.getElementById('batch_select_modal').style.display = 'none';
    document.getElementById('student_select_modal').style.display = 'none';

    if(val=='batch') document.getElementById('batch_select_modal').style.display = 'block';
    else if(val=='student') document.getElementById('student_select_modal').style.display = 'block';
}
</script>
</body>
</html>
