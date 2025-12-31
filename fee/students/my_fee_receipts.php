<?php
session_start();
include '../../database_connection/db_connect.php';

if (!isset($_SESSION['enrollment_id'])) {
    header("Location: ../../login-system/login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];

$stmt = $conn->prepare("
    SELECT * 
    FROM student_monthly_fee
    WHERE enrollment_id = ?
    ORDER BY payment_date DESC
");
$stmt->bind_param("s", $enrollment_id);
$stmt->execute();
$result = $stmt->get_result();
$total_records = $result->num_rows;

// Calculate totals
$total_paid = 0;
$total_pending = 0;
$result->data_seek(0);
while ($row = $result->fetch_assoc()) {
    if ($row['payment_status'] === 'Paid') {
        $total_paid += $row['fee_amount'];
    } else {
        $total_pending += $row['fee_amount'];
    }
}
$result->data_seek(0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Receipts | Student Dashboard</title>
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

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--white);
            padding: 16px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            text-align: center;
        }

        .stat-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-size: 14px;
            color: var(--white);
        }

        .stat-icon.total { background: var(--primary); }
        .stat-icon.paid { background: var(--success); }
        .stat-icon.pending { background: var(--warning); }
        .stat-icon.amount { background: var(--danger); }

        .stat-number {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 2px;
        }

        .stat-label {
            font-size: 11px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        /* Fee Section */
        .fee-section {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .section-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header h2 {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-header h2 i {
            color: var(--primary);
        }

        .receipts-count {
            font-size: 12px;
            color: var(--gray);
            background: var(--light-gray);
            padding: 4px 10px;
            border-radius: 20px;
        }

        .fee-list {
            padding: 20px;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
        }

        .fee-table {
            width: 100%;
            border-collapse: collapse;
        }

        .fee-table thead {
            background: var(--light-gray);
        }

        .fee-table th {
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border);
        }

        .fee-table td {
            padding: 12px 16px;
            font-size: 13px;
            border-bottom: 1px solid var(--border);
        }

        .fee-table tbody tr:hover {
            background: var(--light-gray);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-paid {
            background: #d1fae5;
            color: var(--success);
        }

        .status-pending {
            background: #fee2e2;
            color: var(--danger);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: var(--radius);
            font-size: 12px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn:hover {
            background: var(--primary-light);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* No Records */
        .empty-state {
            padding: 40px 20px;
            text-align: center;
        }

        .empty-state i {
            font-size: 40px;
            color: var(--border);
            margin-bottom: 16px;
        }

        .empty-state h3 {
            font-size: 16px;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .empty-state p {
            color: var(--gray);
            font-size: 13px;
            margin-bottom: 20px;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .header-top {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
            
            .fee-table th,
            .fee-table td {
                padding: 10px 12px;
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .empty-state {
                padding: 30px 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <a href="../../test.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </a>
            </div>
            <div class="welcome-section">
                <h1>Fee Receipts</h1>
                <p>Enrollment ID: <?= $enrollment_id ?> • View your fee payment history</p>
            </div>
        </div>


        <!-- Fee Section -->
        <div class="fee-section">
            <div class="section-header">
                <h2><i class="fas fa-file-invoice-dollar"></i> Fee Receipts</h2>
                <div class="receipts-count"><?= $total_records ?> records</div>
            </div>

            <div class="fee-list">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-container">
                        <table class="fee-table">
                            <thead>
                                <tr>
                                    <th>Fee Type</th>
                                    <th>Month</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Mode</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['fee_type']) ?></td>
                                        <td><?= htmlspecialchars($row['month_name'] ?? '-') ?></td>
                                        <td><strong>₹<?= $row['fee_amount'] ?></strong></td>
                                        <td><?= $row['payment_date'] ?></td>
                                        <td><?= $row['payment_mode'] ?></td>
                                        <td>
                                            <?php
                                            $status_class = $row['payment_status'] === 'Paid' ? 'status-paid' : 'status-pending';
                                            ?>
                                            <span class="status-badge <?= $status_class ?>">
                                                <?php if ($row['payment_status'] === 'Paid'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-clock"></i>
                                                <?php endif; ?>
                                                <?= $row['payment_status'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="view_fee_receipt.php?id=<?= $row['id'] ?>" class="btn">
                                                <i class="fas fa-eye"></i>
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file-invoice"></i>
                        <h3>No Fee Records</h3>
                        <p>No fee payment records found for your account.</p>
                        <a href="../../test.php" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: var(--primary); color: white; text-decoration: none; border-radius: var(--radius); font-size: 13px; font-weight: 500;">
                            <i class="fas fa-home"></i>
                            Return to Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>