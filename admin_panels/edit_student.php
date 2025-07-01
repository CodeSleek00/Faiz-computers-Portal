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
</head>
<body>
    <h2>Edit Student Info</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>First Name:</label><br>
        <input type="text" name="first_name" value="<?= $row['first_name'] ?>" required><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" value="<?= $row['last_name'] ?>" required><br><br>

        <label>Father's Name:</label><br>
        <input type="text" name="fathers_name" value="<?= $row['fathers_name'] ?>" required><br><br>

        <label>Mother's Name:</label><br>
        <input type="text" name="mothers_name" value="<?= $row['mothers_name'] ?>" required><br><br>

        <label>Course:</label><br>
        <input type="text" name="course" value="<?= $row['course'] ?>" required><br><br>

        <label>Address:</label><br>
        <textarea name="address" required><?= $row['address'] ?></textarea><br><br>

        <label>Phone No:</label><br>
        <input type="text" name="phone_no" value="<?= $row['phone_no'] ?>" required><br><br>

        <label>Aadhar Number:</label><br>
        <input type="text" name="aadhar_number" value="<?= $row['aadhar_number'] ?>" required><br><br>

        <label>ABC ID:</label><br>
        <input type="text" name="abc_id" value="<?= $row['abc_id'] ?>"><br><br>

        <label>Birthday:</label><br>
        <input type="date" name="birthday" value="<?= $row['birthday'] ?>"><br><br>

        <label>Change Photo (optional):</label><br>
        <input type="file" name="photo"><br>
        <img src="../uploads/<?= $row['photo'] ?>" width="80"><br><br>

        <label>Change Password:</label><br>
        <input type="password" name="password" required><br><br>

        <input type="submit" value="Update Student">
    </form>
</body>
</html>
