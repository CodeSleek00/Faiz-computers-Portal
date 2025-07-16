<?php
include '../database_connection/db_connect.php';

function generateEnrollmentID($conn) {
    $month = strtoupper(date("F"));
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $course = $_POST['course'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $photo = $_FILES['photo']['name'];
    $tmp = $_FILES['photo']['tmp_name'];
    $upload_dir = "../uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    $target = $upload_dir . basename($photo);
    move_uploaded_file($tmp, $target);

    $enrollment_id = generateEnrollmentID($conn);

    $stmt = $conn->prepare("INSERT INTO students (photo, name, contact_number, address, course, enrollment_id, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $photo, $name, $contact, $address, $course, $enrollment_id, $password);

    if ($stmt->execute()) {
        echo "<p style='color: green; text-align:center;'>✅ Student added! <br>Enrollment ID: <strong>$enrollment_id</strong></p>";
    } else {
        echo "<p style='color: red; text-align:center;'>❌ Error: " . $stmt->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <link rel="icon" type="image/png" href="image.png">
  <link rel="apple-touch-icon" href="image.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        body {
            margin: 0;
            background: #f5f7fa;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .card {
            background: white;
            max-width: 700px;
            width: 100%;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.06);
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }
        form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }
        label {
            font-weight: 500;
            margin-bottom: 6px;
            color: #333;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }
        textarea {
            resize: vertical;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #4A6CF7;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #2f4fe4;
        }

        /* Grid for larger screens */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .image-preview-box {
            width: 100%;
            max-width: 180px;
            height: 180px;
            margin: 0 auto 10px;
            border-radius: 10px;
            border: 2px dashed #ccc;
            background: #fafafa;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .image-preview-box img {
            max-width: 100%;
            max-height: 100%;
            display: none;
        }
        .image-placeholder {
            font-size: 13px;
            color: #999;
            text-align: center;
            padding: 10px;
        }

        .password-wrapper {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 14px;
            color: #888;
        }

        @media (max-width: 600px) {
            .card {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Add New Student</h2>
        <form action="add_student.php" method="POST" enctype="multipart/form-data">
            <div>
                <label for="photo">Photo</label>
                <div class="image-preview-box">
                    <img id="imagePreview">
                    <div class="image-placeholder" id="imagePlaceholder">Image Preview</div>
                </div>
                <input type="file" name="photo" accept="image/*" required onchange="previewImage(event)">
            </div>

            <div class="form-grid">
                <div>
                    <label for="name">Name</label>
                    <input type="text" name="name" required>
                </div>
                <div>
                    <label for="contact">Contact Number</label>
                    <input type="text" name="contact" required>
                </div>
                <div>
                    <label for="address">Address</label>
                    <textarea name="address" rows="3" required></textarea>
                </div>
                <div>
                    <label for="course">Course</label>
                    <input type="text" name="course" required>
                </div>
                <div class="password-wrapper">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="passwordField" required>
                    <span class="toggle-password" onclick="togglePassword()">👁️</span>
                </div>
            </div>

            <button type="submit">Add Student</button>
        </form>
    </div>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('imagePreview');
            const placeholder = document.getElementById('imagePlaceholder');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        }

        function togglePassword() {
            const field = document.getElementById("passwordField");
            const icon = document.querySelector('.toggle-password');
            if (field.type === "password") {
                field.type = "text";
                icon.textContent = "🙈";
            } else {
                field.type = "password";
                icon.textContent = "👁️";
            }
        }
    </script>
</body>
</html>
