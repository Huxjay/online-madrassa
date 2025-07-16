<?php
include('../includes/db.php');

$parent_user_id = $_SESSION['user_id'] ?? 0;

if (!$parent_user_id) {
  echo "Unauthorized";
  exit;
}

// ✅ Get parent profile info (with id)
$stmt = $conn->prepare("
  SELECT u.name, u.email, u.photo, p.gender, p.phone, p.age, l.name AS location, p.id AS parent_id
  FROM users u
  JOIN parents p ON u.id = p.user_id
  LEFT JOIN locations l ON u.location_id = l.id
  WHERE u.id = ?
");
$stmt->bind_param("i", $parent_user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$parent_id = $data['parent_id'] ?? 0;
$assignments = [];

// ✅ Fetch child assignments
$childIds = [];
$childRes = $conn->query("SELECT id FROM students WHERE parent_id = $parent_id");
while ($row = $childRes->fetch_assoc()) {
  $childIds[] = $row['id'];
}

if (!empty($childIds)) {
  $idList = implode(',', $childIds);

  $assignRes = $conn->query("
    SELECT title, description, due_date, created_at, file_path, 'Child' AS who
    FROM assignments
    WHERE student_id IN ($idList)

    UNION

    SELECT title, description, due_date, created_at, file_path, 'You' AS who
    FROM assignments
    WHERE student_id = $parent_id

    ORDER BY created_at DESC
  ");
} else {
  // No children, just parent's own assignments
  $assignRes = $conn->query("
    SELECT title, description, due_date, created_at, file_path, 'You' AS who
    FROM assignments
    WHERE student_id = $parent_id
    ORDER BY created_at DESC
  ");
}

// ✅ Collect results
if ($assignRes) {
  while ($a = $assignRes->fetch_assoc()) {
    $assignments[] = $a;
  }
}
?>

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

<h3>📘 Assignments (You & Your Children)</h3>
<div class="assignment-list">
  <?php if (!empty($assignments)): ?>
    <?php foreach ($assignments as $a): ?>
      <div class="assignment-card">
        <h4><?= htmlspecialchars($a['title']) ?></h4>
        <p><?= htmlspecialchars($a['description']) ?></p>

        <?php if (!empty($a['file_path'])): ?>
          <?php 
            $file = '../uploads/assignments/' . $a['file_path'];
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
          ?>

          <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
            <img src="<?= $file ?>" alt="Assignment Image" class="assignment-image">
          <?php else: ?>
            <p>📄 <a href="<?= $file ?>" target="_blank">View Attachment</a></p>
          <?php endif; ?>
        <?php endif; ?>

        <small>
          👤 <?= $a['who'] ?> |
          📅 Due: <?= $a['due_date'] ?> |
          🕒 Posted: <?= $a['created_at'] ?>
        </small>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p>No assignments found.</p>
  <?php endif; ?>
</div>

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

<style>

.assignment-list {
  max-width: 600px;
  margin: 30px auto;
}
.assignment-card {
  background: #f5faff;
  border: 1px solid #bbdefb;
  padding: 15px;
  margin-top: 12px;
  border-radius: 8px;
}
.assignment-card h4 {
  margin-bottom: 5px;
  color: #1565c0;
}
.assignment-card small {
  color: #555;
}

/* 🌙 Dark Glassy Profile Section */
body {
  font-family: 'Poppins', sans-serif;
}

.profile-section {
  max-width: 600px;
  margin: 30px auto;
  background: rgba(255, 255, 255, 0.07);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 15px;
  padding: 30px;
  color: #fff;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
  animation: fadeIn 0.8s ease-in-out;
}

/* Profile Image */
.profile-photo {
  text-align: center;
  margin-bottom: 20px;
}
.profile-photo img {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  border: 4px solid #2196f3;
  object-fit: cover;
  box-shadow: 0 4px 10px rgba(0,0,0,0.3);
  transition: 0.3s;
}
.profile-photo img:hover {
  transform: scale(1.05);
}
.profile-photo input[type="file"] {
  margin-top: 12px;
  color: #fff;
}

/* Inputs and selects */
.profile-section input,
.profile-section select {
  width: 100%;
  padding: 12px 14px;
  margin-top: 15px;
  border: none;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.12);
  color: #fff;
  font-size: 15px;
  outline: none;
  transition: 0.3s ease;
}
.profile-section input:focus,
.profile-section select:focus {
  background: rgba(255, 255, 255, 0.18);
  box-shadow: 0 0 8px rgba(33, 150, 243, 0.4);
}

/* Button */
.profile-section button {
  background: #2196f3;
  color: white;
  padding: 12px;
  margin-top: 20px;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  font-size: 16px;
  cursor: pointer;
  transition: background 0.3s ease;
}
.profile-section button:hover {
  background: #1976d2;
}

/* Feedback */
#profileMessage {
  text-align: center;
  margin-top: 15px;
  font-weight: bold;
}

/* Assignments Section */
.assignment-list {
  max-width: 700px;
  margin: 40px auto;
}
.assignment-card {
  background: rgba(255, 255, 255, 0.08);
  border-left: 4px solid #2196f3;
  padding: 18px 20px;
  border-radius: 10px;
  color: #fff;
  margin-bottom: 15px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.assignment-card h4 {
  margin-bottom: 6px;
  font-size: 18px;
}
.assignment-card p {
  font-size: 14px;
  margin-bottom: 6px;
}
.assignment-card small {
  font-size: 12px;
  color: #ddd;
}

/* Optional fade-in animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>