<?php
// db_connect.php should contain your DB connection logic like:
// $conn = new mysqli("localhost", "root", "", "your_database");
include '../database_connection/db_connect.php';

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
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f8;
            padding: 20px;
        }
        .form-container {
            position: relative;
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        label {
            font-weight: 500;
            display: block;
            margin-bottom: 5px;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            margin: 6px 0 16px 0;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }
        input:focus, textarea:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
            outline: none;
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
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-weight: 600;
        }
        
        /* Image preview styles */
        .image-preview-container {
            position: absolute;
            top: 30px;
            right: 30px;
            width: 100px;
            height: 120px;
            border: 1px dashed #ccc;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #f9f9f9;
        }
        .image-preview {
            max-width: 100%;
            max-height: 100%;
            display: none;
        }
        .image-preview-placeholder {
            color: #999;
            font-size: 12px;
            text-align: center;
            padding: 10px;
        }
        
        /* Password visibility toggle */
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 40px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            font-size: 14px;
        }
        .password-toggle:hover {
            color: #333;
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
    <script>
    // Image preview functionality
    const photoInput = document.querySelector('input[name="photo"]');
    const imagePreview = document.createElement('div');
    imagePreview.className = 'image-preview-container';
    imagePreview.innerHTML = '<div class="image-preview-placeholder">Image Preview</div><img class="image-preview">';
    document.querySelector('.form-container').prepend(imagePreview);
    
    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const previewImg = imagePreview.querySelector('.image-preview');
                previewImg.src = event.target.result;
                previewImg.style.display = 'block';
                imagePreview.querySelector('.image-preview-placeholder').style.display = 'none';
            }
            reader.readAsDataURL(file);
        }
    });
    
    // Password visibility toggle
    const passwordInput = document.querySelector('input[name="password"]');
    const passwordToggle = document.createElement('span');
    passwordToggle.className = 'password-toggle';
    passwordToggle.textContent = '👁️';
    passwordToggle.title = 'Show password';
    
    const passwordContainer = document.createElement('div');
    passwordContainer.className = 'password-container';
    passwordContainer.appendChild(passwordInput.cloneNode(true));
    passwordInput.replaceWith(passwordContainer);
    passwordContainer.querySelector('input').type = 'password';
    passwordContainer.appendChild(passwordToggle);
    
    passwordToggle.addEventListener('click', function() {
        const input = passwordContainer.querySelector('input');
        if (input.type === 'password') {
            input.type = 'text';
            passwordToggle.textContent = '👁️‍🗨️';
        } else {
            input.type = 'password';
            passwordToggle.textContent = '👁️';
        }
    });
</script>
</body>
</html>
