<?php
include '../database_connection/db_connect.php';

$id = $_GET['id'];

// Fetch current data
$result = $conn->query("SELECT * FROM my_student WHERE student_id = $id");
if (!$result || $result->num_rows == 0) {
    die("Student not found.");
}
$row = $result->fetch_assoc();

// Handle form submission
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

    // Check if new photo uploaded
    if (!empty($_FILES['photo']['name'])) {
        $photo = $_FILES['photo']['name'];
        $target = "../uploads/" . basename($photo);
        move_uploaded_file($_FILES['photo']['tmp_name'], $target);
    } else {
        $photo = $row['photo']; // keep old photo
    }

    $sql = "UPDATE my_student SET 
        first_name=?, last_name=?, fathers_name=?, mothers_name=?, course=?, 
        address=?, phone_no=?, aadhar_number=?, photo=?, abc_id=?, birthday=?, password=?
        WHERE student_id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssi", $first_name, $last_name, $fathers_name, $mothers_name, $course,
        $address, $phone_no, $aadhar_number, $photo, $abc_id, $birthday, $password_hashed, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Student updated successfully!'); window.location.href='student_detail.php?id=$id';</script>";
    } else {
        echo "<p style='color:red;'>Update failed: " . $stmt->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f7fa;
        margin: 0;
        padding: 0;
    }

    h2 {
        text-align: center;
        color: #2c3e50;
        margin-top: 30px;
    }

    form {
        max-width: 800px;
        margin: 30px auto;
        background-color: #ffffff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    label {
        display: block;
        font-weight: 600;
        margin-bottom: 6px;
        color: #333;
    }

    input[type="text"],
    input[type="date"],
    input[type="password"],
    input[type="file"],
    textarea {
        width: 100%;
        padding: 10px 12px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 16px;
        box-sizing: border-box;
        transition: border 0.3s ease;
    }

    input:focus,
    textarea:focus {
        outline: none;
        border-color: #4a90e2;
    }

    textarea {
        min-height: 80px;
        resize: vertical;
    }

    img {
        margin-top: 10px;
        border-radius: 6px;
        border: 1px solid #ddd;
    }

    input[type="submit"] {
        background-color: #4a90e2;
        color: white;
        padding: 12px 25px;
        font-size: 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    input[type="submit"]:hover {
        background-color: #357ab8;
    }

    @media (max-width: 600px) {
        form {
            padding: 20px;
        }
    }
</style>

</head>
<body>
    <h2>Edit Student Info</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>First Name:</label><br>
        <input type="text" name="first_name" value="<?= $row['first_name'] ?>" required><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" value="<?= $row['last_name'] ?>" required><br><br>

        <label>Course:</label><br>
        <input type="text" name="course" value="<?= $row['course'] ?>" required><br><br>

        <label>Address:</label><br>
        <textarea name="address" required><?= $row['address'] ?></textarea><br><br>

        <label>Phone No:</label><br>
        <input type="text" name="phone_no" value="<?= $row['phone_no'] ?>" required><br><br>

        <label>Change Photo (optional):</label><br>
        <input type="file" name="photo"><br>
        <img src="../uploads/<?= $row['photo'] ?>" width="80"><br><br>

        <label>Change Password:</label><br>
        <input type="password" name="password" required><br><br>

        <input type="submit" value="Update Student">
    </form>
</body>
</html>
