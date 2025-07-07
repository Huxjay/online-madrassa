<?php
session_start();
include('../../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $child_id = $_POST['child_id'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];

    $update = $conn->prepare("UPDATE students SET name = ?, age = ?, gender = ? WHERE id = ?");
    $update->bind_param("sisi", $name, $age, $gender, $child_id);

    if ($update->execute()) {
        echo "<script>alert('✅ Child info updated successfully'); window.location.href='../index.php?page=my_children';</script>";
    } else {
        echo "<script>alert('❌ Failed to update child'); window.history.back();</script>";
    }
}
?>