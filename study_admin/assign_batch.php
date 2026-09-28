<?php
$pageTitle='Assign Content to Batch';
require_once __DIR__ . '/includes/header.php';

$contents=$conn->query("
SELECT sc.id,sc.title,sc.content_type,t.topic_name,c.course_name
FROM study_contents sc
JOIN study_topics t ON t.id=sc.topic_id
JOIN study_courses c ON c.id=t.course_id
WHERE sc.status='active'
ORDER BY c.course_name,t.sort_order,sc.sort_order,sc.id
");

/*
 * No assumption about a separate batch master table.
 * Existing batch IDs are read directly from students_batch.
 */
$batches=$conn->query("
SELECT batch_id, COUNT(*) AS student_count
FROM students_batch
WHERE student_table='students26'
GROUP BY batch_id
ORDER BY batch_id DESC
");

if($_SERVER['REQUEST_METHOD']==='POST'){
    $contentId=post_int('content_id');
    $batchId=post_int('batch_id');

    if($contentId<=0 || $batchId<=0){
        flash('error','Select content and batch.');
    }else{
        $check=$conn->prepare("
            SELECT COUNT(*) AS c
            FROM students_batch
            WHERE student_table='students26' AND batch_id=?
        ");
        $check->bind_param('i',$batchId);$check->execute();
        $count=(int)$check->get_result()->fetch_assoc()['c'];$check->close();

        if($count<=0){
            flash('error','No students26 students are mapped to this batch.');
        }else{
            $stmt=$conn->prepare("INSERT INTO study_content_targets (content_id,target_type,target_id) VALUES (?, 'batch', ?)");
            $stmt->bind_param('ii',$contentId,$batchId);
            if($stmt->execute()) flash('success','Content assigned to batch.');
            else flash('error','Assignment already exists or could not be created.');
            $stmt->close();
        }
    }
}
?>
<div class="card">
<form method="post">
<div class="form-grid">
<div class="full"><label>Content *</label><select name="content_id" required>
<option value="">Select content</option>
<?php while($c=$contents->fetch_assoc()): ?>
<option value="<?= (int)$c['id'] ?>"><?= e($c['course_name'].' → '.$c['topic_name'].' → '.$c['title'].' ['.$c['content_type'].']') ?></option>
<?php endwhile; ?>
</select></div>
<div class="full"><label>Batch *</label><select name="batch_id" required>
<option value="">Select batch</option>
<?php while($b=$batches->fetch_assoc()): ?>
<option value="<?= (int)$b['batch_id'] ?>">Batch <?= (int)$b['batch_id'] ?> — <?= (int)$b['student_count'] ?> students26 students</option>
<?php endwhile; ?>
</select></div>
</div><br>
<button class="btn orange">Assign to Batch</button>
<a class="btn light" href="assignments.php">Back</a>
</form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
