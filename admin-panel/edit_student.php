<?php
include '../database_connection/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p style='color:red;'>❌ Invalid student ID.</p>";
    exit;
}

$id = intval($_GET['id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $course = $_POST['course']; 
    $address = $_POST['address'];
    $newPassword = $_POST['password'];

    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE students SET name=?, contact_number=?, address=?, course=?, password=? WHERE student_id=?");
        $stmt->bind_param("sssssi", $name, $contact, $address, $course, $hashedPassword, $id);
    } else {
        $stmt = $conn->prepare("UPDATE students SET name=?, contact_number=?, address=?, course=? WHERE student_id=?");
        $stmt->bind_param("ssssi", $name, $contact, $address, $course, $id);
    }

    if ($stmt->execute()) {
        header("Location: manage_student.php");
        exit;
    } else {
        echo "<p style='color:red;'>❌ Error: " . $stmt->error . "</p>";
    }
}

$result = $conn->query("SELECT * FROM students WHERE student_id = $id");
if (!$result || $result->num_rows == 0) {
    echo "<p style='color:red;'>❌ Student not found.</p>";
    exit;
}
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        .form-container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }

        h2 {
            text-align: center;
            color: #222;
            font-size: 24px;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #333;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 15px;
            transition: 0.3s;
        }

        input:focus, textarea:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.2);
            outline: none;
        }

        button {
            width: 100%;
            background-color: #007bff;
            color: white;
            padding: 12px 16px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        .note {
            font-size: 13px;
            color: #777;
            margin-top: -15px;
            margin-bottom: 20px;
        }

        .student-photo {
            display: block;
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
            margin: 0 auto 25px auto;
            border: 3px solid #ddd;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .form-container {
                padding: 20px;
            }

            h2 {
                font-size: 20px;
            }

            input, textarea {
                font-size: 14px;
            }

            button {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Student</h2>

    <img class="student-photo" src="../uploads/<?= htmlspecialchars($row['photo']) ?>" alt="Student Photo">

    <form method="POST">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($row['name']) ?>" required>

        <label for="contact">Contact Number</label>
        <input type="text" id="contact" name="contact" value="<?= htmlspecialchars($row['contact_number']) ?>" required>

        <label for="address">Address</label>
        <textarea id="address" name="address" rows="3" required><?= htmlspecialchars($row['address']) ?></textarea>

        <label for="course">Course</label>
        <input type="text" id="course" name="course" value="<?= htmlspecialchars($row['course']) ?>" required>

        <label for="password">Change Password (optional)</label>
        <input type="password" id="password" name="password" placeholder="Enter new password">
        <div class="note">Leave blank if you do not wish to change the password.</div>

        <button type="submit">Update Student</button>
    </form>

    <a class="back-link" href="manage_student.php">⬅ Back to Student List</a>
</div>

</body>
</html>
