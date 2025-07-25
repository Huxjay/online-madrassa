<?php
include('../includes/db.php');

$teacher_id = $_SESSION['user_id'] ?? 0;

// Fetch students assigned to this teacher
$query = $conn->prepare("
    SELECT s.id, s.name, s.gender, s.age, s.photo
    FROM students s
    WHERE s.assigned_teacher_id = ?
");
$query->bind_param("i", $teacher_id);
$query->execute();
$result = $query->get_result();

// Today's date
$today = date('Y-m-d');
?>

<h2>🗓 Attendance - <?= date('l, F j, Y') ?></h2>

<form method="POST" action="../includes/save_attendance.php">
  <input type="hidden" name="date" value="<?= $today ?>">

  <div class="attendance-container">
    <?php while ($student = $result->fetch_assoc()): ?>
      <div class="attendance-card">
        <img src="../uploads/students/<?= htmlspecialchars($student['photo']) ?>" alt="Student" class="student-photo">
        <div class="student-info">
          <h3><?= htmlspecialchars($student['name']) ?></h3>
          <p>Age: <?= $student['age'] ?> | Gender: <?= $student['gender'] ?></p>
          
          <div class="status-options">
            <label><input type="radio" name="status[<?= $student['id'] ?>]" value="present" required> ✅ Present</label>
            <label><input type="radio" name="status[<?= $student['id'] ?>]" value="absent"> ❌ Absent</label>
            <label><input type="radio" name="status[<?= $student['id'] ?>]" value="late"> 🕐 Late</label>
          </div>

          <textarea name="remarks[<?= $student['id'] ?>]" placeholder="Remarks (optional)"></textarea>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

  <button type="submit" class="submit-btn">✅ Submit Attendance</button>
</form>

<style>
.attendance-container {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
}
.attendance-card {
  display: flex;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  padding: 15px;
  width: 100%;
  max-width: 600px;
}
.student-photo {
  width: 120px;
  height: 120px;
  object-fit: cover;
  border-radius: 10px;
  margin-right: 20px;
}
.student-info h3 {
  margin: 0 0 5px;
}
.status-options {
  margin: 10px 0;
}
.status-options label {
  margin-right: 15px;
}
textarea {
  width: 100%;
  resize: vertical;
  padding: 5px;
  border-radius: 5px;
}
.submit-btn {
  margin-top: 20px;
  background: #4caf50;
  color: #fff;
  padding: 12px 25px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
}

h2{
  color:white;
}
</style>