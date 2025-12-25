<?php
include("../db_connect.php"); // adjust path if needed

// ================= BASIC SAFETY =================
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset("utf8mb4");

// ================= GET ENROLLMENT =================
$enroll = $_GET['enroll'] ?? '';

if ($enroll == '') {
    die("Enrollment ID missing");
}

// ================= FETCH STUDENT INFO =================
$student = $conn->query("
    SELECT name, photo 
    FROM student_monthly_fee 
    WHERE enrollment_id='$enroll'
    LIMIT 1
")->fetch_assoc();

if (!$student) {
    die("Student not found");
}

$student_name = $student['name'];
$photo = $student['photo'] ?: 'assets/no-photo.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Fee Details | <?= htmlspecialchars($enroll) ?></title>
    
    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #007bff;
            --primary-light: #f0f8ff;
            --secondary: #0056b3;
            --success: #28a745;
            --pending: #ffc107;
            --danger: #dc3545;
            --light: #ffffff;
            --dark: #333333;
            --gray: #666666;
            --gray-light: #e9ecef;
            --border-radius: 4px;
            --box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --transition: all 0.2s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #ffffff;
            color: var(--dark);
            line-height: 1.4;
            padding: 10px;
            font-size: 12px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .header h1 {
            color: var(--primary);
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 3px;
        }
        
        .header p {
            color: var(--gray);
            font-size: 11px;
        }
        
        /* Student Card */
        .student-card {
            background: var(--light);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 12px;
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: var(--transition);
        }
        
        .student-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }
        
        .student-photo {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-light);
            margin-bottom: 8px;
        }
        
        .student-info h2 {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }
        
        .enrollment-id {
            display: inline-block;
            background: var(--primary-light);
            color: var(--primary);
            padding: 3px 8px;
            border-radius: 15px;
            font-weight: 500;
            font-size: 10px;
            margin-bottom: 8px;
        }
        
        /* Fee Sections */
        .fee-section {
            background: var(--light);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 12px;
            margin-bottom: 12px;
            transition: var(--transition);
        }
        
        .fee-section:hover {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }
        
        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--primary-light);
        }
        
        .section-title h3 {
            font-size: 13px;
            font-weight: 600;
            color: var(--secondary);
        }
        
        .fee-count {
            background: var(--primary);
            color: white;
            font-size: 10px;
            font-weight: 500;
            padding: 2px 6px;
            border-radius: 15px;
        }
        
        /* Fee Table */
        .fee-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        
        .fee-table thead {
            background-color: var(--primary-light);
        }
        
        .fee-table th {
            padding: 6px;
            text-align: left;
            font-weight: 600;
            color: var(--primary);
            font-size: 11px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .fee-table td {
            padding: 6px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .fee-table tr:last-child td {
            border-bottom: none;
        }
        
        .fee-table tr:hover {
            background-color: #f9f9ff;
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: 500;
        }
        
        .status-paid {
            background-color: rgba(40, 167, 69, 0.15);
            color: var(--success);
        }
        
        .status-pending {
            background-color: rgba(255, 193, 7, 0.15);
            color: var(--pending);
        }
        
        /* Checkbox Styling */
        .checkbox-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .custom-checkbox {
            position: relative;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }
        
        .custom-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }
        
        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 16px;
            width: 16px;
            background-color: white;
            border: 1px solid var(--gray-light);
            border-radius: 3px;
            transition: var(--transition);
        }
        
        .custom-checkbox:hover input ~ .checkmark {
            border-color: var(--primary);
        }
        
        .custom-checkbox input:checked ~ .checkmark {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }
        
        .custom-checkbox input:checked ~ .checkmark:after {
            display: block;
            left: 4px;
            top: 1px;
            width: 3px;
            height: 7px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        
        /* Payment Button */
        .payment-section {
            background: var(--light);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 12px;
            margin-top: 15px;
            text-align: center;
        }
        
        .total-amount {
            font-size: 14px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
        }
        
        .pay-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 20px;
            font-size: 12px;
            font-weight: 600;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            box-shadow: 0 2px 5px rgba(0, 123, 255, 0.3);
        }
        
        .pay-btn:hover {
            background: var(--secondary);
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 123, 255, 0.4);
        }
        
        .pay-btn:active {
            transform: translateY(0);
        }
        
        .pay-btn i {
            font-size: 12px;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 15px 8px;
            color: var(--gray);
            font-size: 11px;
        }
        
        .empty-state i {
            font-size: 25px;
            margin-bottom: 8px;
            opacity: 0.6;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .student-card {
                padding: 10px;
            }
            
            .student-photo {
                width: 60px;
                height: 60px;
            }
            
            .fee-section {
                padding: 10px;
            }
            
            .section-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
            
            .fee-table {
                display: block;
                overflow-x: auto;
            }
            
            .fee-table th,
            .fee-table td {
                min-width: 80px;
                padding: 5px 4px;
            }
            
            .pay-btn {
                width: 100%;
                padding: 10px;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 8px;
            }
            
            .header h1 {
                font-size: 13px;
            }
            
            .student-info h2 {
                font-size: 13px;
            }
            
            .section-title h3 {
                font-size: 12px;
            }
        }
    </style>
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-university"></i> Student Fee Portal</h1>
            <p>View and manage your fee payments securely</p>
        </div>
        
        <!-- Student Information Card -->
        <div class="student-card">
            <img src="<?= $photo ?>" class="student-photo" onerror="this.src='assets/no-photo.png'">
            <div class="student-info">
                <h2><?= htmlspecialchars($student_name) ?></h2>
                <div class="enrollment-id">
                    <i class="fas fa-id-card"></i> <?= htmlspecialchars($enroll) ?>
                </div>
                <p>Select pending fees and proceed to payment</p>
            </div>
        </div>
        
        <form method="POST" action="fee_payment.php" id="feeForm">
            <input type="hidden" name="enrollment_id" value="<?= htmlspecialchars($enroll) ?>">
            
            <?php
            /* ================= ALL FEE TYPES ================= */
            $groups = ['Registration', 'Semester', 'Monthly', 'Internal', 'Additional'];
            $hasPendingFees = false;
            
            foreach ($groups as $type):
                $result = $conn->query("
                    SELECT * FROM student_monthly_fee
                    WHERE enrollment_id='$enroll'
                    AND fee_type='$type'
                ");
                
                if ($result->num_rows == 0) continue;
                
                $totalRows = $result->num_rows;
                $pendingCount = 0;
                $feeData = [];
                
                while($f = $result->fetch_assoc()) {
                    $feeData[] = $f;
                    if ($f['payment_status'] === 'Pending') {
                        $pendingCount++;
                        $hasPendingFees = true;
                    }
                }
            ?>
            
            <!-- Fee Section -->
            <div class="fee-section">
                <div class="section-title">
                    <h3><i class="fas fa-file-invoice-dollar"></i> <?= $type ?> Fees</h3>
                    <div class="fee-count"><?= $totalRows ?> fee<?= $totalRows > 1 ? 's' : '' ?> (<?= $pendingCount ?> pending)</div>
                </div>
                
                <table class="fee-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Select</th>
                            <th>Fee Type</th>
                            <th>Month / Term</th>
                            <th>Amount (₹)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($feeData as $f): ?>
                        <tr>
                            <td>
                                <?php if ($f['payment_status'] === 'Pending'): ?>
                                    <div class="checkbox-container">
                                        <label class="custom-checkbox">
                                            <input type="checkbox" name="fee_ids[]" value="<?= $f['id'] ?>" data-amount="<?= $f['fee_amount'] ?>">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                <?php else: ?>
                                    <i class="fas fa-check-circle" style="color: var(--success); font-size: 12px;"></i>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($f['fee_type']) ?></td>
                            <td><?= htmlspecialchars($f['month_name'] ?: '-') ?></td>
                            <td><strong>₹<?= number_format($f['fee_amount'], 2) ?></strong></td>
                            <td>
                                <span class="status-badge status-<?= strtolower($f['payment_status']) ?>">
                                    <?= htmlspecialchars($f['payment_status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php endforeach; ?>
            
            <!-- Payment Summary -->
            <div class="payment-section">
                <div class="total-amount" id="totalAmount">Total: ₹0.00</div>
                <button type="submit" class="pay-btn" id="payButton" <?= !$hasPendingFees ? 'disabled' : '' ?>>
                    <i class="fas fa-credit-card"></i> Proceed to Secure Payment
                </button>
                
                <?php if (!$hasPendingFees): ?>
                    <p style="margin-top: 8px; color: var(--success); font-weight: 500; font-size: 11px;">
                        <i class="fas fa-check-circle"></i> All fees are already paid!
                    </p>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <script>
        // Calculate total amount for selected fees
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[name="fee_ids[]"]');
            const totalAmountElement = document.getElementById('totalAmount');
            const payButton = document.getElementById('payButton');
            
            function updateTotalAmount() {
                let total = 0;
                let selectedCount = 0;
                
                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        total += parseFloat(checkbox.dataset.amount);
                        selectedCount++;
                    }
                });
                
                totalAmountElement.textContent = `Total: ₹${total.toFixed(2)}`;
                
                // Update button text based on selection
                if (selectedCount > 0) {
                    payButton.innerHTML = `<i class="fas fa-credit-card"></i> Pay ₹${total.toFixed(2)} (${selectedCount} fee${selectedCount > 1 ? 's' : ''})`;
                    payButton.disabled = false;
                } else {
                    payButton.innerHTML = `<i class="fas fa-credit-card"></i> Proceed to Secure Payment`;
                    payButton.disabled = !payButton.hasAttribute('data-has-pending');
                }
            }
            
            // Add event listeners to all checkboxes
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateTotalAmount);
            });
            
            // Initial calculation
            updateTotalAmount();
            
            // Form submission validation
            document.getElementById('feeForm').addEventListener('submit', function(e) {
                const selectedFees = document.querySelectorAll('input[name="fee_ids[]"]:checked').length;
                
                if (selectedFees === 0) {
                    e.preventDefault();
                    alert('Please select at least one fee to proceed with payment.');
                }
            });
        });
    </script>
</body>
</html>