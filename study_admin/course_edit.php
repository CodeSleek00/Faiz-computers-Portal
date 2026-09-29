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
    $companyName = post_string('company_name');
    $instructorName = post_string('instructor_name');
    $level = post_string('level');
    $timeline = post_string('timeline');
    $detailsToKnow = post_string('details_to_know');
    $specialisation = post_string('specialisation');
    $courseTimeline = post_string('course_timeline');
    $slug = $slug !== '' ? slugify($slug) : slugify($name);

    $newThumbnail = '';
    try {
        if (!empty($_FILES['thumbnail']['name'])) {
            $newThumbnail = save_upload($_FILES['thumbnail'], 'course-thumbnails', ['jpg', 'jpeg', 'png', 'webp'], 5 * 1024 * 1024);
        }
        $thumbnail = $newThumbnail !== '' ? $newThumbnail : (string)($row['thumbnail'] ?? '');
        $stmt = $conn->prepare("UPDATE study_courses SET course_name=?, course_slug=?, description=?, status=?, company_name=?, instructor_name=?, level=?, timeline=?, details_to_know=?, specialisation=?, course_timeline=?, thumbnail=? WHERE id=?");
        $stmt->bind_param('ssssssssssssi', $name, $slug, $desc, $status, $companyName, $instructorName, $level, $timeline, $detailsToKnow, $specialisation, $courseTimeline, $thumbnail, $id);
        if ($stmt->execute()) {
            $stmt->close();
            if ($newThumbnail !== '') {
                delete_relative_file($row['thumbnail'] ?? '');
            }
            flash('success', 'Course updated.');
            redirect('course_edit.php?id=' . $id);
        }
        $stmt->close();
        delete_relative_file($newThumbnail);
        flash('error', 'Could not update course.');
    } catch (Throwable $e) {
        delete_relative_file($newThumbnail);
        flash('error', 'Could not update course. Check the thumbnail and course details.');
    }
    $row['course_name']=$name; $row['course_slug']=$slug; $row['description']=$desc; $row['status']=$status;
    $row['company_name']=$companyName; $row['instructor_name']=$instructorName; $row['level']=$level;
    $row['timeline']=$timeline; $row['details_to_know']=$detailsToKnow; $row['specialisation']=$specialisation;
    $row['course_timeline']=$courseTimeline;
}
?>
<div class="card">
<form method="post" enctype="multipart/form-data">
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
<label>Company Name</label>
<input name="company_name" value="<?= e($row['company_name'] ?? '') ?>">
</div>
<div>
<label>Instructor Name</label>
<input name="instructor_name" value="<?= e($row['instructor_name'] ?? '') ?>">
</div>
<div>
<label>Level</label>
<input name="level" value="<?= e($row['level'] ?? '') ?>" placeholder="Beginner, Intermediate, Advanced">
</div>
<div>
<label>Duration (months)</label>
<input type="number" name="timeline" min="1" step="1" value="<?= e($row['timeline'] ?? '') ?>">
</div>
<div>
<label>Specialisation</label>
<input name="specialisation" value="<?= e($row['specialisation'] ?? '') ?>">
</div>
<div>
<label>Thumbnail</label>
<?php if (!empty($row['thumbnail'])): ?><div><img src="<?= e($row['thumbnail']) ?>" alt="Course thumbnail" style="width:120px;height:80px;object-fit:cover"></div><?php endif; ?>
<input type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp">
</div>
<div class="full">
<label>Details to Know</label>
<textarea name="details_to_know"><?= e($row['details_to_know'] ?? '') ?></textarea>
</div>
<div class="full">
<label>Course Timeline (optional)</label>
<textarea name="course_timeline" placeholder="Leave blank to distribute course topics automatically across the duration."><?= e($row['course_timeline'] ?? '') ?></textarea>
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
