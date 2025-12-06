<?php
// razorpay_payment.php
session_start();
require_once "config.php";

if (!isset($_SESSION['admission_id'])) {
    die("No admission found in session. Please submit the form first.");
}

$admission_id = intval($_SESSION['admission_id']);

// Fetch amount from session or DB (amount must be in paise for Razorpay)
$amount_inr = isset($_SESSION['amount']) ? floatval($_SESSION['amount']) : 0.00;
if ($amount_inr <= 0) {
    // fallback: fetch from DB
    $stmt = $conn->prepare("SELECT reg_fee FROM admissions WHERE id = ?");
    $stmt->bind_param("i", $admission_id);
    $stmt->execute();
    $stmt->bind_result($reg_fee_db);
    $stmt->fetch();
    $stmt->close();
    $amount_inr = floatval($reg_fee_db);
}

$amount_paise = intval($amount_inr * 100);

// Create Razorpay order via Orders API (server-side)
$orderData = [
    'amount' => $amount_paise, // 100 paise = INR 1
    'currency' => 'INR',
    'receipt' => "admission_" . $admission_id,
    'payment_capture' => 1 // auto capture
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.razorpay.com/v1/orders");
curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ":" . RAZORPAY_KEY_SECRET);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_status !== 200 && $http_status !== 201) {
    die("Unable to create Razorpay order. Response: " . htmlspecialchars($response));
}

$order = json_decode($response, true);
$razorpay_order_id = $order['id'];

// Save razorpay_order_id to DB for linking + later verification
$stmt = $conn->prepare("UPDATE admissions SET razorpay_order_id = ? WHERE id = ?");
$stmt->bind_param("si", $razorpay_order_id, $admission_id);
$stmt->execute();
$stmt->close();

// Fetch student name and email for checkout (if provided)
$stmt = $conn->prepare("SELECT full_name, email, phone FROM admissions WHERE id = ?");
$stmt->bind_param("i", $admission_id);
$stmt->execute();
$stmt->bind_result($student_name, $student_email, $student_phone);
$stmt->fetch();
$stmt->close();

// Pass required info to frontend below
$keyId = RAZORPAY_KEY_ID;
$amount_display = number_format($amount_inr, 2);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pay with Razorpay</title>
</head>
<body>
    <h2>Pay Admission Fee — INR <?php echo $amount_display; ?></h2>
    <p>Student: <?php echo htmlspecialchars($student_name); ?></p>

    <!-- Razorpay Checkout script -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <button id="rzp-button">Pay Now</button>

    <form id="verifyForm" method="POST" action="payment_verify.php" style="display:none;">
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
        <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
        <input type="hidden" name="razorpay_signature" id="razorpay_signature">
    </form>

    <script>
    var options = {
        "key": "<?php echo $keyId; ?>",
        "amount": "<?php echo $amount_paise; ?>",
        "currency": "INR",
        "name": "Your Institute Name",
        "description": "Admission Fee",
        "order_id": "<?php echo $razorpay_order_id; ?>",
        "handler": function (response){
            // send payment details to server for verification
            document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
            document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
            document.getElementById('razorpay_signature').value = response.razorpay_signature;
            document.getElementById('verifyForm').submit();
        },
        "prefill": {
            "name": "<?php echo addslashes($student_name); ?>",
            "email": "<?php echo addslashes($student_email); ?>",
            "contact": "<?php echo addslashes($student_phone); ?>"
        },
        "theme": {
            "color": "#007bff"
        }
    };
    var rzp = new Razorpay(options);
    document.getElementById('rzp-button').onclick = function(e){
        rzp.open();
        e.preventDefault();
    }
    </script>
</body>
</html>
