<?php
session_start();
include '../database_connection/db_connect.php';

// Check if student is logged in
if (!isset($_SESSION['enrollment_id'], $_SESSION['student_table'])) {
    header("Location: login-system/login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];
$table = $_SESSION['student_table']; // e.g., students

// Fetch student data
$stmt = $conn->prepare("SELECT * FROM $table WHERE enrollment_id = ?");
$stmt->bind_param("s", $enrollment_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-blue-600 text-white p-4 flex justify-between items-center">
            <h1 class="text-xl font-bold">Student Dashboard</h1>
            <a href="logout.php" class="bg-red-500 px-4 py-2 rounded hover:bg-red-600 transition">Logout</a>
        </header>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <div class="max-w-4xl mx-auto bg-white shadow rounded-lg p-6">
                <div class="flex items-center space-x-6">
                    <img src="<?php echo $student['photo'] ?: 'default_avatar.png'; ?>" 
                         alt="Profile Photo" 
                         class="w-24 h-24 rounded-full object-cover border-2 border-blue-500">
                    <div>
                        <h2 class="text-2xl font-bold"><?php echo $student['name']; ?></h2>
                        <p class="text-gray-600">Enrollment ID: <?php echo $student['enrollment_id']; ?></p>
                        <p class="text-gray-600">Course: <?php echo $student['course']; ?></p>
                        <p class="text-gray-600">Contact: <?php echo $student['contact_number']; ?></p>
                        <p class="text-gray-600">Address: <?php echo $student['address']; ?></p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex space-x-4">
                    <a href="edit_profile.php" 
                       class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition">
                       Edit Profile
                    </a>
                    <a href="student_fee.php" 
                       class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
                       View Fees
                    </a>
                    <a href="assignments.php" 
                       class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
                       Assignments
                    </a>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-200 text-gray-700 text-center p-4">
            &copy; <?php echo date("Y"); ?> Your Institute Name. All rights reserved.
        </footer>
    </div>
</body>
</html>
