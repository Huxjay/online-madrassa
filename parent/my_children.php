<?php
include('../includes/parent_session.php');
include('../includes/db.php');

$parent_id = $_SESSION['user_id'];

$children = $conn->prepare("SELECT id, name, age, gender FROM students WHERE parent_id = ?");
$children->bind_param("i", $parent_id);
$children->execute();
$result = $children->get_result();
?>

<h2><i class="fas fa-child"></i> My Children</h2>

<table class="approval-table">
  <thead>
    <tr>
      <th>Name</th>
      <th>Age</th>
      <th>Gender</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['age']) ?></td>
        <td><?= htmlspecialchars($row['gender']) ?></td>
        <td>
          <button class="btn-approve edit-btn"
                  data-id="<?= $row['id'] ?>"
                  data-name="<?= $row['name'] ?>"
                  data-age="<?= $row['age'] ?>"
                  data-gender="<?= $row['gender'] ?>">
            <i class="fas fa-edit"></i> Edit
          </button>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<!-- Edit Modal -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h3>Edit Child Information</h3>
    <form method="POST" action="pages/update_child.php">
      <input type="hidden" name="child_id" id="child_id">
      <label>Name:</label>
      <input type="text" name="name" id="child_name" required>
      <label>Age:</label>
      <input type="number" name="age" id="child_age" required>
      <label>Gender:</label>
      <select name="gender" id="child_gender" required>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
      </select>
      <button type="submit" class="btn-approve">Update</button>
    </form>
  </div>
</div>

<style>
.modal {
  display: none;
  position: fixed;
  z-index: 999;
  left: 0; top: 0;
  width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.6);
}

.modal-content {
  background: #fff;
  color: #000;
  margin: 10% auto;
  padding: 20px;
  width: 400px;
  border-radius: 10px;
  animation: fadeIn 0.4s ease;
}

.modal-content input, .modal-content select {
  width: 100%;
  padding: 10px;
  margin: 8px 0 15px;
}

.modal-content button {
  width: 100%;
  padding: 10px;
}
</style>

<script>
  document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.getElementById('child_id').value = this.dataset.id;
      document.getElementById('child_name').value = this.dataset.name;
      document.getElementById('child_age').value = this.dataset.age;
      document.getElementById('child_gender').value = this.dataset.gender;
      document.getElementById('editModal').style.display = 'block';
    });
  });

  function closeModal() {
    document.getElementById('editModal').style.display = 'none';
  }
</script>