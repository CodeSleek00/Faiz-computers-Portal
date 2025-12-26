session_start();
include("db_connect.php");

$enroll = $_POST['enrollment_id'];
$pass   = $_POST['password'];

$q = $conn->prepare("SELECT * FROM students WHERE enrollment_id=?");
$q->bind_param("s", $enroll);
$q->execute();
$res = $q->get_result();

if ($res->num_rows == 1) {
    $row = $res->fetch_assoc();

    if (password_verify($pass, $row['password'])) {
        $_SESSION['student_enroll'] = $row['enrollment_id'];
        $_SESSION['student_name']   = $row['name'];
        header("Location: student_dashboard.php");
        exit;
    }
}

echo "Invalid Login Details";
