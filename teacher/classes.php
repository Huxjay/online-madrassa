<?php
include('../includes/db.php');
$teacher_id = $_SESSION['user_id'] ?? null;

// === Fetch students and adults ===
$students = $conn->query("
  SELECT s.id, s.name, s.learning_mode, s.specialization, u.name AS parent_name, s.parent_id
  FROM students s
  JOIN users u ON s.parent_id = u.id
  WHERE s.assigned_teacher_id = $teacher_id
");

$adults = $conn->query("
  SELECT a.id, 'Adult Learner' AS name, a.learning_mode, a.specialization, u.name AS parent_name, a.parent_id
  FROM adult_learners a
  JOIN users u ON a.parent_id = u.id
  WHERE a.assigned_teacher_id = $teacher_id
");

$learners = [];
while ($s = $students->fetch_assoc()) $learners[] = $s;
while ($a = $adults->fetch_assoc())  $learners[] = $a;

// === Group learners ===
$onlineGroups = [];
$inPersonGroup = [];
$homeBasedGroups = [];

foreach ($learners as $l) {
  if ($l['learning_mode'] === 'online') {
    $onlineGroups[$l['specialization']][] = $l;
  } elseif ($l['learning_mode'] === 'in_person') {
    $inPersonGroup[] = $l;
  } elseif ($l['learning_mode'] === 'home') {
    $homeBasedGroups[$l['parent_id']][] = $l;
  }
}

// === Fetch schedules ===
$scheduleQuery = $conn->prepare("SELECT * FROM class_schedule WHERE teacher_id = ?");
$scheduleQuery->bind_param("i", $teacher_id);
$scheduleQuery->execute();
$scheduleResult = $scheduleQuery->get_result();
$schedules = [];

while ($row = $scheduleResult->fetch_assoc()) {
  $schedules[$row['group_type']][$row['group_identifier']][] = $row;
}
?>

<h2><i class="fas fa-book-reader"></i> My Classes</h2>
<div class="class-section">

  <!-- Online Classes -->
  <div class="class-card">
    <h3>📡 Online Classes</h3>
    <?php if (empty($onlineGroups)): ?>
      <p>No online classes yet.</p>
    <?php else: ?>
      <?php foreach ($onlineGroups as $subject => $group): ?>
        <div class="sub-group">
          <h4>📝 <?= htmlspecialchars($subject) ?></h4>
          <ul>
            <?php foreach ($group as $learner): ?>
              <li><?= htmlspecialchars($learner['name']) ?> <small>(Parent: <?= htmlspecialchars($learner['parent_name']) ?>)</small></li>
            <?php endforeach; ?>
          </ul>
          <button onclick="openScheduleForm('online', '<?= $subject ?>')">📅 Schedule</button>
          <?php if (!empty($schedules['online'][$subject])): ?>
            <ul class="schedule-list">
              <?php foreach ($schedules['online'][$subject] as $sch): ?>
                <li>
                  <strong><?= $sch['topic'] ?></strong> on <?= $sch['schedule_date'] ?> at <?= $sch['schedule_time'] ?> (<?= $sch['duration_minutes'] ?> mins)
                  <br><small><?= $sch['subtopics'] ?></small><br>
                  <button onclick='editSchedule(<?= json_encode($sch) ?>)'>✏ Edit</button>
                  <button onclick='deleteSchedule(<?= $sch['id'] ?>)'>🗑 Delete</button>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- In-Person Classes -->
  <div class="class-card">
    <h3>🏫 In-Person Students</h3>
    <?php if (empty($inPersonGroup)): ?>
      <p>No in-person classes yet.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($inPersonGroup as $learner): ?>
          <li><?= htmlspecialchars($learner['name']) ?> <small>(Subject: <?= $learner['specialization'] ?>, Parent: <?= $learner['parent_name'] ?>)</small></li>
        <?php endforeach; ?>
      </ul>
      <button onclick="openScheduleForm('in_person', 'general')">📅 Schedule</button>
      <?php if (!empty($schedules['in_person']['general'])): ?>
        <ul class="schedule-list">
          <?php foreach ($schedules['in_person']['general'] as $sch): ?>
            <li>
              <strong><?= $sch['topic'] ?></strong> on <?= $sch['schedule_date'] ?> at <?= $sch['schedule_time'] ?> (<?= $sch['duration_minutes'] ?> mins)
              <br><small><?= $sch['subtopics'] ?></small><br>
              <button onclick='editSchedule(<?= json_encode($sch) ?>)'>✏ Edit</button>
              <button onclick='deleteSchedule(<?= $sch['id'] ?>)'>🗑 Delete</button>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <!-- Home-Based Classes -->
  <div class="class-card">
    <h3>🏠 Home-Based Students</h3>
    <?php if (empty($homeBasedGroups)): ?>
      <p>No home-based students yet.</p>
    <?php else: ?>
      <?php foreach ($homeBasedGroups as $parent_id => $group): ?>
        <div class="sub-group">
          <h4>👨‍👩‍👧 Family: <?= htmlspecialchars($group[0]['parent_name']) ?></h4>
          <ul>
            <?php foreach ($group as $learner): ?>
              <li><?= htmlspecialchars($learner['name']) ?> <small>(<?= $learner['specialization'] ?>)</small></li>
            <?php endforeach; ?>
          </ul>
          <button onclick="openScheduleForm('home', '<?= $parent_id ?>')">📅 Schedule</button>
          <?php if (!empty($schedules['home'][$parent_id])): ?>
            <ul class="schedule-list">
              <?php foreach ($schedules['home'][$parent_id] as $sch): ?>
                <li>
                  <strong><?= $sch['topic'] ?></strong> on <?= $sch['schedule_date'] ?> at <?= $sch['schedule_time'] ?> (<?= $sch['duration_minutes'] ?> mins)
                  <br><small><?= $sch['subtopics'] ?></small><br>
                  <button onclick='editSchedule(<?= json_encode($sch) ?>)'>✏ Edit</button>
                  <button onclick='deleteSchedule(<?= $sch['id'] ?>)'>🗑 Delete</button>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Schedule Modal -->
<div id="scheduleModal" class="modal" style="display:none;">
  <div class="modal-content">
    <h3>📅 Schedule Class</h3>
    <form id="scheduleForm">
      <input type="hidden" name="schedule_id" id="schedule_id">
      <input type="hidden" name="group_type" id="group_type">
      <input type="hidden" name="group_identifier" id="group_identifier">
      <div>
        <label>Topic:</label>
        <input type="text" name="topic" required>
      </div>
      <div>
        <label>Date:</label>
        <input type="date" name="schedule_date" required>
      </div>
      <div>
        <label>Time:</label>
        <input type="time" name="schedule_time" required>
      </div>
      <div>
        <label>Duration (minutes):</label>
        <input type="number" name="duration_minutes" required min="15" step="15">
      </div>
      <div>
        <label>Subtopics (optional):</label>
        <textarea name="subtopics" rows="2"></textarea>
      </div>
      <div style="margin-top: 15px;">
        <button type="submit">✅ Save</button>
        <button type="button" onclick="closeScheduleForm()">❌ Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Styles -->
<style>
.class-section { display: grid; gap: 20px; margin-top: 20px; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); }
.class-card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 20px; color: white; backdrop-filter: blur(5px); }
.class-card h3 { color: #ffe082; margin-top: 0; }
.sub-group { margin-bottom: 15px; }
.schedule-list { margin-top: 10px; padding-left: 20px; }
.schedule-list li { margin-bottom: 8px; }
.modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-content { background: white; padding: 20px; border-radius: 10px; max-width: 500px; width: 90%; }
.modal-content input, .modal-content textarea, .modal-content button { width: 100%; margin-top: 10px; padding: 8px; border-radius: 5px; border: 1px solid #ccc; }
.modal-content button[type="submit"] { background: #0288d1; color: white; }
</style>

<!-- JavaScript -->
<script>
function openScheduleForm(type, identifier) {
  document.getElementById('schedule_id').value = "";
  document.getElementById('group_type').value = type;
  document.getElementById('group_identifier').value = identifier;
  document.getElementById('scheduleModal').style.display = 'flex';
}

function closeScheduleForm() {
  document.getElementById('scheduleModal').style.display = 'none';
  document.getElementById('scheduleForm').reset();
}

function editSchedule(data) {
  document.getElementById('schedule_id').value = data.id;
  document.getElementById('group_type').value = data.group_type;
  document.getElementById('group_identifier').value = data.group_identifier;
  document.querySelector('[name="topic"]').value = data.topic;
  document.querySelector('[name="schedule_date"]').value = data.schedule_date;
  document.querySelector('[name="schedule_time"]').value = data.schedule_time;
  document.querySelector('[name="duration_minutes"]').value = data.duration_minutes;
  document.querySelector('[name="subtopics"]').value = data.subtopics;
  document.getElementById('scheduleModal').style.display = 'flex';
}

function deleteSchedule(id) {
  if (!confirm("Are you sure you want to delete this schedule?")) return;

  fetch('../includes/delete_schedule.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'id=' + encodeURIComponent(id)
  })
  .then(res => res.text())
  .then(response => {
    if (response.trim() === 'success') {
      alert("🗑 Schedule deleted.");
      location.reload();
    } else {
      alert("❌ Error deleting schedule.");
    }
  });
}

document.getElementById('scheduleForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  fetch('../includes/save_schedule.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(response => {
    if (response.trim() === 'success') {
      alert("✅ Schedule saved!");
      closeScheduleForm();
      location.reload();
    } else {
      alert("❌ " + response);
    }
  });
});
</script>