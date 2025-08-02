<?php
session_start();
require '../../database_connection/db_connect.php';

// Check if user is logged in (add your own authentication logic)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Validate and sanitize exam_id
$exam_id = filter_input(INPUT_GET, 'exam_id', FILTER_VALIDATE_INT);
if (!$exam_id) {
    $_SESSION['error'] = "Invalid exam ID";
    header("Location: exam_dashboard.php");
    exit;
}

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch students and batches
$students = $conn->query("SELECT * FROM students ORDER BY name ASC");
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token";
        header("Location: re_assign_exam.php?exam_id=" . $exam_id);
        exit;
    }

    $type = $_POST['assign_type'] ?? '';
    $success = true;
    $assignments_made = 0;

    try {
        $conn->begin_transaction();

        if ($type == 'student' && !empty($_POST['student_ids'])) {
            $stmt = $conn->prepare("INSERT IGNORE INTO exam_assignments (exam_id, student_id) VALUES (?, ?)");
            foreach ($_POST['student_ids'] as $student_id) {
                $student_id = filter_var($student_id, FILTER_VALIDATE_INT);
                if ($student_id) {
                    $stmt->bind_param("ii", $exam_id, $student_id);
                    $stmt->execute();
                    $assignments_made += $stmt->affected_rows;
                }
            }
        } 
        elseif ($type == 'batch' && !empty($_POST['batch_ids'])) {
            $stmt = $conn->prepare("INSERT IGNORE INTO exam_assignments (exam_id, batch_id) VALUES (?, ?)");
            foreach ($_POST['batch_ids'] as $batch_id) {
                $batch_id = filter_var($batch_id, FILTER_VALIDATE_INT);
                if ($batch_id) {
                    $stmt->bind_param("ii", $exam_id, $batch_id);
                    $stmt->execute();
                    $assignments_made += $stmt->affected_rows;
                }
            }
        } 
        elseif ($type == 'all') {
            $all_students = $conn->query("SELECT student_id FROM students");
            $stmt = $conn->prepare("INSERT IGNORE INTO exam_assignments (exam_id, student_id) VALUES (?, ?)");
            while ($s = $all_students->fetch_assoc()) {
                $sid = filter_var($s['student_id'], FILTER_VALIDATE_INT);
                if ($sid) {
                    $stmt->bind_param("ii", $exam_id, $sid);
                    $stmt->execute();
                    $assignments_made += $stmt->affected_rows;
                }
            }
        } 
        else {
            $success = false;
            $_SESSION['error'] = "Invalid assignment type or no selection made";
        }

        if ($success) {
            $conn->commit();
            $_SESSION['success'] = "Successfully made $assignments_made new assignment(s)";
            header("Location: exam_dashboard.php");
            exit;
        } else {
            $conn->rollback();
            header("Location: re_assign_exam.php?exam_id=" . $exam_id);
            exit;
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: re_assign_exam.php?exam_id=" . $exam_id);
        exit;
    }
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
        .alert {
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
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
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
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
                if (checkbox.id !== 'selectAllStudents' && checkbox.id !== 'selectAllBatches') {
                    checkbox.checked = checked;
                }
            });
        }

        // Initialize the form based on any previously selected type
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