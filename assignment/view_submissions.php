<?php
include '../database_connection/db_connect.php';

$assignment_result = $conn->query("SELECT * FROM assignments ORDER BY created_at DESC");

$filter_assignment = $_GET['assignment_id'] ?? null;
$filter_condition = "";

if (!empty($filter_assignment)) {
    $filter_assignment = intval($filter_assignment);
    $filter_condition = "WHERE s.assignment_id = $filter_assignment";
}

$submission_query = "
    SELECT s.*, st.name AS student_name, st.enrollment_id, a.title AS assignment_title
    FROM assignment_submissions s
    JOIN students st ON s.student_id = st.student_id
    JOIN assignments a ON s.assignment_id = a.assignment_id
    $filter_condition
    ORDER BY s.submitted_at DESC
";
$submissions = $conn->query($submission_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assignment Submissions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f2f4f8;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: 600;
        }

        .filter-form {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .filter-form label {
            font-weight: 500;
        }

        .filter-form select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            overflow-x: auto;
        }

        table th, table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            text-align: left;
            font-size: 14px;
        }

        table th {
            background-color: #f7f9fc;
            font-weight: 600;
            color: #444;
        }

        table tr:hover {
            background-color: #f9f9f9;
        }

        .grade-link {
            background: #007bff;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .grade-link:hover {
            background: #0056b3;
        }

        a[href*="uploads/submissions"] {
            text-decoration: none;
            color: #007bff;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            table th, table td {
                padding: 10px;
                font-size: 13px;
            }

            .filter-form {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-form select {
                width: 100%;
            }
        }

        @media (max-width: 500px) {
            .container {
                padding: 20px;
            }

            h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>All Assignment Submissions</h2>

    <form method="GET" class="filter-form">
        <label for="assignment_id">Filter by Assignment:</label>
        <select name="assignment_id" id="assignment_id" onchange="this.form.submit()">
            <option value="">-- All Assignments --</option>
            <?php while ($a = $assignment_result->fetch_assoc()) { ?>
                <option value="<?= $a['assignment_id'] ?>" <?= ($a['assignment_id'] == $filter_assignment) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['title']) ?>
                </option>
            <?php } ?>
        </select>
    </form>

    <table>
        <thead>
            <tr>
                <th>Assignment</th>
                <th>Student</th>
                <th>Enrollment</th>
                <th>Text</th>
                <th>File</th>
                <th>Marks</th>
                <th>Submitted</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($submissions->num_rows > 0) {
            while ($s = $submissions->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($s['assignment_title']) ?></td>
                    <td><?= htmlspecialchars($s['student_name']) ?></td>
                    <td><?= htmlspecialchars($s['enrollment_id']) ?></td>
                    <td><?= !empty($s['submitted_text']) ? substr(htmlspecialchars($s['submitted_text']), 0, 30) . '...' : '-' ?></td>
                    <td>
                        <?php if ($s['submitted_file']) { ?>
                            <a href="../uploads/submissions/<?= $s['submitted_file'] ?>" target="_blank">📎 View</a>
                        <?php } else { echo "-"; } ?>
                    </td>
                    <td><?= is_null($s['marks_awarded']) ? "Not Graded" : $s['marks_awarded'] ?></td>
                    <td><?= date("d M Y, h:i A", strtotime($s['submitted_at'])) ?></td>
                    <td><a class="grade-link" href="grade_submission.php?id=<?= $s['submission_id'] ?>">Grade</a></td>
                </tr>
        <?php } } else { echo "<tr><td colspan='8'>No submissions found.</td></tr>"; } ?>
        </tbody>
    </table>
</div>

</body>
</html>
