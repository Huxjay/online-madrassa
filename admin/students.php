<?php
include('../includes/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo "<p style='color:red;'>Access Denied</p>";
    exit;
}
?>

<h2 style="text-align:center; margin-top: 30px;">🎓 All Students</h2>

<div id="student-section">
  <?php
  // Fetch students and related info
  $sql = "SELECT s.*, u.name AS parent_name, p.phone, l.name AS location_name, l.district, l.street,
                 t.user_id AS teacher_user_id, tu.name AS teacher_name
          FROM students s
          JOIN parents p ON s.parent_id = p.user_id
          JOIN users u ON u.id = p.user_id
          LEFT JOIN teachers t ON s.assigned_teacher_id = t.user_id
          LEFT JOIN users tu ON t.user_id = tu.id
          LEFT JOIN locations l ON s.location_id = l.id
          ORDER BY s.updated_at DESC";
  $students = $conn->query($sql);

  while ($stu = $students->fetch_assoc()):
      $sid = $stu['id'];
      $chartId = "chart_$sid";

      // Attendance chart data
      $attQ = $conn->query("SELECT status, COUNT(*) as total FROM attendance WHERE student_id = $sid GROUP BY status");
      $attData = ['Present' => 0, 'Absent' => 0, 'Excused' => 0];
      while ($row = $attQ->fetch_assoc()) {
          $attData[$row['status']] = $row['total'];
      }

      $photoPath = !empty($stu['photo']) ? "../uploads/students/" . htmlspecialchars($stu['photo']) : "../assets/default_student.png";
  ?>
  <div class="student-card">
    <img src="<?= $photoPath ?>" alt="Student Photo">
    <div class="student-info">
      <h3><?= $stu['name'] ?> <span class="age">(<?= $stu['age'] ?> yrs)</span></h3>
      <p>👧 Gender: <?= $stu['gender'] ?> | 🎓 <?= $stu['specialization'] ?></p>
      <p>📍 <?= $stu['location_name'] ?>, <?= $stu['district'] ?>, <?= $stu['street'] ?></p>
      <p>👨‍👩‍👧 Parent: <?= $stu['parent_name'] ?> | 📞 <?= $stu['phone'] ?></p>
      <p>👨‍🏫 Teacher: <?= $stu['teacher_name'] ?? '<em>Not Assigned</em>' ?></p>
      <p>🕒 Last Updated: <?= date('d M Y', strtotime($stu['updated_at'])) ?></p>
    </div>
    <div class="chart-box">
      <canvas id="<?= $chartId ?>" width="150" height="150"></canvas>
    </div>
  </div>
  <script>
    new Chart(document.getElementById("<?= $chartId ?>"), {
      type: 'doughnut',
      data: {
        labels: ['Present', 'Absent', 'Excused'],
        datasets: [{
          data: [<?= $attData['Present'] ?>, <?= $attData['Absent'] ?>, <?= $attData['Excused'] ?>],
          backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
          borderWidth: 1
        }]
      },
      options: {
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: '#333', font: { size: 12 } }
          }
        },
        cutout: '65%'
      }
    });
  </script>
  <?php endwhile; ?>
</div>

<!-- Styles -->
<style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(to right, #e0f7fa, #f1f8ff);
    margin: 0;
    padding: 0;
  }

  h2 {
    font-size: 28px;
    color: #0d6efd;
    margin-bottom: 20px;
  }

  .student-card {
    display: flex;
    align-items: center;
    background: rgba(255,255,255,0.7);
    border-radius: 18px;
    padding: 20px;
    margin: 20px auto;
    max-width: 1000px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    backdrop-filter: blur(12px);
    transition: 0.3s ease;
    gap: 25px;
  }

  .student-card:hover {
    transform: scale(1.02);
  }

  .student-card img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 3px solid #0d6efd;
    object-fit: cover;
  }

  .student-info {
    flex: 3;
    color: #333;
  }

  .student-info h3 {
    margin: 0;
    font-size: 20px;
    color: #0d6efd;
  }

  .student-info p {
    margin: 4px 0;
    font-size: 14.5px;
  }

  .student-info .age {
    font-size: 14px;
    color: #666;
  }

  .chart-box {
    flex: 1;
    text-align: center;
  }

  @media (max-width: 768px) {
    .student-card {
      flex-direction: column;
      text-align: center;
    }

    .chart-box {
      margin-top: 10px;
    }
  }
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>