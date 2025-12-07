<?php
include "db_connect.php";

$fee_id = $_GET['id'];
$conn->prepare("UPDATE fee_master SET status='paid' WHERE id=?")->execute([$fee_id]);

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
