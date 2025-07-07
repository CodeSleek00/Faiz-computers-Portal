<?php
include '../database_connection/db_connect.php';

// Fetch all assignments
$assignment_result = $conn->query("SELECT * FROM assignments ORDER BY created_at DESC");

// Check if filtering by assignment
$filter_assignment = $_GET['assignment_id'] ?? null;
$filter_condition = "";

if (!empty($filter_assignment)) {
    $filter_assignment = intval($filter_assignment);
    $filter_condition = "WHERE s.assignment_id = $filter_assignment";
}

// Fetch all submissions with student and assignment info
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
<html>
<head>
    <title>All Submissions</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f0f3f8; padding: 40px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 16px; box-shadow: 0 5px 18px rgba(0,0,0,0.06); }
        h2 { text-align: center; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th, table td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        table th { background: #f9fbfd; font-weight: 600; }
        a.grade-link {
            background: #007bff; color: white;
            padding: 6px 10px; border-radius: 6px;
            text-decoration: none; font-size: 14px;
        }
        .filter-form {
            margin-bottom: 20px;
        }
        .filter-form select {
            padding: 10px; border-radius: 8px; font-size: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>All Assignment Submissions</h2>

    <form method="GET" class="filter-form">
        <label>Filter by Assignment:</label>
        <select name="assignment_id" onchange="this.form.submit()">
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
                <th>Enrollment ID</th>
                <th>Submitted Text</th>
                <th>File</th>
                <th>Marks</th>
                <th>Submitted At</th>
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
