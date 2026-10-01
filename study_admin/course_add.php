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
    $price = max(0, (float)post_string('price', '0'));
    $discountType = post_string('discount_type', 'percentage');
    if (!in_array($discountType, ['percentage', 'fixed'], true)) {
        $discountType = 'percentage';
    }
    $discountValue = max(0, (float)post_string('discount_value', '0'));
    $currency = substr(post_string('currency', 'INR'), 0, 10);
    $currency = $currency !== '' ? strtoupper($currency) : 'INR';
    $isFree = isset($_POST['is_free']) ? 1 : 0;

    if ($name === '') {
        flash('error', 'Course name is required.');
    } else {
        $slug = $slug !== '' ? slugify($slug) : slugify($name);
        $thumbnail = '';
        try {
            if (!empty($_FILES['thumbnail']['name'])) {
                $thumbnail = save_upload($_FILES['thumbnail'], 'course-thumbnails', ['jpg', 'jpeg', 'png', 'webp'], 5 * 1024 * 1024);
            }
            $stmt = $conn->prepare("INSERT INTO study_courses (course_name, course_slug, description, status, company_name, instructor_name, level, timeline, details_to_know, specialisation, course_timeline, thumbnail, price, discount_type, discount_value, currency, is_free) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('ssssssssssssdsdsi', $name, $slug, $desc, $status, $companyName, $instructorName, $level, $timeline, $detailsToKnow, $specialisation, $courseTimeline, $thumbnail, $price, $discountType, $discountValue, $currency, $isFree);
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
<label>Price</label>
<input type="number" name="price" min="0" step="0.01" value="0.00">
</div>
<div>
<label>Discount Type</label>
<select name="discount_type"><option value="percentage">Percentage</option><option value="fixed">Fixed amount</option></select>
</div>
<div>
<label>Discount Value</label>
<input type="number" name="discount_value" min="0" step="0.01" value="0.00">
</div>
<div>
<label>Currency</label>
<input name="currency" maxlength="10" value="INR">
</div>
<div>
<label><input type="checkbox" name="is_free" value="1" style="width:auto"> Free course</label>
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
