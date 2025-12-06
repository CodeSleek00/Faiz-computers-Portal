<?php
include "config.php";

$result = mysqli_query($conn, "SELECT * FROM admissions ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Admissions List</title>
<style>
table { width: 100%; border-collapse: collapse; }
th, td { border:1px solid black; padding: 8px; }
img { width: 80px; }
</style>
</head>
<body>

<h2>All Admission Forms</h2>

<table>
<tr>
<th>Photo</th>
<th>Name</th>
<th>Phone</th>
<th>Course</th>
<th>Date</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)) { ?>
<tr>
<td><img src="uploads/<?php echo $row['photo']; ?>"></td>
<td><?php echo $row['full_name']; ?></td>
<td><?php echo $row['phone']; ?></td>
<td><?php echo $row['course_name']; ?></td>
<td><?php echo $row['created_at']; ?></td>
</tr>
<?php } ?>

</table>

</body>
</html>
