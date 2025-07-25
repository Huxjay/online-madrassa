lets me provide an example of working page under paents

this one is 

index.php 


<?php include('../includes/parent_session.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Parent Dashboard - Online Madrassa</title>
  <link rel="stylesheet" href="../assets/css/parent.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    .notification-bell-container {
      position: relative;
      margin-left: 15px;
    }

    #notificationBell {
      background: none;
      border: none;
      cursor: pointer;
      position: relative;
      font-size: 20px;
      color: #fff;
    }

    .count {
      position: absolute;
      top: -6px;
      right: -6px;
      background: red;
      color: white;
      font-size: 10px;
      padding: 2px 6px;
      border-radius: 50%;
    }

    .dropdown {
      position: absolute;
      right: 0;
      top: 30px;
      width: 260px;
      background: #fff;
      color: #333;
      border: 1px solid #ccc;
      border-radius: 4px;
      z-index: 1000;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .dropdown.hidden {
      display: none;
    }

    #notificationList {
      list-style: none;
      margin: 0;
      padding: 0;
      max-height: 220px;
      overflow-y: auto;
    }

    #notificationList li {
      padding: 10px;
      border-bottom: 1px solid #eee;
      font-size: 14px;
    }

    .dropdown-footer {
      text-align: center;
      padding: 8px;
      font-size: 12px;
      background: #f9f9f9;
    }
  </style>
</head>
<body>

<div class="bg-layer"></div>

<div class="parent-container" id="parentContainer">
  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="logo"><i class="fas fa-mosque"></i> Madrassa</div>
    <nav>
      <ul>
          <li><a href="index.php?page=myself" class="<?= ($_GET['page'] ?? '') === 'myself.php' ? 'active' : '' ?>"><i class="fas fa-user-graduate"></i> myself</a></li>

        <li><a href="index.php?page=my_children"><i class="fas fa-child"></i> My children</a></li>
        <li><a href="index.php?page=chat_box"><i class="fas fa-comments"></i>Live Chat</a></li>
        <li><a href="#"><i class="fas fa-video"></i> Online Classes</a></li>
        <li><a href="index.php?page=learning_preference"><i class="fas fa-cogs"></i> Learning Preference</a></li>
        <li><a href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </nav>
  </aside>

  <!-- Main Content -->
  <div class="main-content">
    <header class="header">
      <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
      <div class="right-actions">
        <!-- Notification Bell -->
        <div class="notification-bell-container">
          <button id="notificationBell">
            <i class="fas fa-bell"></i>
            <span id="notificationCount" class="count">0</span>
          </button>
          <div id="notificationDropdown" class="dropdown hidden">
            <ul id="notificationList"></ul>
            <div class="dropdown-footer"><a href="#">View all</a></div>
          </div>
        </div>
        <button class="dark-mode-toggle" onclick="toggleDarkMode()"><i class="fas fa-moon"></i></button>
      </div>
    </header>

    <main class="dashboard" id="main-content-area">
      <?php
        $page = $_GET['page'] ?? 'home';
        switch ($page) {
          case 'my_children':
            include 'my_children.php';
            break;
          case 'learning_preference':
            include 'learning_preference.php';
            break;

          case 'myself':
            include 'myself.php';
            break;

            case 'chat_box':
            include '../chat/chat_box.php';
            break;
          default:
            echo "<h1>Welcome, {$_SESSION['user_name']} 👋</h1><p>Manage your children’s progress and activities.</p>";
        }


      ?>
    </main>

    <footer class="footer">
      <p>&copy; <?= date('Y') ?> Online Madrassa | Parent Panel</p>
    </footer>
  </div>
</div>

<script>
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
  }

  function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
  }

  document.addEventListener("DOMContentLoaded", function () {
    const bell = document.getElementById("notificationBell");
    const dropdown = document.getElementById("notificationDropdown");
    const list = document.getElementById("notificationList");
    const countEl = document.getElementById("notificationCount");

    // Toggle dropdown & mark as read
    bell.addEventListener("click", function () {
      dropdown.classList.toggle("hidden");

      if (!dropdown.classList.contains("hidden")) {
        // Mark all as read
        fetch("../includes/mark_notifications_read.php", { method: "POST" })
          .then(function () {
            countEl.textContent = "0";
          });
      }
    });

    // Fetch notifications
    fetch("../includes/get_notifications.php")
      .then(function (res) {
        return res.json();
      })
      .then(function (data) {
        countEl.textContent = data.unread > 0 ? data.unread : "0";
        list.innerHTML = "";

        if (data.items.length === 0) {
          list.innerHTML = "<li>No notifications</li>";
        } else {
          data.items.forEach(function (noti) {
            const li = document.createElement("li");
            li.textContent = noti.message + " (" + noti.time + ")";
            list.appendChild(li);
          });
        }
      })
      .catch(function (err) {
        console.error("Error loading notifications:", err);
      });
  });
</script>

</body>
</html>








this one is the learning_preference.php 


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


and this one is the save_preference.php 

<?php
include('../includes/db.php');
session_start();

$parent_id = $_SESSION['user_id'] ?? null;

$student_id = $_POST['student_id'] ?? null;
$mode = $_POST['learning_mode'] ?? '';
$specialization = $_POST['specialization'] ?? '';
$teacher_id = $_POST['assigned_teacher_id'] ?? null;
$join_meetings = $_POST['join_meetings'] ?? '0';

// Basic validation
if (!$parent_id || !$student_id || !$mode || !$specialization || !$teacher_id) {
  http_response_code(400);
  echo "Missing required data.";
  exit;
}

// Sanitize input
$mode = trim($mode);
$specialization = trim($specialization);
$join_meetings = (int)$join_meetings;

// Check if this student belongs to the parent
$check = $conn->prepare("SELECT id FROM students WHERE id = ? AND parent_id = ?");
$check->bind_param("ii", $student_id, $parent_id);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows === 0) {
  http_response_code(403);
  echo "Unauthorized or invalid student.";
  exit;
}

// Update preferences
$stmt = $conn->prepare("
  UPDATE students
  SET learning_mode = ?, specialization = ?, assigned_teacher_id = ?, join_meetings = ?
  WHERE id = ? AND parent_id = ?
");
$stmt->bind_param("ssiiii", $mode, $specialization, $teacher_id, $join_meetings, $student_id, $parent_id);

if ($stmt->execute()) {
  echo "success";
} else {
  echo "error: " . $stmt->error;
}
?>




those file are working fine you can refers how structured then we can 
what we require to archive chatroom for parent side


