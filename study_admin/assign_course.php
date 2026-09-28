<?php
$pageTitle = 'Assign Course or Topic';
require_once __DIR__ . '/includes/header.php';

$message = '';
$messageType = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = post_int('course_id');
    $topicId = post_int('topic_id');
    $studentId = post_int('student_id');
    $transactionStarted = false;

    try {
        $stmt = $conn->prepare("SELECT course_name FROM study_courses WHERE id=? AND status='active'");
        $stmt->bind_param('i', $courseId);
        $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$course) {
            throw new DomainException('Select an active course.');
        }

        if ($topicId > 0) {
            $stmt = $conn->prepare("SELECT topic_name FROM study_topics WHERE id=? AND course_id=? AND status='active'");
            $stmt->bind_param('ii', $topicId, $courseId);
            $stmt->execute();
            $topic = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$topic) {
                throw new DomainException('Select a topic from the chosen course.');
            }
        }

        $stmt = $conn->prepare("SELECT name FROM students26 WHERE id=?");
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$student) {
            throw new DomainException('Select a valid student.');
        }

        $stmt = $conn->prepare("
            SELECT sc.id
            FROM study_contents sc
            JOIN study_topics t ON t.id=sc.topic_id
            WHERE sc.status='active'
              AND t.status='active'
              AND t.course_id=?
              AND (?=0 OR t.id=?)
            ORDER BY t.sort_order, sc.sort_order, sc.id
        ");
        $stmt->bind_param('iii', $courseId, $topicId, $topicId);
        $stmt->execute();
        $contentResult = $stmt->get_result();
        $contentIds = [];
        while ($content = $contentResult->fetch_assoc()) {
            $contentIds[] = (int)$content['id'];
        }
        $stmt->close();

        if (!$contentIds) {
            throw new DomainException('There is no active study content in this selection.');
        }

        $conn->begin_transaction();
        $transactionStarted = true;
        $findTarget = $conn->prepare("SELECT id,status FROM study_content_targets WHERE content_id=? AND target_type='student' AND target_id=? LIMIT 1");
        $activateTarget = $conn->prepare("UPDATE study_content_targets SET status='active' WHERE id=?");
        $insertTarget = $conn->prepare("INSERT INTO study_content_targets (content_id,target_type,target_id,status) VALUES (?,'student',?,'active')");
        $assignedCount = 0;
        $alreadyActiveCount = 0;

        foreach ($contentIds as $contentId) {
            $findTarget->bind_param('ii', $contentId, $studentId);
            $findTarget->execute();
            $existing = $findTarget->get_result()->fetch_assoc();

            if ($existing && $existing['status'] === 'active') {
                $alreadyActiveCount++;
                continue;
            }

            if ($existing) {
                $targetId = (int)$existing['id'];
                $activateTarget->bind_param('i', $targetId);
                $activateTarget->execute();
            } else {
                $insertTarget->bind_param('ii', $contentId, $studentId);
                $insertTarget->execute();
            }
            $assignedCount++;
        }

        $findTarget->close();
        $activateTarget->close();
        $insertTarget->close();
        $conn->commit();
        $transactionStarted = false;

        $scopeName = $topicId > 0 ? $topic['topic_name'] : $course['course_name'];
        $message = $assignedCount . ' lesson(s) assigned or reactivated; ' . $alreadyActiveCount . ' were already assigned to ' . $student['name'] . ' for ' . $scopeName . '.';
        $messageType = 'success';
    } catch (DomainException $e) {
        if ($transactionStarted) {
            $conn->rollback();
        }
        $message = $e->getMessage();
    } catch (Throwable $e) {
        if ($transactionStarted) {
            $conn->rollback();
        }
        $message = 'Assignments could not be saved. Please try again.';
    }
}

$courses = $conn->query("SELECT id,course_name FROM study_courses WHERE status='active' ORDER BY course_name");
$topics = $conn->query("SELECT id,course_id,topic_name FROM study_topics WHERE status='active' ORDER BY course_id,sort_order,topic_name");
$students = $conn->query("SELECT id,name,enrollment_id,contact,course FROM students26 ORDER BY name");
?>
<?php if ($message !== ''): ?>
<div class="alert <?= $messageType === 'success' ? 'success' : 'error' ?>"><?= e($message) ?></div>
<?php endif; ?>

<div class="card">
    <h2>Assign Study Content</h2>
    <p class="muted">Choose a course to assign all its active lessons, or choose one topic to assign only that topic's lessons.</p>
    <form method="post">
        <div class="form-grid">
            <div>
                <label for="course_id">Course *</label>
                <select name="course_id" id="course_id" required>
                    <option value="">Select course</option>
                    <?php while ($course = $courses->fetch_assoc()): ?>
                    <option value="<?= (int)$course['id'] ?>" <?= (int)($_POST['course_id'] ?? 0) === (int)$course['id'] ? 'selected' : '' ?>><?= e($course['course_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label for="topic_id">Topic</label>
                <select name="topic_id" id="topic_id">
                    <option value="0">Entire course</option>
                    <?php while ($topic = $topics->fetch_assoc()): ?>
                    <option value="<?= (int)$topic['id'] ?>" data-course-id="<?= (int)$topic['course_id'] ?>" <?= (int)($_POST['topic_id'] ?? 0) === (int)$topic['id'] ? 'selected' : '' ?>><?= e($topic['topic_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="full">
                <label for="student_id">Student *</label>
                <select name="student_id" id="student_id" required>
                    <option value="">Select student</option>
                    <?php while ($student = $students->fetch_assoc()): ?>
                    <option value="<?= (int)$student['id'] ?>" <?= (int)($_POST['student_id'] ?? 0) === (int)$student['id'] ? 'selected' : '' ?>><?= e($student['name'] . ' | Enrollment ' . $student['enrollment_id'] . ' | ' . $student['course']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <br>
        <button class="btn orange" type="submit"><i class="fas fa-user-plus"></i> Assign to Student</button>
        <a class="btn light" href="assignments.php">Manage Assignments</a>
    </form>
</div>

<script>
const courseSelect = document.getElementById('course_id');
const topicSelect = document.getElementById('topic_id');

function filterTopics() {
    const courseId = courseSelect.value;
    [...topicSelect.options].forEach(option => {
        if (!option.dataset.courseId) return;
        option.hidden = option.dataset.courseId !== courseId;
    });
    const selectedTopic = topicSelect.selectedOptions[0];
    if (selectedTopic?.dataset.courseId && selectedTopic.dataset.courseId !== courseId) {
        topicSelect.value = '0';
    }
}

courseSelect.addEventListener('change', filterTopics);
filterTopics();
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>