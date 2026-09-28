<?php
$pageTitle = 'Study Dashboard';
require_once __DIR__ . '/includes/header.php';

function count_table(mysqli $conn, string $table): int {
    $allowed = ['study_courses','study_topics','study_contents','study_content_targets','study_progress'];
    if (!in_array($table, $allowed, true)) return 0;
    $r = $conn->query("SELECT COUNT(*) c FROM `$table`");
    return (int)$r->fetch_assoc()['c'];
}

$courses = count_table($conn, 'study_courses');
$topics = count_table($conn, 'study_topics');
$contents = count_table($conn, 'study_contents');
$assignments = count_table($conn, 'study_content_targets');

$studentCount = 0;
if ($r = $conn->query("SELECT COUNT(*) c FROM students26")) {
    $studentCount = (int)$r->fetch_assoc()['c'];
}
?>
<div class="grid">
    <div class="stat"><div class="muted">Courses</div><strong><?= $courses ?></strong></div>
    <div class="stat"><div class="muted">Topics</div><strong><?= $topics ?></strong></div>
    <div class="stat"><div class="muted">Contents</div><strong><?= $contents ?></strong></div>
    <div class="stat"><div class="muted">Assignments</div><strong><?= $assignments ?></strong></div>
</div>

<div class="card">
    <h2>Quick Actions</h2>
    <div class="actions">
        <a class="btn orange" href="course_add.php">+ Add Course</a>
        <a class="btn" href="topic_add.php">+ Add Topic</a>
        <a class="btn" href="content_add.php">+ Add Content</a>
        <a class="btn" href="assignments.php">Manage Assignments</a>
    </div>
</div>

<div class="card">
    <h2>Student Source</h2>
    <p class="muted">
        This module reads students only from <strong>students26</strong>.
        Current students26 rows: <strong><?= $studentCount ?></strong>.
        Batch membership is read from <strong>students_batch</strong> with
        <strong>student_table = 'students26'</strong>.
    </p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
