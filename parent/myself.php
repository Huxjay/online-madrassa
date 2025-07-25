<?php
include('../includes/db.php');
$parent_user_id = $_SESSION['user_id'] ?? 0;
if (!$parent_user_id) {
  echo "Unauthorized";
  exit;
}

// ✅ Fetch parent profile data
$stmt = $conn->prepare("
  SELECT u.name, u.email, u.photo, p.gender, p.phone, p.age, l.name AS location, p.id as parent_id
  FROM users u
  JOIN parents p ON u.id = p.user_id
  LEFT JOIN locations l ON u.location_id = l.id
  WHERE u.id = ?
");
$stmt->bind_param("i", $parent_user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$parent_id = $data['parent_id'] ?? 0;

// ✅ Get child IDs
$childIds = [];
$childRes = $conn->query("SELECT id FROM students WHERE parent_id = $parent_id");
while ($row = $childRes->fetch_assoc()) {
  $childIds[] = $row['id'];
}

// ✅ Get adult learner ID
$adultRes = $conn->prepare("SELECT id FROM adult_learners WHERE parent_id = ?");
$adultRes->bind_param("i", $parent_user_id);
$adultRes->execute();
$adultResult = $adultRes->get_result()->fetch_assoc();
$adultLearnerId = $adultResult['id'] ?? 0;

// ✅ Get assignments
$assignments = [];

if (!empty($childIds)) {
  $idList = implode(',', $childIds);
  $childAssign = $conn->query("
    SELECT title, description, due_date, created_at, file_path
    FROM assignments
    WHERE student_id IN ($idList)
    ORDER BY created_at DESC
  ");
  while ($row = $childAssign->fetch_assoc()) {
    $row['who'] = 'Child';
    $assignments[] = $row;
  }
}

if ($adultLearnerId) {
  $adultAssign = $conn->prepare("
    SELECT title, description, due_date, created_at, file_path
    FROM assignments
    WHERE student_id = ?
    ORDER BY created_at DESC
  ");
  $adultAssign->bind_param("i", $adultLearnerId);
  $adultAssign->execute();
  $adultRes = $adultAssign->get_result();
  while ($row = $adultRes->fetch_assoc()) {
    $row['who'] = 'You (Adult Learner)';
    $assignments[] = $row;
  }
}
?>

<!-- ✅ Profile Section -->
<h2>👤 My Profile</h2>
<div class="profile-section">
  <form id="updateProfileForm" enctype="multipart/form-data">
    <div class="profile-photo">
      <img src="../uploads/parents/<?= htmlspecialchars($data['photo'] ?? 'default.png') ?>" alt="Profile Photo">
      <input type="file" name="photo">
    </div>

    <input type="text" name="name" value="<?= htmlspecialchars($data['name']) ?>" placeholder="Full Name" required>
    <input type="email" value="<?= htmlspecialchars($data['email']) ?>" readonly>
    <input type="text" name="phone" value="<?= htmlspecialchars($data['phone']) ?>" placeholder="Phone" required>
    <input type="number" name="age" value="<?= htmlspecialchars($data['age']) ?>" placeholder="Age" required>

    <select name="gender" required>
      <option value="">Select Gender</option>
      <option value="Male" <?= $data['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
      <option value="Female" <?= $data['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
    </select>

    <input type="text" value="<?= htmlspecialchars($data['location']) ?>" readonly>

    <button type="submit">Update Profile</button>
    <div id="profileMessage" style="margin-top:10px;"></div>
  </form>
</div>

<!-- ✅ Assignments Section -->
<h3>📘 Assignments (You & Your Children)</h3>
<div class="assignment-list">
  <?php if (!empty($assignments)): ?>
    <?php foreach ($assignments as $a): ?>
      <div class="assignment-card">
        <h4><?= htmlspecialchars($a['title']) ?></h4>
        <p><?= nl2br(htmlspecialchars($a['description'])) ?></p>

        <?php if (!empty($a['file_path']) && file_exists('../' . $a['file_path'])): ?>
          <div class="assignment-file">
            <?php
              $ext = pathinfo($a['file_path'], PATHINFO_EXTENSION);
              if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])):
            ?>
              <img src="../<?= htmlspecialchars($a['file_path']) ?>" alt="Assignment Image">
            <?php elseif (in_array(strtolower($ext), ['pdf'])): ?>
              <embed src="../<?= htmlspecialchars($a['file_path']) ?>" type="application/pdf" width="100%" height="400px" />
            <?php else: ?>
              📎 <a href="../<?= htmlspecialchars($a['file_path']) ?>" download>📥 Download File</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <small>
          👤 <?= $a['who'] ?> |
          📅 Due: <?= htmlspecialchars($a['due_date']) ?> |
          🕒 Posted: <?= htmlspecialchars($a['created_at']) ?>
        </small>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="no-assignments">No assignments found.</p>
  <?php endif; ?>
</div>

<!-- ✅ JS -->
<script>
document.getElementById("updateProfileForm").addEventListener("submit", function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  const msg = document.getElementById("profileMessage");

  fetch("../includes/update_parent_profile.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(data => {
    msg.innerText = data;
    msg.style.color = data.toLowerCase().includes("success") ? "green" : "red";
  })
  .catch(() => {
    msg.innerText = "❌ Failed to update profile.";
    msg.style.color = "red";
  });
});
</script>

<!-- ✅ CSS -->
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  background: #eef2f7;
  margin: 0;
  padding: 0 10px;
}

h2, h3 {
  text-align: center;
  color: #2c3e50;
  margin-top: 30px;
}

.profile-section {
  max-width: 500px;
  margin: 20px auto;
  background: #ffffff;
  padding: 25px 30px;
  border-radius: 12px;
  box-shadow: 0 4px 25px rgba(0,0,0,0.08);
}

.profile-photo {
  text-align: center;
  margin-bottom: 20px;
}
.profile-photo img {
  width: 130px;
  height: 130px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #3498db;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
input, select, button {
  display: block;
  width: 100%;
  margin-top: 12px;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: 15px;
  transition: 0.3s;
}
input:focus, select:focus {
  border-color: #3498db;
  outline: none;
}
button {
  background-color: #3498db;
  color: white;
  border: none;
  cursor: pointer;
  font-weight: bold;
  transition: 0.3s;
}
button:hover {
  background-color: #2980b9;
}

.assignment-list {
  max-width: 700px;
  margin: 30px auto;
}
.assignment-card {
  background: #ffffff;
  border-left: 5px solid #3498db;
  padding: 20px;
  margin-top: 16px;
  border-radius: 10px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
.assignment-card h4 {
  margin-bottom: 10px;
  color: #2c3e50;
}
.assignment-card p {
  color: #34495e;
  margin-bottom: 10px;
}
.assignment-card small {
  color: #888;
  font-style: italic;
}
.assignment-file {
  margin: 12px 0;
}
.assignment-file img {
  max-width: 100%;
  border-radius: 10px;
  border: 1px solid #ccc;
}
.assignment-file a {
  color: #2980b9;
  font-weight: 500;
  text-decoration: none;
}
.assignment-file a:hover {
  text-decoration: underline;
}
.no-assignments {
  text-align: center;
  font-style: italic;
  color: #777;
}
</style>