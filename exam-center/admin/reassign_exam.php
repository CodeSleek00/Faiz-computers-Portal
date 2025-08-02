<?php
require '../../database_connection/db_connect.php';

// Validate and sanitize exam_id
$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
if ($exam_id <= 0) {
    die("Invalid exam ID");
}

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch students and batches
$students = $conn->query("SELECT * FROM students ORDER BY name ASC");
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['assign_type'] ?? '';
    $assignments_made = 0;

    try {
        // First remove all existing assignments for this exam
        $conn->query("DELETE FROM exam_assignments WHERE exam_id = $exam_id");

        if ($type == 'student' && !empty($_POST['student_ids'])) {
            $stmt = $conn->prepare("INSERT INTO exam_assignments (exam_id, student_id) VALUES (?, ?)");
            foreach ($_POST['student_ids'] as $student_id) {
                $student_id = intval($student_id);
                if ($student_id > 0) {
                    $stmt->bind_param("ii", $exam_id, $student_id);
                    $stmt->execute();
                    $assignments_made++;
                }
            }
        } 
        elseif ($type == 'batch' && !empty($_POST['batch_ids'])) {
            $stmt = $conn->prepare("INSERT INTO exam_assignments (exam_id, batch_id) VALUES (?, ?)");
            foreach ($_POST['batch_ids'] as $batch_id) {
                $batch_id = intval($batch_id);
                if ($batch_id > 0) {
                    $stmt->bind_param("ii", $exam_id, $batch_id);
                    $stmt->execute();
                    $assignments_made++;
                }
            }
        } 
        elseif ($type == 'all') {
            $result = $conn->query("INSERT INTO exam_assignments (exam_id, student_id) 
                                  SELECT $exam_id, student_id FROM students");
            $assignments_made = $conn->affected_rows;
        }

        $message = "Successfully reassigned exam to $assignments_made recipients";
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Re-Assign Exam</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
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
            font-size: 16px;
        }
        .btn:hover {
            background: #4338ca;
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
            padding: 5px;
            border-radius: 4px;
        }
        #studentList div:hover, #batchList div:hover {
            background: #e0e7ff;
        }
        .message {
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
        .error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .select-all {
            margin-bottom: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Re-Assign Exam</h2>
        
        <?php if (!empty($message)): ?>
            <div class="<?= strpos($message, 'Error') !== false ? 'error' : 'message' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Select Assignment Type:</label>
            <select name="assign_type" id="assignType" onchange="toggleSection(this.value)" required>
                <option value="">--Select--</option>
                <option value="student">Specific Students</option>
                <option value="batch">Batch</option>
                <option value="all">All Students</option>
            </select>

            <!-- Students Section -->
            <div id="students" style="display:none;">
                <label>Search Students:</label>
                <input type="text" id="studentSearch" placeholder="Type to search..." onkeyup="filterList('studentSearch', 'studentList')">

                <div class="select-all">
                    <input type="checkbox" id="selectAllStudents" onclick="toggleAllCheckboxes('studentList', this.checked)">
                    <label for="selectAllStudents">Select All Students</label>
                </div>

                <div id="studentList">
                    <?php while ($s = $students->fetch_assoc()): ?>
                        <div>
                            <input type="checkbox" name="student_ids[]" value="<?= htmlspecialchars($s['student_id']) ?>"> 
                            <?= htmlspecialchars($s['name']) ?> (ID: <?= htmlspecialchars($s['student_id']) ?>)
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Batches Section -->
            <div id="batches" style="display:none;">
                <label>Search Batches:</label>
                <input type="text" id="batchSearch" placeholder="Type to search..." onkeyup="filterList('batchSearch', 'batchList')">

                <div class="select-all">
                    <input type="checkbox" id="selectAllBatches" onclick="toggleAllCheckboxes('batchList', this.checked)">
                    <label for="selectAllBatches">Select All Batches</label>
                </div>

                <div id="batchList">
                    <?php mysqli_data_seek($batches, 0); while ($b = $batches->fetch_assoc()): ?>
                        <div>
                            <input type="checkbox" name="batch_ids[]" value="<?= htmlspecialchars($b['batch_id']) ?>"> 
                            <?= htmlspecialchars($b['batch_name']) ?> (ID: <?= htmlspecialchars($b['batch_id']) ?>)
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <button type="submit" class="btn">✅ Re-Assign Exam</button>
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
                const text = items[i].textContent.toLowerCase();
                items[i].style.display = text.includes(input) ? '' : 'none';
            }
        }

        function toggleAllCheckboxes(containerId, checked) {
            const container = document.getElementById(containerId);
            const checkboxes = container.querySelectorAll('input[type="checkbox"]');
            
            checkboxes.forEach(checkbox => {
                if (!checkbox.id.includes('selectAll')) {
                    checkbox.checked = checked;
                }
            });
        }

        // Initialize form based on any previously selected type
        document.addEventListener('DOMContentLoaded', function() {
            const assignType = document.getElementById('assignType');
            if (assignType.value) {
                toggleSection(assignType.value);
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>