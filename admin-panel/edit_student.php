<?php
include '../database_connection/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p style='color:red;'>❌ Invalid student ID.</p>";
    exit;
}

$id = intval($_GET['id']);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $newPassword = $_POST['password'];

    // Update query with or without password
    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE students SET name=?, contact_number=?, address=?, password=? WHERE student_id=?");
        $stmt->bind_param("ssssi", $name, $contact, $address, $hashedPassword, $id);
    } else {
        $stmt = $conn->prepare("UPDATE students SET name=?, contact_number=?, address=? WHERE student_id=?");
        $stmt->bind_param("sssi", $name, $contact, $address, $id);
    }

    if ($stmt->execute()) {
        echo "<p style='color:green;'>✅ Student updated successfully.</p>";
        header("Location: manage_student.php");
        exit;
    } else {
        echo "<p style='color:red;'>❌ Error: " . $stmt->error . "</p>";
    }
}

// Fetch existing data
$result = $conn->query("SELECT * FROM students WHERE student_id = $id");
if (!$result || $result->num_rows == 0) {
    echo "<p style='color:red;'>❌ Student not found.</p>";
    exit;
}
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f6f8;
            padding: 30px;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        label {
            font-weight: bold;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 12px;
            border: none;
            width: 100%;
            border-radius: 8px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .note {
            font-size: 13px;
            color: gray;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Student</h2>
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" required>

        <label>Contact:</label>
        <input type="text" name="contact" value="<?= htmlspecialchars($row['contact_number']) ?>" required>

        <label>Address:</label>
        <textarea name="address" required><?= htmlspecialchars($row['address']) ?></textarea>

        <label>Change Password (optional):</label>
        <input type="password" name="password" placeholder="Enter new password">

        <div class="note">Leave password blank if you don't want to change it.</div>

        <button type="submit">Update Student</button>
    </form>
</div>

</body>
</html>
