<?php
include('../includes/admin_session.php');
include('../includes/db.php');
require_once('../includes/send_email.php'); // ✅ For email sending

// Handle approval submission
if (isset($_POST['approve_teacher'])) {
    $user_id = $_POST['user_id'];

    // 1. Update approval status
    $update = $conn->prepare("UPDATE users SET approved = 1 WHERE id = ?");
    $update->bind_param("i", $user_id);
    $update->execute();
    $update->close();

    // 2. Send in-system notification
    $message = "🎉 You have been approved! You can now log in and start teaching.";
    $stmt = $conn->prepare("INSERT INTO messages (user_id, message, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close();

    // 3. Fetch user email and name for email notification
    $getUser = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
    $getUser->bind_param("i", $user_id);
    $getUser->execute();
    $getUser->bind_result($name, $email);
    $getUser->fetch();
    $getUser->close();

    // 4. Send email via Gmail SMTP
    $sendResult = sendApprovalEmail($email, $name);
    if ($sendResult === true) {
        $approved_success = "✅ Teacher approved and email sent successfully!";
    } else {
        $approved_success = "✅ Teacher approved, but email failed to send: $sendResult";
    }
}

// Fetch unapproved teachers
$query = "
    SELECT u.id AS user_id, u.name, u.email, t.gender, t.age, t.phone, t.qualification, t.specialization
    FROM users u
    JOIN teachers t ON u.id = t.user_id
    WHERE u.role = 'teacher' AND u.approved = 0
";
$result = $conn->query($query);
?>