<?php
session_start();
include '../../database_connection/db_connect.php';

if (!isset($_SESSION['enrollment_id'])) {
    exit("Unauthorized access");
}

$id = $_GET['id'];
$enrollment_id = $_SESSION['enrollment_id'];

$stmt = $conn->prepare("
    SELECT * 
    FROM student_monthly_fee
    WHERE id = ? AND enrollment_id = ?
");
$stmt->bind_param("is", $id, $enrollment_id);
$stmt->execute();
$receipt = $stmt->get_result()->fetch_assoc();

if (!$receipt) {
    exit("Receipt not found");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Receipt | Student Dashboard</title>
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
            max-width: 800px;
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

        /* Receipt Card */
        .receipt-card {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .receipt-header {
            padding: 24px;
            border-bottom: 2px solid var(--primary);
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            text-align: center;
        }

        .receipt-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .receipt-subtitle {
            color: var(--gray);
            font-size: 14px;
            font-weight: 500;
        }

        .receipt-body {
            padding: 32px;
        }

        .receipt-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .receipt-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .receipt-label {
            font-size: 12px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .receipt-value {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            padding: 8px 0;
        }

        .receipt-value.amount {
            font-size: 20px;
            color: var(--success);
            font-weight: 700;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 8px;
        }

        .status-paid {
            background: #d1fae5;
            color: var(--success);
            border: 2px solid #a7f3d0;
        }

        .status-pending {
            background: #fee2e2;
            color: var(--danger);
            border: 2px solid #fecaca;
        }

        /* Actions */
        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            padding: 20px;
            border-top: 1px solid var(--border);
            background: var(--light-gray);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: var(--radius);
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-print {
            background: var(--success);
        }

        .btn-print:hover {
            background: #0da271;
        }

        /* Receipt Footer */
        .receipt-footer {
            padding: 20px;
            text-align: center;
            border-top: 1px dashed var(--border);
            color: var(--gray);
            font-size: 12px;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .header,
            .actions,
            .back-btn {
                display: none;
            }
            
            .receipt-card {
                box-shadow: none;
                border: 1px solid #ddd;
                margin: 0;
            }
            
            .receipt-body {
                padding: 40px;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .receipt-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .receipt-body {
                padding: 24px;
            }
            
            .header-top {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .receipt-title {
                font-size: 20px;
            }
            
            .receipt-header {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <a href="my_fee_receipts.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Receipts
                </a>
            </div>
            <div class="welcome-section">
                <h1>Fee Receipt Details</h1>
                <p>View and print your fee payment receipt</p>
            </div>
        </div>

        <!-- Receipt Card -->
        <div class="receipt-card">
            <div class="receipt-header">
                <div class="receipt-title">FEE RECEIPT</div>
                <div class="receipt-subtitle">Payment Confirmation</div>
            </div>

            <div class="receipt-body">
                <div class="receipt-grid">
                    <div class="receipt-item">
                        <span class="receipt-label">Student Name</span>
                        <span class="receipt-value"><?= htmlspecialchars($receipt['name']) ?></span>
                    </div>
                    
                    <div class="receipt-item">
                        <span class="receipt-label">Enrollment ID</span>
                        <span class="receipt-value"><?= $receipt['enrollment_id'] ?></span>
                    </div>
                    
                    <div class="receipt-item">
                        <span class="receipt-label">Course</span>
                        <span class="receipt-value"><?= htmlspecialchars($receipt['course_name']) ?></span>
                    </div>
                    
                    <div class="receipt-item">
                        <span class="receipt-label">Fee Type</span>
                        <span class="receipt-value"><?= $receipt['fee_type'] ?></span>
                    </div>
                    
                    <?php if ($receipt['fee_type'] == 'Monthly'): ?>
                    <div class="receipt-item">
                        <span class="receipt-label">Month</span>
                        <span class="receipt-value"><?= $receipt['month_name'] ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="receipt-item">
                        <span class="receipt-label">Payment Date</span>
                        <span class="receipt-value"><?= $receipt['payment_date'] ?></span>
                    </div>
                    
                    <div class="receipt-item">
                        <span class="receipt-label">Payment Mode</span>
                        <span class="receipt-value"><?= $receipt['payment_mode'] ?></span>
                    </div>
                    
                    <div class="receipt-item">
                        <span class="receipt-label">Status</span>
                        <div>
                            <?php
                            $status_class = $receipt['payment_status'] === 'Paid' ? 'status-paid' : 'status-pending';
                            ?>
                            <span class="status-badge <?= $status_class ?>">
                                <?php if ($receipt['payment_status'] === 'Paid'): ?>
                                    <i class="fas fa-check-circle"></i>
                                <?php else: ?>
                                    <i class="fas fa-clock"></i>
                                <?php endif; ?>
                                <?= $receipt['payment_status'] ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 32px; padding: 24px; background: var(--light-gray); border-radius: var(--radius); border: 2px solid var(--success);">
                    <div style="font-size: 14px; color: var(--gray); margin-bottom: 8px;">Amount Paid</div>
                    <div style="font-size: 36px; font-weight: 800; color: var(--success);">₹<?= $receipt['fee_amount'] ?></div>
                    <div style="font-size: 12px; color: var(--gray); margin-top: 4px;">INR - Indian Rupees</div>
                </div>
            </div>

            <div class="receipt-footer">
                <p>This is a computer generated receipt. No signature required.</p>
                <p>Receipt ID: <?= $receipt['id'] ?> | Generated on: <?= date('d-m-Y H:i:s') ?></p>
            </div>
        </div>

        <!-- Actions -->
        <div class="actions">
            <a href="my_fee_receipts.php" class="btn">
                <i class="fas fa-list"></i>
                View All Receipts
            </a>
            <button onclick="window.print()" class="btn btn-print">
                <i class="fas fa-print"></i>
                Print Receipt
            </button>
        </div>
    </div>
</body>
</html>