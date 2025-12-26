
session_start();
if (!isset($_SESSION['student_enroll'])) {
    header("Location: student_login.php");
    exit;
}
