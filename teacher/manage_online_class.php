<?php
include('../includes/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    echo "<p style='color:red;'>Access Denied</p>";
    exit;
}

$teacher_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle recording update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_id'])) {
    $class_id = intval($_POST['class_id']);
    $recording_url = trim($_POST['recording_url']);

    $stmt = $conn->prepare("UPDATE online_classes SET recording_url = ? WHERE id = ? AND teacher_id = ?");
    $stmt->bind_param("sii", $recording_url, $class_id, $teacher_id);
    if ($stmt->execute()) {
        $success = "🎥 Recording updated successfully!";
    } else {
        $error = "Failed to update recording.";
    }
}

// Fetch classes by this teacher
$sql = "SELECT id, topic, date, start_time, end_time, recording_url FROM online_classes WHERE teacher_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="manage-wrapper">
  <h2>🎓 Manage Your Classes & Upload Recordings</h2>

  <?php if ($success): ?>
    <p class="msg success"><?= $success ?></p>
  <?php endif; ?>
  <?php if ($error): ?>
    <p class="msg error"><?= $error ?></p>
  <?php endif; ?>

  <table class="glass-table">
    <thead>
      <tr>
        <th>Topic</th>
        <th>Date</th>
        <th>Time</th>
        <th>Recording Link</th>
        <th>Update</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['topic']) ?></td>
          <td><?= date('d M Y', strtotime($row['date'])) ?></td>
          <td><?= date('H:i', strtotime($row['start_time'])) ?> - <?= date('H:i', strtotime($row['end_time'])) ?></td>
          <td>
            <form method="POST" style="display:flex; gap:6px;">
              <input type="hidden" name="class_id" value="<?= $row['id'] ?>" />
              <input type="url" name="recording_url" value="<?= htmlspecialchars($row['recording_url']) ?>" placeholder="https://..." required />
              <button type="submit">Upload</button>
            </form>
          </td>
          <td>
            <?php if (!empty($row['recording_url'])): ?>
              <a href="<?= htmlspecialchars($row['recording_url']) ?>" target="_blank" class="watch-link">▶ View</a>
            <?php else: ?>
              <span style="color:#aaa;">Not uploaded</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- CSS -->
<style>
  .manage-wrapper {
    max-width: 1000px;
    margin: 30px auto;
    padding: 20px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(12px);
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  }

  .manage-wrapper h2 {
    text-align: center;
    color: #007bff;
    margin-bottom: 20px;
  }

  .glass-table {
    width: 100%;
    border-collapse: collapse;
    color: #222;
  }

  .glass-table th, .glass-table td {
    padding: 12px;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    text-align: center;
    font-size: 14px;
  }

  .glass-table thead {
    background: rgba(0,123,255,0.9);
    color: white;
  }

  .glass-table input[type="url"] {
    flex: 1;
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 13px;
    width: 250px;
  }

  .glass-table button {
    background: #28a745;
    border: none;
    color: white;
    padding: 6px 10px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    font-size: 13px;
  }

  .glass-table button:hover {
    background: #218838;
  }

  .watch-link {
    background: #007bff;
    color: white;
    padding: 4px 10px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
  }

  .watch-link:hover {
    background: #0056b3;
  }

  .msg {
    text-align: center;
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 8px;
  }

  .msg.success {
    background: #d4edda;
    color: #155724;
  }

  .msg.error {
    background: #f8d7da;
    color: #721c24;
  }
</style>