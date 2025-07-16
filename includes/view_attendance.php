<?php
include 'db.php';

$student_id = $_GET['student_id'] ?? 0;

$stmt = $conn->prepare("SELECT date, status, remarks FROM attendance WHERE student_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>No attendance records found.</p>";
} else {
    while ($row = $result->fetch_assoc()) {
        $statusColor = match ($row['status']) {
            'present' => 'green',
            'absent' => 'red',
            'late' => 'orange',
            default => '#333'
        };

        echo "<div class='attendance-record'>";
        echo "<p><strong>Date:</strong> {$row['date']}</p>";
        echo "<p><strong>Status:</strong> <span style='color: {$statusColor}; text-transform: capitalize;'>{$row['status']}</span></p>";
        if ($row['remarks']) {
            echo "<p><strong>Remarks:</strong> " . htmlspecialchars($row['remarks']) . "</p>";
        }
        echo "</div>";
    }
}
?>