<?php
// db_connect.php should contain your DB connection logic like:
// $conn = new mysqli("localhost", "root", "", "your_database");
include 'db_connect.php';

// Function to generate unique enrollment ID
function generateEnrollmentID($conn) {
    $month = strtoupper(date("F")); // e.g., JULY
    $prefix = "FAIZ-$month-";

    $query = "SELECT enrollment_id FROM students WHERE enrollment_id LIKE '$prefix%' ORDER BY student_id DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        $lastId = intval(substr($row['enrollment_id'], strrpos($row['enrollment_id'], '-') + 1));
        $nextId = $lastId + 1;
    } else {
        $nextId = 1001;
    }

    return $prefix . $nextId;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Photo upload
    $photo = $_FILES['photo']['name'];
    $tmp = $_FILES['photo']['tmp_name'];
    $upload_dir = "../uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $target = $upload_dir . basename($photo);
    move_uploaded_file($tmp, $target);

    // Generate Enrollment ID
    $enrollment_id = generateEnrollmentID($conn);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO students (photo, name, contact_number, address, enrollment_id, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $photo, $name, $contact, $address, $enrollment_id, $password);
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Student added successfully!<br>Enrollment ID: <strong>$enrollment_id</strong></p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . $stmt->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Student</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 20px;
        }
        .form-container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        label {
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 6px 0 16px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add New Student</h2>
        <form action="add_student.php" method="POST" enctype="multipart/form-data">
            <label>Photo:</label>
            <input type="file" name="photo" accept="image/*" required>

            <label>Name:</label>
            <input type="text" name="name" required>

            <label>Contact Number:</label>
            <input type="text" name="contact" required>

            <label>Address:</label>
            <textarea name="address" required></textarea>

            <label>Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Add Student</button>
        </form>
    </div>
</body>
</html>
