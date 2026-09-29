<?php
$pageTitle = 'Add Course';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = post_string('course_name');
    $slug = post_string('course_slug');
    $desc = post_string('description');
    $status = post_string('status', 'active');
    $companyName = post_string('company_name');
    $instructorName = post_string('instructor_name');
    $level = post_string('level');
    $timeline = post_string('timeline');
    $detailsToKnow = post_string('details_to_know');
    $specialisation = post_string('specialisation');
    $courseTimeline = post_string('course_timeline');

    if ($name === '') {
        flash('error', 'Course name is required.');
    } else {
        $slug = $slug !== '' ? slugify($slug) : slugify($name);
        $thumbnail = '';
        try {
            if (!empty($_FILES['thumbnail']['name'])) {
                $thumbnail = save_upload($_FILES['thumbnail'], 'course-thumbnails', ['jpg', 'jpeg', 'png', 'webp'], 5 * 1024 * 1024);
            }
            $stmt = $conn->prepare("INSERT INTO study_courses (course_name, course_slug, description, status, company_name, instructor_name, level, timeline, details_to_know, specialisation, course_timeline, thumbnail) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('ssssssssssss', $name, $slug, $desc, $status, $companyName, $instructorName, $level, $timeline, $detailsToKnow, $specialisation, $courseTimeline, $thumbnail);
            if ($stmt->execute()) {
                $id = $stmt->insert_id;
                $stmt->close();
                flash('success', 'Course created.');
                redirect('course_edit.php?id=' . $id);
            }
            $stmt->close();
            delete_relative_file($thumbnail);
            flash('error', 'Could not create course. Slug may already exist.');
        } catch (Throwable $e) {
            delete_relative_file($thumbnail);
            flash('error', 'Could not create course. Check the thumbnail and course details.');
        }
    }
}
?>
<div class="card">
<form method="post" enctype="multipart/form-data">
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
<label>Company Name</label>
<input name="company_name">
</div>
<div>
<label>Instructor Name</label>
<input name="instructor_name">
</div>
<div>
<label>Level</label>
<input name="level" placeholder="Beginner, Intermediate, Advanced">
</div>
<div>
<label>Duration (months)</label>
<input type="number" name="timeline" min="1" step="1">
</div>
<div>
<label>Specialisation</label>
<input name="specialisation">
</div>
<div>
<label>Thumbnail</label>
<input type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp">
</div>
<div class="full">
<label>Details to Know</label>
<textarea name="details_to_know"></textarea>
</div>
<div class="full">
<label>Course Timeline (optional)</label>
<textarea name="course_timeline" placeholder="Leave blank to distribute course topics automatically across the duration."></textarea>
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
