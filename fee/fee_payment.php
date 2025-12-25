<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Payment Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #ffffff;
            color: #333333;
            margin: 0;
            padding: 20px;
            font-size: 14px;
            line-height: 1.4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-size: 18px;
            font-weight: 600;
            color: #007bff;
            margin-bottom: 20px;
            text-align: center;
        }
        h3 {
            font-size: 16px;
            font-weight: 500;
            color: #007bff;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 14px;
        }
        input[type="radio"] {
            margin-right: 8px;
        }
        .fee-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #e9ecef;
            font-size: 13px;
        }
        .total {
            font-weight: 600;
            font-size: 15px;
            color: #007bff;
            text-align: right;
            margin-top: 10px;
        }
        button {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 20px;
            align-self: flex-start;
        }
        button:hover {
            background-color: #0056b3;
        }
        input[type="hidden"] {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        include("db_connect.php");

        $fee_ids = $_POST['fee_ids'];
        $enroll  = $_POST['enrollment_id'];

        $total = 0;
        $ids = implode(",", $fee_ids);

        $fees = $conn->query("SELECT * FROM student_monthly_fee WHERE id IN ($ids)");
        ?>

        <h2>Select Payment Mode</h2>

        <form method="POST" action="submit_fee_action.php">
            <input type="hidden" name="fee_ids" value="<?= $ids ?>">

            <label>
                <input type="radio" name="payment_mode" value="Cash" required> Cash
            </label>
            <label>
                <input type="radio" name="payment_mode" value="Online"> Online
            </label>

            <h3>Fee Summary</h3>
            <?php while($f = $fees->fetch_assoc()):
            $total += $f['fee_amount']; ?>
            <div class="fee-item">
                <span><?= $f['fee_type'] ?> <?= $f['month_name'] ?></span>
                <span>₹<?= $f['fee_amount'] ?></span>
            </div>
            <?php endwhile; ?>

            <div class="total">Total: ₹<?= $total ?></div>

            <button type="submit">Submit Fee</button>
        </form>
    </div>
</body>
</html>