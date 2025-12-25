<?php
include("db_connect.php"); // adjust path if needed

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
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --secondary: #3a0ca3;
            --success: #06d6a0;
            --pending: #ff9e00;
            --danger: #ef476f;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #dee2e6;
            --border-radius: 10px;
            --box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
            
            /* Responsive base font size */
            font-size: 14px;
        }
        
        @media (min-width: 768px) {
            :root {
                font-size: 15px;
            }
        }
        
        @media (min-width: 1200px) {
            :root {
                font-size: 16px;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7ff;
            color: var(--dark);
            line-height: 1.5;
            padding: 1rem;
            min-height: 100vh;
            font-size: 0.95rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        
        /* Header - More Compact */
        .header {
            text-align: center;
            margin-bottom: 1.5rem;
            padding: 0.5rem;
        }
        
        .header h1 {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 0.3rem;
            font-size: 1.5rem;
        }
        
        .header p {
            color: var(--gray);
            font-size: 0.85rem;
        }
        
        /* Student Card - More Compact */
        .student-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.2rem;
            margin-bottom: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: var(--transition);
        }
        
        .student-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        }
        
        .student-photo {
            width: 5rem;
            height: 5rem;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-light);
            margin-bottom: 0.8rem;
        }
        
        .student-info h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.3rem;
        }
        
        .enrollment-id {
            display: inline-block;
            background: var(--primary-light);
            color: var(--primary);
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
        }
        
        /* Fee Sections - More Compact */
        .fee-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.2rem;
            margin-bottom: 1rem;
            transition: var(--transition);
        }
        
        .fee-section:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.09);
        }
        
        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid var(--primary-light);
        }
        
        .section-title h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--secondary);
        }
        
        .fee-count {
            background: var(--primary);
            color: white;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
        }
        
        /* Fee Table - More Compact */
        .fee-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        
        .fee-table thead {
            background-color: var(--primary-light);
        }
        
        .fee-table th {
            padding: 0.8rem 0.5rem;
            text-align: left;
            font-weight: 600;
            color: var(--primary);
            border-bottom: 1px solid var(--gray-light);
        }
        
        .fee-table td {
            padding: 0.7rem 0.5rem;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .fee-table tr:last-child td {
            border-bottom: none;
        }
        
        .fee-table tr:hover {
            background-color: #f9f9ff;
        }
        
        /* Status Badges - Smaller */
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.7rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-paid {
            background-color: rgba(6, 214, 160, 0.15);
            color: var(--success);
        }
        
        .status-pending {
            background-color: rgba(255, 158, 0, 0.15);
            color: var(--pending);
        }
        
        /* Checkbox Styling - Smaller */
        .checkbox-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .custom-checkbox {
            position: relative;
            width: 18px;
            height: 18px;
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
            height: 18px;
            width: 18px;
            background-color: white;
            border: 1.5px solid var(--gray-light);
            border-radius: 4px;
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
            left: 5px;
            top: 2px;
            width: 5px;
            height: 8px;
            border: solid white;
            border-width: 0 1.5px 1.5px 0;
            transform: rotate(45deg);
        }
        
        /* Payment Section - More Compact */
        .payment-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.2rem;
            margin-top: 1.5rem;
            text-align: center;
        }
        
        .total-amount {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .pay-btn {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 3px 10px rgba(67, 97, 238, 0.2);
            min-width: 200px;
        }
        
        .pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .pay-btn:active {
            transform: translateY(0);
        }
        
        .pay-btn i {
            font-size: 1rem;
        }
        
        .pay-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 0.8rem;
            opacity: 0.6;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 0.8rem;
            }
            
            .container {
                padding: 0 0.5rem;
            }
            
            .header {
                margin-bottom: 1rem;
            }
            
            .header h1 {
                font-size: 1.3rem;
            }
            
            .student-card {
                padding: 1rem;
                margin-bottom: 1rem;
            }
            
            .student-photo {
                width: 4rem;
                height: 4rem;
                margin-bottom: 0.6rem;
            }
            
            .student-info h2 {
                font-size: 1.1rem;
            }
            
            .fee-section {
                padding: 1rem;
                margin-bottom: 0.8rem;
            }
            
            .section-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
                margin-bottom: 0.8rem;
            }
            
            .fee-table {
                font-size: 0.8rem;
                display: block;
                overflow-x: auto;
            }
            
            .fee-table th,
            .fee-table td {
                min-width: 90px;
                padding: 0.6rem 0.4rem;
            }
            
            .fee-table th:first-child,
            .fee-table td:first-child {
                min-width: 50px;
            }
            
            .pay-btn {
                width: 100%;
                padding: 0.9rem;
                min-width: auto;
            }
            
            .payment-section {
                padding: 1rem;
                margin-top: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            :root {
                font-size: 13px;
            }
            
            body {
                padding: 0.5rem;
            }
            
            .header h1 {
                font-size: 1.1rem;
            }
            
            .header p {
                font-size: 0.75rem;
            }
            
            .student-info h2 {
                font-size: 1rem;
            }
            
            .section-title h3 {
                font-size: 0.9rem;
            }
            
            .total-amount {
                font-size: 1.1rem;
            }
            
            .custom-checkbox {
                width: 16px;
                height: 16px;
            }
            
            .checkmark {
                width: 16px;
                height: 16px;
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
                <p style="font-size: 0.85rem; color: var(--gray);">Select pending fees and proceed to payment</p>
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
                            <th style="width: 40px;">Select</th>
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
                                    <i class="fas fa-check-circle" style="color: var(--success); font-size: 0.9rem;"></i>
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
                <button type="submit" class="pay-btn" id="payButton" <?= !$hasPendingFees ? 'disabled' : '' ?> data-has-pending="<?= $hasPendingFees ? 'true' : 'false' ?>">
                    <i class="fas fa-credit-card"></i> Proceed to Secure Payment
                </button>
                
                <?php if (!$hasPendingFees): ?>
                    <p style="margin-top: 0.8rem; color: var(--success); font-weight: 500; font-size: 0.85rem;">
                        <i class="fas fa-check-circle"></i> All fees are already paid!
                    </p>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <script>
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
                    payButton.disabled = !(payButton.dataset.hasPending === 'true');
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