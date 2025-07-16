<?php
include('../includes/db.php');

$parent_id = $_SESSION['user_id'];

// Get children for this parent
$children = $conn->query("SELECT id, name FROM students WHERE parent_id = $parent_id");
$has_children = $children->num_rows > 0;
?>



<h2><i class="fas fa-cogs"></i> Learning Preference</h2>

<div class="form-wrapper">
  <?php if ($has_children): ?>
  <form method="POST" action="includes/save_learning_preference.php" class="form-box" id="childForm">
    <h3>👶 My Children</h3>
    <div class="input-group">
      <label>Select Child:</label>
      <select name="student_id" required>
        <?php while ($child = $children->fetch_assoc()): ?>
          <option value="<?= $child['id'] ?>"><?= htmlspecialchars($child['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="input-group">
      <label>Learning Mode:</label>
      <select name="learning_mode" class="child-learning-mode" required>
        <option value="">-- Select Mode --</option>
        <option value="in_person">In-Person</option>
        <option value="home">Home-based</option>
      </select>
    </div>

    <div class="input-group">
      <label>Subject Specialization:</label>
      <select name="specialization" class="child-specialization" required>
        <option value="">-- Select Subject --</option>
        <option>Tajweed</option>
        <option>Hadith</option>
        <option>Fiqh</option>
        <option>Aqidah</option>
        <option>Seerah</option>
        <option>Tafsir</option>
        <option>Arabic</option>
      </select>
    </div>

    <div class="input-group">
      <label>Join Parent Meetings?</label>
      <select name="join_meetings" required>
        <option value="">-- Choose Option --</option>
        <option value="1">Yes</option>
        <option value="0">No</option>
      </select>
    </div>

    <div class="input-group">
      <label>Select Teacher:</label>
      <select name="assigned_teacher_id" class="child-teacher" required disabled>
        <option>Please select subject and mode</option>
      </select>
    </div>
      <div id="child-teacher-preview" class="teacher-preview" style="margin-top: 10px;"></div>


    <button type="submit" class="submit-btn">Save Preference</button>
    <div class="success-message" id="childSuccess">✅ Preferences saved!</div>
  </form>
  <?php endif; ?>

  <form method="POST" action="includes/save_adult_preference.php" class="form-box" id="adultForm">
    <h3>🧑 Myself</h3>

    <div class="input-group">
      <label>Learning Mode:</label>
      <select name="learning_mode" class="adult-learning-mode" required>
        <option value="">-- Select Mode --</option>
        <option value="online">Online</option>
        <option value="home">Home-based</option>
      </select>
    </div>

    <div class="input-group">
      <label>Subject Specialization:</label>
      <select name="specialization" class="adult-specialization" required>
        <option value="">-- Select Subject --</option>
        <option>Tajweed</option>
        <option>Hadith</option>
        <option>Fiqh</option>
        <option>Aqidah</option>
        <option>Seerah</option>
        <option> Tafsir</option>
        <option>Arabic</option>
      </select>
    </div>

   <div class="input-group">
      <label>Join Parent Meetings?</label>
      <select name="join_meetings" required>
        <option value="">-- Choose Option --</option>
        <option value="1">Yes</option>
        <option value="0">No</option>
      </select>
    </div>

    <div class="input-group">
      <label>Select Teacher:</label>
      <select name="assigned_teacher_id" class="adult-teacher" required disabled>
        <option>Please select subject and mode</option>
      </select>
    </div>
      <div id="adult-teacher-preview" class="teacher-preview" style="margin-top: 10px;"></div>


    <button type="submit" class="submit-btn">Save Preference</button>
    <div class="success-message" id="adultSuccess">✅Preferences saved!</div>
  </form>
</div>

<script>
function setupTeacherLoader(type) {
  const specializationSelect = document.querySelector(`.${type}-specialization`);
  const modeSelect = document.querySelector(`.${type}-learning-mode`);
  const teacherSelect = document.querySelector(`.${type}-teacher`);
  const previewDiv = document.getElementById(`${type}-teacher-preview`);

  specializationSelect.addEventListener('change', fetchTeachers);
  modeSelect.addEventListener('change', fetchTeachers);

  function fetchTeachers() {
    const specialization = specializationSelect.value;
    const mode = modeSelect.value;

    if (!specialization || !mode) {
      teacherSelect.innerHTML = '<option>Please select subject and mode</option>';
      teacherSelect.disabled = true;
      if (previewDiv) previewDiv.innerHTML = '';
      return;
    }

    teacherSelect.innerHTML = '<option>Loading teachers...</option>';
    teacherSelect.disabled = true;
    if (previewDiv) previewDiv.innerHTML = '';

    fetch(`../includes/fetch_teachers.php?specialization=${encodeURIComponent(specialization)}&mode=${encodeURIComponent(mode)}`)
      .then(res => res.json())
.then(data => {
  teacherSelect.innerHTML = '';
  const previewBox = document.getElementById(`${type}TeacherPreview`);

  if (data.length === 0) {
    teacherSelect.innerHTML = '<option>No teacher found</option>';
    if (previewBox) previewBox.innerHTML = '';
  } else {
    teacherSelect.innerHTML = '<option value="">Select a teacher</option>';
    data.forEach(t => {
      const opt = document.createElement('option');
      opt.value = t.id;
      opt.textContent = `${t.name} (${t.specialization})`;
      teacherSelect.appendChild(opt);
    });

    // Add change listener to show image when selection changes
    teacherSelect.addEventListener('change', function () {
      const selectedId = this.value;
      const selected = data.find(t => t.id == selectedId);
      if (selected && previewBox) {
        previewBox.innerHTML = `
          <img src="${selected.picture}" alt="Profile" style="width:60px;height:60px;border-radius:50%;">
          <p>${selected.name}</p>
        `;
      } else if (previewBox) {
        previewBox.innerHTML = '';
      }
    });
  }

  teacherSelect.disabled = false;
})
      .catch(err => {
        console.error(err);
        teacherSelect.innerHTML = '<option>Error loading</option>';
        teacherSelect.disabled = true;
        if (previewDiv) previewDiv.innerHTML = '';
      });
  }
}

// Setup both forms
setupTeacherLoader('child');
setupTeacherLoader('adult');

// Submit handlers
document.getElementById('childForm')?.addEventListener('submit', e => {
  e.preventDefault();
  const formData = new FormData(e.target);
  fetch('../includes/save_learning_preference.php', {
    method: 'POST', body: formData
  })
  .then(res => res.text())
  .then(() => document.getElementById('childSuccess').style.display = 'block');
});

document.getElementById('adultForm')?.addEventListener('submit', e => {
  e.preventDefault();
  const formData = new FormData(e.target);
  fetch('../includes/save_adult_preference.php', {
    method: 'POST', body: formData
  })
  .then(res => res.text())
  .then(() => document.getElementById('adultSuccess').style.display = 'block');
});
</script>