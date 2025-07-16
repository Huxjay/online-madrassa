<?php
include 'db.php';

$student_id = $_POST['student_id'] ?? 0;
$name = $_POST['name'] ?? '';
$age = $_POST['age'] ?? 0;
$gender = $_POST['gender'] ?? '';
$specialization = $_POST['specialization'] ?? '';
$photo = $_FILES['photo']['name'] ?? '';

if ($student_id && $name) {
    if ($photo) {
        $targetDir = "../uploads/students/";
        $targetPath = $targetDir . basename($photo);

        // ✅ Move uploaded file
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("UPDATE students SET name=?, age=?, gender=?, specialization=?, photo=? WHERE id=?");
            $stmt->bind_param("sisssi", $name, $age, $gender, $specialization, $photo, $student_id);
        } else {
            echo "❌ Failed to upload photo.";
            exit;
        }
    } else {
        $stmt = $conn->prepare("UPDATE students SET name=?, age=?, gender=?, specialization=? WHERE id=?");
        $stmt->bind_param("sissi", $name, $age, $gender, $specialization, $student_id);
    }

    if ($stmt->execute()) {
        echo "✅ Info updated!";
    } else {
        echo "❌ Update failed: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "❌ Invalid data.";
}
?>