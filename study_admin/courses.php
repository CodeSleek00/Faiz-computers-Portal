<?php
$pageTitle = 'Courses';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post_string('action');
    if ($action === 'delete') {
        $id = post_int('id');
        $stmt = $conn->prepare("DELETE FROM study_courses WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        flash('success', 'Course deleted.');
        redirect('courses.php');
    }
}

$q = post_string('q');
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $conn->prepare("SELECT * FROM study_courses WHERE course_name LIKE ? OR course_slug LIKE ? ORDER BY id DESC");
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $rows = $stmt->get_result();
} else {
    $rows = $conn->query("SELECT * FROM study_courses ORDER BY id DESC");
}
?>
<div class="card">
    <div class="topbar">
        <h2>All Courses</h2>
        <a class="btn orange" href="course_add.php">+ Add Course</a>
    </div>
    <form class="searchbar" method="post">
        <input name="q" placeholder="Search course..." value="<?= e($q) ?>">
        <button class="btn" type="submit">Search</button>
        <a class="btn light" href="courses.php">Reset</a>
    </form>
    <div class="table-wrap">
    <table>
        <thead><tr><th>ID</th><th>Course</th><th>Slug</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while ($row = $rows->fetch_assoc()): ?>
        <tr>
            <td><?= (int)$row['id'] ?></td>
            <td><strong><?= e($row['course_name']) ?></strong><br><span class="muted"><?= e($row['description'] ?? '') ?></span></td>
            <td><?= e($row['course_slug']) ?></td>
            <td><span class="badge <?= $row['status']==='active'?'active':'inactive' ?>"><?= e($row['status']) ?></span></td>
            <td><?= e($row['created_at']) ?></td>
            <td>
                <div class="actions">
                    <a class="btn small" href="course_edit.php?id=<?= (int)$row['id'] ?>">Edit</a>
                    <a class="btn small light" href="topics.php?course_id=<?= (int)$row['id'] ?>">Topics</a>
                    <form class="inline" method="post" onsubmit="return confirm('Delete this course and all its topics/content?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                        <button class="btn small danger">Delete</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
