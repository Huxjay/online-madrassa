<?php
session_start();

include '../includes/db.php';
require_once '../includes/send_sms.php'; // SMS sender

$teacher_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $teacher_id) {
    // Sanitize input
    $schedule_id      = $_POST['id'] ?? null;
    $group_type       = $_POST['group_type'] ?? '';
    $group_identifier = $_POST['group_identifier'] ?? '';
    $topic            = trim($_POST['topic'] ?? '');
    $schedule_date    = $_POST['schedule_date'] ?? '';
    $schedule_time    = $_POST['schedule_time'] ?? '';
    $duration         = (int)($_POST['duration_minutes'] ?? 0);
    $subtopics        = trim($_POST['subtopics'] ?? '');

    // Validate
    if (!$group_type || !$topic || !$schedule_date || !$schedule_time || !$duration) {
        http_response_code(400);
        echo "Missing required fields.";
        exit;
    }

    // === Save Schedule ===
    if ($schedule_id) {
        $stmt = $conn->prepare("
            UPDATE class_schedule
            SET topic = ?, schedule_date = ?, schedule_time = ?, duration_minutes = ?, subtopics = ?
            WHERE id = ? AND teacher_id = ?
        ");
        $stmt->bind_param("sssdsii", 
            $topic,
            $schedule_date,
            $schedule_time,
            $duration,
            $subtopics,
            $schedule_id,
            $teacher_id
        );
    } else {
        $stmt = $conn->prepare("
            INSERT INTO class_schedule
            (teacher_id, group_type, group_identifier, topic, schedule_date, schedule_time, duration_minutes, subtopics)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssssds", 
            $teacher_id,
            $group_type,
            $group_identifier,
            $topic,
            $schedule_date,
            $schedule_time,
            $duration,
            $subtopics
        );
    }

    if ($stmt->execute()) {
        // Notify parent only on insert
        if (!$schedule_id) {
            $parent_id = null;

            // Determine parent_id
            if ($group_type === 'home') {
                $parent_id = (int)$group_identifier;

            } elseif ($group_type === 'in_person') {
                $sql = "SELECT parent_id FROM students WHERE assigned_teacher_id = ? LIMIT 1";
                $q = $conn->prepare($sql);
                $q->bind_param("i", $teacher_id);
                $q->execute();
                $res = $q->get_result();
                if ($row = $res->fetch_assoc()) {
                    $parent_id = $row['parent_id'];
                }
                $q->close();

            } elseif ($group_type === 'online') {
                $sql = "SELECT parent_id FROM students WHERE assigned_teacher_id = ? AND specialization = ? LIMIT 1";
                $q = $conn->prepare($sql);
                $q->bind_param("is", $teacher_id, $group_identifier);
                $q->execute();
                $res = $q->get_result();
                if ($row = $res->fetch_assoc()) {
                    $parent_id = $row['parent_id'];
                }
                $q->close();
            }

            // Get parent's phone from parents table
            if ($parent_id) {
                $q = $conn->prepare("SELECT phone FROM parents WHERE id = ?");
                $q->bind_param("i", $parent_id);
                $q->execute();
                $res = $q->get_result();

                if ($row = $res->fetch_assoc()) {
                    $phone = $row['phone'];
                    $message = "🕌 New Class: '$topic' on $schedule_date at $schedule_time ($duration mins).";

                    // ✅ Send SMS
                    sendSMS($phone, $message);

                    // ✅ Log Notification
                    $noti = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'sms')");
                    $noti->bind_param("is", $parent_id, $message);
                    $noti->execute();
                    $noti->close();
                }

                $q->close();
            }
        }

        echo "success";
    } else {
        http_response_code(500);
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    http_response_code(403);
    echo "Unauthorized.";
}