<?php

include '../database_connection/db_connect.php';


// ============================================================
// ASSIGNMENTS FOR FILTER
// ============================================================

$assignment_result = $conn->query("
    SELECT
        assignment_id,
        title
    FROM assignments
    ORDER BY created_at DESC
");


// ============================================================
// FILTER
// ============================================================

$filter_assignment = $_GET['assignment_id'] ?? '';

if (
    $filter_assignment !== '' &&
    !is_numeric($filter_assignment)
) {
    $filter_assignment = '';
}

if ($filter_assignment !== '') {
    $filter_assignment = (int)$filter_assignment;
}


// ============================================================
// SUBMISSIONS
// ONLY students26
// ============================================================

if ($filter_assignment !== '') {

    $sql = "
        SELECT
            s.submission_id,
            s.assignment_id,
            s.student_id,
            s.submitted_file,
            s.submitted_text,
            s.submitted_at,
            s.marks_awarded,
            s.feedback,
            s.status,

            st.name AS student_name,
            st.enrollment_id,

            a.title AS assignment_title,
            a.marks AS maximum_marks

        FROM assignment_submissions s

        INNER JOIN assignments a
            ON s.assignment_id = a.assignment_id

        INNER JOIN students26 st
            ON s.student_id = st.id

        WHERE s.assignment_id = ?

        ORDER BY s.submitted_at DESC
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die(
            "Prepare failed: " .
            htmlspecialchars($conn->error)
        );
    }

    $stmt->bind_param(
        "i",
        $filter_assignment
    );

    $stmt->execute();

    $submissions =
        $stmt->get_result();

} else {

    $sql = "
        SELECT
            s.submission_id,
            s.assignment_id,
            s.student_id,
            s.submitted_file,
            s.submitted_text,
            s.submitted_at,
            s.marks_awarded,
            s.feedback,
            s.status,

            st.name AS student_name,
            st.enrollment_id,

            a.title AS assignment_title,
            a.marks AS maximum_marks

        FROM assignment_submissions s

        INNER JOIN assignments a
            ON s.assignment_id = a.assignment_id

        INNER JOIN students26 st
            ON s.student_id = st.id

        ORDER BY s.submitted_at DESC
    ";

    $submissions =
        $conn->query($sql);

    if (!$submissions) {
        die(
            "Query failed: " .
            htmlspecialchars($conn->error)
        );
    }
}


// ============================================================
// HELPERS
// ============================================================

function h($value)
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


function submissionSource($row)
{
    /*
     * App submissions are identified by
     * files/text coming through the Flutter API.
     *
     * Website submissions can be identified
     * using the optional submission_source column
     * if you add it later.
     */

    if (
        isset($row['submission_source']) &&
        $row['submission_source'] !== ''
    ) {
        return strtolower(
            $row['submission_source']
        );
    }

    // Existing records:
    // treat submissions as App Upload by default
    return 'app';
}


function statusClass($status)
{
    $status =
        strtolower(
            trim(
                $status ?? 'submitted'
            )
        );

    if ($status === 'evaluated') {
        return 'evaluated';
    }

    if ($status === 'submitted') {
        return 'submitted';
    }

    return 'pending';
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<link rel="icon"
      type="image/png"
      href="image.png">

<title>Assignment Submissions</title>

<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
    rel="stylesheet"
>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    padding: 20px;

    background: #f4f6f9;

    color: #1f2937;

    font-family: 'Inter', sans-serif;
}


/* =========================================================
   CONTAINER
========================================================= */

.container {

    max-width: 1400px;

    margin: auto;

    background: #ffffff;

    border-radius: 16px;

    padding: 25px;

    box-shadow:
        0 4px 20px
        rgba(0,0,0,0.06);
}


/* =========================================================
   HEADER
========================================================= */

.header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    margin-bottom: 25px;

    flex-wrap: wrap;
}

.header h2 {

    margin: 0;

    font-size: 24px;

    font-weight: 700;

    color: #111827;
}

.header p {

    margin: 6px 0 0;

    color: #6b7280;

    font-size: 14px;
}


/* =========================================================
   FILTER
========================================================= */

.filter-box {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-bottom: 20px;

    flex-wrap: wrap;
}

.filter-box label {

    font-size: 14px;

    font-weight: 600;
}

.filter-box select {

    min-width: 280px;

    padding: 11px 14px;

    border: 1px solid #d1d5db;

    border-radius: 9px;

    background: white;

    font-size: 14px;

    outline: none;
}

.filter-box select:focus {

    border-color: #2563eb;
}


/* =========================================================
   SUMMARY
========================================================= */

.summary {

    display: flex;

    gap: 12px;

    margin-bottom: 20px;

    flex-wrap: wrap;
}

.summary-card {

    padding: 14px 18px;

    border-radius: 12px;

    background: #f8fafc;

    border: 1px solid #e5e7eb;
}

.summary-card .number {

    font-size: 22px;

    font-weight: 700;
}

.summary-card .label {

    font-size: 12px;

    color: #6b7280;

    margin-top: 3px;
}


/* =========================================================
   TABLE
========================================================= */

.table-responsive {

    width: 100%;

    overflow-x: auto;

    border: 1px solid #e5e7eb;

    border-radius: 12px;
}

table {

    width: 100%;

    min-width: 1100px;

    border-collapse: collapse;
}

th {

    background: #f8fafc;

    color: #374151;

    font-size: 12px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .3px;

    padding: 14px;

    text-align: left;

    border-bottom:
        1px solid #e5e7eb;
}

td {

    padding: 14px;

    border-bottom:
        1px solid #f0f0f0;

    font-size: 13px;

    vertical-align: middle;
}

tr:last-child td {

    border-bottom: none;
}

tr:hover td {

    background: #fafafa;
}


/* =========================================================
   STUDENT
========================================================= */

.student-name {

    font-weight: 600;

    color: #111827;
}

.enrollment {

    color: #6b7280;

    font-size: 12px;

    margin-top: 3px;
}


/* =========================================================
   SOURCE BADGES
========================================================= */

.source {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 700;
}

.source-app {

    background: #e8f1ff;

    color: #2563eb;
}

.source-web {

    background: #ecfdf3;

    color: #15803d;
}


/* =========================================================
   STATUS
========================================================= */

.status {

    display: inline-block;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 700;
}

.status-submitted {

    background: #eaf2ff;

    color: #2563eb;
}

.status-evaluated {

    background: #ecfdf3;

    color: #15803d;
}

.status-pending {

    background: #fff7ed;

    color: #c2410c;
}


/* =========================================================
   FILE
========================================================= */

.file-link {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    text-decoration: none;

    color: #2563eb;

    font-weight: 600;
}

.file-link:hover {

    text-decoration: underline;
}


/* =========================================================
   MARKS
========================================================= */

.marks {

    font-weight: 700;
}

.not-graded {

    color: #9ca3af;

    font-size: 12px;
}


/* =========================================================
   GRADE BUTTON
========================================================= */

.grade-link {

    display: inline-block;

    padding: 8px 13px;

    border-radius: 8px;

    background: #2563eb;

    color: white;

    text-decoration: none;

    font-size: 12px;

    font-weight: 600;
}

.grade-link:hover {

    background: #1d4ed8;
}


/* =========================================================
   EMPTY
========================================================= */

.empty {

    text-align: center;

    padding: 60px 20px;

    color: #6b7280;
}

.empty-icon {

    font-size: 40px;

    margin-bottom: 10px;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    body {
        padding: 10px;
    }

    .container {
        padding: 15px;
    }

    .header h2 {
        font-size: 20px;
    }

    .filter-box {
        align-items: stretch;
        flex-direction: column;
    }

    .filter-box select {
        width: 100%;
        min-width: 0;
    }
}

</style>

</head>

<body>

<div class="container">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="header">

        <div>

            <h2>
                Assignment Submissions
            </h2>

            <p>
                View submissions uploaded from the app and website.
            </p>

        </div>

    </div>


    <!-- =====================================================
         FILTER
    ====================================================== -->

    <form
        method="GET"
        class="filter-box"
    >

        <label
            for="assignment_id"
        >
            Assignment:
        </label>

        <select
            name="assignment_id"
            id="assignment_id"
            onchange="this.form.submit()"
        >

            <option value="">
                -- All Assignments --
            </option>

            <?php
            while (
                $a =
                $assignment_result
                    ->fetch_assoc()
            ):
            ?>

                <option
                    value="<?= h($a['assignment_id']) ?>"
                    <?= (
                        (string)$a['assignment_id']
                        ===
                        (string)$filter_assignment
                    )
                        ? 'selected'
                        : ''
                    ?>
                >

                    <?= h($a['title']) ?>

                </option>

            <?php endwhile; ?>

        </select>

    </form>


    <!-- =====================================================
         SUMMARY
    ====================================================== -->

    <?php

    $total =
        $submissions
            ->num_rows;

    ?>

    <div class="summary">

        <div class="summary-card">

            <div class="number">
                <?= $total ?>
            </div>

            <div class="label">
                Total Submissions
            </div>

        </div>

    </div>


    <!-- =====================================================
         TABLE
    ====================================================== -->

    <div class="table-responsive">

        <table>

            <thead>

                <tr>

                    <th>
                        Assignment
                    </th>

                    <th>
                        Student
                    </th>

                    <th>
                        Source
                    </th>

                    <th>
                        Text
                    </th>

                    <th>
                        File
                    </th>

                    <th>
                        Marks
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Submitted
                    </th>

                    <th>
                        Action
                    </th>

                </tr>

            </thead>


            <tbody>

            <?php if ($total > 0): ?>

                <?php
                while (
                    $s =
                    $submissions
                        ->fetch_assoc()
                ):
                ?>

                    <?php

                    $source =
                        submissionSource($s);

                    $status =
                        strtolower(
                            $s['status']
                            ?? 'submitted'
                        );

                    ?>

                    <tr>


                        <!-- ASSIGNMENT -->

                        <td>

                            <strong>
                                <?= h(
                                    $s[
                                        'assignment_title'
                                    ]
                                ) ?>
                            </strong>

                            <div
                                style="
                                margin-top:4px;
                                color:#9ca3af;
                                font-size:11px;
                                "
                            >
                                ID:
                                <?= h(
                                    $s[
                                        'assignment_id'
                                    ]
                                ) ?>
                            </div>

                        </td>


                        <!-- STUDENT -->

                        <td>

                            <div
                                class="student-name"
                            >
                                <?= h(
                                    $s[
                                        'student_name'
                                    ]
                                ) ?>
                            </div>

                            <div
                                class="enrollment"
                            >
                                <?= h(
                                    $s[
                                        'enrollment_id'
                                    ]
                                ) ?>
                            </div>

                        </td>


                        <!-- SOURCE -->

                        <td>

                            <?php if (
                                $source === 'website' ||
                                $source === 'web'
                            ): ?>

                                <span
                                    class="
                                    source
                                    source-web
                                    "
                                >
                                    🌐 Website Upload
                                </span>

                            <?php else: ?>

                                <span
                                    class="
                                    source
                                    source-app
                                    "
                                >
                                    📱 App Upload
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- TEXT -->

                        <td>

                            <?php

                            $text =
                                trim(
                                    $s[
                                        'submitted_text'
                                    ]
                                    ?? ''
                                );

                            ?>

                            <?php if (
                                $text !== ''
                            ): ?>

                                <?= h(
                                    mb_substr(
                                        $text,
                                        0,
                                        50
                                    )
                                ) ?>

                                <?php if (
                                    mb_strlen(
                                        $text
                                    ) > 50
                                ): ?>
                                    ...
                                <?php endif; ?>

                            <?php else: ?>

                                <span
                                    style="
                                    color:#9ca3af;
                                    "
                                >
                                    -
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- FILE -->

                        <td>

                            <?php

                            $file =
                                trim(
                                    $s[
                                        'submitted_file'
                                    ]
                                    ?? ''
                                );

                            ?>

                            <?php if (
                                $file !== ''
                            ): ?>

                                <?php

                                /*
                                 * API upload location
                                 *
                                 * /api/assignment/uploads/
                                 */

                                $fileUrl =
                                    '../' .
                                    ltrim(
                                        $file,
                                        '/'
                                    );

                                ?>

                                <a
                                    class="file-link"
                                    href="<?= h($fileUrl) ?>"
                                    target="_blank"
                                >
                                    📎 View File
                                </a>

                            <?php else: ?>

                                <span
                                    style="
                                    color:#9ca3af;
                                    "
                                >
                                    No file
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- MARKS -->

                        <td>

                            <?php if (
                                $s[
                                    'marks_awarded'
                                ] !== null &&
                                $s[
                                    'marks_awarded'
                                ] !== ''
                            ): ?>

                                <span
                                    class="marks"
                                >
                                    <?= h(
                                        $s[
                                            'marks_awarded'
                                        ]
                                    ) ?>

                                    /

                                    <?= h(
                                        $s[
                                            'maximum_marks'
                                        ]
                                    ) ?>

                                </span>

                            <?php else: ?>

                                <span
                                    class="
                                    not-graded
                                    "
                                >
                                    Not Graded
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- STATUS -->

                        <td>

                            <span
                                class="
                                status
                                status-<?= h(
                                    statusClass(
                                        $status
                                    )
                                )
                                ?>
                                "
                            >

                                <?= ucfirst(
                                    h($status)
                                ) ?>

                            </span>

                        </td>


                        <!-- DATE -->

                        <td>

                            <?php

                            if (
                                !empty(
                                    $s[
                                        'submitted_at'
                                    ]
                                )
                            ) {

                                echo date(
                                    "d M Y, h:i A",
                                    strtotime(
                                        $s[
                                            'submitted_at'
                                        ]
                                    )
                                );

                            } else {

                                echo '-';

                            }

                            ?>

                        </td>


                        <!-- ACTION -->

                        <td>

                            <a
                                class="grade-link"
                                href="
                                grade_submission.php?id=
                                <?= h(
                                    $s[
                                        'submission_id'
                                    ]
                                )
                                ?>
                                "
                            >
                                <?= (
                                    $s[
                                        'marks_awarded'
                                    ] !== null
                                )
                                    ? 'Edit Grade'
                                    : 'Grade'
                                ?>
                            </a>

                        </td>


                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="9"
                        class="empty"
                    >

                        <div
                            class="empty-icon"
                        >
                            📭
                        </div>

                        <strong>
                            No submissions found
                        </strong>

                        <div
                            style="
                            margin-top:6px;
                            "
                        >
                            Students' app submissions
                            will appear here.
                        </div>

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>