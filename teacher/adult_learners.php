<?php
include('../includes/db.php');
$teacher_id = $_SESSION['user_id'] ?? 0;

$query = $conn->prepare("
  SELECT 
    a.id, u.name AS parent_name, a.specialization, a.join_meetings, a.photo
  FROM adult_learners a
  LEFT JOIN users u ON a.parent_id = u.id
  WHERE a.assigned_teacher_id = ?
");
$query->bind_param("i", $teacher_id);
$query->execute();
$result = $query->get_result();
?>

<h2>🎓 Adult Learners</h2>

<div class="adult-learners-cards">
  <?php while ($row = $result->fetch_assoc()): ?>
    <div class="learner-card">
      <img src="../uploads/adult_learners/<?= htmlspecialchars($row['photo'] ?: 'default.png') ?>" class="learner-photo" alt="Adult Learner Photo">

      <div class="learner-info">
        <h3><?= htmlspecialchars($row['parent_name']) ?></h3>
        <p><strong>Specialization:</strong> <?= htmlspecialchars($row['specialization']) ?></p>
        <p><strong>Join Online:</strong> <?= $row['join_meetings'] ? '✅ Yes' : '❌ No' ?></p>
      </div>

      <div class="learner-actions">
        <button class="assign-btn" onclick="openAdultAssignmentModal(<?= $row['id'] ?>, '<?= addslashes($row['parent_name']) ?>')">📘 Add Assignment</button>
        <button class="attendance-btn" onclick="openAttendanceModal(<?= $row['id'] ?>, '<?= addslashes($row['parent_name']) ?>')">📅 Mark Attendance</button>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<!-- 📘 Assignment Modal -->
<div id="adultAssignmentModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeAdultAssignmentModal()">&times;</span>
    <h3>📘 Add Assignment for <span id="adultLearnerName"></span></h3>
    <form id="adultAssignmentForm" enctype="multipart/form-data">
      <input type="hidden" name="learner_id" id="adultLearnerId">
      <input type="text" name="title" placeholder="Assignment Title" required>
      <textarea name="description" placeholder="Description" required></textarea>
      <input type="date" name="due_date" required>
      <input type="file" name="attachment" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
      <button type="submit">Submit</button>
      <div id="adultFormMessage" style="margin-top:10px;"></div>
    </form>
  </div>
</div>

<!-- 📅 Attendance Modal -->
<div id="attendanceModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeAttendanceModal()">&times;</span>
    <h3>📅 Mark Attendance for <span id="attendanceNameLabel"></span></h3>
    <form id="attendanceForm">
      <input type="hidden" name="learner_id" id="attendanceLearnerId">
      <label>
        <input type="radio" name="status" value="Attending" required> ✅ Attending
      </label><br>
      <label>
        <input type="radio" name="status" value="Not Attending"> ❌ Not Attending
      </label><br><br>
      <button type="submit">Submit</button>
      <div id="attendanceMessage" style="margin-top:10px;"></div>
    </form>
  </div>
</div>

<!-- ✅ JavaScript -->
<script>
function openAdultAssignmentModal(id, name) {
  document.getElementById("adultAssignmentModal").style.display = "block";
  document.getElementById("adultLearnerId").value = id;
  document.getElementById("adultLearnerName").textContent = name;
}

function closeAdultAssignmentModal() {
  document.getElementById("adultAssignmentModal").style.display = "none";
  document.getElementById("adultAssignmentForm").reset();
  document.getElementById("adultFormMessage").textContent = '';
}

function openAttendanceModal(id, name) {
  document.getElementById("attendanceModal").style.display = "block";
  document.getElementById("attendanceLearnerId").value = id;
  document.getElementById("attendanceNameLabel").textContent = name;
}

function closeAttendanceModal() {
  document.getElementById("attendanceModal").style.display = "none";
  document.getElementById("attendanceForm").reset();
  document.getElementById("attendanceMessage").textContent = '';
}

document.getElementById("adultAssignmentForm").addEventListener("submit", function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  const msg = document.getElementById("adultFormMessage");

  fetch("../includes/save_adult_assignment.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(data => {
    msg.innerText = data;
    msg.style.color = data.toLowerCase().includes("success") ? "green" : "red";
  })
  .catch(() => {
    msg.innerText = "❌ Failed to submit assignment.";
    msg.style.color = "red";
  });
});

document.getElementById("attendanceForm").addEventListener("submit", function(e) {
  e.preventDefault();
  const formData = new URLSearchParams(new FormData(this));
  const msg = document.getElementById("attendanceMessage");

  fetch("../includes/mark_adult_attendance.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: formData
  })
  .then(res => res.text())
  .then(data => {
    msg.innerText = data;
    msg.style.color = data.toLowerCase().includes("success") ? "green" : "red";
  })
  .catch(() => {
    msg.innerText = "❌ Failed to submit attendance.";
    msg.style.color = "red";
  });
});

// Close modals on outside click
window.onclick = function(event) {
  if (event.target === document.getElementById("adultAssignmentModal")) closeAdultAssignmentModal();
  if (event.target === document.getElementById("attendanceModal")) closeAttendanceModal();
};
</script>

<!-- ✅ Basic Styling -->
<style>
.adult-learners-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-top: 20px;
  justify-content: center;
}

.learner-card {
  background: #ffffff;
  border-radius: 10px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
  padding: 20px;
  width: 280px;
  transition: 0.3s ease-in-out;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.learner-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.15);
}

.learner-photo {
  width: 100%;
  height: 160px;
  object-fit: cover;
  border-radius: 8px;
  border: 2px solid #ccc;
}

.learner-info {
  margin-top: 10px;
  text-align: center;
}

.learner-info h3 {
  font-size: 18px;
  margin-bottom: 5px;
}

.learner-info p {
  margin: 4px 0;
  font-size: 14px;
  color: #333;
}

.learner-actions {
  margin-top: 15px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  width: 100%;
}

.learner-actions button {
  flex: 1;
  padding: 8px 10px;
  font-size: 14px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  color: white;
  transition: background 0.3s ease;
}

.assign-btn {
  background-color: #2196f3;
}
.assign-btn:hover {
  background-color: #0d8ae2;
}

.attendance-btn {
  background-color: #4caf50;
}
.attendance-btn:hover {
  background-color: #388e3c;
}

.modal {
  display: none;
  position: fixed;
  z-index: 9999;
  padding-top: 70px;
  left: 0; top: 0;
  width: 100%; height: 100%;
  background-color: rgba(0,0,0,0.5);
}

.modal-content {
  background: white;
  margin: auto;
  padding: 20px;
  border-radius: 10px;
  width: 90%;
  max-width: 500px;
  position: relative;
  color: #333;
}

.modal-content input,
.modal-content textarea,
.modal-content button {
  display: block;
  width: 100%;
  margin-top: 10px;
  padding: 10px;
  font-size: 14px;
  border-radius: 6px;
  border: 1px solid #ccc;
}

.modal-content button {
  background-color: #4caf50;
  color: white;
  border: none;
  cursor: pointer;
}

.close {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 25px;
  font-weight: bold;
  cursor: pointer;
  color: #777;
}
</style>