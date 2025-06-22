<?php include('../includes/teacher_session.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Teacher Dashboard - Online Madrassa</title>
  <link rel="stylesheet" href="../assets/css/teacher.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

  <!-- Background Layer -->
  <div class="bg-layer"></div>

  <div class="teacher-container" id="teacherContainer">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="logo">📘 Madrassa</div>
      <nav>
        <ul>
          <li><a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
          <li><a href="#"><i class="fas fa-book-reader"></i> My Classes</a></li>
          <li><a href="#"><i class="fas fa-users"></i> Students</a></li>
          <li><a href="#"><i class="fas fa-tasks"></i> Assignments</a></li>
          <li><a href="#"><i class="fas fa-check-circle"></i> Attendance</a></li>
          <li><a href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
      <header class="header">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <div class="right-actions">
          <button class="dark-mode-toggle" onclick="toggleDarkMode()"><i class="fas fa-moon"></i></button>
        </div>
      </header>

      <main class="dashboard">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</h1>
        <p>This is your teacher dashboard. You can manage classes, students, and assignments here.</p>
      </main>

      <footer class="footer">
        <p>&copy; 2025 Online Madrassa. All rights reserved.</p>
      </footer>
    </div>
  </div>

  <script src="../assets/js/teacher.js"></script>
</body>
</html>