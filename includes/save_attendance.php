<?php
session_start(); // ✅ Required to access $_SESSION
include '../includes/db.php';

$teacher_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $teacher_id) {
    $date = $_POST['date'] ?? date('Y-m-d');
    $statuses = $_POST['status'] ?? [];
    $remarks  = $_POST['remarks'] ?? [];

    // Prepare once
    $stmt = $conn->prepare("
        INSERT INTO attendance (student_id, date, status, remarks)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE status = VALUES(status), remarks = VALUES(remarks)
    ");

    foreach ($statuses as $student_id => $status) {
        $remark = $remarks[$student_id] ?? null;

        $stmt->bind_param("isss", $student_id, $date, $status, $remark);
        $stmt->execute();
    }

    echo "<script>
        alert('✅ Attendance saved successfully!');
        window.history.back();
    </script>";
} else {
    http_response_code(403);
    echo "Unauthorized access.";
}
?>