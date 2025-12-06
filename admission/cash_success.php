<?php
// cash_success.php
session_start();
require_once "config.php";
require_once __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['admission_id'])) {
    die("No admission found in session.");
}
$admission_id = intval($_SESSION['admission_id']);

// Mark payment status as pending (cash)
$stmt = $conn->prepare("UPDATE admissions SET payment_status = 'pending', payment_note = 'Student chose cash. Please collect at office.' WHERE id = ?");
$stmt->bind_param("i", $admission_id);
$stmt->execute();
$stmt->close();

// Fetch record
$stmt = $conn->prepare("SELECT id, full_name, email, phone, course_name, reg_fee FROM admissions WHERE id = ?");
$stmt->bind_param("i", $admission_id);
$stmt->execute();
$stmt->bind_result($id, $full_name, $email, $phone, $course_name, $reg_fee);
$stmt->fetch();
$stmt->close();

// Generate basic receipt (shows pending)
$receipt_html = '
<html><body>
<h1>Cash Payment Instruction / Receipt</h1>
<p><strong>Admission ID:</strong> ' . htmlspecialchars($id) . '</p>
<p><strong>Name:</strong> ' . htmlspecialchars($full_name) . '</p>
<p><strong>Course:</strong> ' . htmlspecialchars($course_name) . '</p>
<p><strong>Amount Due:</strong> INR ' . number_format($reg_fee,2) . '</p>
<p><strong>Payment Method:</strong> Cash (Pay at Office)</p>
<p><strong>Note:</strong> Please bring this receipt and pay at the admissions desk. After payment, admin will update status.</p>
<p><strong>Date:</strong> ' . date('d-m-Y H:i') . '</p>
</body></html>';

// Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$dompdf->loadHtml($receipt_html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$receipt_output = $dompdf->output();
$receipt_path = __DIR__ . "/pdfs/cash_receipt_admission_{$id}.pdf";
file_put_contents($receipt_path, $receipt_output);

// Save path to DB
$stmt = $conn->prepare("UPDATE admissions SET invoice_pdf = ? WHERE id = ?");
$receipt_file_db = "pdfs/cash_receipt_admission_{$id}.pdf";
$stmt->bind_param("si", $receipt_file_db, $id);
$stmt->execute();
$stmt->close();

?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Cash Payment</title></head>
<body>
    <h2>Cash Selected — Please pay at admissions desk</h2>
    <p>Admission ID: <strong><?php echo htmlspecialchars($id); ?></strong></p>
    <p>Amount Due: <strong>INR <?php echo number_format($reg_fee,2); ?></strong></p>

    <p><a href="<?php echo htmlspecialchars($receipt_file_db); ?>" target="_blank">Download Cash Receipt (PDF)</a></p>
    <p>After you pay cash at the office, admin can mark the record as <strong>paid</strong> in the admin panel.</p>

    <p><a href="index.php">Back to Home</a></p>
</body>
</html>
