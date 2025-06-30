<?php
include '../database_connection/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name']; 
    $fathers_name = $_POST['fathers_name'];
    $mothers_name = $_POST['mothers_name'];
    $course = $_POST['course'];
    $address = $_POST['address'];
    $phone_no = $_POST['phone_no'];
    $aadhar_number = $_POST['aadhar_number'];
    $abc_id = $_POST['abc_id'];
    $birthday = $_POST['birthday'];
    $password_plain = $_POST['password'];
    $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);


    // Photo upload (you can improve this later)
    $photo = $_FILES['photo']['name'];
    $target = "../uploads/" . basename($photo);
    move_uploaded_file($_FILES['photo']['tmp_name'], $target);

       $sql = "INSERT INTO my_student 
        (first_name, last_name, fathers_name, mothers_name, course, address, phone_no, aadhar_number, photo, abc_id, birthday, password)
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
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
<style>
    * {
        font-family: 'Poppins', sans-serif;
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    
    body {
        background-color: #f5f7fa;
        color: #333;
        line-height: 1.6;
        padding: 20px;
    }
    
    h2 {
        color: #2c3e50;
        text-align: center;
        margin-bottom: 30px;
        font-weight: 600;
    }
    
    form {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #2c3e50;
    }
    
    input[type="text"],
    input[type="password"],
    input[type="date"],
    input[type="file"],
    textarea,
    select {
        width: 100%;
        padding: 12px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
        transition: border-color 0.3s;
    }
    
    input[type="text"]:focus,
    input[type="password"]:focus,
    input[type="date"]:focus,
    textarea:focus,
    select:focus {
        border-color: #3498db;
        outline: none;
    }
    
    textarea {
        min-height: 100px;
        resize: vertical;
    }
    
    input[type="submit"] {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 14px 28px;
        font-size: 16px;
        border-radius: 4px;
        cursor: pointer;
        width: 100%;
        font-weight: 500;
        transition: background-color 0.3s;
    }
    
    input[type="submit"]:hover {
        background-color: #2980b9;
    }
    
    p {
        text-align: center;
        margin: 20px 0;
        padding: 10px;
        border-radius: 4px;
    }
    
    /* Responsive adjustments */
    @media (min-width: 600px) {
        form {
            padding: 40px;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .form-group {
            flex: 1 0 calc(50% - 20px);
            min-width: 0;
        }
    }
    
    @media (max-width: 599px) {
        body {
            padding: 10px;
        }
        
        form {
            padding: 20px;
        }
    }
</style>
</head>
<body>
    <h2>Add New Student</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <label>First Name:</label><br>
        <input type="text" name="first_name" required><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" required><br><br>

        <label>Father's Name:</label><br>
        <input type="text" name="fathers_name" required><br><br>

        <label>Mother's Name:</label><br>
        <input type="text" name="mothers_name" required><br><br>

        <label>Course:</label><br>
        <input type="text" name="course" required><br><br>

        <label>Address:</label><br>
        <textarea name="address" required></textarea><br><br>

        <label>Phone No:</label><br>
        <input type="text" name="phone_no" required><br><br>

        <label>Photo:</label><br>
        <input type="file" name="photo" required><br><br>

        <label>Aadhar Number:</label><br>
        <input type="text" name="aadhar_number" required><br><br>

        <label>ABC ID:</label><br>
        <input type="text" name="abc_id"><br><br>

        <label>Birthday:</label><br>
        <input type="date" name="birthday"><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <input type="submit" value="Add Student">
    </form>
</body>
</html>
