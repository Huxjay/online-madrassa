<?php
session_start();
include 'db.php';

$teacher_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $teacher_id) {
    $student_id  = $_POST['student_id'] ?? null;
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date    = $_POST['due_date'] ?? '';
    $file_path   = null;

    // === Basic Validation ===
    if (!$student_id || !$title || !$description || !$due_date) {
        echo "All fields are required.";
        exit;
    }

    // === Handle File Upload ===
    if (!empty($_FILES['attachment']['name'])) {
        $targetDir = '../uploads/assignments/';
        $fileName = time() . '_' . basename($_FILES['attachment']['name']);
        $targetFilePath = $targetDir . $fileName;

        $allowedTypes = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg'];
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        if (!in_array($fileType, $allowedTypes)) {
            echo "Invalid file type.";
            exit;
        }

        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFilePath)) {
            $file_path = 'uploads/assignments/' . $fileName; // relative path for DB
        } else {
            echo "Failed to upload file.";
            exit;
        }
    }

    // === Insert into DB ===
    $stmt = $conn->prepare("
        INSERT INTO assignments (student_id, teacher_id, title, description, due_date, file_path)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iissss", $student_id, $teacher_id, $title, $description, $due_date, $file_path);

    if ($stmt->execute()) {
        echo "Assignment saved successfully.";
    } else {
        echo "Error saving assignment: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Unauthorized.";
}
?>