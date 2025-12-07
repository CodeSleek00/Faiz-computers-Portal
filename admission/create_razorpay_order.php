<?php
require 'vendor/autoload.php';

use Razorpay\Api\Api;

$api = new Api("rzp_test_Rc7TynjHcNrEfB", "W2wBaETyh0J8UlE55tPSkEPc");

// Amount in paisa
$amount = 10000; // ₹100

$order = $api->order->create([
    'amount' => $amount,
    'currency' => 'INR',
    'payment_capture' => 1
]);

echo json_encode([
    "key" => "RAZORPAY_KEY",
    "amount" => $amount,
    "order_id" => $order['id']
]);
?>
