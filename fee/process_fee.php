<?php
// process_fee.php
include 'db_connect.php';
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    $enrolled_id = intval($_GET['enrolled_id']);
    $schedule_id = intval($_GET['schedule_id']);
    $studentQ = $conn->prepare("SELECT * FROM student_enrolled WHERE enrolled_id=?");
    $studentQ->bind_param("i",$enrolled_id); $studentQ->execute(); $student = $studentQ->get_result()->fetch_assoc();
    $sQ = $conn->prepare("SELECT * FROM fee_schedule WHERE schedule_id=?");
    $sQ->bind_param("i",$schedule_id); $sQ->execute(); $schedule = $sQ->get_result()->fetch_assoc();
}
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $enrolled_id = intval($_POST['enrolled_id']);
    $schedule_id = intval($_POST['schedule_id']);
    $amount = floatval($_POST['amount']);
    $mode = $_POST['mode'] ?? 'cash';
    $reference_no = $_POST['reference_no'] ?? null;
    $phone = $_POST['phone'] ?? null;

    // handle proof upload
    $proof_path = null;
    if(isset($_FILES['proof']) && $_FILES['proof']['error']===0){
        if(!is_dir('uploads/proof')) mkdir('uploads/proof',0755,true);
        $ext = pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION);
        $fn = 'uploads/proof/'.time().'_'.rand(100,999).'.'.$ext;
        move_uploaded_file($_FILES['proof']['tmp_name'],$fn);
        $proof_path = $fn;
    }

    $ins = $conn->prepare("INSERT INTO payments (enrolled_id,schedule_id,amount,mode,reference_no,phone_sent_sms,proof_path,status) VALUES (?,?,?,?,?,?,?,?)");
    $sms_flag = 0;
    $status = 'verified';
    $ins->bind_param("iiississ",$enrolled_id,$schedule_id,$amount,$mode,$reference_no,$sms_flag,$proof_path,$status);
    $ins->execute();
    $payment_id = $ins->insert_id;

    // mark schedule paid
    $upd = $conn->prepare("UPDATE fee_schedule SET status='paid', amount=? WHERE schedule_id=?");
    $upd->bind_param("di",$amount,$schedule_id);
    $upd->execute();

    // generate receipt (call generate_receipt.php or include code)
    // we'll generate simple PDF using mPDF
    require_once __DIR__ . '/vendor/autoload.php';
    $mpdf = new \Mpdf\Mpdf();
    // fetch student and schedule for printing
    $studentQ = $conn->prepare("SELECT * FROM student_enrolled WHERE enrolled_id=?");
    $studentQ->bind_param("i",$enrolled_id); $studentQ->execute(); $student = $studentQ->get_result()->fetch_assoc();
    $sQ = $conn->prepare("SELECT * FROM fee_schedule WHERE schedule_id=?");
    $sQ->bind_param("i",$schedule_id); $sQ->execute(); $schedule = $sQ->get_result()->fetch_assoc();

    $receipt_no = 'FCI-'.date('Ymd').'-'.$payment_id;
    $html = '<h2>Faiz Computer Institute</h2>';
    $html .= '<p><b>Receipt No:</b> '.$receipt_no.'<br>';
    $html .= '<b>Name:</b> '.htmlspecialchars($student['name']).'<br>';
    $html .= '<b>Enrollment:</b> '.htmlspecialchars($student['enrollment_no']).'<br>';
    $html .= '<b>Item:</b> '.htmlspecialchars($schedule['item_name']).' ('.htmlspecialchars($schedule['item_type']).')<br>';
    $html .= '<b>Amount:</b> ₹'.number_format($amount,2).'</p>';
    $mpdf->WriteHTML($html);
    if(!is_dir('receipts')) mkdir('receipts',0755,true);
    $path = 'receipts/receipt_'.$payment_id.'.pdf';
    $mpdf->Output($path, \Mpdf\Output\Destination::FILE);

    // insert into receipts table
    $insr = $conn->prepare("INSERT INTO receipts (payment_id,receipt_no,receipt_path) VALUES (?,?,?)");
    $insr->bind_param("iss",$payment_id,$receipt_no,$path);
    $insr->execute();

    // send SMS if phone provided
    if(!empty($phone)){
        // send_sms_msg91($phone, "Your payment of ₹{$amount} received. Receipt: ".( (isset($_SERVER['HTTP_HOST'])? 'https://'.$_SERVER['HTTP_HOST'].'/' : '' ) . $path) );
        // We'll call a helper function below. Replace keys inside function.
        $sms_sent = send_sms_msg91($phone, "Dear {$student['name']}, your payment of ₹{$amount} for {$schedule['item_name']} received. Receipt no: {$receipt_no}.");
        if($sms_sent) {
            $conn->query("UPDATE payments SET phone_sent_sms=1 WHERE payment_id={$payment_id}");
        }
    }

    header("Location: receipts/receipt_{$payment_id}.pdf");
    exit;
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Pay</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head>
<body class="p-4">
<div class="container">
  <?php if($_SERVER['REQUEST_METHOD'] === 'GET'): ?>
    <h4>Pay: <?php echo htmlspecialchars($schedule['item_name']); ?></h4>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="enrolled_id" value="<?php echo $student['enrolled_id']; ?>">
      <input type="hidden" name="schedule_id" value="<?php echo $schedule['schedule_id']; ?>">
      <div class="mb-2"><label>Amount (₹)</label><input name="amount" required class="form-control" value="<?php echo htmlspecialchars($schedule['amount'] ?: '0'); ?>"></div>
      <div class="mb-2"><label>Mode</label>
        <select name="mode" class="form-control">
          <option>cash</option><option>upi</option><option>bank</option><option>cheque</option>
        </select>
      </div>
      <div class="mb-2"><label>Reference No (Optional)</label><input name="reference_no" class="form-control"></div>
      <div class="mb-2"><label>Phone (for SMS)</label><input name="phone" class="form-control" value="<?php echo htmlspecialchars($student['phone']); ?>"></div>
      <div class="mb-2"><label>Upload Proof (Optional)</label><input type="file" name="proof" class="form-control"></div>
      <button class="btn btn-success">Submit Payment & Generate Receipt</button>
    </form>
  <?php endif; ?>
</div>
</body></html>

<?php
// helper function for SMS (MSG91) - replace AUTH KEY, ROUTE, SENDER
function send_sms_msg91($mobile, $message){
    $authKey = "MSG91_AUTH_KEY"; // replace
    $sender = "FAIZIN"; // 6 char sender id approved on DLT ideally
    $route = "4";
    $postData = array(
        'sender' => $sender,
        'route' => $route,
        'country' => '91',
        'sms' => json_encode(array(array("message"=> $message, "to"=> array($mobile))))
    );
    $url="https://api.msg91.com/api/v5/send/"; // new MSG91 endpoint (may change) - replace if necessary
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => array(
            "authkey: $authKey",
            "Content-Type: application/json"
        ),
    ));
    $output = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if($err) return false;
    $resp = json_decode($output, true);
    // basic success check (adjust per provider response)
    if(isset($resp['type']) && $resp['type']=='success') return true;
    return false;
}
?>
