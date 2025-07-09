<?php
include('../includes/db.php');

// Fetch all approved teachers with user and location info
$sql = "
    SELECT u.id AS user_id, u.name, u.email, l.name AS location, t.gender, t.age, t.phone, t.qualification, 
           t.specialization, t.profile_picture
    FROM users u
    JOIN teachers t ON u.id = t.user_id
    JOIN locations l ON u.location_id = l.id
    WHERE u.role = 'teacher' AND u.approved = 1
";
$result = $conn->query($sql);
?>

<h2><i class="fas fa-chalkboard-teacher"></i> Approved Teachers</h2>

<div class="teacher-cards-container">
<?php while ($row = $result->fetch_assoc()): ?>
  <?php
    $user_id = $row['user_id'];

    // Count assigned students and parents
    $studentsQuery = $conn->query("SELECT COUNT(*) AS count FROM students WHERE assigned_teacher_id = $user_id");
    $studentCount = $studentsQuery ? $studentsQuery->fetch_assoc()['count'] : 0;

    $parentsQuery = $conn->query("
        SELECT COUNT(DISTINCT s.parent_id) AS count
        FROM students s
        WHERE assigned_teacher_id = $user_id
    ");
    $parentCount = $parentsQuery ? $parentsQuery->fetch_assoc()['count'] : 0;

    // Corrected profile picture usage
    $filename = htmlspecialchars($row['profile_picture']);
    $path = "../uploads/teachers/" . $filename;
    $photo = (!empty($filename) && file_exists($path)) ? $path : "../uploads/teachers/default.png";
  ?>

  <div class="teacher-card" onclick="toggleSummary(this)">
    <img src="<?= $photo ?>" alt="Teacher Photo">
    <h3><?= htmlspecialchars($row['name']) ?></h3>
    <p><strong>📍 Location:</strong> <?= htmlspecialchars($row['location']) ?></p>
    <p><strong>🎓 Qualification:</strong> <?= htmlspecialchars($row['qualification']) ?></p>
    <p><strong>📘 Specialization:</strong> <?= htmlspecialchars($row['specialization']) ?></p>

    <div class="summary-box">
      <p><strong>👨‍👩‍👧‍👦 Parents:</strong> <?= $parentCount ?></p>
      <p><strong>🧒 Students:</strong> <?= $studentCount ?></p>
    </div>
  </div>
<?php endwhile; ?>
</div>

<style>
.teacher-cards-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 20px;
  margin-top: 25px;
}

.teacher-card {
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(6px);
  border-radius: 10px;
  padding: 20px;
  color: #fff;
  box-shadow: 0 8px 18px rgba(0,0,0,0.1);
  position: relative;
  cursor: pointer;
  transition: transform 0.3s ease;
}

.teacher-card:hover {
  transform: translateY(-5px);
}

.teacher-card img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 15px;
  background: #ccc;
}

.teacher-card h3 {
  margin-top: 0;
  font-size: 1.3rem;
}

.summary-box {
  margin-top: 12px;
  background: rgba(0,0,0,0.3);
  padding: 10px 15px;
  border-radius: 8px;
  display: none;
  animation: fadeIn 0.3s ease;
}

.teacher-card.active .summary-box {
  display: block;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
function toggleSummary(card) {
  card.classList.toggle("active");
}
</script>