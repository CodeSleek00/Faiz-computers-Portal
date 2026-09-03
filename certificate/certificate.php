<?php

require_once __DIR__ . '/config.php';

/*
|--------------------------------------------------------------------------
| GET CERTIFICATE ID
|--------------------------------------------------------------------------
*/

$id = trim((string)($_GET['id'] ?? ''));

if ($id === '') {
    http_response_code(400);
    exit('Certificate ID is required.');
}


/*
|--------------------------------------------------------------------------
| FETCH CERTIFICATE
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM certificates
    WHERE certificate_id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$c = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$c) {
    http_response_code(404);
    exit('Certificate not found.');
}


/*
|--------------------------------------------------------------------------
| HTML ESCAPE
|--------------------------------------------------------------------------
*/

function h($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| CERTIFICATE DATA
|--------------------------------------------------------------------------
*/

$certificateId = trim(
    (string)($c['certificate_id'] ?? '')
);

$studentName = strtoupper(
    trim((string)($c['student_name'] ?? ''))
);

$courseName = strtoupper(
    trim((string)($c['course_name'] ?? ''))
);

$enrollmentNo = strtoupper(
    trim((string)($c['enrollment_no'] ?? ''))
);


/*
|--------------------------------------------------------------------------
| INSTITUTE
|--------------------------------------------------------------------------
*/

$instituteName =
    'FAIZ COMPUTER INSTITUTE , DIST:-LUCKNOW (UP)';


/*
|--------------------------------------------------------------------------
| ISSUE DATE
|--------------------------------------------------------------------------
*/

$issueDate = '';

if (!empty($c['issue_date'])) {

    $timestamp = strtotime($c['issue_date']);

    if ($timestamp !== false) {

        $issueDate = date(
            'd/m/Y',
            $timestamp
        );
    }
}


/*
|--------------------------------------------------------------------------
| VERIFY URL
|--------------------------------------------------------------------------
*/

$verifyUrl =
    rtrim($siteUrl, '/') .
    '/verify.php?id=' .
    rawurlencode($certificateId);


/*
|--------------------------------------------------------------------------
| QR CODE
|--------------------------------------------------------------------------
*/

$qrUrl =
    'https://quickchart.io/qr?size=300&margin=1&text=' .
    rawurlencode($verifyUrl);


/*
|--------------------------------------------------------------------------
| STUDENT PHOTO
|--------------------------------------------------------------------------
*/

$photoUrl = '';

if (!empty($c['photo'])) {

    $photoUrl =
        rtrim($siteUrl, '/') .
        '/' .
        ltrim($c['photo'], '/');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    <?= h($certificateId) ?> - Certificate
</title>


<style>

/* =========================================================
   RESET
========================================================= */

* {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    padding: 0;
}


/* =========================================================
   BODY
========================================================= */

body {

    background: #e5e7eb;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    min-height: 100vh;

    display: flex;

    justify-content: center;

    align-items: flex-start;
}


/* =========================================================
   TOOLBAR
========================================================= */

.toolbar {

    position: fixed;

    top: 15px;
    right: 15px;

    z-index: 99999;

    display: flex;

    gap: 10px;

    font-family: Arial, sans-serif;
}


.toolbar button {

    border: none;

    border-radius: 8px;

    padding: 11px 18px;

    background: #4338ca;

    color: #ffffff;

    font-size: 14px;

    font-weight: 700;

    cursor: pointer;

    box-shadow:
        0 4px 12px rgba(0,0,0,.20);
}


.toolbar button:hover {

    background: #312e81;
}


/* =========================================================
   CERTIFICATE CONTAINER
========================================================= */

.certificate-wrapper {

    position: relative;

    width: 866px;

    height: 1202px;

    margin: 20px auto;

    background: #ffffff;

    overflow: hidden;

    box-shadow:
        0 8px 30px rgba(0,0,0,.18);
}


/* =========================================================
   BACKGROUND TEMPLATE

   IMPORTANT:
    template1.png should contain ONLY the background,
   logos, watermark and signature/seal artwork.
========================================================= */

.certificate-background {

    position: absolute;

    left: 0;
    top: 0;

    width: 866px;
    height: 1202px;

    display: block;

    z-index: 1;

    user-select: none;

    pointer-events: none;
}


/* =========================================================
   COMMON TEXT
========================================================= */

.text {

    position: absolute;

    z-index: 10;

    left: 20px;

    width: 826px;

    text-align: center;

    font-family:
        "XB Niloofar",
        "Niloofar",
        Georgia,
        "Times New Roman",
        serif;

    letter-spacing: .2px;

    color: #000000;
}


/* =========================================================
   FOUNDATION NAME
========================================================= */

.foundation-name {

    top: 160px;

    font-family:
        "ITC Benguiat",
        "Benguiat",
        Georgia,
        "Times New Roman",
        serif;

    font-size: 50px;

    line-height: 1;

    font-weight: 700;

    color: #c9a227;

    letter-spacing: .8px;

    text-shadow:
        1px 1px 1px rgba(0,0,0,.25);
}


/* =========================================================
   NGO DESCRIPTION
========================================================= */

.ngo-description {

    top: 217px;

    font-size: 20px;

    line-height: 30px;

    font-weight: 400;

    padding: 0 45px;
}


/* =========================================================
   CERTIFICATE OF COMPLETION
========================================================= */

.certificate-title {

    top: 352px;

    font-family:
        "ITC Benguiat",
        "Benguiat",
        Georgia,
        "Times New Roman",
        serif;

    font-size: 40px;

    line-height: 1;

    font-weight: 400;

    color: #c9a227;

    letter-spacing: .6px;

    text-shadow:
        1px 1px 1px rgba(0,0,0,.20);
}


/* =========================================================
   STUDENT PHOTO
========================================================= */

.student-photo-box {

    position: absolute;

    z-index: 12;

    left: 349px;

    top: 419px;

    width: 168px;

    height: 194px;

    background: #ffffff;

    border: 4px solid #06010f;

    overflow: hidden;
}


.student-photo {

    width: 100%;

    height: 100%;

    display: block;

    object-fit: cover;

    object-position: center;
}


/* =========================================================
   PRESENTED TO
========================================================= */

.presented-text {

    top: 625px;

    font-size: 25px;

    line-height: 1.1;

    font-weight: 400;

    white-space: nowrap;
}


/* =========================================================
   STUDENT NAME
========================================================= */

.student-name {

    top: 664px;

    font-family:
        "ITC Benguiat",
        "Benguiat",
        Georgia,
        "Times New Roman",
        serif;

    font-size: 40px;

    line-height: 1;

    font-weight: 700;

    letter-spacing: .7px;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


/* =========================================================
   COMPLETION LINE 1
========================================================= */

.completion-line-1 {

    top: 723px;

    font-size: 23px;

    line-height: 1.1;

    font-weight: 400;
}


/* =========================================================
   COMPLETION LINE 2
========================================================= */

.completion-line-2 {

    top: 754px;

    font-size: 23px;

    line-height: 1.1;

    font-weight: 400;
}


/* =========================================================
   COURSE NAME
========================================================= */

.course-name {

    top: 790px;

    font-size: 23px;

    line-height: 1.1;

    font-weight: 700;

    color: #10076f;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;

    padding: 0 25px;
}

.certificate-description {

    position: absolute;

    z-index: 10;

    left: 90px;

    top: 825px;

    width: 686px;

    height: 46px;

    overflow: hidden;

    text-align: center;

    font-family:
        "XB Niloofar",
        "Niloofar",
        Georgia,
        "Times New Roman",
        serif;

    font-size: 17px;

    line-height: 23px;

    color: #333333;

    display: -webkit-box;

    -webkit-box-orient: vertical;

    line-clamp: 2;

    -webkit-line-clamp: 2;
}


/* =========================================================
   ENROLLMENT NUMBER
========================================================= */

.enrollment-number {

    top: 878px;

    font-size: 25px;

    line-height: 1.1;

    font-weight: 700;

    white-space: nowrap;
}


/* =========================================================
   INSTITUTE
========================================================= */

.institute-name {

    top: 916px;

    font-size: 18px;

    line-height: 1.1;

    font-weight: 700;

    color: #10076f;

    white-space: nowrap;
}


/* =========================================================
   ISSUE DATE
========================================================= */

.issue-date {

    top: 968px;

    font-size: 23px;

    line-height: 1.1;

    font-weight: 700;
}


/* =========================================================
   QR CODE
========================================================= */

.qr-wrapper {

    position: absolute;

    z-index: 15;

    left: 639px;

    top: 1001px;

    width: 134px;

    height: 134px;

    background: #ffffff;

    padding: 3px;
}


.qr-code {

    display: block;

    width: 128px;

    height: 128px;

    object-fit: contain;
}


/* =========================================================
   CENTER DIRECTOR
========================================================= */

.director-line {

    position: absolute;

    z-index: 20;

    left: 265px;

    top: 1097px;

    width: 330px;

    text-align: center;

    font-family:
        "ITC Benguiat",
        "Benguiat",
        Georgia,
        "Times New Roman",
        serif;

    font-size: 23px;

    font-weight: 400;

    letter-spacing: .6px;

    color: #c9a227;

    border-top: 1px solid #555;

    padding-top: 9px;
}


/* =========================================================
   VERIFICATION MESSAGE
========================================================= */

.verify-message {

    position: absolute;

    z-index: 20;

    left: 40px;

    top: 1158px;

    width: 786px;

    text-align: center;

    font-family:
        "XB Niloofar",
        "Niloofar",
        Georgia,
        "Times New Roman",
        serif;

    font-size: 16px;

    line-height: 1.1;

    letter-spacing: .2px;

    color: #00389f;
}


/* =========================================================
   CERTIFICATE ID
========================================================= */

.certificate-id {

    position: absolute;

    z-index: 20;

    left: 15px;

    bottom: 4px;

    font-family: Arial, sans-serif;

    font-size: 8px;

    color: #777777;

    opacity: .8;
}


/* =========================================================
   MOBILE / SCREEN RESPONSIVE
========================================================= */

@media screen and (max-width: 900px) {

    body {

        display: block;

        overflow-x: hidden;

    }

    .certificate-wrapper {

        transform-origin: top left;

        transform:
            scale(
                calc((100vw - 20px) / 866)
            );

        margin-left: 10px;

        margin-top: 15px;

        margin-bottom:
            calc(
                -1202px +
                (
                    1202px *
                    ((100vw - 20px) / 866)
                )
            );
    }

}


/* =========================================================
   PRINT
========================================================= */

@media print {

    @page {

        size: 866px 1202px;

        margin: 0;

    }


    html,
    body {

        width: 866px;

        height: 1202px;

        margin: 0;

        padding: 0;

        background: #ffffff !important;

    }


    body {

        display: block;

    }


    .toolbar {

        display: none !important;

    }


    .certificate-wrapper {

        width: 866px;

        height: 1202px;

        margin: 0;

        box-shadow: none;

        transform: none !important;

    }


    .certificate-background {

        width: 866px;

        height: 1202px;

    }

}

</style>
<link rel="stylesheet" href="assets/certificate.css">

</head>


<body>


<!-- =====================================================
     PRINT BUTTON
===================================================== -->

<div class="toolbar">

    <button class="btn btn-primary" onclick="printCertificate()">
        Print / Save PDF
    </button>

</div>



<!-- =====================================================
     CERTIFICATE
===================================================== -->

<div class="certificate-wrapper">


    <!-- =================================================
         BACKGROUND ONLY
    ================================================== -->

    <img
        src="assets/template1.png"
        class="certificate-background"
        alt=""
    >



    <!-- =================================================
         FOUNDATION NAME
    ================================================== -->

    <div class="text foundation-name">

        CODE SLEEK FOUNDATION

    </div>



    <!-- =================================================
         NGO DESCRIPTION
    ================================================== -->

    <div class="text ngo-description">

        Code Sleek Foundation is a duly registered Non-Governmental<br>

        Organization (NGO/Society) registered under the Societies Registration<br>

        Act, 1860 in the State of Uttar Pradesh.

    </div>



    <!-- =================================================
         CERTIFICATE TITLE
    ================================================== -->

    <div class="text certificate-title">

        CERTIFICATE OF COMPLETION

    </div>



    <!-- =================================================
         STUDENT PHOTO
    ================================================== -->

    <?php if ($photoUrl): ?>

        <div class="student-photo-box">

            <img
                src="<?= h($photoUrl) ?>"
                class="student-photo"
                alt="Student Photo"
            >

        </div>

    <?php endif; ?>



    <!-- =================================================
         PRESENTATION TEXT
    ================================================== -->

    <div class="text presented-text">

        THIS CERTIFICATE IS PROUDLY PRESENTED TO

    </div>



    <!-- =================================================
         STUDENT NAME
    ================================================== -->

    <div class="text student-name">

        <?= h($studentName) ?>

    </div>



    <!-- =================================================
         COMPLETION TEXT
    ================================================== -->

    <div class="text completion-line-1">

        for successful completion of all required evaluation

    </div>


    <div class="text completion-line-2">

        process for the course

    </div>



    <!-- =================================================
         COURSE
    ================================================== -->

    <div class="text course-name">

        <?= h($courseName) ?>

    </div>

    <?php if (!empty($c['description'])): ?>

        <div class="certificate-description">

            <?= nl2br(h($c['description'])) ?>

        </div>

    <?php endif; ?>



    <!-- =================================================
         ENROLLMENT
    ================================================== -->

    <div class="text enrollment-number">

        ENROLLMENT NO. : <?= h($enrollmentNo) ?>

    </div>



    <!-- =================================================
         INSTITUTE
    ================================================== -->

    <div class="text institute-name">

        ASC NAME : <?= h($instituteName) ?>

    </div>



    <!-- =================================================
         ISSUE DATE
    ================================================== -->

    <div class="text issue-date">

        Date of Issue:- <?= h($issueDate) ?>

    </div>



    <!-- =================================================
         QR CODE
    ================================================== -->

    <div class="qr-wrapper">

        <img
            src="<?= h($qrUrl) ?>"
            class="qr-code"
            alt="Certificate Verification QR"
        >

    </div>



    <!-- =================================================
         DIRECTOR
    ================================================== -->

    <div class="director-line">

        CENTER DIRECTOR

    </div>



    <!-- =================================================
         VERIFICATION MESSAGE
    ================================================== -->

    <div class="verify-message">

        To verify your Marksheet / Certificates visit scan QR Code

    </div>



    <!-- =================================================
         CERTIFICATE ID
    ================================================== -->

    <div class="certificate-id">

        Certificate ID: <?= h($certificateId) ?>

    </div>


</div>



<script>

function printCertificate()
{
    window.print();
}

</script>


</body>

</html>