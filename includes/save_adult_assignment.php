<?php
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $learner_id = $_POST['learner_id'] ?? null;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $due_date = $_POST['due_date'] ?? '';

    if (!$learner_id || !$title || !$description || !$due_date) {
        echo "❌ All fields are required.";
        exit;
    }

    // Handle file upload
    $file_path = null;
    if (!empty($_FILES['attachment']['name'])) {
        $upload_dir = "../uploads/assignments/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_tmp = $_FILES['attachment']['tmp_name'];
        $file_name = time() . "_" . basename($_FILES['attachment']['name']);
        $destination = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp, $destination)) {
            $file_path = "uploads/assignments/" . $file_name;
        } else {
            echo "❌ Failed to upload file.";
            exit;
        }
    }

    // Insert into assignments table
    $stmt = $conn->prepare("
        INSERT INTO assignments (student_id, title, description, due_date, file_path, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("issss", $learner_id, $title, $description, $due_date, $file_path);

    if ($stmt->execute()) {
        echo "✅ Assignment submitted successfully.";
    } else {
        echo "❌ Failed to submit assignment.";
    }
} else {
    echo "❌ Invalid request method.";
}
?>