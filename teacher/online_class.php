

<?php

die("test file - stop here");

include('../includes/db.php');
session_start();

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

// Fetch online classes from that teacher
$sql = "SELECT title, description, class_date, mode, link FROM online_classes WHERE teacher_id = ? ORDER BY class_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<h2>📚 Online Classes from Your Teacher</h2>

<table border="1" cellpadding="8" cellspacing="0" style="width:100%; margin-top:15px;">
    <tr>
        <th>Title</th>
        <th>Description</th>
        <th>Date</th>
        <th>Mode</th>
        <th>Join</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['title']) ?></td>
        <td><?= htmlspecialchars($row['description']) ?></td>
        <td><?= date('d M Y H:i', strtotime($row['class_date'])) ?></td>
        <td><?= htmlspecialchars($row['mode']) ?></td>
        <td>
            <?php if ($row['mode'] === 'Online' && !empty($row['link'])): ?>
                <a href="<?= htmlspecialchars($row['link']) ?>" target="_blank">Join</a>
            <?php else: ?>
                N/A
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>