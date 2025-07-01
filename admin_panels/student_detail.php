<?php
include '../database_connection/db_connect.php';

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM my_student WHERE student_id = $id");
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Details - <?= htmlspecialchars($row['first_name']) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      margin: 0;
      padding: 40px 20px;
      background-color: #f5f7fa;
      color: #333;
    }

    .container {
      max-width: 700px;
      margin: 0 auto;
      background-color: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    .profile-photo {
      text-align: center;
      margin-bottom: 20px;
    }

    .profile-photo img {
      width: 130px;
      height: 130px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid #3498db;
    }

    h2 {
      text-align: center;
      margin-bottom: 10px;
      font-size: 1.8rem;
      color: #2c3e50;
    }

    .info {
      margin-top: 25px;
    }

    .info p {
      font-size: 1rem;
      padding: 10px 15px;
      border-bottom: 1px solid #eee;
    }

    .info p strong {
      width: 160px;
      display: inline-block;
      color: #555;
    }

    .edit-link {
      display: inline-block;
      text-align: center;
      margin-top: 25px;
      padding: 10px 16px;
      background-color: #2d89ef;
      color: white;
      text-decoration: none;
      font-weight: 500;
      border-radius: 6px;
      transition: background 0.2s ease-in-out;
    }

    .edit-link:hover {
      background-color: #216dd8;
    }

    @media (max-width: 600px) {
      .info p strong {
        display: block;
        margin-bottom: 4px;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <div class="profile-photo">
      <img src="../uploads/<?= htmlspecialchars($row['photo']) ?>" alt="Student Photo">
    </div>

    <h2><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></h2>

    <div class="info">
      <p><strong>Course:</strong> <?= htmlspecialchars($row['course']) ?></p>
      <p><strong>Address:</strong> <?= htmlspecialchars($row['address']) ?></p>
      <p><strong>Phone:</strong> <?= htmlspecialchars($row['phone_no']) ?></p>
      </div>

    <div style="text-align:center;">
      <a class="edit-link" href="edit_student.php?id=<?= $row['student_id'] ?>">✏️ Edit This Student</a>
    </div>
  </div>

</body>
</html>
