<?php
include '../../database_connection/db_connect.php';

// Fetch all exams
$exams = $conn->query("SELECT * FROM exams ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Exam Dashboard</title>
    <link rel="icon" type="image/png" href="image.png">
  <link rel="apple-touch-icon" href="image.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --light: #f8f9fa;
            --gray: #6c757d;
            --radius: 10px;
            --shadow: 0 6px 16px rgba(0,0,0,0.07);
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f3f4f6;
            padding: 30px 15px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 14px 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #f1f5f9;
            color: #333;
        }

        td a {
            text-decoration: none;
            font-weight: 500;
        }

        .btn {
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            margin-top: 6px;
            display: inline-block;
        }

        .btn.declare {
            background: var(--success);
            color: white;
        }

        .btn.clear {
            background: var(--danger);
            color: white;
        }

        .btn.view, .btn.delete {
            color: var(--primary);
        }

        .tag {
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 13px;
        }

        .tag.declared {
            background: #e6f4ea;
            color: #28a745;
        }

        .tag.undeclared {
            background: #fdecea;
            color: #dc3545;
        }

        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }

            thead {
                display: none;
            }

            tr {
                margin-bottom: 15px;
                background: #fafafa;
                padding: 12px;
                border-radius: var(--radius);
                box-shadow: var(--shadow);
            }

            td {
                padding: 10px;
                border: none;
                position: relative;
            }

            td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #555;
                display: block;
                margin-bottom: 4px;
            }

            .btn {
                margin-right: 10px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📋 All Exams</h2>

    <table>
        <thead>
            <tr>
                <th>Exam Name</th>
                <th>Questions</th>
                <th>Duration</th>
                <th>Created On</th>
                <th>Result</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($exam = $exams->fetch_assoc()) { ?>
            <tr>
                <td data-label="Exam"><?= htmlspecialchars($exam['exam_name']) ?></td>
                <td data-label="Questions"><?= $exam['total_questions'] ?></td>
                <td data-label="Duration"><?= $exam['duration'] ?> min</td>
                <td data-label="Created"><?= date("d M Y", strtotime($exam['created_at'])) ?></td>
                <td data-label="Result">
                    <?php if ($exam['result_declared']) { ?>
                        <span class="tag declared">✅ Declared</span><br>
                        <a href="undeclare_result.php?exam_id=<?= $exam['exam_id'] ?>" class="btn clear" onclick="return confirm('Remove result declaration?')">Clear</a>
                    <?php } else { ?>
                        <span class="tag undeclared">❌ Not Declared</span><br>
                        <a href="declare_result.php?exam_id=<?= $exam['exam_id'] ?>" class="btn declare">Declare Now</a>
                    <?php } ?>
                </td>
                <td data-label="Actions">
                    <a class="btn view" href="view_results_admin.php?exam_id=<?= $exam['exam_id'] ?>">📊 View</a><br>
                    <a class="btn delete" href="delete_exam.php?exam_id=<?= $exam['exam_id'] ?>" onclick="return confirm('Are you sure?')">🗑 Delete</a>
                    <a class="btn" href="reassign_exam.php?exam_id=<?= $exam['exam_id'] ?>" style="background:#ffc107; color:#000;">♻ Re-Assign</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
