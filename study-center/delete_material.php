
<?php
include '../database_connection/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}
$material_id = intval($_GET['id']);

// Delete material
$conn->query("DELETE FROM study_materials WHERE id = $material_id");
$conn->query("DELETE FROM study_material_targets WHERE material_id = $material_id");

header("Location: view_materials_admin.php?msg=deleted");
exit;
?>