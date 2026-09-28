<?php
$pageTitle='Study Content';
require_once __DIR__ . '/includes/header.php';

$topicId=(int)($_GET['topic_id'] ?? 0);
$type=post_string('type');
if($type!=='') $type=post_string('type');

if($_SERVER['REQUEST_METHOD']==='POST' && post_string('action')==='delete'){
    $id=post_int('id');
    $stmt=$conn->prepare("SELECT file_url,video_url,thumbnail_url FROM study_contents WHERE id=?");
    $stmt->bind_param('i',$id);$stmt->execute();$old=$stmt->get_result()->fetch_assoc();$stmt->close();

    $stmt=$conn->prepare("DELETE FROM study_contents WHERE id=?");
    $stmt->bind_param('i',$id);$stmt->execute();$stmt->close();

    if($old){
        delete_relative_file($old['file_url']);
        delete_relative_file($old['video_url']);
        delete_relative_file($old['thumbnail_url']);
    }
    flash('success','Content deleted.');
    redirect('contents.php'.($topicId?'?topic_id='.$topicId:''));
}

$topics=$conn->query("SELECT t.id,t.topic_name,c.course_name FROM study_topics t JOIN study_courses c ON c.id=t.course_id ORDER BY c.course_name,t.sort_order,t.topic_name");

if($topicId){
    $stmt=$conn->prepare("SELECT sc.*,t.topic_name,c.course_name FROM study_contents sc JOIN study_topics t ON t.id=sc.topic_id JOIN study_courses c ON c.id=t.course_id WHERE sc.topic_id=? ORDER BY sc.sort_order,sc.id");
    $stmt->bind_param('i',$topicId);$stmt->execute();$rows=$stmt->get_result();
}else{
    $rows=$conn->query("SELECT sc.*,t.topic_name,c.course_name FROM study_contents sc JOIN study_topics t ON t.id=sc.topic_id JOIN study_courses c ON c.id=t.course_id ORDER BY c.course_name,t.sort_order,sc.sort_order,sc.id");
}
?>
<div class="card">
<div class="topbar">
<h2><?= $topicId?'Topic Content':'All Content' ?></h2>
<a class="btn orange" href="content_add.php<?= $topicId?'?topic_id='.$topicId:'' ?>">+ Add Content</a>
</div>
<form class="searchbar" method="get">
<select name="topic_id" onchange="this.form.submit()">
<option value="0">All Topics</option>
<?php while($t=$topics->fetch_assoc()): ?>
<option value="<?= (int)$t['id'] ?>" <?= $topicId===(int)$t['id']?'selected':'' ?>><?= e($t['course_name'].' → '.$t['topic_name']) ?></option>
<?php endwhile; ?>
</select>
</form>
<div class="table-wrap"><table>
<thead><tr><th>ID</th><th>Course / Topic</th><th>Content</th><th>Type</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php while($r=$rows->fetch_assoc()): ?>
<tr>
<td><?= (int)$r['id'] ?></td>
<td><?= e($r['course_name']) ?><br><strong><?= e($r['topic_name']) ?></strong></td>
<td><strong><?= e($r['title']) ?></strong><br><span class="muted"><?= e($r['description']) ?></span></td>
<td><span class="badge"><?= e($r['content_type']) ?></span></td>
<td><?= (int)$r['sort_order'] ?></td>
<td><span class="badge <?= $r['status']==='active'?'active':'inactive' ?>"><?= e($r['status']) ?></span></td>
<td><div class="actions">
<a class="btn small" href="content_edit.php?id=<?= (int)$r['id'] ?>">Edit</a>
<a class="btn small light" href="attachments.php?content_id=<?= (int)$r['id'] ?>">Files</a>
<form class="inline" method="post" onsubmit="return confirm('Delete this content and its assignments?')">
<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<button class="btn small danger">Delete</button>
</form>
</div></td>
</tr>
<?php endwhile; ?>
</tbody></table></div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
