<?php
include '../database_connection/db_connect.php';

// Search functionality
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $sql = "SELECT * FROM students WHERE 
            name LIKE ? OR 
            contact_number LIKE ? OR 
            course LIKE ? OR
            enrollment_id LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%$search%";
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm , $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM students");
}
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
    
    .controls {
        display: flex;
        justify-content: flex-start;
        margin-bottom: 1.5rem;
        gap: 0.5rem;
    }
    
    input[type="text"] {
        padding: 0.5rem 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: 'Inter', sans-serif;
        width: 300px;
    }
    
    button {
        padding: 0.5rem 1rem;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        transition: background-color 0.2s;
    }
    
    button:hover {
        background-color: #45a049;
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
    
    .student-photo {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 50%;
        border: 1px solid #eee;
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .student-photo:hover {
        transform: scale(1.1);
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
    
    .reset-btn {
        background-color: #f44336;
        color: white;
    }
    
    .btn:hover {
        opacity: 0.8;
        transform: translateY(-1px);
    }
    
    .actions-cell {
        white-space: nowrap;
    }
    
    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.7);
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        max-width: 500px;
        width: 90%;
        text-align: center;
        position: relative;
    }
    
    .modal-img {
        max-width: 100%;
        max-height: 70vh;
        border-radius: 4px;
    }
    
    .close-modal {
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 28px;
        font-weight: bold;
        color: #aaa;
        cursor: pointer;
    }
    
    .close-modal:hover {
        color: #333;
    }
</style>
</head>
<body>
    <h2>All Students</h2>
    
    <div class="controls">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search by name, contact or enrollment ID , Course " value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
            <?php if (!empty($search)): ?>
                <a href="?" class="btn reset-btn">Reset</a>
            <?php endif; ?>
        </form>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Enrollment ID</th>
                <th>Course</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td>
                    <img src="../uploads/<?= htmlspecialchars($row['photo']) ?>" 
                         alt="Student photo" 
                         class="student-photo"
                         onclick="openModal('../uploads/<?= htmlspecialchars($row['photo']) ?>')">
                </td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['contact_number']) ?></td>
                <td><?= htmlspecialchars($row['enrollment_id']) ?></td>
                <td><?= htmlspecialchars($row['course']) ?></td>
                <td class="actions-cell">
                    <a class="btn view-btn" href="view_student.php?id=<?= $row['student_id'] ?>">View</a>
                    <a class="btn edit-btn" href="edit_student.php?id=<?= $row['student_id'] ?>">Edit</a>
                    <a class="btn delete-btn" href="delete_student.php?id=<?= $row['student_id'] ?>" onclick="return confirm('Are you sure to delete this student?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    
    <!-- Image Preview Modal -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <img id="modalImage" class="modal-img" src="" alt="Preview">
        </div>
    </div>
    
    <script>
        // Modal functions
        function openModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
        
        // Close modal when clicking outside the image
        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>