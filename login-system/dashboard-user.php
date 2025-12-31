<?php
session_start();
include '../database_connection/db_connect.php';

if (!isset($_SESSION['enrollment_id'])) {
    header("Location: ../login-system/login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];
$student = null;

/* 🔹 students table */
$stmt = $conn->prepare("SELECT * FROM students WHERE enrollment_id = ?");
$stmt->bind_param("s", $enrollment_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 1) {
    $student = $res->fetch_assoc();
    $student['contact_number'] = $student['contact_number'];

} else {
    /* 🔹 students26 table */
    $stmt = $conn->prepare("SELECT * FROM students26 WHERE enrollment_id = ?");
    $stmt->bind_param("s", $enrollment_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $student = $res->fetch_assoc();
        $student['contact_number'] = $student['contact'];
    }
}

if (!$student) {
    echo "Student record not found!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile | Student Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1f2937;
            --gray: #6b7280;
            --light-gray: #f3f4f6;
            --border: #e5e7eb;
            --white: #ffffff;
            --radius: 8px;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--light-gray);
            color: var(--dark);
            line-height: 1.5;
            padding: 16px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            margin-bottom: 24px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: var(--white);
            color: var(--primary);
            text-decoration: none;
            border-radius: var(--radius);
            font-size: 13px;
            font-weight: 500;
            border: 1px solid var(--border);
            transition: all 0.2s;
        }

        .back-btn:hover {
            background: var(--light-gray);
            border-color: var(--primary);
        }

        .welcome-section h1 {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .welcome-section p {
            color: var(--gray);
            font-size: 13px;
        }

        /* Profile Content */
        .profile-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 24px;
        }

        /* Profile Card */
        .profile-card {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .profile-header {
            padding: 24px;
            text-align: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: var(--white);
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid var(--white);
            object-fit: cover;
            margin: 0 auto 16px;
        }

        .profile-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .profile-enrollment {
            font-size: 13px;
            opacity: 0.9;
        }

        .profile-stats {
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 11px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Details Card */
        .details-card {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .details-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            font-weight: 600;
            color: var(--dark);
        }

        .details-header i {
            color: var(--primary);
        }

        .details-body {
            padding: 20px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .detail-label {
            font-size: 12px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        .detail-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            padding: 8px 12px;
            background: var(--light-gray);
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }

        /* Actions */
        .actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            border-radius: var(--radius);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-light);
            transform: translateY(-1px);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background: #d1fae5;
            color: var(--success);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-stats {
                grid-template-columns: repeat(4, 1fr);
            }
            
            .actions {
                grid-template-columns: 1fr;
            }
            
            .header-top {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {
            .profile-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .profile-header {
                padding: 20px;
            }
            
            .profile-avatar {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <a href="../test.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </a>
            </div>
            <div class="welcome-section">
                <h1>Student Profile</h1>
                <p>View and manage your profile information</p>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Left Column: Profile Card -->
            <div class="profile-card">
                <div class="profile-header">
                    <img src="../uploads/<?= htmlspecialchars($student['photo']) ?>" 
                         alt="Profile Photo" 
                         class="profile-avatar"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($student['name']) ?>&background=2563eb&color=fff&size=100'">
                    <div class="profile-name"><?= htmlspecialchars($student['name']) ?></div>
                    <div class="profile-enrollment"><?= htmlspecialchars($student['enrollment_id']) ?></div>
                </div>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?= htmlspecialchars($student['course']) ?></div>
                        <div class="stat-label">Course</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-number">
                            <span class="status-badge status-active">
                                <i class="fas fa-circle" style="font-size: 8px;"></i>
                                Active
                            </span>
                        </div>
                        <div class="stat-label">Status</div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Details Card -->
            <div class="details-card">
                <div class="details-header">
                    <i class="fas fa-user-circle"></i>
                    <span>Personal Information</span>
                </div>
                
                <div class="details-body">
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="detail-label">Full Name</span>
                            <span class="detail-value"><?= htmlspecialchars($student['name']) ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Enrollment ID</span>
                            <span class="detail-value"><?= htmlspecialchars($student['enrollment_id']) ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Course</span>
                            <span class="detail-value"><?= htmlspecialchars($student['course']) ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Contact Number</span>
                            <span class="detail-value"><?= htmlspecialchars($student['contact_number']) ?></span>
                        </div>
                        
                        <div class="detail-item" style="grid-column: span 2;">
                            <span class="detail-label">Address</span>
                            <span class="detail-value"><?= htmlspecialchars($student['address']) ?></span>
                        </div>
                    </div>

                    <div class="actions">
                        <a href="edit_profile.php" class="btn btn-primary">
                            <i class="fas fa-edit"></i>
                            Edit Profile
                        </a>
                        <a href="../login-system/logout.php" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>