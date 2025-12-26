<?php
session_start();
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $enroll = trim($_POST['enrollment_id']);
    $pass   = trim($_POST['password']);

    $q = $conn->prepare("SELECT * FROM students26 WHERE enrollment_id=? LIMIT 1");
    $q->bind_param("s", $enroll);
    $q->execute();
    $res = $q->get_result();

    if ($res->num_rows === 1) {

        $row = $res->fetch_assoc();

        // ✅ Password verify
        if (password_verify($pass, $row['password'])) {

            $_SESSION['student_enroll'] = $row['enrollment_id'];
            $_SESSION['student_name']   = $row['name'];

            header("Location: student_dashboard.php");
            exit;
        }
    }

    // ❌ Login failed
    echo "<script>
            alert('Invalid Enrollment ID or Password');
            window.location='student_login.php';
          </script>";
}
?>
