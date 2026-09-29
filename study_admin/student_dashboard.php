<?php
session_start();
require_once dirname(__DIR__) . '/database_connection/db_connect.php';

if (!isset($_SESSION['enrollment_id'], $_SESSION['student_table'], $_SESSION['student_id'])) {
    header('Location: ../login-system/login.php');
    exit;
}

$enrollmentId = (string)$_SESSION['enrollment_id'];
$studentTable = (string)$_SESSION['student_table'];
$studentId = (int)$_SESSION['student_id'];
$studentTables = ['students' => 'student_id', 'students26' => 'id'];

if (!isset($studentTables[$studentTable])) {
    session_destroy();
    header('Location: ../login-system/login.php');
    exit;
}

$idColumn = $studentTables[$studentTable];
$stmt = $conn->prepare("SELECT name, enrollment_id, photo FROM `$studentTable` WHERE `$idColumn` = ? AND enrollment_id = ? LIMIT 1");
$stmt->bind_param('is', $studentId, $enrollmentId);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    session_destroy();
    header('Location: ../login-system/login.php?error=student_not_found');
    exit;
}

$studyContents = [];
if ($studentTable === 'students26') {
    $stmt = $conn->prepare("
        SELECT
            sc.id, sc.title, sc.content_type, sc.description, sc.content_body,
            sc.file_url, sc.video_url, sc.thumbnail_url,
            t.id AS topic_id, t.topic_name, t.sort_order AS topic_sort_order,
            c.id AS course_id, c.course_name, c.company_name, c.instructor_name,
            c.level, c.timeline, c.details_to_know, c.specialisation,
            c.course_timeline, c.thumbnail,
            MAX(sct.assigned_at) AS assigned_at
        FROM study_content_targets sct
        JOIN study_contents sc ON sc.id = sct.content_id
        JOIN study_topics t ON t.id = sc.topic_id
        JOIN study_courses c ON c.id = t.course_id
        LEFT JOIN student_batches sb
            ON sct.target_type = 'batch'
           AND sb.batch_id = sct.target_id
           AND sb.student_table = 'students26'
           AND sb.student_id = ?
        WHERE sc.status = 'active'
          AND sct.status = 'active'
          AND (
              (sct.target_type = 'student' AND sct.target_id = ?)
              OR sb.student_id IS NOT NULL
          )
        GROUP BY
            sc.id, sc.title, sc.content_type, sc.description, sc.content_body,
            sc.file_url, sc.video_url, sc.thumbnail_url,
            t.id, t.topic_name, t.sort_order, c.id, c.course_name, c.company_name,
            c.instructor_name, c.level, c.timeline, c.details_to_know,
            c.specialisation, c.course_timeline, c.thumbnail
        ORDER BY assigned_at DESC, c.course_name, t.sort_order, t.topic_name, sc.sort_order, sc.title
    ");
    $stmt->bind_param('ii', $studentId, $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $studyContents[] = $row;
    }
    $stmt->close();
}

$courseFolders = [];
foreach ($studyContents as $content) {
    $courseId = (int)$content['course_id'];
    $topicId = (int)$content['topic_id'];
    if (!isset($courseFolders[$courseId])) {
        $courseFolders[$courseId] = [
            'name' => $content['course_name'],
            'company_name' => $content['company_name'],
            'instructor_name' => $content['instructor_name'],
            'level' => $content['level'],
            'timeline' => $content['timeline'],
            'details_to_know' => $content['details_to_know'],
            'specialisation' => $content['specialisation'],
            'course_timeline' => $content['course_timeline'],
            'thumbnail' => $content['thumbnail'],
            'topics' => [],
            'lessons' => 0,
            'videos' => 0,
        ];
    }
    if (!isset($courseFolders[$courseId]['topics'][$topicId])) {
        $courseFolders[$courseId]['topics'][$topicId] = [
            'name' => $content['topic_name'],
            'sort_order' => (int)$content['topic_sort_order'],
            'contents' => [],
        ];
    }
    $courseFolders[$courseId]['topics'][$topicId]['contents'][] = $content;
    $courseFolders[$courseId]['lessons']++;
    if ($content['content_type'] === 'video') {
        $courseFolders[$courseId]['videos']++;
    }
}

$courseCount = count($courseFolders);
$videoCount = count(array_filter($studyContents, static fn($item) => $item['content_type'] === 'video'));

function study_escape($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Study Dashboard | Faiz Computer Institute</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--ink:#18252f;--muted:#65747d;--paper:#f3f6f4;--white:#fff;--line:#dce5e1;--green:#176b57;--green-soft:#e5f2ed;--orange:#ed8a35;--shadow:0 8px 24px rgba(24,37,47,.06)}
        *{box-sizing:border-box}
        body{margin:0;background:var(--paper);color:var(--ink);font-family:Arial,Helvetica,sans-serif;line-height:1.5}
        a{color:inherit}
        .shell{max-width:1180px;margin:auto;padding:24px}
        .topbar{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:24px}
        .back{display:inline-flex;align-items:center;gap:9px;text-decoration:none;font-weight:700;color:var(--green)}
        .identity{color:var(--muted);font-size:14px;text-align:right}
        .hero{background:var(--green);color:#fff;padding:28px 32px;border-radius:10px;display:flex;justify-content:space-between;align-items:center;gap:24px;margin-bottom:18px}
        .eyebrow{font-size:12px;text-transform:uppercase;font-weight:700;letter-spacing:1px;color:#c5e5d9}
        h1{font-size:28px;line-height:1.2;margin:5px 0 8px}
        .hero p{margin:0;color:#e0f0e9}
        .hero-mark{font-size:42px;color:#c5e5d9;padding:8px 14px}
        .stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:24px}
        .stat{background:var(--white);border:1px solid var(--line);border-radius:8px;padding:16px 18px;box-shadow:var(--shadow)}
        .stat strong{display:block;font-size:24px;line-height:1.15}
        .stat span{font-size:13px;color:var(--muted)}
        .toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:14px}
        .toolbar h2{font-size:19px;margin:0}
        .filters{display:flex;gap:8px;flex:0 1 510px}
        .filters input,.filters select{min-width:0;border:1px solid var(--line);border-radius:6px;background:#fff;padding:10px 12px;font:inherit;color:var(--ink)}
        .filters input{flex:1}
        .filters select{width:155px}
        .course-list{display:grid;gap:12px}
        .course-folder,.topic-folder{background:var(--white);border:1px solid var(--line);border-radius:8px;box-shadow:var(--shadow);overflow:hidden}
        .folder-summary{list-style:none;cursor:pointer;display:flex;align-items:center;gap:13px;padding:17px 19px}
        .folder-summary::-webkit-details-marker{display:none}
        .folder-icon{width:38px;height:38px;flex:0 0 38px;display:grid;place-items:center;background:var(--green-soft);color:var(--green);border-radius:7px}
        .folder-label{min-width:0;flex:1}
        .folder-label strong{display:block;font-size:15px;overflow-wrap:anywhere}
        .folder-label small{display:block;color:var(--muted);font-size:12px;margin-top:2px}
        .course-overview{display:flex;gap:18px;padding:0 19px 17px}
        .course-thumbnail{width:150px;aspect-ratio:3/2;object-fit:cover;border-radius:6px;background:#eef2ef}
        .course-meta{min-width:0;flex:1;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px 16px;font-size:12px}
        .course-meta strong{color:var(--ink)}
        .course-extra{grid-column:1/-1;color:#475761;white-space:pre-line;overflow-wrap:anywhere}
        .course-schedule{margin:0 19px 17px;border-top:1px solid var(--line);padding-top:12px}
        .course-schedule h3{font-size:13px;margin:0 0 8px}
        .month-row{display:grid;grid-template-columns:85px 1fr;gap:10px;padding:7px 0;border-top:1px solid #edf1ef;font-size:12px}
        .month-row strong{color:var(--green)}
        .chevron{color:var(--muted);transition:transform .18s ease}
        details[open]>.folder-summary .chevron{transform:rotate(180deg)}
        .topic-list{padding:0 13px 13px;display:grid;gap:9px}
        .topic-folder{box-shadow:none;background:#fbfcfb}
        .topic-folder .folder-summary{padding:13px 15px}
        .topic-folder .folder-icon{width:32px;height:32px;flex-basis:32px;background:#eef2ef;color:#53665f}
        .content-list{padding:0 14px 8px}
        .lesson{display:flex;align-items:flex-start;gap:13px;padding:13px 0;border-top:1px solid var(--line)}
        .lesson[hidden],.topic-folder[hidden],.course-folder[hidden]{display:none}
        .lesson-icon{width:34px;height:34px;flex:0 0 34px;display:grid;place-items:center;border-radius:6px;background:#f1f4f2;color:var(--green)}
        .lesson-info{min-width:0;flex:1}
        .lesson-title{font-size:14px;font-weight:700;line-height:1.35;overflow-wrap:anywhere}
        .lesson-description{font-size:12px;color:var(--muted);margin-top:3px;white-space:pre-line}
        .type{display:inline-block;font-size:10px;text-transform:uppercase;font-weight:700;color:var(--green);background:var(--green-soft);padding:3px 7px;border-radius:4px;margin-top:6px}
        .open{display:inline-flex;align-items:center;gap:7px;flex:0 0 auto;padding:8px 10px;border-radius:6px;background:var(--green);color:#fff;text-decoration:none;font-size:12px;font-weight:700}
        .open:hover{background:#105743}
        .open.material{background:#eef2f7;color:var(--ink)}
        .body-copy{font-size:13px;color:#475761;white-space:pre-wrap;overflow-wrap:anywhere;margin-top:8px;max-height:300px;overflow:auto}
        .lesson details{margin-top:6px}
        .lesson details summary{cursor:pointer;color:var(--green);font-size:12px;font-weight:700}
        .empty{background:#fff;border:1px dashed #bdcec6;border-radius:8px;text-align:center;padding:42px 20px;color:var(--muted)}
        .empty i{font-size:26px;color:var(--green);margin-bottom:10px}
        .empty h2{font-size:18px;color:var(--ink);margin:0 0 5px}
        .empty p{margin:0}
        @media(max-width:700px){.shell{padding:16px}.hero{padding:23px 20px}.hero-mark{font-size:32px}.toolbar{align-items:stretch;flex-direction:column}.filters{flex-basis:auto}.folder-summary{padding:14px}.topic-list{padding:0 8px 8px}.content-list{padding:0 10px 6px}.lesson{gap:9px}.open{padding:8px;font-size:11px}.course-overview{padding:0 14px 14px;gap:12px}.course-thumbnail{width:112px}.course-meta{grid-template-columns:1fr}.course-schedule{margin:0 14px 14px}}
        @media(max-width:450px){.stats{gap:8px}.stat{padding:13px 10px}.stat strong{font-size:20px}.stat span{font-size:11px}.filters{flex-direction:column}.filters select{width:100%}.identity{font-size:12px}.hero h1{font-size:23px}}
    </style>
</head>
<body>
<main class="shell">
    <header class="topbar">
        <a class="back" href="../test.php"><i class="fa-solid fa-arrow-left"></i> Student Portal</a>
        <div class="identity"><a class="back" href="student_courses.php">Browse All Courses</a><br><?= study_escape($student['name']) ?> · <?= study_escape($student['enrollment_id']) ?></div>
    </header>

    <section class="hero">
        <div>
            <div class="eyebrow">Your learning space</div>
            <h1>Student Study Dashboard</h1>
            <p>Study material assigned to you by your institute.</p>
        </div>
        <i class="fa-solid fa-book-open hero-mark" aria-hidden="true"></i>
    </section>

    <section class="stats" aria-label="Study dashboard summary">
        <div class="stat"><strong><?= count($studyContents) ?></strong><span>Assigned lessons</span></div>
        <div class="stat"><strong><?= $courseCount ?></strong><span>Courses</span></div>
        <div class="stat"><strong><?= $videoCount ?></strong><span>Video lessons</span></div>
    </section>

    <section>
        <div class="toolbar">
            <h2>My Study Content</h2>
            <?php if ($studyContents): ?>
            <div class="filters">
                <input id="studySearch" type="search" placeholder="Search lessons or courses" aria-label="Search lessons or courses">
                <select id="typeFilter" aria-label="Filter by content type">
                    <option value="">All types</option>
                    <?php foreach (array_unique(array_column($studyContents, 'content_type')) as $type): ?>
                    <option value="<?= study_escape(strtolower($type)) ?>"><?= study_escape(ucfirst($type)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!$studyContents): ?>
        <div class="empty">
            <i class="fa-solid fa-book-open" aria-hidden="true"></i>
            <h2>No study content assigned yet</h2>
            <p>When your instructor assigns lessons or course material, it will appear here.</p>
        </div>
        <?php else: ?>
        <div class="course-list" id="courseList">
            <?php foreach ($courseFolders as $courseId => $course): ?>
            <details class="course-folder" open>
                <summary class="folder-summary">
                    <span class="folder-icon"><i class="fa-solid fa-folder-open"></i></span>
                    <span class="folder-label"><strong><?= study_escape($course['name']) ?></strong><small><?= $course['lessons'] ?> lessons · <?= $course['videos'] ?> videos</small></span>
                    <i class="fa-solid fa-chevron-down chevron" aria-hidden="true"></i>
                </summary>
                <?php
                    uasort($course['topics'], static fn($left, $right) => ($left['sort_order'] <=> $right['sort_order']) ?: strcmp($left['name'], $right['name']));
                    $monthCount = max(1, (int)$course['timeline']);
                    $topicCount = count($course['topics']);
                    $schedule = [];
                    $topicIndex = 0;
                    foreach ($course['topics'] as $topicForSchedule) {
                        if ($topicCount <= $monthCount && $topicCount > 1) {
                            $month = (int)round($topicIndex * ($monthCount - 1) / ($topicCount - 1)) + 1;
                        } else {
                            $month = (int)floor($topicIndex * $monthCount / max(1, $topicCount)) + 1;
                        }
                        $schedule[$month][] = $topicForSchedule['name'];
                        $topicIndex++;
                    }
                ?>
                <?php if ($course['thumbnail'] || $course['company_name'] || $course['instructor_name'] || $course['level'] || $course['timeline'] || $course['specialisation'] || $course['details_to_know']): ?>
                <div class="course-overview">
                    <?php if ($course['thumbnail']): ?><img class="course-thumbnail" src="<?= study_escape($course['thumbnail']) ?>" alt="<?= study_escape($course['name']) ?> thumbnail"><?php endif; ?>
                    <div class="course-meta">
                        <?php if ($course['company_name']): ?><div><strong>Company:</strong> <?= study_escape($course['company_name']) ?></div><?php endif; ?>
                        <?php if ($course['instructor_name']): ?><div><strong>Instructor:</strong> <?= study_escape($course['instructor_name']) ?></div><?php endif; ?>
                        <?php if ($course['level']): ?><div><strong>Level:</strong> <?= study_escape($course['level']) ?></div><?php endif; ?>
                        <?php if ($course['timeline']): ?><div><strong>Duration:</strong> <?= (int)$course['timeline'] ?> month<?= (int)$course['timeline'] === 1 ? '' : 's' ?></div><?php endif; ?>
                        <?php if ($course['specialisation']): ?><div><strong>Specialisation:</strong> <?= study_escape($course['specialisation']) ?></div><?php endif; ?>
                        <?php if ($course['details_to_know']): ?><div class="course-extra"><strong>Details to know:</strong><br><?= study_escape($course['details_to_know']) ?></div><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (trim((string)$course['course_timeline']) !== '' || $schedule): ?>
                <section class="course-schedule">
                    <h3>Course Timeline</h3>
                    <?php if (trim((string)$course['course_timeline']) !== ''): ?>
                    <div class="course-extra"><?= study_escape($course['course_timeline']) ?></div>
                    <?php else: ?>
                    <?php foreach ($schedule as $month => $monthTopics): ?>
                    <div class="month-row"><strong>Month <?= (int)$month ?></strong><span><?= study_escape(implode(', ', $monthTopics)) ?></span></div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </section>
                <?php endif; ?>
                <div class="topic-list">
                    <?php foreach ($course['topics'] as $topic): ?>
                    <details class="topic-folder" open>
                        <summary class="folder-summary">
                            <span class="folder-icon"><i class="fa-solid fa-folder"></i></span>
                            <span class="folder-label"><strong><?= study_escape($topic['name']) ?></strong><small><?= count($topic['contents']) ?> items</small></span>
                            <i class="fa-solid fa-chevron-down chevron" aria-hidden="true"></i>
                        </summary>
                        <div class="content-list">
                            <?php foreach ($topic['contents'] as $content): ?>
                            <?php
                                $description = trim(strip_tags((string)$content['description']));
                                $searchText = strtolower($content['title'] . ' ' . $course['name'] . ' ' . $topic['name'] . ' ' . $content['content_type'] . ' ' . $description);
                                $materialUrl = $content['file_url'];
                            ?>
                            <article class="lesson" data-type="<?= study_escape(strtolower($content['content_type'])) ?>" data-search="<?= study_escape($searchText) ?>">
                                <span class="lesson-icon"><i class="fa-solid <?= $content['content_type'] === 'video' ? 'fa-circle-play' : 'fa-file-lines' ?>"></i></span>
                                <div class="lesson-info">
                                    <div class="lesson-title"><?= study_escape($content['title']) ?></div>
                                    <?php if ($description !== ''): ?><div class="lesson-description"><?= study_escape($description) ?></div><?php endif; ?>
                                    <span class="type"><?= study_escape($content['content_type']) ?></span>
                                    <?php if (trim((string)$content['content_body']) !== ''): ?>
                                    <details>
                                        <summary>Read notes</summary>
                                        <div class="body-copy"><?= study_escape(trim(strip_tags((string)$content['content_body']))) ?></div>
                                    </details>
                                    <?php endif; ?>
                                </div>
                                <?php if ($content['content_type'] === 'video' && $content['video_url']): ?>
                                <a class="open" href="student_video.php?id=<?= (int)$content['id'] ?>"><i class="fa-solid fa-play"></i> Play</a>
                                <?php elseif ($materialUrl): ?>
                                <a class="open material" href="<?= study_escape($materialUrl) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square"></i> Open</a>
                                <?php endif; ?>
                            </article>
                            <?php endforeach; ?>
                        </div>
                    </details>
                    <?php endforeach; ?>
                </div>
            </details>
            <?php endforeach; ?>
        </div>
        <div class="empty" id="noMatches" hidden><h2>No matching lessons</h2><p>Try another search or content type.</p></div>
        <?php endif; ?>
    </section>
</main>
<script>
const searchInput = document.getElementById('studySearch');
const typeFilter = document.getElementById('typeFilter');
const lessonCards = [...document.querySelectorAll('.lesson')];
const topicFolders = [...document.querySelectorAll('.topic-folder')];
const courseFolders = [...document.querySelectorAll('.course-folder')];
const noMatches = document.getElementById('noMatches');

function filterLessons() {
    const query = searchInput.value.trim().toLowerCase();
    const type = typeFilter.value;
    let visible = 0;
    lessonCards.forEach(card => {
        const matches = card.dataset.search.includes(query) && (!type || card.dataset.type === type);
        card.hidden = !matches;
        if (matches) visible++;
    });
    topicFolders.forEach(folder => {
        folder.hidden = ![...folder.querySelectorAll('.lesson')].some(card => !card.hidden);
    });
    courseFolders.forEach(folder => {
        folder.hidden = ![...folder.querySelectorAll('.lesson')].some(card => !card.hidden);
    });
    noMatches.hidden = visible > 0;
}

searchInput?.addEventListener('input', filterLessons);
typeFilter?.addEventListener('change', filterLessons);
</script>
</body>
</html>