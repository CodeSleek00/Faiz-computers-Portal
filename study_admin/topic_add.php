<?php
$pageTitle = 'Add Topic';
require_once __DIR__ . '/includes/header.php';

$courses = $conn->query("SELECT id,course_name FROM study_courses WHERE status='active' ORDER BY course_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = post_int('course_id');
    $name = post_string('topic_name');
    $slug = post_string('topic_slug');
    $desc = post_string('description');
    $order = post_int('sort_order');

    if ($courseId <= 0 || $name === '') {
        flash('error','Course and topic name are required.');
    } else {
        $slug = $slug !== '' ? slugify($slug) : slugify($name);
        $stmt=$conn->prepare("INSERT INTO study_topics (course_id,topic_name,topic_slug,description,sort_order) VALUES (?,?,?,?,?)");
        $stmt->bind_param('isssi',$courseId,$name,$slug,$desc,$order);
        if($stmt->execute()){
            $id=$stmt->insert_id; $stmt->close();
            flash('success','Topic created.');
            redirect('topic_edit.php?id='.$id);
        }
        $stmt->close();
        flash('error','Could not create topic. Slug may already exist for this course.');
    }
}
$selectedCourse=(int)($_GET['course_id'] ?? $_POST['course_id'] ?? 0);
?>
<div class="card">
<form method="post">
<div class="form-grid">
<div>
<label>Course *</label>
<select name="course_id" required>
<option value="">Select course</option>
<?php while($c=$courses->fetch_assoc()): ?>
<option value="<?= (int)$c['id'] ?>" <?= $selectedCourse===(int)$c['id']?'selected':'' ?>><?= e($c['course_name']) ?></option>
<?php endwhile; ?>
</select>
</div>
<div>
<label>Topic Name *</label>
<input name="topic_name" required>
</div>
<div>
<label>Topic Slug</label>
<input name="topic_slug">
</div>
<div>
<label>Sort Order</label>
<input type="number" name="sort_order" value="0">
</div>
<div class="full">
<label>Description</label>
<textarea name="description"></textarea>
</div>
</div>
<br><button class="btn orange">Create Topic</button>
<a class="btn light" href="topics.php">Cancel</a>
</form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
