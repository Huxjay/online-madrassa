<?php  
include('../includes/db.php');  
$parent_id = $_SESSION['user_id'] ?? 0;  
  
$query = $conn->prepare("  
  SELECT   
    s.id, s.name, s.age, s.gender, s.specialization, s.photo,  
    COALESCE(u.name, 'Not assigned') AS teacher_name  
  FROM students s  
  LEFT JOIN users u ON s.assigned_teacher_id = u.id  
  WHERE s.parent_id = ?  
");  
$query->bind_param("i", $parent_id);  
$query->execute();  
$result = $query->get_result();  
?>  
  
<h2>👨‍👧‍👦 My Children</h2>  
  
<div class="children-cards">  
<?php while ($child = $result->fetch_assoc()): ?>  
  <div class="child-card">  
    <img src="../uploads/students/<?= htmlspecialchars($child['photo']) ?>" alt="Student Photo" class="child-photo">  
  
    <div class="child-info">  
      <h3><?= htmlspecialchars($child['name']) ?></h3>  
      <p><strong>Age:</strong> <?= $child['age'] ?></p>  
      <p><strong>Gender:</strong> <?= $child['gender'] ?></p>  
      <p><strong>Specialization:</strong> <?= htmlspecialchars($child['specialization']) ?></p>  
      <p><strong>Teacher:</strong> <?= htmlspecialchars($child['teacher_name']) ?></p>  
  
      <div class="child-buttons">  
        <button class="edit-btn" onclick="editChild(<?= $child['id'] ?>)">✏ Edit</button>  
        <button class="assignment-btn" onclick="viewAssignments(<?= $child['id'] ?>, '<?= addslashes($child['name']) ?>')">📘 Assignments</button>  
        <button class="attendance-btn" onclick="viewAttendance(<?= $child['id'] ?>)">📅 Attendance</button>  
      </div>  
    </div>  
  </div>  
<?php endwhile; ?>  
</div>  
  
<!-- ✏ Edit Modal -->  
<div id="editChildModal" class="modal">  
  <div class="modal-content">  
    <span class="close" onclick="closeEditModal()">&times;</span>  
    <h3>✏ Edit Child Info</h3>  
    <form id="editChildForm" method="POST" enctype="multipart/form-data">  
      <input type="hidden" name="student_id" id="editStudentId">  
      <input type="text" name="name" id="editName" placeholder="Full Name" required>  
      <input type="number" name="age" id="editAge" placeholder="Age" required>  
      <select name="gender" id="editGender" required>  
        <option value="">Select Gender</option>  
        <option value="male">Male</option>  
        <option value="female">Female</option>  
      </select>  
      <input type="text" name="specialization" id="editSpecialization" placeholder="Specialization" required>  
      <input type="file" name="photo">  
      <button type="submit">Update Info</button>  
    </form>  
  </div>  
</div>  
  
<!-- 📘 Assignment Modal -->  
<div id="assignmentModal" class="modal">  
  <div class="modal-content">  
    <span class="close" onclick="closeAssignmentModal()">&times;</span>  
    <h3>📘 Assignments for <span id="assignChildName"></span></h3>  
    <div id="assignmentList">Loading...</div>  
  </div>  
</div>  
  
<!-- 📅 Attendance Modal -->  
<div id="attendanceModal" class="modal">  
  <div class="modal-content">  
    <span class="close" onclick="closeAttendanceModal()">&times;</span>  
    <h3>📅 Attendance Records</h3>  
    <div id="attendanceList">Loading...</div>  
  </div>  
</div>  
  
<!-- JS -->  
<script>  
function editChild(childId) {  
  fetch(`../includes/get_child_info.php?id=${childId}`)  
    .then(res => res.json())  
    .then(data => {  
      if (data.success) {  
        document.getElementById("editStudentId").value = data.child.id;  
        document.getElementById("editName").value = data.child.name;  
        document.getElementById("editAge").value = data.child.age;  
        document.getElementById("editGender").value = data.child.gender;  
        document.getElementById("editSpecialization").value = data.child.specialization;  
        document.getElementById("editChildModal").style.display = "block";  
      } else {  
        alert("❌ Failed to load child info.");  
      }  
    });  
}  
  
function closeEditModal() {  
  document.getElementById("editChildModal").style.display = "none";  
}  
  
document.getElementById("editChildForm").onsubmit = function(e) {  
  e.preventDefault();  
  const formData = new FormData(this);  
  fetch("../includes/update_child.php", {  
    method: "POST",  
    body: formData  
  })  
  .then(res => res.text())  
  .then(msg => {  
    alert(msg);  
    closeEditModal();  
    location.reload();  
  });  
};  
  
function viewAssignments(studentId, studentName) {  
  const modal = document.getElementById('assignmentModal');  
  document.getElementById('assignChildName').textContent = studentName;  
  document.getElementById('assignmentList').innerHTML = "⏳ Loading...";  
  
  fetch(`../includes/view_assignments.php?student_id=${studentId}`)  
    .then(res => res.text())  
    .then(html => {  
      document.getElementById('assignmentList').innerHTML = html;  
      modal.style.display = "block";  
    })  
    .catch(err => {  
      document.getElementById('assignmentList').innerHTML = "❌ Error loading assignments.";  
    });  
}  
  
function closeAssignmentModal() {  
  document.getElementById("assignmentModal").style.display = "none";  
}  
  
function viewAttendance(studentId) {  
  const modal = document.getElementById('attendanceModal');  
  document.getElementById('attendanceList').innerHTML = "⏳ Loading...";  
  
  fetch(`../includes/view_attendance.php?student_id=${studentId}`)  
    .then(res => res.text())  
    .then(html => {  
      document.getElementById('attendanceList').innerHTML = html;  
      modal.style.display = "block";  
    })  
    .catch(err => {  
      document.getElementById('attendanceList').innerHTML = "❌ Error loading attendance.";  
      console.error(err);  
    });  
}  
  
function closeAttendanceModal() {  
  document.getElementById('attendanceModal').style.display = "none";  
}  
  
window.onclick = function(event) {  
  const editModal = document.getElementById("editChildModal");  
  const assignModal = document.getElementById("assignmentModal");  
  const attendanceModal = document.getElementById("attendanceModal");  
  if (event.target === editModal) closeEditModal();  
  if (event.target === assignModal) closeAssignmentModal();  
  if (event.target === attendanceModal) closeAttendanceModal();  
}  
</script>




<style>
.children-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: center;
}

.child-card {
  background: linear-gradient(135deg, #f5f7fa, #e4ecf5);
  padding: 15px;
  border-radius: 12px;
  width: 280px;
  color: #222;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.child-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.2);
}

.child-photo {
  width: 100%;
  height: 160px;
  object-fit: cover;
  border-radius: 8px;
  border: 2px solid #ccc;
}

.child-info {
  margin-top: 10px;
  text-align: center;
  color: #333;
}

.child-info h3 {
  font-size: 18px;
  margin-bottom: 5px;
}

.child-info p {
  margin: 2px 0;
  font-size: 14px;
  color:black;
}

.child-buttons {
  margin-top: 15px;
  display: flex;
  justify-content: space-between;
  gap: 10px;
  flex-wrap: wrap;
  width: 100%;
}

.child-buttons button {
  flex: 1;
  padding: 8px 10px;
  font-size: 14px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  color: white;
  transition: background 0.3s ease;
}

.edit-btn {
  background-color: #f57c00;
}

.edit-btn:hover {
  background-color: #e65100;
}

.assignment-btn {
  background-color: #2196f3;
}

.assignment-btn:hover {
  background-color: #0d8ae2;
}

.attendance-btn {
  background-color: #4caf50;
}

.attendance-btn:hover {
  background-color: #388e3c;
}

/* Modal styles */
.modal {
  display: none;
  position: fixed;
  z-index: 9999;
  padding-top: 70px;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
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
.modal-content select,
.modal-content button,
.modal-content textarea {
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

.modal-content button:hover {
  background-color: #388e3c;
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

.close:hover {
  color: #000;
}

/* Optional: Dark Mode Compatibility */
body.dark-mode .child-card {
  background: #2b2b2b;
  color: #f5f5f5;
}

body.dark-mode .child-info {
  color: #ddd;
}

body.dark-mode .modal-content {
  background-color: #1e1e1e;
  color: #f5f5f5;
}

body.dark-mode .modal-content input,
body.dark-mode .modal-content select,
body.dark-mode .modal-content textarea {
  background-color: #2d2d2d;
  color: #f5f5f5;
  border: 1px solid #555;
}



.assignment-card {
  background: #f9f9f9;
  border-left: 5px solid #2196f3;
  padding: 10px;
  margin-bottom: 15px;
  border-radius: 8px;

  color:black;
}
.assignment-card h4,
.assignment-card p{
  color: #000;
  margin: 0 0 5px;
}

.assignment-card p {
  margin: 5px 0;
}

.download-link {
  color: #007bff;
  text-decoration: underline;
}

21.attendance-record, {
  background: #fefefe;
  border-left: 5px solid #4caf50;
  padding: 10px;
  margin-bottom: 15px;
  border-radius: 8px;
  box-shadow: 0 1px 5px rgba(0,0,0,0.1);
}

/* Ensure modal heading and content for attendance is black */
#attendanceModal h3 {
  color: black;
}

#attendanceList {
  color: black;
}

#attendanceModal {
  color: black;
  background-color: rgba(0, 0, 0, 0.5); /* keep dimmed background */
}
.attendance-record p {
  margin: 5px 0;
  font-size: 14px;
  color:black;
}
</style>