<?php
$pageTitle = 'Add Course';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = post_string('course_name');
    $slug = post_string('course_slug');
    $desc = post_string('description');
    $status = post_string('status', 'active');

    if ($name === '') {
        flash('error', 'Course name is required.');
    } else {
        $slug = $slug !== '' ? slugify($slug) : slugify($name);
        $stmt = $conn->prepare("INSERT INTO study_courses (course_name, course_slug, description, status) VALUES (?,?,?,?)");
        $stmt->bind_param('ssss', $name, $slug, $desc, $status);
        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            $stmt->close();
            flash('success', 'Course created.');
            redirect('course_edit.php?id=' . $id);
        }
        $stmt->close();
        flash('error', 'Could not create course. Slug may already exist.');
    }
}
?>
<div class="card">
<form method="post">
<div class="form-grid">
<div>
<label>Course Name *</label>
<input name="course_name" required>
</div>
<div>
<label>Course Slug</label>
<input name="course_slug" placeholder="Auto generated if empty">
</div>
<div class="full">
<label>Description</label>
<textarea name="description"></textarea>
</div>
<div>
<label>Status</label>
<select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select>
</div>
</div>
<br>
<button class="btn orange">Create Course</button>
<a class="btn light" href="courses.php">Cancel</a>
</form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
