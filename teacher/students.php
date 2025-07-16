<?php
include('../includes/db.php');

$teacher_id = $_SESSION['user_id'] ?? 0;

$query = $conn->prepare("
    SELECT 
        s.id, s.name AS student_name, s.age, s.gender, s.specialization,
        s.photo,
        COALESCE(u.name, 'Unknown') AS parent_name
    FROM students s
    LEFT JOIN parents p ON s.parent_id = p.id
    LEFT JOIN users u ON p.user_id = u.id
    WHERE s.assigned_teacher_id = ?
");
$query->bind_param("i", $teacher_id);
$query->execute();
$result = $query->get_result();
?>

<h2>📚 My Students</h2>

<div class="student-cards">
<?php while ($row = $result->fetch_assoc()): ?>
  <div class="student-card">
    <img src="../uploads/students/<?= htmlspecialchars($row['photo']) ?>" alt="Student Photo" class="student-photo">
    <div class="student-info">
      <h3><?= htmlspecialchars($row['student_name']) ?></h3>
      <p><strong>Age:</strong> <?= $row['age'] ?></p>
      <p><strong>Gender:</strong> <?= $row['gender'] ?></p>
      <p><strong>Specialization:</strong> <?= $row['specialization'] ?></p>
      <p><strong>Parent:</strong> <?= $row['parent_name'] ?></p>
    </div>
    <div class="student-actions">
      <button class="assign-btn" onclick="openAssignmentModal(<?= $row['id'] ?>, '<?= addslashes($row['student_name']) ?>')">📘 Add Assignment</button>
      <button class="mark-btn">📝 Mark</button>
    </div>
  </div>
<?php endwhile; ?>
</div>

<!-- 📄 Assignment Modal -->
<div id="assignmentModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h3>📘 Add Assignment for <span id="studentNameLabel"></span></h3>
    <form id="assignmentForm" enctype="multipart/form-data">
      <input type="hidden" name="student_id" id="modalStudentId">
      <input type="text" name="title" placeholder="Assignment Title" required>
      <textarea name="description" placeholder="Description" required></textarea>
      <input type="date" name="due_date" required>
      <input type="file" name="attachment" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
      <button type="submit">Submit</button>
      <div id="formMessage" style="margin-top:10px;"></div>
    </form>
  </div>
</div>

<script>
function openAssignmentModal(studentId, studentName) {
  document.getElementById("assignmentModal").style.display = "block";
  document.getElementById("modalStudentId").value = studentId;
  document.getElementById("studentNameLabel").textContent = studentName;
}

function closeModal() {
  document.getElementById("assignmentModal").style.display = "none";
  document.getElementById("formMessage").innerText = "";
  document.getElementById("assignmentForm").reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
  const modal = document.getElementById("assignmentModal");
  if (event.target === modal) {
    closeModal();
  }
}

// Handle AJAX assignment form submission
document.getElementById("assignmentForm").addEventListener("submit", function(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  const messageDiv = document.getElementById("formMessage");

  fetch("../includes/save_assignment.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(data => {
    messageDiv.innerText = data;
    messageDiv.style.color = data.toLowerCase().includes("success") ? "green" : "red";
  })
  .catch(err => {
    messageDiv.innerText = "Error submitting assignment.";
    messageDiv.style.color = "red";
  });
});
</script>

<style>
.student-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
}
.student-card {
  background: #fff;
  padding: 15px;
  border-radius: 10px;
  width: 280px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}
.student-photo {
  width: 100%;
  height: 160px;
  object-fit: cover;
  border-radius: 8px;
}
.student-info {
  margin-top: 10px;
}
.student-actions {
  margin-top: 10px;
  display: flex;
  justify-content: space-between;
}
.student-actions button {
  background: #4caf50;
  color: white;
  padding: 6px 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}
.student-actions .mark-btn {
  background: #2196f3;
}

/* MODAL */
.modal {
  display: none;
  position: fixed;
  z-index: 9999;
  padding-top: 80px;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.5);
}
.modal-content {
  background-color: #fff;
  margin: auto;
  padding: 20px;
  border-radius: 10px;
  width: 90%;
  max-width: 500px;
  position: relative;
}
.close {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
  position: absolute;
  top: 10px;
  right: 20px;
  cursor: pointer;
}
.close:hover {
  color: #000;
}
.modal-content form input,
.modal-content form textarea,
.modal-content form button {
  width: 100%;
  padding: 10px;
  margin-top: 10px;
}
.modal-content form button {
  background-color: #4caf50;
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

h2{
  color:white;
}
</style>