<?php
include '../database_connection/db_connect.php';

// Fetch all students
$result = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
   <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');
    
    body {
        font-family: 'Inter', sans-serif;
        background-color: #fafafa;
        padding: 2rem;
        color: #333;
        line-height: 1.5;
    }
    
    h2 {
        font-weight: 600;
        color: #222;
        margin-bottom: 1.5rem;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        border-radius: 8px;
        overflow: hidden;
    }
    
    th, td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    th {
        font-weight: 500;
        background-color: #f7f7f7;
        color: #555;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    
    tr:hover {
        background-color: #f9f9f9;
    }
    
    img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 50%;
        border: 1px solid #eee;
    }
    
    .btn {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        text-decoration: none;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 500;
        margin-right: 0.5rem;
        transition: all 0.2s ease;
    }
    
    .view-btn { 
        background-color: transparent;
        color: #4CAF50;
        border: 1px solid #4CAF50;
    }
    
    .edit-btn { 
        background-color: transparent;
        color: #FF9800;
        border: 1px solid #FF9800;
    }
    
    .delete-btn { 
        background-color: transparent;
        color: #F44336;
        border: 1px solid #F44336;
    }
    
    .btn:hover {
        opacity: 0.8;
        transform: translateY(-1px);
    }
    
    .actions-cell {
        white-space: nowrap;
    }
</style>
</head>
<body>
    <h2>All Students</h2>
    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Enrollment ID</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><img src="../uploads/<?= $row['photo'] ?>" alt="photo"></td>
                <td><?= $row['name'] ?></td>
                <td><?= $row['contact_number'] ?></td>
                <td><?= $row['enrollment_id'] ?></td>
                <td>
                    <a class="btn view-btn" href="view_student.php?id=<?= $row['student_id'] ?>">View</a>
                    <a class="btn edit-btn" href="edit_student.php?id=<?= $row['student_id'] ?>">Edit</a>
                    <a class="btn delete-btn" href="delete_student.php?id=<?= $row['student_id'] ?>" onclick="return confirm('Are you sure to delete this student?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
