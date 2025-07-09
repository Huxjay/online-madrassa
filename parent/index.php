<?php include('../includes/parent_session.php'); ?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Parent Dashboard - Online Madrassa</title>
  <link rel="stylesheet" href="../assets/css/parent.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

  <div class="bg-layer"></div>

  <div class="parent-container" id="parentContainer">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="logo"><i class="fas fa-mosque"></i> Madrassa</div>
      <nav>
        <ul>
          <li><a href="index.php?page=my_children"><i class="fas fa-child"></i> My children</a></li>
          <li><a href="#"><i class="fas fa-video"></i> Online Classes</a></li>
          <li><a href="#"><i class="fas fa-comments"></i> Live Chat</a></li>
          <li><a href="index.php?page=learning_preference"><i class="fas fa-cogs"></i> Learning Preference</a></li>
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
        <?php
          $page = $_GET['page'] ?? 'home';
          switch ($page) {
            case 'my_children':
              include 'my_children.php';
              break;

          case 'learning_preference':
              include 'learning_preference.php';
              break;    
            default:
              echo "<h1>Welcome, {$_SESSION['user_name']} 👋</h1><p>Manage your children’s progress and activities.</p>";
          }
        ?>
      </main>

      <footer class="footer">
        <p>&copy; <?= date('Y') ?> Online Madrassa | Parent Panel</p>
      </footer>
    </div>
  </div>


  <script>
    // Load dynamic sections via AJAX
    document.querySelectorAll('[data-section]').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        const section = this.getAttribute('data-section');

        fetch(section)
          .then(res => res.text())
          .then(html => {
            const container = document.getElementById('main-content-area');
            container.innerHTML = html;
            container.classList.add('fade-in');
            setTimeout(() => container.classList.remove('fade-in'), 500);
          })
          .catch(err => {
            document.getElementById('main-content-area').innerHTML = "<p>Error loading content.</p>";
            console.error(err);
          });
      });
    });

    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('collapsed');
    }

    function toggleDarkMode() {
      document.body.classList.toggle('dark-mode');
    }
  </script>

  <script src="../assets/js/parent.js"></script>
</body>
</html>






  