<?php
$pageTitle = 'Topics';
require_once __DIR__ . '/includes/header.php';

$courseId = (int)($_GET['course_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (post_string('action') === 'delete') {
        $id = post_int('id');
        $stmt = $conn->prepare("DELETE FROM study_topics WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute(); $stmt->close();
        flash('success', 'Topic deleted.');
        redirect('topics.php' . ($courseId ? '?course_id='.$courseId : ''));
    }
}

$courses = $conn->query("SELECT id,course_name FROM study_courses ORDER BY course_name ASC");

if ($courseId > 0) {
    $stmt = $conn->prepare("SELECT t.*,c.course_name FROM study_topics t JOIN study_courses c ON c.id=t.course_id WHERE t.course_id=? ORDER BY t.sort_order ASC,t.id ASC");
    $stmt->bind_param('i',$courseId); $stmt->execute(); $rows=$stmt->get_result();
} else {
    $rows = $conn->query("SELECT t.*,c.course_name FROM study_topics t JOIN study_courses c ON c.id=t.course_id ORDER BY c.course_name,t.sort_order,t.id");
}
?>
<div class="card">
<div class="topbar">
<h2><?= $courseId ? 'Course Topics' : 'All Topics' ?></h2>
<a class="btn orange" href="topic_add.php<?= $courseId ? '?course_id='.$courseId : '' ?>">+ Add Topic</a>
</div>
<form class="searchbar" method="get">
<select name="course_id" onchange="this.form.submit()">
<option value="0">All Courses</option>
<?php while($c=$courses->fetch_assoc()): ?>
<option value="<?= (int)$c['id'] ?>" <?= $courseId===(int)$c['id']?'selected':'' ?>><?= e($c['course_name']) ?></option>
<?php endwhile; ?>
</select>
</form>
<div class="table-wrap">
<table>
<thead><tr><th>ID</th><th>Course</th><th>Topic</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php while($r=$rows->fetch_assoc()): ?>
<tr>
<td><?= (int)$r['id'] ?></td>
<td><?= e($r['course_name']) ?></td>
<td><strong><?= e($r['topic_name']) ?></strong><br><span class="muted"><?= e($r['description']) ?></span></td>
<td><?= (int)$r['sort_order'] ?></td>
<td><span class="badge <?= $r['status']==='active'?'active':'inactive' ?>"><?= e($r['status']) ?></span></td>
<td><div class="actions">
<a class="btn small" href="topic_edit.php?id=<?= (int)$r['id'] ?>">Edit</a>
<a class="btn small light" href="contents.php?topic_id=<?= (int)$r['id'] ?>">Content</a>
<form class="inline" method="post" onsubmit="return confirm('Delete this topic and its content?')">
<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<button class="btn small danger">Delete</button>
</form>
</div></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
