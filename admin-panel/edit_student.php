<?php
include '../database_connection/db_connect.php';

// Check if ID exists and is valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_students.php?error=invalid_id");
    exit();
}

$id = (int)$_GET['id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    
    // Basic validation
    $errors = [];
    if (empty($name)) $errors[] = "Name is required";
    if (empty($contact)) $errors[] = "Contact number is required";
    if (empty($address)) $errors[] = "Address is required";
    
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE students SET name=?, contact_number=?, address=? WHERE student_id=?");
            $stmt->bind_param("sssi", $name, $contact, $address, $id);
            
            if ($stmt->execute()) {
                header("Location: manage_students.php?success=student_updated");
                exit();
            } else {
                $errors[] = "Error updating student: " . $conn->error;
            }
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch student data using prepared statement
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_students.php?error=student_not_found");
    exit();
}

$row = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    :root {
        --primary-color: #4361ee;
        --primary-hover: #3a56d4;
        --error-color: #ef233c;
        --success-color: #4cc9f0;
        --text-color: #2b2d42;
        --light-gray: #f8f9fa;
        --border-color: #e0e0e0;
    }
    
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    
    body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--light-gray);
        color: var(--text-color);
        line-height: 1.6;
        padding: 20px;
    }
    
    .container {
        max-width: 600px;
        margin: 30px auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 30px;
    }
    
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
    }
    
    h2 {
        color: var(--text-color);
        font-weight: 600;
        font-size: 24px;
    }
    
    .back-btn {
        display: inline-flex;
        align-items: center;
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .back-btn i {
        margin-right: 8px;
    }
    
    .back-btn:hover {
        color: var(--primary-hover);
        text-decoration: none;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        font-size: 14px;
    }
    
    .input-field {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        transition: all 0.3s;
    }
    
    .input-field:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        outline: none;
    }
    
    textarea.input-field {
        min-height: 120px;
        resize: vertical;
    }
    
    .btn {
        display: inline-block;
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
        width: 100%;
    }
    
    .btn:hover {
        background-color: var(--primary-hover);
        transform: translateY(-2px);
    }
    
    .alert {
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    
    .alert-error {
        background-color: rgba(239, 35, 60, 0.1);
        color: var(--error-color);
        border-left: 4px solid var(--error-color);
    }
    
    .alert-success {
        background-color: rgba(76, 201, 240, 0.1);
        color: var(--success-color);
        border-left: 4px solid var(--success-color);
    }
    
    .photo-preview {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #f0f0f0;
        margin: 0 auto 20px;
        display: block;
    }
    
    @media (max-width: 768px) {
        .container {
            padding: 20px;
        }
        
        h2 {
            font-size: 20px;
        }
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Edit Student</h2>
            <a href="manage_students.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Students
            </a>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Error:</strong>
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <strong>Success!</strong> Student information has been updated.
            </div>
        <?php endif; ?>
        
        <?php if (!empty($row['photo'])): ?>
            <img src="uploads/<?= htmlspecialchars($row['photo']) ?>" class="photo-preview" alt="Student Photo">
        <?php endif; ?>
        
        <form method="POST" id="studentForm">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="input-field" 
                       value="<?= htmlspecialchars($row['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="contact">Contact Number</label>
                <input type="text" id="contact" name="contact" class="input-field" 
                       value="<?= htmlspecialchars($row['contact_number']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="input-field" required><?= 
                    htmlspecialchars($row['address']) ?></textarea>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-save"></i> Update Student
            </button>
        </form>
    </div>
    
    <script>
        // Client-side validation
        document.getElementById('studentForm').addEventListener('submit', function(e) {
            let valid = true;
            const name = document.getElementById('name').value.trim();
            const contact = document.getElementById('contact').value.trim();
            const address = document.getElementById('address').value.trim();
            
            if (!name) {
                alert('Please enter the student name');
                valid = false;
            }
            
            if (!contact) {
                alert('Please enter the contact number');
                valid = false;
            }
            
            if (!address) {
                alert('Please enter the address');
                valid = false;
            }
            
            if (!valid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>