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
    <title>All Submissions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f3f4f6;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        h2 {
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 25px;
            color: #222;
        }

        .filter-form {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
        }

        .filter-form select {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
            width: 100%;
            max-width: 300px;
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            text-align: left;
            font-size: 14px;
        }

        th {
            background-color: #f9fbfd;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f6f6f6;
        }

        .grade-link {
            background: #007bff;
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            display: inline-block;
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
            body {
                padding: 10px;
            }

            .container {
                padding: 15px;
            }

            h2 {
                font-size: 20px;
            }

            th, td {
                font-size: 13px;
                padding: 10px;
            }

            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-form select {
                width: 100%;
            }

            table {
                min-width: 600px;
            }
        }

        @media (max-width: 480px) {
            th, td {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>All Assignment Submissions</h2>

    <form method="GET" class="filter-form">
        <label for="assignment_id" style="display:none;">Assignment Filter</label>
        <select name="assignment_id" id="assignment_id" onchange="this.form.submit()">
            <option value="">-- All Assignments --</option>
            <?php while ($a = $assignment_result->fetch_assoc()) { ?>
                <option value="<?= $a['assignment_id'] ?>" <?= ($a['assignment_id'] == $filter_assignment) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['title']) ?>
                </option>
            <?php } ?>
        </select>
    </form>

    <div class="table-responsive">
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
            <?php if ($submissions->num_rows > 0): ?>
                <?php while ($s = $submissions->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['assignment_title']) ?></td>
                        <td><?= htmlspecialchars($s['student_name']) ?></td>
                        <td><?= htmlspecialchars($s['enrollment_id']) ?></td>
                        <td><?= !empty($s['submitted_text']) ? substr(htmlspecialchars($s['submitted_text']), 0, 30) . '...' : '-' ?></td>
                        <td>
                            <?php if ($s['submitted_file']): ?>
                                <a href="../uploads/submissions/<?= $s['submitted_file'] ?>" target="_blank">📎 View</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= is_null($s['marks_awarded']) ? "Not Graded" : $s['marks_awarded'] ?></td>
                        <td><?= date("d M Y, h:i A", strtotime($s['submitted_at'])) ?></td>
                        <td><a class="grade-link" href="grade_submission.php?id=<?= $s['submission_id'] ?>">Grade</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align: center;">No submissions found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
