<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../includes/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo "<p style='color:red;'>Access Denied</p>";
    exit;
}
?>

<h2 style="text-align:center;">👥 All Users (Teachers & Parents)</h2>

<div class="tabs">
  <button onclick="showTab('teachers')">Teachers</button>
  <button onclick="showTab('parents')">Parents</button>
</div>

<!-- Teachers Section -->
<div id="teachers" class="tab-section">
  <h3>📘 Teachers</h3>
  <?php
  $sql = "SELECT u.id AS user_id, u.name, u.email, u.approved,
                 t.gender, t.age, t.phone, t.district, t.street,
                 t.qualification, t.specialization, t.profile_picture
          FROM users u
          JOIN teachers t ON u.id = t.user_id
          WHERE u.role = 'teacher'";
  $teachers = $conn->query($sql);
  while ($t = $teachers->fetch_assoc()):
$image = !empty($t['profile_picture']) ? "../uploads/teachers/{$t['profile_picture']}" : "../assets/default_teacher.png";  ?>
  <div class="user-card">
    <img src="<?= $image ?>" alt="Teacher Photo">
    <div class="user-details">
      <strong><?= htmlspecialchars($t['name']) ?></strong> (<?= htmlspecialchars($t['email']) ?>)<br>
      📞 <?= htmlspecialchars($t['phone']) ?> | 🎓 <?= htmlspecialchars($t['qualification']) ?><br>
      🧭 <?= htmlspecialchars($t['district']) ?>, <?= htmlspecialchars($t['street']) ?> | 🧬 <?= htmlspecialchars($t['specialization']) ?><br>
      <a href="toggle_user.php?id=<?= $t['user_id'] ?>&status=<?= $t['approved'] ? 0 : 1 ?>" class="btn <?= $t['approved'] ? 'block' : 'unblock' ?>">
        <?= $t['approved'] ? 'Block' : 'Unblock' ?>
      </a>
    </div>
    <div class="user-sub">
      <u>👨‍👩‍👧 Students:</u><br>
      <?php
      $stmt = $conn->prepare("SELECT s.name, s.age, s.gender FROM students s WHERE s.assigned_teacher_id = ?");
      $stmt->bind_param("i", $t['user_id']);
      $stmt->execute();
      $res = $stmt->get_result();
      while ($stu = $res->fetch_assoc()) {
          echo "- " . htmlspecialchars($stu['name']) . " ({$stu['gender']}, {$stu['age']} yrs)<br>";
      }
      $stmt->close();
      ?>
    </div>
  </div>
  <?php endwhile; ?>
</div>

<!-- Parents Section -->
<div id="parents" class="tab-section" style="display:none;">
  <h3>📗 Parents</h3>
  <?php
  $sql = "SELECT u.id AS user_id, u.name, u.email, u.approved, u.photo,
                 p.gender, p.age, p.phone, p.id_number
          FROM users u
          JOIN parents p ON u.id = p.user_id
          WHERE u.role = 'parent'";
  $parents = $conn->query($sql);
  while ($p = $parents->fetch_assoc()):
    $image = !empty($p['photo']) ? "../uploads/parents/{$p['photo']}" : "../assets/default_parent.png";

    ?>
  <div class="user-card">
    <img src="<?= $image ?>" alt="Parent Photo">
    <div class="user-details">
      <strong><?= htmlspecialchars($p['name']) ?></strong> (<?= htmlspecialchars($p['email']) ?>)<br>
      📞 <?= htmlspecialchars($p['phone']) ?> | 🆔 <?= htmlspecialchars($p['id_number']) ?><br>
      👤 <?= htmlspecialchars($p['gender']) ?> | 🎂 <?= htmlspecialchars($p['age']) ?> yrs<br>
      <a href="toggle_user.php?id=<?= $p['user_id'] ?>&status=<?= $p['approved'] ? 0 : 1 ?>" class="btn <?= $p['approved'] ? 'block' : 'unblock' ?>">
        <?= $p['approved'] ? 'Block' : 'Unblock' ?>
      </a>
    </div>
    <div class="user-sub">
      <u>👶 Children:</u><br>
      <?php
      $stmt = $conn->prepare("SELECT name, age, gender, specialization FROM students WHERE parent_id = ?");
      $stmt->bind_param("i", $p['user_id']);
      $stmt->execute();
      $res = $stmt->get_result();
      while ($stu = $res->fetch_assoc()) {
          echo "- " . htmlspecialchars($stu['name']) . " ({$stu['gender']}, {$stu['age']} yrs, " . htmlspecialchars($stu['specialization']) . ")<br>";
      }
      $stmt->close();
      ?>
    </div>
  </div>
  <?php endwhile; ?>
</div>

<!-- Styles -->
<style>
  .tabs {
    text-align: center;
    margin: 20px;
  }
  .tabs button {
    padding: 10px 20px;
    margin: 0 5px;
    cursor: pointer;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    background: #007bff;
    color: white;
    transition: background 0.3s;
  }
  .tabs button:hover {
    background: #0056b3;
  }
  .tab-section {
    margin-top: 20px;
  }
  .user-card {
    background: rgba(255,255,255,0.9);
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    margin: 20px auto;
    max-width: 900px;
    display: flex;
    gap: 20px;
  }
  .user-card img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #007bff;
  }
  .user-details {
    flex: 2;
    font-size: 15px;
  }
  .user-sub {
    flex: 1;
    font-size: 13px;
    color: #333;
  }
  .btn {
    padding: 6px 12px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    font-weight: bold;
    display: inline-block;
    margin-top: 6px;
  }
  .btn.block {
    background: #dc3545;
    color: white;
  }
  .btn.unblock {
    background: #28a745;
    color: white;
  }
</style>

<!-- JS -->
<script>
  function showTab(tab) {
    document.getElementById('teachers').style.display = (tab === 'teachers') ? 'block' : 'none';
    document.getElementById('parents').style.display = (tab === 'parents') ? 'block' : 'none';
  }
</script>