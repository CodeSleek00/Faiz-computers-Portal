<?php
session_start();
include '../database_connection/db_connect.php';

if (!isset($_SESSION['enrollment_id'])) {
    header("Location: login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];
$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Profile | <?= htmlspecialchars($student['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --danger: #f72585;
            --success: #4cc9f0;
            --warning: #f8961e;
            --dark: #212529;
            --light: #f8f9fa;
            --gray: #6c757d;
            --white: #ffffff;
            --card-shadow: 0 10px 20px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
        }

        .back-btn {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--gray);
            color: var(--white);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            z-index: 10;
        }

        .back-btn:hover {
            background: var(--dark);
            transform: translateX(-3px);
        }

        .profile-card {
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            position: relative;
            margin-top: 20px;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 30px;
            color: var(--white);
            text-align: center;
            position: relative;
        }

        .profile-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .profile-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid var(--white);
            object-fit: cover;
            margin: -70px auto 20px;
            display: block;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            background: var(--light);
            position: relative;
            z-index: 2;
        }

        .profile-body {
            padding: 30px;
        }

        .profile-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid rgba(67, 97, 238, 0.1);
        }

        .section-title i {
            font-size: 1.1rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .info-label {
            font-size: 0.85rem;
            color: var(--gray);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: var(--dark);
            word-break: break-word;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            width: fit-content;
        }

        .status-active {
            background-color: rgba(76, 201, 240, 0.2);
            color: var(--success);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            border: none;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(63, 55, 201, 0.3);
        }

        .btn-danger {
            background: var(--danger);
            color: var(--white);
        }

        .btn-danger:hover {
            background: #d3166b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(248, 37, 133, 0.3);
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .profile-header {
                padding: 25px 20px;
            }
            
            .profile-body {
                padding: 25px 20px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (min-width: 992px) {
            .profile-pic {
                width: 150px;
                height: 150px;
                margin-top: -85px;
            }
            
            .profile-header h1 {
                font-size: 2rem;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-card {
            animation: fadeIn 0.6s ease-out forwards;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back Button -->
        <button class="back-btn" onclick="window.history.back()">
            <i class="fas fa-arrow-left"></i>
        </button>
        
            <br><br><br>     
            <img src="../uploads/<?= htmlspecialchars($student['photo']) ?>" alt="Profile Photo" class="profile-pic">
            
            <div class="profile-body">
                <div class="profile-section">
                    <h2 class="section-title"><i class="fas fa-user-graduate"></i> Basic Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Student ID</span>
                            <span class="info-value"><?= htmlspecialchars($student['enrollment_id']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Full Name</span>
                            <span class="info-value"><?= htmlspecialchars($student['name']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Course</span>
                            <span class="info-value"><?= htmlspecialchars($student['course']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Status</span>
                            <span class="status-badge status-active">
                                <i class="fas fa-circle" style="font-size: 8px;"></i>
                                Active
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="profile-section">
                    <h2 class="section-title"><i class="fas fa-address-card"></i> Contact Details</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Contact Number</span>
                            <span class="info-value"><?= htmlspecialchars($student['contact_number']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email Address</span>
                            <span class="info-value"><?= htmlspecialchars($student['email'] ?? 'Not provided') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Address</span>
                            <span class="info-value"><?= htmlspecialchars($student['address']) ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Optional: Add smooth back button behavior
        document.querySelector('.back-btn').addEventListener('click', function() {
            window.history.back();
        });
    </script>
</body>
</html>