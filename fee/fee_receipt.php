<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Receipt</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .receipt-container {
            max-width: 400px;
            width: 100%;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .receipt-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(to right, #007bff, #0056b3);
        }
        .receipt-container::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(to right, #007bff, #0056b3);
        }
        h2 {
            font-size: 18px;
            font-weight: 600;
            color: #007bff;
            text-align: center;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .receipt-item {
            border: 1px dashed #cccccc;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fafafa;
            border-radius: 4px;
            font-size: 13px;
            line-height: 1.5;
        }
        .receipt-item b {
            color: #333333;
            font-weight: 500;
        }
        .receipt-item br {
            margin-bottom: 5px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666666;
            margin-top: 20px;
            border-top: 1px solid #e0e0e0;
            padding-top: 10px;
        }
        .footer::before {
            content: 'Thank you for your payment';
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <?php
        include("db_connect.php");

        $ids = $_GET['ids'];
        $fees = $conn->query("
            SELECT * FROM student_monthly_fee WHERE id IN ($ids)
        ");
        ?>

        <h2>Fee Receipt</h2>

        <?php while($f = $fees->fetch_assoc()): ?>
        <div class="receipt-item">
            <b>Name:</b> <?= $f['name'] ?><br>
            <b>Enrollment:</b> <?= $f['enrollment_id'] ?><br>
            <b>Fee Type:</b> <?= $f['fee_type'] ?><br>
            <b>Month:</b> <?= $f['month_name'] ?><br>
            <b>Amount:</b> ₹<?= $f['fee_amount'] ?><br>
            <b>Mode:</b> <?= $f['payment_mode'] ?><br>
            <b>Phone:</b> <?= $f['phone'] ?><br>
            <b>Date:</b> <?= date('d-M-Y', strtotime($f['payment_date'])) ?>
        </div>
        <?php endwhile; ?>

        <div class="footer">
            This is an official receipt. Please keep for your records.
        </div>
    </div>
</body>
</html>