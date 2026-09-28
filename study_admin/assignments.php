<?php
$pageTitle='Assignments';
require_once __DIR__ . '/includes/header.php';

if($_SERVER['REQUEST_METHOD']==='POST' && post_string('action')==='remove'){
    $id=post_int('id');
    $stmt=$conn->prepare("DELETE FROM study_content_targets WHERE id=?");
    $stmt->bind_param('i',$id);$stmt->execute();$stmt->close();
    flash('success','Assignment removed.');
    redirect('assignments.php');
}

/* Content list */
$contents=$conn->query("
    SELECT sc.id,sc.title,sc.content_type,t.topic_name,c.course_name
    FROM study_contents sc
    JOIN study_topics t ON t.id=sc.topic_id
    JOIN study_courses c ON c.id=t.course_id
    ORDER BY c.course_name,t.sort_order,sc.sort_order,sc.id
");

/* Existing assignments */
$assignments=$conn->query("
    SELECT
        sct.id,
        sct.target_type,
        sct.target_id,
        sct.assigned_at,
        sct.status,
        sc.title,
        sc.content_type,
        s.name AS student_name,
        s.enrollment_id,
        sbc.student_count
    FROM study_content_targets sct
    JOIN study_contents sc ON sc.id=sct.content_id
    LEFT JOIN students26 s
        ON sct.target_type='student'
       AND sct.target_id=s.id
    LEFT JOIN (
        SELECT batch_id, COUNT(*) AS student_count
        FROM students_batch
        WHERE student_table='students26'
        GROUP BY batch_id
    ) sbc
        ON sct.target_type='batch'
       AND sct.target_id=sbc.batch_id
    ORDER BY sct.id DESC
");
?>
<div class="card">
<h2>Assign Content</h2>
<div class="actions">
<a class="btn orange" href="assign_student.php">Assign to Student</a>
<a class="btn" href="assign_batch.php">Assign to Batch</a>
</div>
</div>

<div class="card">
<h2>Current Assignments</h2>
<div class="table-wrap">
<table>
<thead><tr><th>ID</th><th>Content</th><th>Target</th><th>Assigned</th><th>Status</th><th>Action</th></tr></thead>
<tbody>
<?php while($r=$assignments->fetch_assoc()): ?>
<tr>
<td><?= (int)$r['id'] ?></td>
<td><strong><?= e($r['title']) ?></strong><br><span class="badge"><?= e($r['content_type']) ?></span></td>
<td>
<?php if($r['target_type']==='student'): ?>
<strong>Student</strong><br>
<?= e($r['student_name'] ?? 'Unknown') ?><br>
<small>ID: <?= (int)$r['target_id'] ?> | <?= e($r['enrollment_id'] ?? '') ?></small>
<?php else: ?>
<strong>Batch</strong><br>
Batch ID: <?= (int)$r['target_id'] ?><br>
<small><?= (int)($r['student_count'] ?? 0) ?> students</small>
<?php endif; ?>
</td>
<td><?= e($r['assigned_at']) ?></td>
<td><span class="badge <?= $r['status']==='active'?'active':'inactive' ?>"><?= e($r['status']) ?></span></td>
<td>
<form method="post" onsubmit="return confirm('Remove this assignment?')">
<input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<button class="btn small danger">Remove</button>
</form>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
