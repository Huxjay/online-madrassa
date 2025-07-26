<?php include('../includes/admin_session.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - Online Madrassa</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

  <!-- Background Image Layer -->
  <div class="bg-layer"></div>

  <div class="admin-container" id="adminContainer">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="logo">📚 Madrassa</div>
      <nav>
        <ul>
          <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
          <li><a href="index.php?page=users"><i class="fas fa-users"></i> Users</a></li>
          <li><a href="index.php?page=teachers"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
          <li><a href="index.php?page=approve"><i class="fas fa-user-check"></i> Approvals</a></li>
          <li><a href="#"><i class="fas fa-child"></i> Students</a></li>
          <li><a href="#"><i class="fas fa-book"></i> Classes</a></li>
          <li><a href="index.php?page=chat_box"><i class="fas fa-comments"></i>Live Chat</a></li>

          <li><a href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
        </ul>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content" id = "mainContent"> 
      <header class="header">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <div class="right-actions">
          <button class="dark-mode-toggle" onclick="toggleDarkMode()"><i class="fas fa-moon"></i></button>
        </div>
      </header>

      <main class="dashboard">
        <?php
        $page = $_GET['page'] ?? 'home';

        switch ($page) {
          case 'approve':
              include 'approve_teachers.php';
              break;
          case 'teachers':
              include 'teachers_list.php';
              break;

          case 'chat_box':
            include 'chat_box.php';
            break;

             case 'users':
            include 'users.php';
            break;
          default:
              echo "<h1>Welcome to the Admin Panel</h1><p>Manage users, classes, and content.</p>";
        }
        ?>
      </main>

      <footer class="footer">
        <p>&copy; 2025 Online Madrassa. All rights reserved.</p>
      </footer>
    </div>
  </div>

  <script src="../assets/js/admin.js"></script>
</body>
</html>