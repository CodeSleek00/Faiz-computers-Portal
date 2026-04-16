<?php
session_start();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../admin_dashboard.php");
    exit;
}

// Password protection
$correct_password = "faiz123"; // Change this to your desired password

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === $correct_password) {
            $_SESSION['authenticated'] = true;
        } else {
            $error = "Incorrect password!";
        }
    }

    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        // Show password form
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Access Restricted</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
                    background: #f9fafb;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    margin: 0;
                }
                .login-container {
                    background: white;
                    padding: 40px;
                    border-radius: 12px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    text-align: center;
                    max-width: 400px;
                    width: 100%;
                }
                h1 {
                    color: #374151;
                    margin-bottom: 8px;
                }
                p {
                    color: #6b7280;
                    margin-bottom: 24px;
                }
                input[type="password"] {
                    width: 100%;
                    padding: 12px;
                    border: 1px solid #d1d5db;
                    border-radius: 8px;
                    font-size: 16px;
                    margin-bottom: 16px;
                }
                button {
                    background: #2563eb;
                    color: white;
                    border: none;
                    padding: 12px 24px;
                    border-radius: 8px;
                    font-size: 16px;
                    cursor: pointer;
                    width: 100%;
                }
                .error {
                    color: #ef4444;
                    margin-bottom: 16px;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <h1>🔒 Access Restricted</h1>
                <p>Enter the password to access the Day End Fee Report.</p>
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="password" name="password" placeholder="Enter password" required>
                    <button type="submit">Access Report</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

include("db_connect.php");

// Date select logic
$selected_date = $_GET['date'] ?? date('Y-m-d');

// Day-wise fee list
$fees = $conn->query("
    SELECT name, enrollment_id, fee_type, month_name, fee_amount, payment_mode
    FROM student_monthly_fee
    WHERE payment_status='Paid'
    AND payment_date='$selected_date'
");

// Day-end total
$total = $conn->query("
    SELECT SUM(fee_amount) AS total
    FROM student_monthly_fee
    WHERE payment_status='Paid'
    AND payment_date='$selected_date'
")->fetch_assoc();

// Cash / Online breakup
$modes = $conn->query("
    SELECT payment_mode, SUM(fee_amount) AS amount
    FROM student_monthly_fee
    WHERE payment_status='Paid'
    AND payment_date='$selected_date'
    GROUP BY payment_mode
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Day End Fee Report</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background: #fff; 
            padding: 20px; 
            color: #333; 
        }
        h2 { 
            margin-bottom: 10px; 
            color: #2f3640; 
        }
        h3 { 
            margin-top: 20px; 
            color: #2f3640; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            background: #fff; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
        }
        th, td { 
            padding: 10px; 
            border: 1px solid #ddd; 
            text-align: center; 
        }
        th { 
            background: #2f3640; 
            color: #fff; 
            font-weight: 600; 
        }
        .total-box {
            background: #27ae60;
            color: #fff;
            padding: 15px;
            font-size: 20px;
            margin-top: 15px;
            text-align: center;
            font-weight: 600;
        }
        .filter-box {
            margin-bottom: 15px;
            background: #fff;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .filter-box label {
            margin-right: 10px;
            font-weight: 600;
        }
        .filter-box input[type="date"] {
            padding: 5px;
            margin-right: 10px;
        }
        .filter-box button {
            padding: 5px 10px;
            background: #2f3640;
            color: #fff;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }
        .filter-box a {
            margin-left: 10px;
            color: #27ae60;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>📅 Day End Fee Report</h2>
    <a href="?logout=1" style="background: #e74c3c; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; font-weight: 600;">Logout</a>
</div>

<div class="filter-box">
    <form method="GET">
        <label>Select Date:</label>
        <input type="date" name="date" value="<?= $selected_date ?>">
        <button type="submit">Check</button>
        <a href="day_end_fee_report.php">Today</a>
    </form>
</div>

<table>
<tr>
    <th>Name</th>
    <th>Enrollment</th>
    <th>Fee Type</th>
    <th>Month</th>
    <th>Amount</th>
    <th>Mode</th>
</tr>

<?php if($fees->num_rows > 0): ?>
    <?php while($row = $fees->fetch_assoc()): ?>
    <tr>
        <td><?= $row['name'] ?></td>
        <td><?= $row['enrollment_id'] ?></td>
        <td><?= $row['fee_type'] ?></td>
        <td><?= $row['month_name'] ?? '-' ?></td>
        <td>₹<?= $row['fee_amount'] ?></td>
        <td><?= $row['payment_mode'] ?></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr>
    <td colspan="6">No payment found for this date</td>
</tr>
<?php endif; ?>
</table>

<div class="total-box">
    Day End Total: ₹<?= $total['total'] ?? 0 ?>
</div>

<h3>💳 Payment Mode Summary</h3>
<table>
<tr>
    <th>Mode</th>
    <th>Amount</th>
</tr>
<?php while($m = $modes->fetch_assoc()): ?>
<tr>
    <td><?= $m['payment_mode'] ?></td>
    <td>₹<?= $m['amount'] ?></td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>