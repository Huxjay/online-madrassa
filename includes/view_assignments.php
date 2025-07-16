<?php
include '../includes/db.php';

$student_id = $_GET['student_id'] ?? 0;

$query = $conn->prepare("
    SELECT title, description, due_date, file_path
    FROM assignments
    WHERE student_id = ?
    ORDER BY due_date DESC
");
$query->bind_param("i", $student_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo "<p>No assignments found.</p>";
    exit;
}

while ($row = $result->fetch_assoc()) {
    $title = htmlspecialchars($row['title']);
    $desc = nl2br(htmlspecialchars($row['description']));
    $due  = htmlspecialchars($row['due_date']);
    $file = $row['file_path']; // already includes full path like "uploads/assignments/..."

    echo "<div class='assignment-card'>";
    echo "<h4>$title</h4>";
    echo "<p>$desc</p>";
    echo "<p style='color:black;'><strong>Due:</strong> $due</p>";

    if (!empty($file)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $fullPath = "../" . $file;

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            echo "<img src='$fullPath' alt='Assignment Image' style='max-width:100%; border-radius:8px; margin-top:10px;'>";
        } else {
            $filename = basename($file);
            echo "<p><a href='$fullPath' download class='download-link'>📎 Download $filename</a></p>";
        }
    }

    echo "</div>";
}
?>