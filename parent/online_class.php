<?php
include('../includes/db.php');

$parent_id = $_SESSION['user_id'] ?? null;
if (!$parent_id || $_SESSION['user_role'] !== 'parent') {
    echo "<p style='color:red;'>Unauthorized access</p>";
    exit;
}

// Get assigned teacher ID
$sql = "SELECT assigned_teacher_id FROM adult_learners WHERE parent_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$stmt->bind_result($teacher_id);
$stmt->fetch();
$stmt->close();

if (!$teacher_id) {
    echo "<p>No teacher assigned yet.</p>";
    exit;
}

// Fetch classes
$sql = "SELECT topic, description, date, start_time, end_time, meeting_link, recording_url 
        FROM online_classes 
        WHERE teacher_id = ? 
        ORDER BY date DESC, start_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2 style="text-align:center; margin-bottom: 20px;">📚 Online Classes from Your Teacher</h2>

<!-- Search -->
<div class="search-container">
  <input type="text" id="classSearch" placeholder="🔍 Search topic or description..." />
</div>

<!-- Table Wrapper -->
<div class="class-table-wrapper">
  <table class="glass-table" id="classTable">
    <thead>
      <tr>
        <th>Topic</th>
        <th>Description</th>
        <th>Date</th>
        <th>Time</th>
        <th>Status</th>
        <th>Join</th>
        <th>Recording</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $today = date('Y-m-d');
        $now = date('H:i');
        while ($row = $result->fetch_assoc()):
            $classDate = $row['date'];
            $start = $row['start_time'];
            $end = $row['end_time'];

            // Determine status
            if ($classDate > $today) {
                $status = '<span class="badge upcoming">Upcoming</span>';
            } elseif ($classDate == $today) {
                if ($end < $now) {
                    $status = '<span class="badge ended">Ended</span>';
                } elseif ($start <= $now && $end >= $now) {
                    $status = '<span class="badge live">Today</span>';
                } else {
                    $status = '<span class="badge upcoming">Upcoming</span>';
                }
            } else {
                $status = '<span class="badge ended">Ended</span>';
            }

            $rowClass = ($classDate === $today) ? 'today-row' : '';
      ?>
      <tr class="<?= $rowClass ?>">
        <td><?= htmlspecialchars($row['topic']) ?></td>
        <td><?= htmlspecialchars($row['description']) ?></td>
        <td><?= date('d M Y', strtotime($row['date'])) ?></td>
        <td><?= date('H:i', strtotime($row['start_time'])) ?> - <?= date('H:i', strtotime($row['end_time'])) ?></td>
        <td><?= $status ?></td>
        <td>
          <?php if (!empty($row['meeting_link'])): ?>
            <a href="<?= htmlspecialchars($row['meeting_link']) ?>" target="_blank" class="join-btn">Join</a>
          <?php else: ?>
            N/A
          <?php endif; ?>
        </td>
        <td>
          <?php if (!empty($row['recording_url'])): ?>
            <a href="<?= htmlspecialchars($row['recording_url']) ?>" target="_blank" class="watch-btn">📼 Watch</a>
          <?php else: ?>
            <span style="color: #aaa;">Not available</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- Style -->
<style>
  .search-container {
    text-align: center;
    margin-bottom: 12px;
  }

  #classSearch {
    padding: 8px 14px;
    width: 300px;
    max-width: 90%;
    border-radius: 8px;
    border: 1px solid #ccc;
    outline: none;
    font-size: 14px;
  }

  .class-table-wrapper {
    max-width: 1000px;
    margin: auto;
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    overflow-x: auto;
  }

  .glass-table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #222;
  }

  .glass-table thead {
    background: rgba(0, 123, 255, 0.85);
    color: white;
  }

  .glass-table th, .glass-table td {
    padding: 12px 16px;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    text-align: center;
    font-size: 15px;
  }

  .glass-table tbody tr {
    background: rgba(255, 255, 255, 0.9);
  }

  .glass-table tbody tr.today-row {
    background: #fff3cd !important;
  }

  .glass-table tbody tr:hover {
    background: rgba(0, 123, 255, 0.1);
  }

  .join-btn {
    padding: 6px 12px;
    background: #28a745;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    transition: background 0.3s ease;
    font-weight: bold;
  }

  .join-btn:hover {
    background: #218838;
  }

  .watch-btn {
    padding: 6px 12px;
    background: #007bff;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    transition: background 0.3s ease;
    font-weight: bold;
  }

  .watch-btn:hover {
    background: #0056b3;
  }

  .badge {
    padding: 4px 8px;
    font-size: 12px;
    border-radius: 12px;
    color: white;
    font-weight: bold;
    display: inline-block;
  }

  .badge.upcoming { background: #17a2b8; }
  .badge.ended { background: #dc3545; }
  .badge.live { background: #ffc107; color: black; }

  @media (max-width: 600px) {
    .glass-table th, .glass-table td {
      font-size: 13px;
      padding: 8px;
    }
  }
</style>

<!-- Script: Filter -->
<script>
  document.getElementById('classSearch').addEventListener('keyup', function () {
    const search = this.value.toLowerCase();
    const rows = document.querySelectorAll('#classTable tbody tr');

    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(search) ? '' : 'none';
    });
  });
</script>