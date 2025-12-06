<?php
// payment_verify.php
session_start();
require_once "config.php";
require_once __DIR__ . '/vendor/autoload.php'; // dompdf

use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$razorpay_payment_id = $_POST['razorpay_payment_id'] ?? '';
$razorpay_order_id   = $_POST['razorpay_order_id'] ?? '';
$razorpay_signature  = $_POST['razorpay_signature'] ?? '';

if (empty($razorpay_payment_id) || empty($razorpay_order_id) || empty($razorpay_signature)) {
    die("Payment parameters missing.");
}

// Verify signature
$generated_signature = hash_hmac('sha256', $razorpay_order_id . "|" . $razorpay_payment_id, RAZORPAY_KEY_SECRET);

if ($generated_signature !== $razorpay_signature) {
    // signature mismatch — possible fraud
    // update DB with failed status
    $stmt = $conn->prepare("UPDATE admissions SET payment_status = 'failed', razorpay_payment_id = ?, razorpay_signature = ? WHERE razorpay_order_id = ?");
    $stmt->bind_param("sss", $razorpay_payment_id, $razorpay_signature, $razorpay_order_id);
    $stmt->execute();
    $stmt->close();
    die("Payment verification failed. Signature mismatch.");
}

// Signature OK — mark paid
$stmt = $conn->prepare("UPDATE admissions SET payment_status = 'paid', razorpay_payment_id = ?, razorpay_signature = ? WHERE razorpay_order_id = ?");
$stmt->bind_param("sss", $razorpay_payment_id, $razorpay_signature, $razorpay_order_id);
$stmt->execute();
$stmt->close();

// Fetch admission record to generate PDFs
$stmt = $conn->prepare("SELECT id, full_name, email, phone, course_name, reg_fee, duration, dob, address, permanent_address FROM admissions WHERE razorpay_order_id = ?");
$stmt->bind_param("s", $razorpay_order_id);
$stmt->execute();
$stmt->bind_result($id, $full_name, $email, $phone, $course_name, $reg_fee, $duration, $dob, $address, $permanent_address);
$stmt->fetch();
$stmt->close();

// Generate Bill PDF (invoice)
$invoice_html = '
<html><body>
<h1>Payment Receipt / Invoice</h1>
<p><strong>Admission ID:</strong> ' . htmlspecialchars($id) . '</p>
<p><strong>Name:</strong> ' . htmlspecialchars($full_name) . '</p>
<p><strong>Course:</strong> ' . htmlspecialchars($course_name) . '</p>
<p><strong>Amount Paid:</strong> INR ' . number_format($reg_fee,2) . '</p>
<p><strong>Payment ID:</strong> ' . htmlspecialchars($razorpay_payment_id) . '</p>
<p><strong>Payment Method:</strong> Razorpay (Online)</p>
<p><strong>Date:</strong> ' . date('d-m-Y H:i') . '</p>
</body></html>';

// Generate Admission Form PDF (summary of submission)
$admission_html = '
<html><body>
<h1>Admission Form — Summary</h1>
<p><strong>Admission ID:</strong> ' . htmlspecialchars($id) . '</p>
<p><strong>Name:</strong> ' . htmlspecialchars($full_name) . '</p>
<p><strong>DOB:</strong> ' . htmlspecialchars($dob) . '</p>
<p><strong>Phone:</strong> ' . htmlspecialchars($phone) . '</p>
<p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
<p><strong>Address:</strong> ' . nl2br(htmlspecialchars($address)) . '</p>
<p><strong>Permanent Address:</strong> ' . nl2br(htmlspecialchars($permanent_address)) . '</p>
<p><strong>Course:</strong> ' . htmlspecialchars($course_name) . '</p>
</body></html>';

// Dompdf options
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Invoice PDF
$dompdf->loadHtml($invoice_html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$invoice_output = $dompdf->output();
$invoice_path = __DIR__ . "/pdfs/invoice_admission_{$id}.pdf";
file_put_contents($invoice_path, $invoice_output);

// Admission PDF
$dompdf->loadHtml($admission_html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$admission_output = $dompdf->output();
$admission_path = __DIR__ . "/pdfs/admission_form_{$id}.pdf";
file_put_contents($admission_path, $admission_output);

// Save PDF paths to DB (optional)
$stmt = $conn->prepare("UPDATE admissions SET invoice_pdf = ?, admission_pdf = ? WHERE id = ?");
$invoice_file_db = "pdfs/invoice_admission_{$id}.pdf";
$admission_file_db = "pdfs/admission_form_{$id}.pdf";
$stmt->bind_param("ssi", $invoice_file_db, $admission_file_db, $id);
$stmt->execute();
$stmt->close();

// Unset session keys if you want
unset($_SESSION['amount']);
// Show success and links
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Payment Successful</title></head>
<body>
    <h2>Payment Successful ✅</h2>
    <p>Admission ID: <strong><?php echo htmlspecialchars($id); ?></strong></p>
    <p>Name: <strong><?php echo htmlspecialchars($full_name); ?></strong></p>
    <p>Amount Paid: <strong>INR <?php echo number_format($reg_fee,2); ?></strong></p>

    <p>
        <a href="<?php echo htmlspecialchars($invoice_file_db); ?>" target="_blank">Download Invoice (PDF)</a>
    </p>
    <p>
        <a href="<?php echo htmlspecialchars($admission_file_db); ?>" target="_blank">Download Admission Form (PDF)</a>
    </p>

    <p><a href="index.php">Back to Home</a></p>
</body>
</html>
