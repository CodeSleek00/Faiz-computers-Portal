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
            t.topic_name, c.course_name,
            MAX(sct.assigned_at) AS assigned_at
        FROM study_content_targets sct
        JOIN study_contents sc ON sc.id = sct.content_id
        JOIN study_topics t ON t.id = sc.topic_id
        JOIN study_courses c ON c.id = t.course_id
        LEFT JOIN students_batch sb
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
            t.topic_name, c.course_name
        ORDER BY assigned_at DESC, c.course_name, t.topic_name, sc.sort_order, sc.title
    ");
    $stmt->bind_param('ii', $studentId, $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $studyContents[] = $row;
    }
    $stmt->close();
}

$courseCount = count(array_unique(array_column($studyContents, 'course_name')));
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
        .lessons{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
        .lesson{background:var(--white);border:1px solid var(--line);border-radius:8px;padding:19px;box-shadow:var(--shadow);min-width:0}
        .lesson-meta{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:11px}
        .type{font-size:11px;text-transform:uppercase;font-weight:700;color:var(--green);background:var(--green-soft);padding:4px 8px;border-radius:4px}
        .assigned{font-size:11px;color:var(--muted)}
        .path{font-size:12px;color:var(--muted);margin-bottom:4px}
        .lesson h3{font-size:17px;line-height:1.3;margin:0 0 8px;overflow-wrap:anywhere}
        .description{font-size:13px;color:#475761;margin:0 0 14px;white-space:pre-line}
        .lesson-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
        .open{display:inline-flex;align-items:center;gap:8px;padding:9px 12px;border-radius:6px;background:var(--green);color:#fff;text-decoration:none;font-size:13px;font-weight:700}
        .open:hover{background:#105743}
        details{margin-top:13px;border-top:1px solid var(--line);padding-top:10px}
        summary{cursor:pointer;color:var(--green);font-size:13px;font-weight:700}
        .body-copy{font-size:13px;color:#475761;white-space:pre-wrap;overflow-wrap:anywhere;margin-top:9px;max-height:300px;overflow:auto}
        .empty{background:#fff;border:1px dashed #bdcec6;border-radius:8px;text-align:center;padding:42px 20px;color:var(--muted)}
        .empty i{font-size:26px;color:var(--green);margin-bottom:10px}
        .empty h2{font-size:18px;color:var(--ink);margin:0 0 5px}
        .empty p{margin:0}
        @media(max-width:700px){.shell{padding:16px}.hero{padding:23px 20px}.hero-mark{font-size:32px}.toolbar{align-items:stretch;flex-direction:column}.filters{flex-basis:auto}.lessons{grid-template-columns:1fr}}
        @media(max-width:450px){.stats{gap:8px}.stat{padding:13px 10px}.stat strong{font-size:20px}.stat span{font-size:11px}.filters{flex-direction:column}.filters select{width:100%}.identity{font-size:12px}.hero h1{font-size:23px}}
    </style>
</head>
<body>
<main class="shell">
    <header class="topbar">
        <a class="back" href="../test.php"><i class="fa-solid fa-arrow-left"></i> Student Portal</a>
        <div class="identity"><?= study_escape($student['name']) ?><br><?= study_escape($student['enrollment_id']) ?></div>
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
        <div class="lessons" id="lessons">
            <?php foreach ($studyContents as $content): ?>
            <?php
                $resourceUrl = $content['video_url'] ?: $content['file_url'];
                $description = trim(strip_tags((string)$content['description']));
                if ($description === '') {
                    $description = trim(strip_tags((string)$content['content_body']));
                }
                $description = strlen($description) > 180 ? substr($description, 0, 177) . '...' : $description;
                $searchText = strtolower($content['title'] . ' ' . $content['course_name'] . ' ' . $content['topic_name'] . ' ' . $content['content_type']);
            ?>
            <article class="lesson" data-type="<?= study_escape(strtolower($content['content_type'])) ?>" data-search="<?= study_escape($searchText) ?>">
                <div class="lesson-meta">
                    <span class="type"><?= study_escape($content['content_type']) ?></span>
                    <span class="assigned"><?= study_escape(date('M j, Y', strtotime($content['assigned_at']))) ?></span>
                </div>
                <div class="path"><?= study_escape($content['course_name']) ?> / <?= study_escape($content['topic_name']) ?></div>
                <h3><?= study_escape($content['title']) ?></h3>
                <?php if ($description !== ''): ?><p class="description"><?= study_escape($description) ?></p><?php endif; ?>
                <div class="lesson-actions">
                    <?php if ($resourceUrl): ?>
                    <a class="open" href="<?= study_escape($resourceUrl) ?>" target="_blank" rel="noopener">
                        <i class="fa-solid <?= $content['video_url'] ? 'fa-play' : 'fa-arrow-up-right-from-square' ?>"></i>
                        <?= $content['video_url'] ? 'Watch lesson' : 'Open material' ?>
                    </a>
                    <?php endif; ?>
                </div>
                <?php if (trim((string)$content['content_body']) !== ''): ?>
                <details>
                    <summary>Read lesson notes</summary>
                    <div class="body-copy"><?= study_escape(trim(strip_tags((string)$content['content_body']))) ?></div>
                </details>
                <?php endif; ?>
            </article>
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
    noMatches.hidden = visible > 0;
}

searchInput?.addEventListener('input', filterLessons);
typeFilter?.addEventListener('change', filterLessons);
</script>
</body>
</html>