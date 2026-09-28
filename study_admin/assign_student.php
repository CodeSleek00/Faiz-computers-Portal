<?php
$pageTitle='Assign Content to Student';
require_once __DIR__ . '/includes/header.php';

$contents=$conn->query("
SELECT sc.id,sc.title,sc.content_type,t.topic_name,c.course_name
FROM study_contents sc
JOIN study_topics t ON t.id=sc.topic_id
JOIN study_courses c ON c.id=t.course_id
WHERE sc.status='active'
ORDER BY c.course_name,t.sort_order,sc.sort_order,sc.id
");

$students=$conn->query("
SELECT id,name,enrollment_id,contact,course,status
FROM students26
ORDER BY name ASC
");

if($_SERVER['REQUEST_METHOD']==='POST'){
    $contentId=post_int('content_id');
    $studentId=post_int('student_id');

    if($contentId<=0 || $studentId<=0){
        flash('error','Select content and student.');
    }else{
        $check=$conn->prepare("SELECT id FROM students26 WHERE id=?");
        $check->bind_param('i',$studentId);$check->execute();$exists=$check->get_result()->fetch_assoc();$check->close();

        if(!$exists){
            flash('error','Student does not exist in students26.');
        }else{
            $stmt=$conn->prepare("INSERT INTO study_content_targets (content_id,target_type,target_id) VALUES (?, 'student', ?)");
            $stmt->bind_param('ii',$contentId,$studentId);
            if($stmt->execute()) flash('success','Content assigned to student.');
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
<div class="full"><label>Student from students26 *</label><select name="student_id" required>
<option value="">Select student</option>
<?php while($s=$students->fetch_assoc()): ?>
<option value="<?= (int)$s['id'] ?>">
<?= e($s['name'].' | ID '.$s['id'].' | Enrollment '.$s['enrollment_id'].' | '.$s['contact']) ?>
</option>
<?php endwhile; ?>
</select></div>
</div><br>
<button class="btn orange">Assign to Student</button>
<a class="btn light" href="assignments.php">Back</a>
</form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
