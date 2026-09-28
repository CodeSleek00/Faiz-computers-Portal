<?php
$id = (int)($_GET['id'] ?? 0);
$pageTitle = 'Edit Course';
require_once __DIR__ . '/includes/header.php';

$stmt = $conn->prepare("SELECT * FROM study_courses WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    flash('error', 'Course not found.');
    redirect('courses.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = post_string('course_name');
    $slug = post_string('course_slug');
    $desc = post_string('description');
    $status = post_string('status', 'active');
    $slug = $slug !== '' ? slugify($slug) : slugify($name);

    $stmt = $conn->prepare("UPDATE study_courses SET course_name=?, course_slug=?, description=?, status=? WHERE id=?");
    $stmt->bind_param('ssssi', $name, $slug, $desc, $status, $id);
    if ($stmt->execute()) {
        flash('success', 'Course updated.');
        redirect('course_edit.php?id=' . $id);
    }
    flash('error', 'Could not update course.');
    $stmt->close();
    $row['course_name']=$name; $row['course_slug']=$slug; $row['description']=$desc; $row['status']=$status;
}
?>
<div class="card">
<form method="post">
<div class="form-grid">
<div>
<label>Course Name *</label>
<input name="course_name" required value="<?= e($row['course_name']) ?>">
</div>
<div>
<label>Course Slug</label>
<input name="course_slug" required value="<?= e($row['course_slug']) ?>">
</div>
<div class="full">
<label>Description</label>
<textarea name="description"><?= e($row['description']) ?></textarea>
</div>
<div>
<label>Status</label>
<select name="status">
<option value="active" <?= $row['status']==='active'?'selected':'' ?>>Active</option>
<option value="inactive" <?= $row['status']==='inactive'?'selected':'' ?>>Inactive</option>
</select>
</div>
</div>
<br>
<button class="btn orange">Save Changes</button>
<a class="btn light" href="courses.php">Back</a>
</form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
