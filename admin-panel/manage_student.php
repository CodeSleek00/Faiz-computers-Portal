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
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM students");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: #fafafa; padding: 2rem; margin: 0; color: #333; line-height: 1.5; }
        h2 { font-weight: 600; color: #222; margin-bottom: 1.5rem; text-align: center; }
        .controls { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-bottom: 1.5rem; }

        input[type="text"] {
            padding: 0.5rem 0.75rem; border: 1px solid #ddd; border-radius: 6px;
            width: 300px; font-size: 15px;
        }

        button {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: 0.2s;
        }

        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
        }

        .reset-btn {
            background-color: #f44336;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            display: inline-block;
        }

        .reset-btn:hover { background-color: #d32f2f; }
        button:hover { opacity: 0.9; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: auto;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 15px;
        }

        th {
            background-color: #f7f7f7;
            text-transform: uppercase;
            font-weight: 600;
            font-size: 13px;
            color: #555;
        }

        tr:hover { background-color: #f9f9f9; }

        .student-photo {
            width: 40px; height: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: 1px solid #eee;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .student-photo:hover { transform: scale(1.1); }

        .btn {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            margin: 2px;
            transition: 0.2s ease;
        }

        .view-btn { background-color: transparent; color: #4CAF50; border: 1px solid #4CAF50; }
        .edit-btn { background-color: transparent; color: #FF9800; border: 1px solid #FF9800; }
        .delete-btn { background-color: transparent; color: #F44336; border: 1px solid #F44336; }
        .btn:hover { opacity: 0.85; transform: translateY(-1px); }

        .actions-cell { white-space: nowrap; }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; height: 100%;
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

        .modal-img { max-width: 100%; max-height: 70vh; border-radius: 4px; }
        .close-modal {
            position: absolute;
            top: 10px; right: 20px;
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }

        .close-modal:hover { color: #333; }

        @media (max-width: 768px) {
            .controls { flex-direction: column; align-items: stretch; }
            input[type="text"], button, .reset-btn { width: 100%; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
            th, td { padding: 0.75rem 1rem; font-size: 14px; }
        }

        @media (max-width: 500px) {
            .modal-content { padding: 15px; }
            .modal-img { max-height: 60vh; }
        }
    </style>
</head>
<body>
    <h2>All Students</h2>

    <div class="controls">
        <!-- Search Form -->
        <form method="GET" action="" style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
            <input type="text" name="search" placeholder="Search by name, contact, course or ID" value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
            <?php if (!empty($search)): ?>
                <a href="?" class="reset-btn">Reset</a>
            <?php endif; ?>
        </form>

        <!-- Excel Export Form -->
        <form method="GET" action="export_students_excel.php">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <button type="submit" style="background-color: #2196F3; color: white;">Download Excel</button>
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

    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <img id="modalImage" class="modal-img" src="" alt="Preview">
        </div>
    </div>

    <script>
        function openModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>
