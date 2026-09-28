<?php
session_start();
require_once dirname(__DIR__) . '/database_connection/db_connect.php';

if (!isset($_SESSION['enrollment_id'], $_SESSION['student_table'], $_SESSION['student_id']) || $_SESSION['student_table'] !== 'students26') {
    header('Location: ../login-system/login.php');
    exit;
}

$studentId = (int)$_SESSION['student_id'];
$enrollmentId = (string)$_SESSION['enrollment_id'];
$contentId = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare('SELECT name FROM students26 WHERE id=? AND enrollment_id=? LIMIT 1');
$stmt->bind_param('is', $studentId, $enrollmentId);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student || $contentId <= 0) {
    http_response_code(404);
    exit('Video not found.');
}

$stmt = $conn->prepare("
    SELECT sc.title, sc.description, sc.video_url, t.topic_name, c.course_name
    FROM study_content_targets sct
    JOIN study_contents sc ON sc.id=sct.content_id
    JOIN study_topics t ON t.id=sc.topic_id
    JOIN study_courses c ON c.id=t.course_id
    LEFT JOIN student_batches sb
        ON sct.target_type='batch'
       AND sb.batch_id=sct.target_id
       AND sb.student_table='students26'
       AND sb.student_id=?
    WHERE sc.id=?
      AND sc.content_type='video'
      AND sc.status='active'
      AND sct.status='active'
      AND ((sct.target_type='student' AND sct.target_id=?) OR sb.student_id IS NOT NULL)
    LIMIT 1
");
$stmt->bind_param('iii', $studentId, $contentId, $studentId);
$stmt->execute();
$video = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$video || empty($video['video_url'])) {
    http_response_code(404);
    exit('Video not found.');
}

$videoPrefix = 'uploads/videos/';
$storedPath = str_replace('\\', '/', (string)$video['video_url']);
if (strpos($storedPath, $videoPrefix) !== 0) {
    http_response_code(404);
    exit('Video not found.');
}

$filename = substr($storedPath, strlen($videoPrefix));
$videoRoot = realpath(__DIR__ . '/uploads/videos');
$videoPath = $videoRoot ? realpath($videoRoot . DIRECTORY_SEPARATOR . $filename) : false;
if (!$videoRoot || !$videoPath || basename($filename) !== $filename || strpos($videoPath, $videoRoot . DIRECTORY_SEPARATOR) !== 0 || !is_file($videoPath)) {
    http_response_code(404);
    exit('Video file not found.');
}

$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$mimeTypes = [
    'mp4' => 'video/mp4',
    'm4v' => 'video/mp4',
    'webm' => 'video/webm',
    'mov' => 'video/quicktime',
];
$mimeType = $mimeTypes[$extension] ?? (new finfo(FILEINFO_MIME_TYPE))->file($videoPath);
if (!$mimeType || strpos($mimeType, 'video/') !== 0) {
    $mimeType = 'application/octet-stream';
}

if (isset($_GET['stream'])) {
    $fileSize = filesize($videoPath);
    $start = 0;
    $end = $fileSize - 1;
    $range = $_SERVER['HTTP_RANGE'] ?? '';

    if ($range !== '') {
        if (!preg_match('/^bytes=(\d*)-(\d*)$/', $range, $matches) || ($matches[1] === '' && $matches[2] === '')) {
            http_response_code(416);
            header('Content-Range: bytes */' . $fileSize);
            exit;
        }
        if ($matches[1] === '') {
            $suffixLength = (int)$matches[2];
            $start = max(0, $fileSize - $suffixLength);
        } else {
            $start = (int)$matches[1];
            if ($matches[2] !== '') {
                $end = min((int)$matches[2], $end);
            }
        }
        if ($start > $end || $start >= $fileSize) {
            http_response_code(416);
            header('Content-Range: bytes */' . $fileSize);
            exit;
        }
        http_response_code(206);
        header("Content-Range: bytes $start-$end/$fileSize");
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: inline; filename="' . basename($filename) . '"');
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . ($end - $start + 1));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store');

    $file = fopen($videoPath, 'rb');
    if ($file === false) {
        http_response_code(500);
        exit;
    }
    fseek($file, $start);
    $remaining = $end - $start + 1;
    while ($remaining > 0 && !feof($file)) {
        $chunk = fread($file, min(8192, $remaining));
        if ($chunk === false || $chunk === '') {
            break;
        }
        echo $chunk;
        $remaining -= strlen($chunk);
        flush();
    }
    fclose($file);
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?> | Study Video</title>
    <style>
        :root{--ink:#18252f;--muted:#65747d;--paper:#f3f6f4;--white:#fff;--line:#dce5e1;--green:#176b57}
        *{box-sizing:border-box}
        body{margin:0;background:var(--paper);color:var(--ink);font-family:Arial,Helvetica,sans-serif;line-height:1.5}
        main{max-width:1000px;margin:auto;padding:24px}
        a{color:var(--green);text-decoration:none;font-weight:700}
        .crumb{font-size:13px;color:var(--muted);margin:20px 0 4px}
        h1{font-size:25px;line-height:1.25;margin:0 0 8px}
        p{color:var(--muted);margin:0 0 18px}
        .player{background:#101719;border-radius:8px;overflow:hidden;border:1px solid #263335}
        video{display:block;width:100%;max-height:72vh;background:#000}
        @media(max-width:600px){main{padding:16px}h1{font-size:21px}}
    </style>
</head>
<body>
<main>
    <a href="student_dashboard.php">&larr; Back to Study Dashboard</a>
    <div class="crumb"><?= htmlspecialchars($video['course_name'] . ' / ' . $video['topic_name'], ENT_QUOTES, 'UTF-8') ?></div>
    <h1><?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?></h1>
    <?php if ($video['description'] !== ''): ?><p><?= nl2br(htmlspecialchars($video['description'], ENT_QUOTES, 'UTF-8')) ?></p><?php endif; ?>
    <div class="player">
        <video controls playsinline preload="metadata" controlslist="nodownload">
            <source src="student_video.php?id=<?= $contentId ?>&amp;stream=1" type="<?= htmlspecialchars($mimeType, ENT_QUOTES, 'UTF-8') ?>">
            Your browser cannot play this video format.
        </video>
    </div>
</main>
</body>
</html>