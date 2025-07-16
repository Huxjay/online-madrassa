<?php
session_start();
include('../includes/db.php');

$parent_id = $_SESSION['user_id'] ?? 0;
if (!$parent_id) {
  echo "Unauthorized";
  exit;
}

// Sanitize inputs
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$age = $_POST['age'] ?? '';
$gender = $_POST['gender'] ?? '';

// ✅ Update photo if uploaded
$photo_name = null;
if (!empty($_FILES['photo']['name'])) {
  $target_dir = "../uploads/parents/";
  $photo_name = time() . '_' . basename($_FILES["photo"]["name"]);
  $target_file = $target_dir . $photo_name;

  $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
  $valid_types = ["jpg", "jpeg", "png", "gif"];

  if (!in_array($imageFileType, $valid_types)) {
    echo "❌ Invalid image format.";
    exit;
  }

  if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
    echo "❌ Failed to upload image.";
    exit;
  }

  // ✅ Update user photo
  $updatePhoto = $conn->prepare("UPDATE users SET photo = ? WHERE id = ?");
  $updatePhoto->bind_param("si", $photo_name, $parent_id);
  $updatePhoto->execute();
}

// ✅ Update parent info
$updateParent = $conn->prepare("UPDATE parents SET phone = ?, age = ?, gender = ? WHERE user_id = ?");
$updateParent->bind_param("sisi", $phone, $age, $gender, $parent_id);
$updateParent->execute();

// ✅ Update name
$updateName = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
$updateName->bind_param("si", $name, $parent_id);
$updateName->execute();

echo "✅ Profile updated successfully.";