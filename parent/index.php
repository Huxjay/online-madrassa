<?php
session_start();
include('../includes/parent_session.php'); // this one sets session info
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Parent Dashboard - Online Madrassa</title>
  <link rel="stylesheet" href="../assets/css/parent.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    .notification-bell-container {
      position: relative;
      margin-left: 15px;
    }

    #notificationBell {
      background: none;
      border: none;
      cursor: pointer;
      position: relative;
      font-size: 20px;
      color: #fff;
    }

    .count {
      position: absolute;
      top: -6px;
      right: -6px;
      background: red;
      color: white;
      font-size: 10px;
      padding: 2px 6px;
      border-radius: 50%;
    }

    .dropdown {
      position: absolute;
      right: 0;
      top: 30px;
      width: 260px;
      background: #fff;
      color: #333;
      border: 1px solid #ccc;
      border-radius: 4px;
      z-index: 1000;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .dropdown.hidden {
      display: none;
    }

    #notificationList {
      list-style: none;
      margin: 0;
      padding: 0;
      max-height: 220px;
      overflow-y: auto;
    }

    #notificationList li {
      padding: 10px;
      border-bottom: 1px solid #eee;
      font-size: 14px;
    }

    .dropdown-footer {
      text-align: center;
      padding: 8px;
      font-size: 12px;
      background: #f9f9f9;
    }
  </style>
</head>
<body>

<div class="bg-layer"></div>

<div class="parent-container" id="parentContainer">
  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="logo"><i class="fas fa-mosque"></i> Madrassa</div>
    <nav>
      <ul>
          <li><a href="index.php?page=myself" class="<?= ($_GET['page'] ?? '') === 'myself.php' ? 'active' : '' ?>"><i class="fas fa-user-graduate"></i> myself</a></li>

        <li><a href="index.php?page=my_children"><i class="fas fa-child"></i> My children</a></li>
        <li><a href="index.php?page=chat_box"><i class="fas fa-comments"></i>Live Chat</a></li>
        <li><a href="#"><i class="fas fa-video"></i> Online Classes</a></li>
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
        <!-- Notification Bell -->
        <div class="notification-bell-container">
          <button id="notificationBell">
            <i class="fas fa-bell"></i>
            <span id="notificationCount" class="count">0</span>
          </button>
          <div id="notificationDropdown" class="dropdown hidden">
            <ul id="notificationList"></ul>
            <div class="dropdown-footer"><a href="#">View all</a></div>
          </div>
        </div>
        <button class="dark-mode-toggle" onclick="toggleDarkMode()"><i class="fas fa-moon"></i></button>
      </div>
    </header>

    <main class="dashboard" id="main-content-area">
      <?php
        $page = $_GET['page'] ?? 'home';
        switch ($page) {
          case 'my_children':
            include 'my_children.php';
            break;
          case 'learning_preference':
            include 'learning_preference.php';
            break;

          case 'myself':
            include 'myself.php';
            break;

            case 'chat_box':
            include 'chat_box.php';
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
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
  }

  function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
  }

  document.addEventListener("DOMContentLoaded", function () {
    const bell = document.getElementById("notificationBell");
    const dropdown = document.getElementById("notificationDropdown");
    const list = document.getElementById("notificationList");
    const countEl = document.getElementById("notificationCount");

    // Toggle dropdown & mark as read
    bell.addEventListener("click", function () {
      dropdown.classList.toggle("hidden");

      if (!dropdown.classList.contains("hidden")) {
        // Mark all as read
        fetch("../includes/mark_notifications_read.php", { method: "POST" })
          .then(function () {
            countEl.textContent = "0";
          });
      }
    });

    // Fetch notifications
    fetch("../includes/get_notifications.php")
      .then(function (res) {
        return res.json();
      })
      .then(function (data) {
        countEl.textContent = data.unread > 0 ? data.unread : "0";
        list.innerHTML = "";

        if (data.items.length === 0) {
          list.innerHTML = "<li>No notifications</li>";
        } else {
          data.items.forEach(function (noti) {
            const li = document.createElement("li");
            li.textContent = noti.message + " (" + noti.time + ")";
            list.appendChild(li);
          });
        }
      })
      .catch(function (err) {
        console.error("Error loading notifications:", err);
      });
  });
</script>

</body>
</html>