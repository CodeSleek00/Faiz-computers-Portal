<?php
include '../database_connection/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name']; 
    $course = $_POST['course'];
    $address = $_POST['address'];
    $phone_no = $_POST['phone_no'];
    $password_plain = $_POST['password'];
    $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

    $photo = $_FILES['photo']['name'];
    $target = "../uploads/" . basename($photo);
    move_uploaded_file($_FILES['photo']['tmp_name'], $target);

    $sql = "INSERT INTO my_student 
        (first_name, last_name, course, address, phone_no, photo, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssss", $first_name, $last_name, $fathers_name, $mothers_name, $course, $address, $phone_no, $aadhar_number, $photo, $abc_id, $birthday, $password_hashed);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Student added successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student - Faiz Computer Institute</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: #eef3f8;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: 500;
            margin-bottom: 6px;
            color: #333;
        }

        input[type="text"],
        input[type="password"],
        input[type="date"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 10px 14px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 0.95rem;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        input[type="submit"] {
            background: #3498db;
            color: white;
            padding: 12px;
            font-size: 1rem;
            font-weight: 500;
            border: none;
            border-radius: 6px;
            width: 100%;
            cursor: pointer;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .col {
            flex: 1;
            min-width: 280px;
        }

        .photo-preview {
            position: absolute;
            top: 30px;
            right: 30px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #3498db;
        }

        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @media (max-width: 600px) {
            .photo-preview {
                position: static;
                margin: 20px auto;
                display: flex;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Add New Student</h2>

    <div class="photo-preview">
        <img id="previewImage" src="https://via.placeholder.com/100" alt="Photo Preview">
    </div>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>First Name:</label>
                    <input type="text" name="first_name" required>
                </div>

                <div class="form-group">
                    <label>Course:</label>
                    <input type="text" name="course" required>
                </div>

                <div class="form-group">
                    <label>Phone No:</label>
                    <input type="text" name="phone_no" required>
                </div>

                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
            </div>

            <div class="col">
                <div class="form-group">
                    <label>Last Name:</label>
                    <input type="text" name="last_name" required>
                </div>

                <div class="form-group">
                    <label>Address:</label>
                    <textarea name="address" required></textarea>
                </div>


                <div class="form-group">
                    <label>Photo:</label>
                    <input type="file" name="photo" accept="image/*" onchange="previewPhoto(event)" required>
                </div>
            </div>
        </div>

        <input type="submit" value="Add Student">
    </form>
</div>

<script>
    function previewPhoto(event) {
        const reader = new FileReader();
        reader.onload = function () {
            const output = document.getElementById('previewImage');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

</body>
</html>
