<?php
include '../../database_connection/db_connect.php';

$exam_id = $_GET['exam_id'];
$students = $conn->query("SELECT * FROM students ORDER BY name ASC");
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['assign_type'];

    if ($type == 'student' && isset($_POST['student_ids'])) {
        foreach ($_POST['student_ids'] as $student_id) {
            $conn->query("INSERT INTO exam_assignments (exam_id, student_id) VALUES ('$exam_id', '$student_id')");
        }
    } elseif ($type == 'batch' && isset($_POST['batch_ids'])) {
        foreach ($_POST['batch_ids'] as $batch_id) {
            $conn->query("INSERT INTO exam_assignments (exam_id, batch_id) VALUES ('$exam_id', '$batch_id')");
        }
    } elseif ($type == 'all') {
        $all_students = $conn->query("SELECT student_id FROM students");
        while ($s = $all_students->fetch_assoc()) {
            $sid = $s['student_id'];
            $conn->query("INSERT INTO exam_assignments (exam_id, student_id) VALUES ('$exam_id', '$sid')");
        }
    }

    echo "<script>alert('Exam reassigned successfully'); location.href='exam_dashboard.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Re-Assign Exam</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding: 20px;
            background: #f3f4f6;
        }
        .form-box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            max-width: 750px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #4f46e5;
            margin-bottom: 20px;
        }
        label {
            font-weight: 600;
            display: block;
            margin-top: 20px;
        }
        select, input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .btn {
            margin-top: 25px;
            padding: 10px 20px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        #studentList, #batchList {
            max-height: 250px;
            overflow-y: auto;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #f9f9f9;
        }
        #studentList div, #batchList div {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Re-Assign Exam</h2>
        <form method="POST">
            <label>Select Assignment Type:</label>
            <select name="assign_type" onchange="toggleSection(this.value)" required>
                <option value="">--Select--</option>
                <option value="student">Specific Students</option>
                <option value="batch">Batch</option>
                <option value="all">All Students</option>
            </select>

            <!-- Students Section -->
            <div id="students" style="display:none;">
                <label>Search Students:</label>
                <input type="text" id="studentSearch" placeholder="Type to search..." onkeyup="filterList('studentSearch', 'studentList')">

                <div id="studentList">
                    <?php while ($s = $students->fetch_assoc()) { ?>
                        <div>
                            <input type="checkbox" name="student_ids[]" value="<?= $s['student_id'] ?>"> <?= htmlspecialchars($s['name']) ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Batches Section -->
            <div id="batches" style="display:none;">
                <label>Search Batches:</label>
                <input type="text" id="batchSearch" placeholder="Type to search..." onkeyup="filterList('batchSearch', 'batchList')">

                <div id="batchList">
                    <?php mysqli_data_seek($batches, 0); while ($b = $batches->fetch_assoc()) { ?>
                        <div>
                            <input type="checkbox" name="batch_ids[]" value="<?= $b['batch_id'] ?>"> <?= htmlspecialchars($b['batch_name']) ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <button type="submit" class="btn">✅ Re-Assign</button>
        </form>
    </div>

    <script>
        function toggleSection(type) {
            document.getElementById('students').style.display = (type === 'student') ? 'block' : 'none';
            document.getElementById('batches').style.display = (type === 'batch') ? 'block' : 'none';
        }

        function filterList(searchInputId, listContainerId) {
            const input = document.getElementById(searchInputId).value.toLowerCase();
            const items = document.getElementById(listContainerId).getElementsByTagName('div');

            for (let i = 0; i < items.length; i++) {
                const label = items[i].innerText.toLowerCase();
                items[i].style.display = label.includes(input) ? '' : 'none';
            }
        }
    </script>
</body>
</html>
