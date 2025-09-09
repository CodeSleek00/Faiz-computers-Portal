<?php
// admin_fee_dashboard.php
include '../database_connection/db_connect.php'; // ye file $conn define karegi

if (!$conn) {
    die("Database connection not found");
}

// Students aur unki fees fetch karna
$sql = "SELECT sf.id, sf.student_id, s.name AS student_name, s.course, s.photo,
               sf.total_fee, 
               (sf.internal1 + sf.internal2 + sf.semester1 + sf.semester2 + 
                sf.month_jan + sf.month_feb + sf.month_mar + sf.month_apr + 
                sf.month_may + sf.month_jun + sf.month_jul + sf.month_aug + 
                sf.month_sep + sf.month_oct + sf.month_nov + sf.month_dec
               ) AS paid_fee
        FROM student_fees sf
        JOIN students s ON sf.student_id = s.student_id
        ORDER BY sf.student_id ASC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Fee Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-white: #ffffff;
      --primary-blue: #1a56db;
      --light-blue: #e1effe;
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8fafc;
      color: #1f2937;
    }
    
    .container-main {
      background-color: var(--primary-white);
      border-radius: 12px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      padding: 2rem;
      margin-top: 2rem;
    }
    
    .header-section {
      background: linear-gradient(135deg, var(--primary-blue) 0%, #1e40af 100%);
      color: white;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .student-photo {
      width: 50px;
      height: 50px;
      object-fit: cover;
      border-radius: 50%;
      border: 2px solid var(--light-blue);
    }
    
    .btn-space {
      margin-bottom: 2px;
    }
    
    .table th {
      background-color: var(--light-blue);
      font-weight: 600;
    }
    
    .search-box {
      background-color: var(--primary-white);
      border-radius: 8px;
      padding: 1.5rem;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .btn-primary {
       background-color: #dce4ffff;
      border: 1px solid var(--primary-blue)
    }
    
    .btn-primary:hover {
      background-color: var(--primary-blue);
      border: 1px solid var(--primary-blue);
    }
    
    .password-modal {
      font-family: 'Inter', sans-serif;
    }
    
    .password-modal .modal-content {
      border-radius: 12px;
    }
    
    .stats-card {
      background-color: var(--primary-white);
      border-radius: 8px;
      padding: 1rem;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      border-left: 4px solid var(--primary-blue);
    }
    
    @media (max-width: 768px) {
      .container-main {
        padding: 1rem;
      }
      
      .header-section {
        padding: 1rem;
      }
      
      .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
      }
    }
  </style>
</head>
<body>
  <!-- Password Modal -->
  <div class="modal fade password-modal" id="passwordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Admin Authentication Required</h5>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="passwordInput" class="form-label">Enter Admin Password</label>
            <input type="password" class="form-control" id="passwordInput" placeholder="Password">
            <div id="passwordError" class="form-text text-danger d-none">Incorrect password. Please try again.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="submitPassword">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <div class="container my-4 container-main">
    <div class="header-section">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h2 class="mb-1">Student Fee Dashboard</h2>
          <p class="mb-0">Manage and track student fee payments</p>
        </div>
        <div class="col-md-4 text-md-end">
          <button class="btn btn-outline-light" id="lockButton">
            <i class="bi bi-lock"></i> Lock Dashboard
          </button>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
      <div class="col-md-4 mb-3">
        <div class="stats-card">
          <h6 class="text-muted">Total Students</h6>
          <h3><?php echo $result->num_rows; ?></h3>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="stats-card">
          <h6 class="text-muted">Total Fees Collected</h6>
          <h3>
            <?php
            $total_collected = 0;
            if ($result->num_rows > 0) {
              $result->data_seek(0); // Reset pointer
              while($row = $result->fetch_assoc()) {
                $total_collected += $row['paid_fee'];
              }
              $result->data_seek(0); // Reset pointer again for main display
            }
            echo '₹' . number_format($total_collected, 2);
            ?>
          </h3>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="stats-card">
          <h6 class="text-muted">Pending Collections</h6>
          <h3>
            <?php
            $total_pending = 0;
            if ($result->num_rows > 0) {
              $result->data_seek(0); // Reset pointer
              while($row = $result->fetch_assoc()) {
                $total_pending += ($row['total_fee'] - $row['paid_fee']);
              }
              $result->data_seek(0); // Reset pointer again for main display
            }
            echo '₹' . number_format($total_pending, 2);
            ?>
          </h3>
        </div>
      </div>
    </div>

    <!-- Search by Student ID or Name -->
    <div class="search-box mb-4">
      <form method="get">
        <div class="row g-2">
          <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by ID or Name" 
                  value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Search</button>
          </div>
          <div class="col-md-6 text-md-end">
            <a href="../admin_dashboard.php" class="btn btn-outline-primary">Main Dashboard</a>
          </div>
        </div>
      </form>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover bg-white align-middle">
        <thead class="table-light text-center">
          <tr>
            <th>Photo</th>
            <th>Student ID</th>
            <th>Name</th>
            <th>Course</th>
            <th>Total Fee</th>
            <th>Paid Fee</th>
            <th>Balance</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $has_result = false;
          if ($result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
              // Search filter
              if (isset($_GET['search']) && $_GET['search'] !== '') {
                  $search = strtolower($_GET['search']);
                  if (strpos(strtolower($row['student_id']), $search) === false &&
                      strpos(strtolower($row['student_name']), $search) === false) {
                      continue; // Skip non-matching
                  }
              }
              $has_result = true;
              $balance = $row['total_fee'] - $row['paid_fee'];
          ?>
              <tr>
                <td class="text-center">
                  <?php if (!empty($row['photo']) && file_exists("../uploads/" . $row['photo'])): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($row['photo']); ?>" 
                        alt="Photo" class="student-photo">
                  <?php else: ?>
                    <img src="https://via.placeholder.com/50?text=No+Image" alt="No Photo" class="student-photo">
                  <?php endif; ?>
                </td>
                <td class="fw-bold"><?php echo htmlspecialchars($row['student_id']); ?></td>
                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                <td><?php echo htmlspecialchars($row['course']); ?></td>
                <td class="text-end fw-bold">₹<?php echo number_format($row['total_fee'], 2); ?></td>
                <td class="text-end text-success">₹<?php echo number_format($row['paid_fee'], 2); ?></td>
                <td class="text-end <?php echo $balance > 0 ? 'text-danger' : 'text-success'; ?>">
                  ₹<?php echo number_format($balance, 2); ?>
                </td>
                <td class="text-center">
                  <div class="d-flex flex-wrap gap-1 justify-content-center">
                    <a href="show_fee.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary btn-space">Show Fee</a>
                    <a href="admin_fee_main.php?student_id=<?php echo $row['student_id']; ?>" class="btn btn-sm btn-success">Submit Fee</a>
                    <a href="complete_course.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger btn-space"
                      onclick="return confirm('Are you sure you want to mark this course as completed and delete fee record?')">
                      Complete Course
                    </a>
                  </div>
                </td>
              </tr>
          <?php
            endwhile;
          endif;

          if (!$has_result):
          ?>
            <tr><td colspan="8" class="text-center py-4">No records found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Password protection system
      const passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'));
      const correctPassword = "admin123"; // Change this to your desired password
      
      // Check if already authenticated
      if (!sessionStorage.getItem('authenticated')) {
        passwordModal.show();
      }
      
      // Submit password handler
      document.getElementById('submitPassword').addEventListener('click', function() {
        const inputPassword = document.getElementById('passwordInput').value;
        const errorElement = document.getElementById('passwordError');
        
        if (inputPassword === correctPassword) {
          sessionStorage.setItem('authenticated', 'true');
          passwordModal.hide();
        } else {
          errorElement.classList.remove('d-none');
          document.getElementById('passwordInput').value = '';
        }
      });
      
      // Lock dashboard functionality
      document.getElementById('lockButton').addEventListener('click', function() {
        sessionStorage.removeItem('authenticated');
        passwordModal.show();
      });
      
      // Allow Enter key to submit password
      document.getElementById('passwordInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          document.getElementById('submitPassword').click();
        }
      });
    });
  </script>
</body>
</html>