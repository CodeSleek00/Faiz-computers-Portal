<?php
$id=(int)($_GET['id'] ?? 0);
$pageTitle='Edit Topic';
require_once __DIR__ . '/includes/header.php';

$stmt=$conn->prepare("SELECT * FROM study_topics WHERE id=?");
$stmt->bind_param('i',$id);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();$stmt->close();
if(!$row){flash('error','Topic not found.');redirect('topics.php');}

$courses=$conn->query("SELECT id,course_name FROM study_courses ORDER BY course_name");

if($_SERVER['REQUEST_METHOD']==='POST'){
    $courseId=post_int('course_id');
    $name=post_string('topic_name');
    $slug=post_string('topic_slug');
    $desc=post_string('description');
    $order=post_int('sort_order');
    $status=post_string('status','active');
    $slug=$slug!==''?slugify($slug):slugify($name);

    $stmt=$conn->prepare("UPDATE study_topics SET course_id=?,topic_name=?,topic_slug=?,description=?,sort_order=?,status=? WHERE id=?");
    $stmt->bind_param('isssisi',$courseId,$name,$slug,$desc,$order,$status,$id);
    if($stmt->execute()){
        flash('success','Topic updated.');
        redirect('topic_edit.php?id='.$id);
    }
    $stmt->close();
    flash('error','Could not update topic.');
    $row=array_merge($row,['course_id'=>$courseId,'topic_name'=>$name,'topic_slug'=>$slug,'description'=>$desc,'sort_order'=>$order,'status'=>$status]);
}
?>
<div class="card">
<form method="post">
<div class="form-grid">
<div><label>Course</label><select name="course_id" required>
<?php while($c=$courses->fetch_assoc()): ?>
<option value="<?= (int)$c['id'] ?>" <?= (int)$row['course_id']===(int)$c['id']?'selected':'' ?>><?= e($c['course_name']) ?></option>
<?php endwhile; ?></select></div>
<div><label>Topic Name</label><input name="topic_name" value="<?= e($row['topic_name']) ?>" required></div>
<div><label>Topic Slug</label><input name="topic_slug" value="<?= e($row['topic_slug']) ?>"></div>
<div><label>Sort Order</label><input type="number" name="sort_order" value="<?= (int)$row['sort_order'] ?>"></div>
<div><label>Status</label><select name="status">
<option value="active" <?= $row['status']==='active'?'selected':'' ?>>Active</option>
<option value="inactive" <?= $row['status']==='inactive'?'selected':'' ?>>Inactive</option>
</select></div>
<div class="full"><label>Description</label><textarea name="description"><?= e($row['description']) ?></textarea></div>
</div>
<br><button class="btn orange">Save Changes</button>
<a class="btn light" href="topics.php?course_id=<?= (int)$row['course_id'] ?>">Back</a>
</form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
