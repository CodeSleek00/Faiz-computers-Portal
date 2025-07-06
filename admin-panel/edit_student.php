<?php
include '../database_connection/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p style='color:red;'>❌ Invalid student ID.</p>";
    exit;
}

$id = intval($_GET['id']);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $newPassword = $_POST['password'];

    // Password update if entered
    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE students SET name=?, contact_number=?, address=?, password=? WHERE student_id=?");
        $stmt->bind_param("ssssi", $name, $contact, $address, $hashedPassword, $id);
    } else {
        $stmt = $conn->prepare("UPDATE students SET name=?, contact_number=?, address=? WHERE student_id=?");
        $stmt->bind_param("sssi", $name, $contact, $address, $id);
    }

    if ($stmt->execute()) {
        echo "<script>showNotification('✅ Student updated successfully', 'success');</script>";
        header("Refresh: 2; URL=manage_student.php");
        exit;
    } else {
        echo "<script>showNotification('❌ Error: " . addslashes($stmt->error) . "', 'error');</script>";
    }
}

// Fetch existing student
$result = $conn->query("SELECT * FROM students WHERE student_id = $id");
if (!$result || $result->num_rows == 0) {
    echo "<p style='color:red;'>❌ Student not found.</p>";
    exit;
}
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --success: #4cc9f0;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --border: #e9ecef;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-hover: 0 10px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }
        
        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .form-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow);
            overflow: hidden;
            position: relative;
        }
        
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 30px;
            border-bottom: 1px solid var(--border);
        }
        
        .form-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .student-photo-container {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 4px solid white;
        }
        
        .student-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .form-body {
            padding: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group.full-width {
            grid-column: span 2;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
            font-size: 14px;
        }
        
        input, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            transition: var(--transition);
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            width: 100%;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }
        
        .note {
            font-size: 13px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            box-shadow: var(--shadow-hover);
            z-index: 1000;
            transform: translateX(200%);
            transition: transform 0.3s ease;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background-color: var(--success);
        }
        
        .notification.error {
            background-color: var(--danger);
        }
        
        @media (max-width: 768px) {
            .form-body {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
            
            .form-header {
                flex-direction: column-reverse;
                align-items: center;
                text-align: center;
            }
            
            .student-photo-container {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <div>
                    <h1 class="form-title">Edit Student Profile</h1>
                    <p>Update student information below</p>
                </div>
                <div class="student-photo-container">
                    <img class="student-photo" src="../uploads/<?= htmlspecialchars($row['photo']) ?>" alt="Student Photo">
                </div>
            </div>
            
            <div class="form-body">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($row['name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="contact">Contact Number</label>
                    <input type="text" id="contact" name="contact" value="<?= htmlspecialchars($row['contact_number']) ?>" required>
                </div>
                
                <div class="form-group full-width">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" required><?= htmlspecialchars($row['address']) ?></textarea>
                </div>
                
                <div class="form-group full-width">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter new password (leave blank to keep current)">
                </div>
                
                <div class="form-group full-width">
                    <button type="submit" class="btn">Update Student Profile</button>
                </div>
            </div>
        </div>
    </div>
    
    <div id="notification" class="notification"></div>
    
    <script>
        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification show ${type}`;
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
        
        // Show any PHP-generated notifications
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            window.addEventListener('DOMContentLoaded', () => {
                <?php if ($stmt->execute()): ?>
                    showNotification('✅ Student updated successfully', 'success');
                <?php else: ?>
                    showNotification('❌ Error: <?= addslashes($stmt->error) ?>', 'error');
                <?php endif; ?>
            });
        <?php endif; ?>
    </script>
</body>
</html>