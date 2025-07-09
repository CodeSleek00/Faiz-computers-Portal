<?php
session_start();
include '../database_connection/db_connect.php';

if (!isset($_SESSION['enrollment_id'])) {
    header("Location: login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];
$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Profile | <?= htmlspecialchars($student['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
    :root {
  --primary-color: #00bcd4;
  --primary-dark: #0097a7;
  --text-color: #333;
  --text-light: #666;
  --text-lighter: #999;
  --bg-color: #f9fbff;
  --card-bg: #fff;
  --border-color: #eee;
  --status-active: #e0f7fa;
  --status-text: #00796b;
  --tab-inactive: #f5f5f5;
  --status1:rgb(225, 91, 91)
  --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  background: var(--bg-color);
  color: var(--text-color);
  line-height: 1.6;
  min-height: 100vh;
  padding: 20px;
  display: flex;
  flex-direction: column;
}

.container {
  background: var(--card-bg);
  border-radius: 16px;
  padding: 24px;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  box-shadow: var(--shadow);
  flex: 1;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 20px;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.header h2 {
  font-size: clamp(1.5rem, 4vw, 1.8rem);
  color: var(--text-color);
  font-weight: 600;
}

.edit-btn {
  background: var(--primary-color);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.edit-btn:hover {
  background: var(--primary-dark);
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 188, 212, 0.3);
}

.profile-content {
  display: grid;
  grid-template-columns: 1fr;
  gap: 32px;
}

.profile-photo {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.profile-photo img {
  width: clamp(120px, 25vw, 180px);
  height: clamp(120px, 25vw, 180px);
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid var(--primary-color);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  margin-bottom: 16px;
  transition: transform 0.3s ease;
}

.profile-photo img:hover {
  transform: scale(1.03);
}

.profile-photo h3 {
  font-size: clamp(1.2rem, 4vw, 1.5rem);
  font-weight: 600;
  margin: 8px 0 0;
  color: var(--text-color);
}

.profile-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.info-item {
  display: flex;
  flex-direction: column;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--border-color);
}

.info-item label {
  color: var(--text-lighter);
  font-weight: 500;
  font-size: 0.85rem;
  margin-bottom: 4px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-item div {
  color: var(--text-color);
  font-size: clamp(0.95rem, 3vw, 1.05rem);
  font-weight: 500;
  word-break: break-word;
}

.status {
  background: var(--status-active);
  color: var(--status-text);
  padding: 6px 12px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.8rem;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  width: fit-content;
}
.status1 {
    background:var(--status1);
    color: white;
    padding: 6px 12px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.8rem;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  width: fit-content;

}


.status::before {
  content: "";
  display: block;
  width: 8px;
  height: 8px;
  background: var(--status-text);
  border-radius: 50%;
}

.tabs {
  margin-top: 32px;
  display: flex;
  gap: 12px;
  overflow-x: auto;
  padding-bottom: 8px;
  scrollbar-width: thin;
}

.tabs::-webkit-scrollbar {
  height: 4px;
}

.tabs::-webkit-scrollbar-thumb {
  background: var(--primary-color);
  border-radius: 4px;
}

.tab {
  padding: 10px 20px;
  background: var(--tab-inactive);
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  color: var(--text-light);
  font-size: 0.9rem;
  white-space: nowrap;
  transition: all 0.3s ease;
  border: none;
}

.tab.active {
  background: var(--primary-color);
  color: white;
  box-shadow: 0 2px 8px rgba(0, 188, 212, 0.3);
}

.tab:hover:not(.active) {
  background: #e0e0e0;
}

/* Responsive Breakpoints */
@media (min-width: 640px) {
  .container {
    padding: 28px;
  }
  
  .profile-content {
    grid-template-columns: 240px 1fr;
    align-items: flex-start;
  }
  
  .profile-photo {
    position: sticky;
    top: 20px;
  }
}

@media (min-width: 768px) {
  body {
    padding: 30px;
  }
  
  .container {
    padding: 32px 40px;
  }
  
  .profile-info {
    gap: 24px 32px;
  }
}

@media (min-width: 1024px) {
  .profile-content {
    grid-template-columns: 280px 1fr;
  }
  
  .profile-info {
    grid-template-columns: repeat(2, minmax(220px, 1fr));
  }
}

/* Animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.profile-content {
  animation: fadeIn 0.5s ease-out forwards;
}

/* Loading State */
.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 4px;
  color: transparent;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2>Profile</h2>
        </div>

        <div class="profile-content">
            <div class="profile-photo">
                <img src="../uploads/<?= htmlspecialchars($student['photo']) ?>" alt="Profile Photo">
                <h3><?= htmlspecialchars($student['name']) ?></h3>
            </div>

            <div class="profile-info">
                <div>
                    <label>Student ID</label><br>
                    <?= htmlspecialchars($student['enrollment_id']) ?>
                </div>
                <div>
                    <label>Course</label><br>
                    <?= htmlspecialchars($student['course']) ?>
                </div>
                
                <div>
                    <label>Contact Number</label><br>
                    <?= htmlspecialchars($student['contact_number']) ?>
                </div>
                <div>
                    <label>Address</label><br>
                    <?= htmlspecialchars($student['address']) ?>
                </div>
                <div>
                    <label>Status</label><br>
                    <span class="status">Active</span>
                </div>
                <div>
                    <span class="status1"><a href="logout.php">Logout</a></span>

                </div>
            </div>
        </div>

       

</body>
</html>
