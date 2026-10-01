<?php
require_once dirname(__DIR__) . '/database_connection/db_connect.php';

$courseId = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT id, course_name, description, company_name, instructor_name, level, timeline, details_to_know, specialisation, course_timeline, thumbnail, price, sale_price, discount_type, discount_value, currency, is_free FROM study_courses WHERE id=? AND status='active' LIMIT 1");
$stmt->bind_param('i', $courseId);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    http_response_code(404);
} else {
    $price = (float)$course['price'];
    $discountValue = (float)$course['discount_value'];
    $displayPrice = (float)$course['sale_price'] > 0
        ? (float)$course['sale_price']
        : ($course['discount_type'] === 'fixed'
            ? max(0, $price - $discountValue)
            : max(0, $price * (1 - $discountValue / 100)));
    $hasDiscount = $displayPrice < $price;
    $currency = $course['currency'] ?: 'INR';
}

$topics = [];
if ($course) {
    $stmt = $conn->prepare("SELECT topic_name FROM study_topics WHERE course_id=? AND status='active' ORDER BY sort_order, topic_name");
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($topic = $result->fetch_assoc()) {
        $topics[] = $topic['topic_name'];
    }
    $stmt->close();
}

function detail_escape($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$monthCount = max(1, (int)($course['timeline'] ?? 0));
$topicCount = count($topics);
$schedule = [];
foreach ($topics as $topicIndex => $topicName) {
    if ($topicCount <= $monthCount && $topicCount > 1) {
        $month = (int)round($topicIndex * ($monthCount - 1) / ($topicCount - 1)) + 1;
    } else {
        $month = (int)floor($topicIndex * $monthCount / max(1, $topicCount)) + 1;
    }
    $schedule[$month][] = $topicName;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $course ? detail_escape($course['course_name']) . ' | Course Details' : 'Course Not Found' ?> | Faiz Computer Institute</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--ink:#18252f;--muted:#65747d;--paper:#f3f6f4;--white:#fff;--line:#dce5e1;--green:#176b57;--green-soft:#e5f2ed;--orange:#ed8a35}
        *{box-sizing:border-box}
        body{margin:0;background:var(--paper);color:var(--ink);font-family:Arial,Helvetica,sans-serif;line-height:1.55}
        a{color:inherit}
        .shell{max-width:980px;margin:auto;padding:24px}
        .topbar{display:flex;justify-content:space-between;align-items:center;gap:14px;margin-bottom:22px}
        .back{display:inline-flex;align-items:center;gap:9px;color:var(--green);font-weight:700;text-decoration:none}
        .detail{background:var(--white);border:1px solid var(--line);border-radius:8px;overflow:hidden}
        .cover{width:100%;max-height:430px;aspect-ratio:16/7;display:block;object-fit:cover;background:#e5eeea}
        .cover-placeholder{display:grid;place-items:center;color:var(--green);font-size:48px;background:linear-gradient(135deg,#e5f2ed,#f4eee3)}
        .content{padding:26px}
        h1{font-size:30px;line-height:1.2;margin:0 0 10px;overflow-wrap:anywhere}
        .description{font-size:15px;color:#475761;white-space:pre-line;margin:0 0 20px}
        .pricing{display:flex;align-items:baseline;gap:10px;margin:0 0 20px;font-weight:700}
        .pricing-current{font-size:22px;color:var(--green)}
        .pricing-original{font-size:15px;color:var(--muted);font-weight:400}
        .free-label{color:var(--green);font-size:22px}
        .meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px 20px;padding:18px 0;border-top:1px solid var(--line);border-bottom:1px solid var(--line)}
        .meta div{min-width:0;font-size:14px;overflow-wrap:anywhere}
        .meta strong{display:block;color:var(--muted);font-size:11px;text-transform:uppercase;margin-bottom:2px}
        .section{margin-top:23px}
        .section h2{font-size:18px;margin:0 0 10px}
        .copy{font-size:14px;color:#475761;white-space:pre-line;overflow-wrap:anywhere}
        .month{display:grid;grid-template-columns:92px 1fr;gap:12px;padding:11px 0;border-top:1px solid #edf1ef;font-size:14px}
        .month strong{color:var(--green)}
        .topics{color:#475761;overflow-wrap:anywhere}
        .topic-list{margin:0;padding-left:21px;color:#475761}
        .topic-list li{padding:3px 0}
        .empty{background:var(--white);border:1px dashed #bdcec6;border-radius:8px;text-align:center;padding:42px 20px}
        .empty h1{font-size:22px}
        @media(max-width:600px){.shell{padding:15px}.content{padding:19px}.meta{grid-template-columns:1fr}.cover{aspect-ratio:4/3}.topbar{align-items:flex-start}h1{font-size:25px}}
    </style>
</head>
<body>
<main class="shell">
    <header class="topbar">
        <a class="back" href="student_courses.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> All Courses</a>
        <a class="back" href="../login-system/login.php">Student Login</a>
    </header>

    <?php if (!$course): ?>
    <section class="empty"><h1>Course not found</h1><p>This course may no longer be available.</p></section>
    <?php else: ?>
    <article class="detail">
        <?php if ($course['thumbnail']): ?>
        <img class="cover" src="<?= detail_escape($course['thumbnail']) ?>" alt="<?= detail_escape($course['course_name']) ?> thumbnail">
        <?php else: ?>
        <div class="cover cover-placeholder" aria-hidden="true"><i class="fa-solid fa-book-open"></i></div>
        <?php endif; ?>
        <div class="content">
            <h1><?= detail_escape($course['course_name']) ?></h1>
            <?php if (trim((string)$course['description']) !== ''): ?><p class="description"><?= detail_escape($course['description']) ?></p><?php endif; ?>
            <div class="pricing" aria-label="Course price">
                <?php if ((int)$course['is_free'] === 1): ?>
                <span class="free-label">Free</span>
                <?php else: ?>
                <span class="pricing-current"><?= detail_escape($currency) ?> <?= number_format($displayPrice, 2) ?></span>
                <?php if ($hasDiscount): ?><del class="pricing-original"><?= detail_escape($currency) ?> <?= number_format($price, 2) ?></del><?php endif; ?>
                <?php endif; ?>
            </div>

            <section class="meta" aria-label="Course information">
                <?php foreach ([
                    'Company' => $course['company_name'],
                    'Instructor' => $course['instructor_name'],
                    'Level' => $course['level'],
                    'Duration' => $course['timeline'] ? (int)$course['timeline'] . ' month' . ((int)$course['timeline'] === 1 ? '' : 's') : '',
                    'Specialisation' => $course['specialisation'],
                ] as $label => $value): ?>
                <?php if (trim((string)$value) !== ''): ?><div><strong><?= detail_escape($label) ?></strong><?= detail_escape($value) ?></div><?php endif; ?>
                <?php endforeach; ?>
            </section>

            <?php if (trim((string)$course['details_to_know']) !== ''): ?>
            <section class="section"><h2>Details to Know</h2><div class="copy"><?= detail_escape($course['details_to_know']) ?></div></section>
            <?php endif; ?>

            <?php if (trim((string)$course['course_timeline']) !== ''): ?>
            <section class="section"><h2>Course Timeline</h2><div class="copy"><?= detail_escape($course['course_timeline']) ?></div></section>
            <?php elseif ($schedule): ?>
            <section class="section">
                <h2>Course Timeline</h2>
                <?php foreach ($schedule as $month => $monthTopics): ?>
                <div class="month"><strong>Month <?= (int)$month ?></strong><span class="topics"><?= detail_escape(implode(', ', $monthTopics)) ?></span></div>
                <?php endforeach; ?>
            </section>
            <?php endif; ?>

            <?php if ($topics): ?>
            <section class="section"><h2>Topics Included</h2><ol class="topic-list">
                <?php foreach ($topics as $topicName): ?><li><?= detail_escape($topicName) ?></li><?php endforeach; ?>
            </ol></section>
            <?php endif; ?>
        </div>
    </article>
    <?php endif; ?>
</main>
</body>
</html>