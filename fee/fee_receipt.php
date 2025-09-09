<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fee Receipt - Faiz Computer Institute</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
  body { 
    background: #f0f2f5; 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  .receipt-container {
    max-width: 800px;
    margin: 30px auto;
    background: #fff;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    overflow: hidden;
  }
  .receipt-header {
    background: linear-gradient(135deg, #1a237e, #283593);
    color: white;
    padding: 25px;
    text-align: center;
    border-bottom: 5px solid #ffc107;
  }
  .institute-name {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 5px;
    letter-spacing: 1px;
  }
  .institute-tagline {
    font-size: 14px;
    opacity: 0.9;
    margin-bottom: 15px;
  }
  .receipt-body {
    padding: 30px;
  }
  .receipt-title {
    text-align: center;
    font-size: 24px;
    margin-bottom: 25px;
    color: #1a237e;
    border-bottom: 2px dashed #e0e0e0;
    padding-bottom: 15px;
  }
  .student-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 25px;
  }
  .student-details {
    flex: 2;
  }
  .student-photo-container {
    flex: 1;
    text-align: center;
  }
  .student-photo {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 5px;
    border: 3px solid #e0e0e0;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  }
  .detail-item {
    margin-bottom: 8px;
    display: flex;
  }
  .detail-label {
    font-weight: 600;
    min-width: 120px;
    color: #555;
  }
  .fee-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
  }
  .fee-table th {
    background-color: #f5f5f5;
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
  }
  .fee-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
  }
  .fee-table tr:last-child td {
    border-bottom: none;
  }
  .total-row {
    font-weight: bold;
    background-color: #f9f9f9;
  }
  .payment-details {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
    border-left: 4px solid #283593;
  }
  .thank-you {
    text-align: center;
    margin-top: 30px;
    font-style: italic;
    color: #555;
  }
  .receipt-footer {
    text-align: center;
    padding: 20px;
    background: #f5f5f5;
    border-top: 1px dashed #ddd;
    font-size: 14px;
    color: #777;
  }
  .watermark {
    position: absolute;
    opacity: 0.05;
    font-size: 120px;
    transform: rotate(-45deg);
    pointer-events: none;
    z-index: -1;
    font-weight: bold;
    color: #1a237e;
    top: 40%;
    left: 15%;
  }
  @media print {
    body { 
      background: #fff; 
      font-size: 14px;
    }
    .receipt-container {
      box-shadow: none;
      margin: 0;
      max-width: 100%;
    }
    .no-print { 
      display: none; 
    }
    .receipt-header {
      background: #1a237e !important;
      color: #000;
      -webkit-print-color-adjust: exact;
    }
  }
</style>
</head>
<body>
<div class="receipt-container">
  <div class="receipt-header">
    <div class="institute-name">FAIZ COMPUTER INSTITUTE</div>
    <div class="institute-tagline">Empowering Minds Through Technology</div>
    <div>Contact: +91 XXXXX XXXXX | Email: info@faizcomputerinstitute.com</div>
  </div>
  
  <div class="receipt-body">
    <div class="watermark">FAIZ COMPUTER INSTITUTE</div>
    
    <div class="receipt-title">
      <i class="fas fa-receipt me-2"></i> FEE PAYMENT RECEIPT
    </div>
    
    <div class="student-info">
      <div class="student-details">
        <div class="detail-item">
          <span class="detail-label">Student Name:</span>
          <span><?php echo htmlspecialchars($student['name']); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Enrollment No:</span>
          <span><?php echo htmlspecialchars($student['student_id']); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Course:</span>
          <span><?php echo htmlspecialchars($student['course']); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Receipt Date:</span>
          <span><?php echo date('d/m/Y'); ?></span>
        </div>
      </div>
      
      <div class="student-photo-container">
        <?php if(!empty($student['photo']) && file_exists("../uploads/".$student['photo'])): ?>
          <img src="../uploads/<?php echo htmlspecialchars($student['photo']); ?>" class="student-photo" alt="Student Photo">
        <?php else: ?>
          <img src="https://via.placeholder.com/120x120?text=No+Image" class="student-photo" alt="No Photo Available">
        <?php endif; ?>
      </div>
    </div>
    
    <table class="fee-table">
      <thead>
        <tr>
          <th>Description</th>
          <th>Amount (₹)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?php echo htmlspecialchars($fee_type); ?></td>
          <td>₹<?php echo number_format($fee_amount, 2); ?></td>
        </tr>
        <tr class="total-row">
          <td>Total Amount Paid</td>
          <td>₹<?php echo number_format($fee_amount, 2); ?></td>
        </tr>
      </tbody>
    </table>
    
    <div class="payment-details">
      <div class="detail-item">
        <span class="detail-label">Payment Date:</span>
        <span><?php echo htmlspecialchars($payment_date); ?></span>
      </div>
      <div class="detail-item">
        <span class="detail-label">Payment Mode:</span>
        <span>Cash</span> <!-- You can update this if you have payment mode in your database -->
      </div>
      <div class="detail-item">
        <span class="detail-label">Transaction ID:</span>
        <span>FCI<?php echo date('Ymd').$student_id; ?></span> <!-- Generated transaction ID -->
      </div>
    </div>
    
    <div class="thank-you">
      <p>Thank you for your payment. This computer generated receipt is valid without signature.</p>
      <p><i class="fas fa-shield-alt me-2"></i> Payment secured by Faiz Computer Institute</p>
    </div>
  </div>
  
  <div class="receipt-footer">
    <div>FAIZ COMPUTER INSTITUTE • Address: Main Road, City, State - PINCODE</div>
    <div>Contact: +91 XXXXX XXXXX • Website: www.faizcomputerinstitute.com</div>
    <div class="mt-2"><?php echo date('l, F j, Y, h:i A'); ?></div>
  </div>
</div>

<div class="text-center mt-3 no-print">
  <button class="btn btn-primary" onclick="window.print()">
    <i class="fas fa-print me-2"></i> Print Receipt
  </button>
  <a href="../dashboard.php" class="btn btn-secondary ms-2">
    <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
  </a>
</div>

</body>
</html>