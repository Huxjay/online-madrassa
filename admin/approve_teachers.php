<?php
include('../includes/admin_session.php');
include('../includes/db.php');

// Success or error message from redirect
$approved_success = '';
if (isset($_GET['success'])) {
    $approved_success = "✅ Teacher approved and email sent successfully!";
}
if (isset($_GET['email_error'])) {
    $approved_success = "✅ Teacher approved, but email failed to send: " . htmlspecialchars($_GET['email_error']);
}

// Fetch unapproved teachers
$query = "
    SELECT u.id AS user_id, u.name, u.email, t.gender, t.age, t.phone, t.qualification, t.specialization
    FROM users u
    JOIN teachers t ON u.id = t.user_id
    WHERE u.role = 'teacher' AND u.approved = 0
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Approve Teachers - Admin Panel</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .approval-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background: rgba(10, 1, 12, 0.95);
      border-radius: 10px;
      overflow: hidden;
    }

    .approval-table th, .approval-table td {
      padding: 12px 16px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    .approval-table th {
      background-color: #f8b400;
      color: #2d3436;
    }

    .approval-table tr:hover {
      background-color: rgb(43, 8, 8);
    }

    .btn-approve {
      background-color: #27ae60;
      color: white;
      border: none;
      padding: 8px 12px;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .btn-approve:hover {
      background-color: #219150;
    }

    .message-box {
      margin-top: 20px;
      background-color: #dff0d8;
      color: #3c763d;
      padding: 15px 20px;
      border-left: 5px solid #3c763d;
      border-radius: 6px;
      font-weight: bold;
      animation: fadeIn 0.7s ease-in-out;
    }

    .info {
      background-color: #e3f2fd;
      color: #0d47a1;
      border-left: 5px solid #0d47a1;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to   { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="bg-layer"></div>

  <div class="admin-container">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="logo">📚 Madrassa</div>
      <nav>
        <ul>
          <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
          <li><a href="#"><i class="fas fa-users"></i> Users</a></li>
          <li><a href="teachers_list.php"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
          <li><a href="approve_teachers.php" class="active"><i class="fas fa-user-check"></i> Approvals</a></li>
          <li><a href="#"><i class="fas fa-child"></i> Students</a></li>
          <li><a href="#"><i class="fas fa-book"></i> Classes</a></li>
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
        <h2><i class="fas fa-user-check"></i> Pending Teacher Approvals</h2>

        <?php if (!empty($approved_success)): ?>
          <div class="message-box"><?= $approved_success ?></div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
          <table class="approval-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Gender</th>
                <th>Age</th>
                <th>Phone</th>
                <th>Qualification</th>
                <th>Specialization</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= htmlspecialchars($row['email']) ?></td>
                  <td><?= htmlspecialchars($row['gender']) ?></td>
                  <td><?= htmlspecialchars($row['age']) ?></td>
                  <td><?= htmlspecialchars($row['phone']) ?></td>
                  <td><?= htmlspecialchars($row['qualification']) ?></td>
                  <td><?= htmlspecialchars($row['specialization']) ?></td>
                  <td>
                    <form method="POST" action="approve_teachers_action.php">
                      <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                      <button type="submit" name="approve_teacher" class="btn-approve">
                        <i class="fas fa-check-circle"></i> Approve
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="message-box info">🎓 All teachers are approved. No pending approvals.</p>
        <?php endif; ?>
      </main>

      <footer class="footer">
        <p>&copy; 2025 Online Madrassa. All rights reserved.</p>
      </footer>
    </div>
  </div>

  <script src="../assets/js/admin.js"></script>
</body>
</html>