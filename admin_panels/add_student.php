<?php
include '../database_connection/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $fathers_name = $_POST['fathers_name'];
    $mothers_name = $_POST['mothers_name'];
    $course = $_POST['course'];
    $address = $_POST['address'];
    $phone_no = $_POST['phone_no'];
    $abc_id = $_POST['abc_id'];
    $birthday = $_POST['birthday'];

    // Photo upload (you can improve this later)
    $photo = $_FILES['photo']['name'];
    $target = "uploads/" . basename($photo);
    move_uploaded_file($_FILES['photo']['tmp_name'], $target);

    $sql = "INSERT INTO my_student (first_name, fathers_name, mothers_name, course, address, phone_no, photo, abc_id, birthday)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $first_name, $fathers_name, $mothers_name, $course, $address, $phone_no, $photo, $abc_id, $birthday);

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
</head>
<body>
    <h2>Add New Student</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <label>First Name:</label><br>
        <input type="text" name="first_name" required><br><br>

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

        <label>ABC ID:</label><br>
        <input type="text" name="abc_id"><br><br>

        <label>Birthday:</label><br>
        <input type="date" name="birthday"><br><br>

        <input type="submit" value="Add Student">
    </form>
</body>
</html>
