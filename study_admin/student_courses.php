<?php
require_once dirname(__DIR__) . '/database_connection/db_connect.php';
$courses = [];
$result = $conn->query("SELECT id, course_name, description, company_name, instructor_name, level, timeline, details_to_know, specialisation, course_timeline, thumbnail, price, sale_price, discount_type, discount_value, currency, is_free FROM study_courses WHERE status='active' ORDER BY course_name");
while ($course = $result->fetch_assoc()) {
    $course['topics'] = [];
    $courses[(int)$course['id']] = $course;
}

if ($courses) {
    $topicResult = $conn->query("SELECT t.course_id, t.topic_name FROM study_topics t JOIN study_courses c ON c.id=t.course_id WHERE t.status='active' AND c.status='active' ORDER BY t.course_id, t.sort_order, t.topic_name");
    while ($topic = $topicResult->fetch_assoc()) {
        $courseId = (int)$topic['course_id'];
        if (isset($courses[$courseId])) {
            $courses[$courseId]['topics'][] = $topic['topic_name'];
        }
    }
}

function catalog_escape($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>All Courses | Faiz Computer Institute</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--ink:#18252f;--muted:#65747d;--paper:#f3f6f4;--white:#fff;--line:#dce5e1;--green:#176b57;--green-soft:#e5f2ed;--orange:#ed8a35}
        *{box-sizing:border-box}
        body{margin:0;background:var(--paper);color:var(--ink);font-family:Arial,Helvetica,sans-serif;line-height:1.5}
        a{color:inherit}
        .shell{max-width:1180px;margin:auto;padding:24px}
        .topbar{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:22px}
        .back{display:inline-flex;align-items:center;gap:9px;text-decoration:none;font-weight:700;color:var(--green)}
        .identity{color:var(--muted);font-size:14px;text-align:right}
        .intro{display:flex;align-items:end;justify-content:space-between;gap:20px;border-bottom:1px solid var(--line);padding-bottom:18px;margin-bottom:20px}
        h1{font-size:28px;line-height:1.2;margin:0 0 5px}
        .intro p{margin:0;color:var(--muted)}
        .search{width:min(100%,340px);border:1px solid var(--line);border-radius:6px;background:#fff;padding:11px 13px;font:inherit;color:var(--ink)}
        .course-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
        .course{background:var(--white);border:1px solid var(--line);border-radius:8px;overflow:hidden}
        .cover{width:100%;aspect-ratio:16/7;display:block;object-fit:cover;background:#e5eeea}
        .cover-placeholder{display:grid;place-items:center;color:var(--green);font-size:32px;background:linear-gradient(135deg,#e5f2ed,#f4eee3)}
        .course-body{padding:17px}
        .course h2{font-size:19px;line-height:1.3;margin:0 0 7px;overflow-wrap:anywhere}
        .description{font-size:13px;color:#475761;margin:0 0 13px;white-space:pre-line}
        .meta{display:flex;flex-wrap:wrap;gap:7px 15px;color:var(--muted);font-size:12px;padding-bottom:13px;border-bottom:1px solid var(--line)}
        .meta span{display:inline-flex;align-items:center;gap:6px}
        .meta i{color:var(--green)}
        .pricing{display:flex;align-items:baseline;gap:9px;margin-top:13px;font-weight:700}
        .pricing-current{font-size:17px;color:var(--green)}
        .pricing-original{font-size:13px;color:var(--muted);font-weight:400}
        .free-label{color:var(--green);font-size:17px}
        .detail{font-size:13px;color:#475761;white-space:pre-line;overflow-wrap:anywhere;margin-top:12px}
        .detail strong,.timeline-title{color:var(--ink)}
        .timeline{margin-top:14px}
        .timeline-title{font-size:13px;font-weight:700;margin:0 0 6px}
        .month{display:grid;grid-template-columns:76px 1fr;gap:10px;padding:7px 0;border-top:1px solid #edf1ef;font-size:12px}
        .month strong{color:var(--green)}
        .topics{color:#475761;overflow-wrap:anywhere}
        .empty{grid-column:1/-1;background:#fff;border:1px dashed #bdcec6;border-radius:8px;text-align:center;padding:42px 20px;color:var(--muted)}
        .empty h2{font-size:18px;color:var(--ink);margin:0 0 5px}
        @media(max-width:700px){.shell{padding:16px}.intro{align-items:stretch;flex-direction:column}.search{width:100%}.course-grid{grid-template-columns:1fr}.topbar{align-items:flex-start}}
    </style>
</head>
<body>
<main class="shell">
    <header class="topbar">
        <a class="back" href="../index.html"><i class="fa-solid fa-house" aria-hidden="true"></i> Faiz Computer Institute</a>
        <a class="back" href="../login-system/login.php">Student Login <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </header>

    <section class="intro">
        <div>
            <h1>All Courses</h1>
            <p><?= count($courses) ?> active course<?= count($courses) === 1 ? '' : 's' ?></p>
        </div>
        <?php if ($courses): ?><input class="search" id="courseSearch" type="search" placeholder="Search courses" aria-label="Search courses"><?php endif; ?>
    </section>

    <section class="course-grid" id="courseGrid">
        <?php if (!$courses): ?>
        <div class="empty"><h2>No courses available</h2><p>Active courses will appear here.</p></div>
        <?php endif; ?>
        <?php foreach ($courses as $course): ?>
        <?php
            $price = (float)$course['price'];
            $displayPrice = (float)$course['sale_price'];
            $hasDiscount = $displayPrice < $price;
            $currency = $course['currency'] ?: 'INR';
            $searchText = strtolower(implode(' ', [
                $course['course_name'], $course['description'], $course['company_name'],
                $course['instructor_name'], $course['level'], $course['specialisation'],
                implode(' ', $course['topics']),
            ]));
            $monthCount = max(1, (int)$course['timeline']);
            $topicCount = count($course['topics']);
            $schedule = [];
            foreach ($course['topics'] as $topicIndex => $topicName) {
                if ($topicCount <= $monthCount && $topicCount > 1) {
                    $month = (int)round($topicIndex * ($monthCount - 1) / ($topicCount - 1)) + 1;
                } else {
                    $month = (int)floor($topicIndex * $monthCount / max(1, $topicCount)) + 1;
                }
                $schedule[$month][] = $topicName;
            }
        ?>
        <article class="course" data-search="<?= catalog_escape($searchText) ?>">
            <?php if ($course['thumbnail']): ?>
            <a href="course_detail.php?id=<?= (int)$course['id'] ?>" aria-label="View details for <?= catalog_escape($course['course_name']) ?>"><img class="cover" src="<?= catalog_escape($course['thumbnail']) ?>" alt="<?= catalog_escape($course['course_name']) ?> thumbnail" loading="lazy"></a>
            <?php else: ?>
            <a href="course_detail.php?id=<?= (int)$course['id'] ?>" aria-label="View details for <?= catalog_escape($course['course_name']) ?>"><div class="cover cover-placeholder" aria-hidden="true"><i class="fa-solid fa-book-open"></i></div></a>
            <?php endif; ?>
            <div class="course-body">
                <h2><a href="course_detail.php?id=<?= (int)$course['id'] ?>"><?= catalog_escape($course['course_name']) ?></a></h2>
                <?php if (trim((string)$course['description']) !== ''): ?><p class="description"><?= catalog_escape($course['description']) ?></p><?php endif; ?>
                <div class="meta">
                    <?php if ($course['company_name']): ?><span><i class="fa-regular fa-building"></i><?= catalog_escape($course['company_name']) ?></span><?php endif; ?>
                    <?php if ($course['instructor_name']): ?><span><i class="fa-regular fa-user"></i><?= catalog_escape($course['instructor_name']) ?></span><?php endif; ?>
                    <?php if ($course['level']): ?><span><i class="fa-solid fa-signal"></i><?= catalog_escape($course['level']) ?></span><?php endif; ?>
                    <?php if ($course['timeline']): ?><span><i class="fa-regular fa-clock"></i><?= (int)$course['timeline'] ?> month<?= (int)$course['timeline'] === 1 ? '' : 's' ?></span><?php endif; ?>
                    <?php if ($course['specialisation']): ?><span><i class="fa-solid fa-award"></i><?= catalog_escape($course['specialisation']) ?></span><?php endif; ?>
                </div>
                <div class="pricing" aria-label="Course price">
                    <?php if ((int)$course['is_free'] === 1): ?>
                    <span class="free-label">Free</span>
                    <?php else: ?>
                    <span class="pricing-current"><?= catalog_escape($currency) ?> <?= number_format($displayPrice, 2) ?></span>
                    <?php if ($hasDiscount): ?><del class="pricing-original"><?= catalog_escape($currency) ?> <?= number_format($price, 2) ?></del><?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php if (trim((string)$course['details_to_know']) !== ''): ?><div class="detail"><strong>Details to know</strong><br><?= catalog_escape($course['details_to_know']) ?></div><?php endif; ?>
                <?php if (trim((string)$course['course_timeline']) !== '' || $schedule): ?>
                <div class="timeline">
                    <div class="timeline-title">Course Timeline</div>
                    <?php if (trim((string)$course['course_timeline']) !== ''): ?>
                    <div class="detail"><?= catalog_escape($course['course_timeline']) ?></div>
                    <?php else: ?>
                    <?php foreach ($schedule as $month => $monthTopics): ?>
                    <div class="month"><strong>Month <?= (int)$month ?></strong><span class="topics"><?= catalog_escape(implode(', ', $monthTopics)) ?></span></div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
        <?php if ($courses): ?><div class="empty" id="noCourses" hidden><h2>No matching courses</h2><p>Try a different search.</p></div><?php endif; ?>
    </section>
</main>
<?php if ($courses): ?>
<script>
const courseSearch = document.getElementById('courseSearch');
const courseCards = [...document.querySelectorAll('.course')];
const noCourses = document.getElementById('noCourses');
courseSearch.addEventListener('input', () => {
    const query = courseSearch.value.trim().toLowerCase();
    let visibleCount = 0;
    courseCards.forEach(card => {
        const matches = card.dataset.search.includes(query);
        card.hidden = !matches;
        if (matches) visibleCount++;
    });
    noCourses.hidden = visibleCount > 0;
});
</script>
<?php endif; ?>
</body>
</html>