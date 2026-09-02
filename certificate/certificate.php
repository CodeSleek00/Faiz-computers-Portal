<?php
require_once __DIR__ . '/config.php';

$id = trim((string)($_GET['id'] ?? ''));

$stmt = $pdo->prepare("
    SELECT *
    FROM certificates
    WHERE certificate_id = ?
    LIMIT 1
");
$stmt->execute([$id]);

$c = $stmt->fetch();

if (!$c) {
    http_response_code(404);
    exit('Certificate not found.');
}

function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/*
|--------------------------------------------------------------------------
| URLs
|--------------------------------------------------------------------------
*/

$verifyUrl =
    $siteUrl .
    '/verify.php?id=' .
    rawurlencode($c['certificate_id']);

$qrUrl =
    'https://quickchart.io/qr?size=300&margin=1&text=' .
    rawurlencode($verifyUrl);

$photoUrl = '';

if (!empty($c['photo'])) {
    $photoUrl =
        $siteUrl .
        '/' .
        ltrim($c['photo'], '/');
}

$displayDate = date(
    'd/m/Y',
    strtotime($c['issue_date'])
);

/*
|--------------------------------------------------------------------------
| SVG Helpers
|--------------------------------------------------------------------------
*/

$studentName = strtoupper(trim($c['student_name'] ?? ''));
$courseName  = strtoupper(trim($c['course_name'] ?? ''));
$enrollment  = strtoupper(trim($c['enrollment_no'] ?? ''));
$certificateId = $c['certificate_id'] ?? '';

/*
|--------------------------------------------------------------------------
| Optional values
|--------------------------------------------------------------------------
*/

$instituteName = 'FAIZ COMPUTER INSTITUTE , DIST:-LUCKNOW (UP)';

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title><?= h($certificateId) ?> - Certificate</title>

<style>

/* -------------------------------------------------------
   PAGE
------------------------------------------------------- */

html,
body {
    margin: 0;
    padding: 0;
    width: 100%;
    min-height: 100%;
}

body {
    background: #e5e7eb;
    font-family: Georgia, "Times New Roman", serif;
}

/* -------------------------------------------------------
   TOOLBAR
------------------------------------------------------- */

.toolbar {
    position: fixed;
    top: 15px;
    right: 15px;
    z-index: 9999;

    display: flex;
    gap: 10px;

    font-family: Arial, sans-serif;
}

.toolbar button {
    border: 0;
    border-radius: 8px;

    padding: 11px 18px;

    background: #4338ca;
    color: white;

    font-size: 14px;
    font-weight: 700;

    cursor: pointer;
}

.toolbar button:hover {
    background: #312e81;
}

/* -------------------------------------------------------
   CERTIFICATE WRAPPER
------------------------------------------------------- */

.certificate-wrapper {

    width: 848px;
    height: 1224px;

    margin: 30px auto;

    background: white;

    box-shadow:
        0 8px 30px rgba(0,0,0,.18);
}

/* -------------------------------------------------------
   SVG
------------------------------------------------------- */

.certificate-svg {

    display: block;

    width: 848px;
    height: 1224px;

    overflow: hidden;
}

/* -------------------------------------------------------
   PRINT
------------------------------------------------------- */

@media print {

    @page {
        size: 848px 1224px;
        margin: 0;
    }

    html,
    body {
        width: 848px;
        height: 1224px;
        margin: 0;
        padding: 0;

        background: white !important;
    }

    .toolbar {
        display: none !important;
    }

    .certificate-wrapper {

        width: 848px;
        height: 1224px;

        margin: 0;

        box-shadow: none;
    }

    .certificate-svg {

        width: 848px;
        height: 1224px;
    }
}

/* -------------------------------------------------------
   MOBILE VIEW
------------------------------------------------------- */

@media screen and (max-width: 880px) {

    body {
        overflow-x: hidden;
    }

    .certificate-wrapper {

        transform-origin: top left;

        transform:
            scale(
                calc((100vw - 20px) / 848)
            );

        margin-left: 10px;
        margin-top: 15px;

        margin-bottom:
            calc(
                -1224px +
                (1224px * ((100vw - 20px) / 848))
            );
    }
}

</style>

</head>

<body>

<!-- =====================================================
     TOOLBAR
===================================================== -->

<div class="toolbar">

    <button onclick="printCertificate()">
        Print / Save PDF
    </button>

</div>


<!-- =====================================================
     CERTIFICATE
===================================================== -->

<div class="certificate-wrapper">

<svg
    class="certificate-svg"
    xmlns="http://www.w3.org/2000/svg"

    xmlns:xlink="http://www.w3.org/1999/xlink"

    width="848"
    height="1224"

    viewBox="0 0 848 1224"

    preserveAspectRatio="none"
>


<!-- =====================================================
     BACKGROUND TEMPLATE
===================================================== -->

<image
    href="assets/template.png"

    x="0"
    y="0"

    width="848"
    height="1224"

    preserveAspectRatio="none"
/>



<!-- =====================================================
     WHITE MASKS
     
     These hide dynamic/sample text from the template.
===================================================== -->

<rect
    x="40"
    y="665"
    width="768"
    height="70"

    fill="#ffffff"
    opacity="0.97"
/>


<rect
    x="30"
    y="795"
    width="788"
    height="55"

    fill="#ffffff"
    opacity="0.97"
/>


<rect
    x="30"
    y="860"
    width="788"
    height="48"

    fill="#ffffff"
    opacity="0.97"
/>


<rect
    x="30"
    y="910"
    width="788"
    height="45"

    fill="#ffffff"
    opacity="0.97"
/>


<rect
    x="150"
    y="970"
    width="548"
    height="60"

    fill="#ffffff"
    opacity="0.97"
/>


<!-- =====================================================
     STUDENT PHOTO AREA
===================================================== -->

<rect
    x="340"
    y="425"
    width="168"
    height="190"

    fill="white"
/>

<rect
    x="343"
    y="428"
    width="162"
    height="185"

    fill="none"

    stroke="#111111"
    stroke-width="2"
/>


<?php if ($photoUrl): ?>

<image

    href="<?= h($photoUrl) ?>"

    x="345"
    y="430"

    width="158"
    height="181"

    preserveAspectRatio="xMidYMid slice"

/>

<?php endif; ?>


<!-- =====================================================
     STUDENT NAME
===================================================== -->

<text

    x="424"
    y="708"

    text-anchor="middle"

    font-family="Georgia, Times New Roman, serif"

    font-size="40"

    font-weight="700"

    fill="#000000"

>

<?= h($studentName) ?>

</text>



<!-- =====================================================
     PRESENTATION LINE
===================================================== -->

<text

    x="424"
    y="758"

    text-anchor="middle"

    font-family="Georgia, Times New Roman, serif"

    font-size="27"

    fill="#000000"

>

THIS CERTIFICATE IS PROUDLY PRESENTED TO

</text>



<!-- =====================================================
     STUDENT NAME SECOND / HIGHLIGHT
===================================================== -->

<text

    x="424"
    y="805"

    text-anchor="middle"

    font-family="Georgia, Times New Roman, serif"

    font-size="39"

    font-weight="700"

    fill="#000000"

>

<?= h($studentName) ?>

</text>



<!-- =====================================================
     COMPLETION TEXT
===================================================== -->

<text

    x="424"
    y="855"

    text-anchor="middle"

    font-family="Georgia, Times New Roman, serif"

    font-size="23"

    fill="#000000"

>

for successful completion of all required evaluation

</text>


<text

    x="424"
    y="889"

    text-anchor="middle"

    font-family="Georgia, Times New Roman, serif"

    font-size="23"

    fill="#000000"

>

process for the course

</text>



<!-- =====================================================
     COURSE
===================================================== -->

<text

    x="424"
    y="928"

    text-anchor="middle"

    font-family="Georgia, Times New Roman, serif"

    font-size="24"

    font-weight="700"

    fill="#0d086d"

>

<?= h($courseName) ?>

</text>



<!-- =====================================================
     ENROLLMENT
===================================================== -->

<text

    x="424"
    y="978"

    text-anchor="middle"

    font-family="Georgia, Times New Roman, serif"

    font-size="26"

    font-weight="700"

    fill="#000000"

>

ENROLLMENT NO. : <?= h($enrollment) ?>

</text>



<!-- =====================================================
     INSTITUTE
===================================================== -->

<text

    x="424"
    y="1015"

    text-anchor="middle"

    font-family="Georgia, Times New Roman, serif"

    font-size="18"

    font-weight="700"

    fill="#0d086d"

>

ASC NAME : <?= h($instituteName) ?>

</text>



<!-- =====================================================
     ISSUE DATE
===================================================== -->

<text

    x="424"
    y="1060"

    text-anchor="middle"

    font-family="Georgia, Times New Roman, serif"

    font-size="23"

    font-weight="700"

    fill="#000000"

>

Date of Issue:- <?= h($displayDate) ?>

</text>



<!-- =====================================================
     QR CODE
===================================================== -->

<rect

    x="628"
    y="1005"

    width="135"
    height="135"

    fill="#ffffff"

/>


<image

    href="<?= h($qrUrl) ?>"

    x="632"
    y="1009"

    width="127"
    height="127"

    preserveAspectRatio="none"

/>



<!-- =====================================================
     CERTIFICATE ID
===================================================== -->

<text

    x="40"
    y="1195"

    font-family="Arial, sans-serif"

    font-size="10"

    fill="#666666"

>

Certificate ID: <?= h($certificateId) ?>

</text>


</svg>

</div>



<script>

function printCertificate() {

    window.print();

}

</script>

</body>

</html>