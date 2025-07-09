<?php
include('../includes/db.php');
$parent_id = $_SESSION['user_id'];
?>

<h2><i class="fas fa-cogs"></i> Learning Preference</h2>

<form method="POST" action="includes/save_learning_preference.php" id="preferenceForm" class="preference-form">
  <div class="input-group">
    <label for="student_id">Select Child:</label>
    <select name="student_id" id="student_id" required>
      <?php
        $children = $conn->query("SELECT id, name FROM students WHERE parent_id = $parent_id");
        while ($child = $children->fetch_assoc()):
      ?>
        <option value="<?= $child['id'] ?>"><?= htmlspecialchars($child['name']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>

  <div class="input-group">
    <label for="learning_mode">Learning Mode:</label>
    <select name="learning_mode" id="learning_mode" required>
      <option value="">-- Select Mode --</option>
      <option value="in_person">In-Person</option>
      <option value="online">Online</option>
      <option value="home">Home-based</option>
    </select>
  </div>

  <div class="input-group">
    <label for="specialization">Subject Specialization:</label>
    <select name="specialization" id="specialization" required>
      <option value="">-- Select Subject --</option>
      <option>Tajweed</option>
      <option>Hadith</option>
      <option>Fiqh</option>
      <option>Arabic</option>
    </select>
  </div>

  <div class="input-group">
    <label for="join_meetings">Join Parent Meetings?</label>
    <select name="join_meetings" id="join_meetings" required>
      <option value="">-- Choose Option --</option>
      <option value="1">Yes</option>
      <option value="0">No</option>
    </select>
  </div>

  <div class="input-group">
    <label for="teacherDropdown">Select Teacher:</label>
 <select name="assigned_teacher_id" id="teacherDropdown" required disabled>
  <option value="">Please select subject and mode</option>
</select>
</select>
  </div>

  <button type="submit" class="submit-btn">Save Preferences</button>
</form>

<div id="preferenceSuccess" class="success-message">
  ✅ Preferences saved successfully!
</div>

<script>
document.getElementById('specialization').addEventListener('change', fetchTeachers);
document.getElementById('learning_mode').addEventListener('change', fetchTeachers);

function fetchTeachers() {
  const specialization = document.getElementById('specialization').value;
  const mode = document.getElementById('learning_mode').value;

  const dropdown = document.getElementById('teacherDropdown');
  dropdown.innerHTML = ''; // Clear before populating

  if (specialization && mode) {
    dropdown.disabled = true; // disable while loading
    dropdown.innerHTML = '<option>Loading teachers...</option>';

    fetch(`../includes/fetch_teachers.php?specialization=${encodeURIComponent(specialization)}&mode=${encodeURIComponent(mode)}`)
      .then(res => res.json())
      .then(data => {
        dropdown.innerHTML = ''; // Clear loading state

        if (data.length === 0) {
          dropdown.innerHTML = '<option value="">No teacher found</option>';
        } else {
          dropdown.innerHTML = '<option value="">Select a teacher</option>';
          data.forEach(teacher => {
            const opt = document.createElement('option');
            opt.value = teacher.id;
            opt.textContent = `${teacher.name} (${teacher.specialization})`;
            dropdown.appendChild(opt);
          });
          dropdown.disabled = false; // enable after loading
        }
      })
      .catch(err => {
        console.error("Error loading teachers", err);
        dropdown.innerHTML = '<option value="">⚠ Error loading teachers</option>';
        dropdown.disabled = true;
      });
  } else {
    dropdown.innerHTML = '<option value="">Please select subject and mode</option>';
    dropdown.disabled = true;
  }
}

// Handle form submission via AJAX
document.getElementById('preferenceForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);

  fetch('includes/save_learning_preference.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(data => {
    document.getElementById('preferenceSuccess').style.display = 'block';
    setTimeout(() => {
      document.getElementById('preferenceSuccess').style.display = 'none';
    }, 3000);
  })
  .catch(err => alert("Failed to save preferences."));
});
</script>