<?php
include 'db_connect.php';
$id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("UPDATE students SET name=?, contact_number=?, address=? WHERE student_id=?");
    $stmt->bind_param("sssi", $name, $contact, $address, $id);
    $stmt->execute();

    header("Location: manage_students.php");
    exit;
}

$result = $conn->query("SELECT * FROM students WHERE student_id = $id");
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head><title>Edit Student</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f5f7fa;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        color: #333;
    }
    
    .container {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        padding: 30px;
        width: 100%;
        max-width: 500px;
        margin: 20px;
    }
    
    h2 {
        color: #2c3e50;
        margin-top: 0;
        margin-bottom: 25px;
        font-weight: 600;
        text-align: center;
        font-size: 24px;
    }
    
    form {
        display: flex;
        flex-direction: column;
    }
    
    label {
        font-weight: 500;
        margin-bottom: 8px;
        font-size: 14px;
        color: #555;
    }
    
    input[type="text"],
    textarea {
        padding: 12px 15px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    input[type="text"]:focus,
    textarea:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        outline: none;
    }
    
    textarea {
        min-height: 100px;
        resize: vertical;
    }
    
    button[type="submit"] {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 12px;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s;
        margin-top: 10px;
    }
    
    button[type="submit"]:hover {
        background-color: #2980b9;
    }
    
    .back-link {
        display: inline-block;
        margin-top: 20px;
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
        transition: color 0.3s;
    }
    
    .back-link:hover {
        color: #2980b9;
        text-decoration: underline;
    }
    
    .back-link::before {
        content: "←";
        margin-right: 5px;
    }
    
    @media (max-width: 600px) {
        .container {
            padding: 20px;
            margin: 15px;
        }
        
        h2 {
            font-size: 20px;
            margin-bottom: 20px;
        }
    }
</style>
</head>
<body>
    <h2>Edit Student</h2>
    <form method="POST">
        <label>Name:</label><br>
        <input type="text" name="name" value="<?= $row['name'] ?>" required><br><br>

        <label>Contact:</label><br>
        <input type="text" name="contact" value="<?= $row['contact_number'] ?>" required><br><br>

        <label>Address:</label><br>
        <textarea name="address" required><?= $row['address'] ?></textarea><br><br>

        <button type="submit">Update</button>
    </form>
    <br><a href="manage_students.php">⬅ Back</a>
</body>
</html>
