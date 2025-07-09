<?php
include('db.php');
session_start();

header('Content-Type: application/json');

$specialization = $_GET['specialization'] ?? '';
$mode = $_GET['mode'] ?? '';
$parent_id = $_SESSION['user_id'] ?? null;

if (!$parent_id || !$specialization || !$mode) {
  echo json_encode([]);
  exit;
}

// Get parent's location_id
$locationResult = $conn->query("SELECT location_id FROM users WHERE id = $parent_id");
$locationData = $locationResult->fetch_assoc();
$location_id = $locationData['location_id'] ?? null;

// Build query
$sql = "
  SELECT u.id AS id, u.name AS name, t.specialization AS specialization
  FROM users u
  JOIN teachers t ON u.id = t.user_id
  WHERE u.role = 'teacher'
    AND u.approved = 1
    AND t.specialization = ?
";

$params = [$specialization];

if ($mode !== 'online' && $location_id) {
  $sql .= " AND u.location_id = ?";
  $params[] = $location_id;
}

$stmt = $conn->prepare($sql);
if (count($params) === 2) {
  $stmt->bind_param("si", $params[0], $params[1]);
} else {
  $stmt->bind_param("s", $params[0]);
}

$stmt->execute();
$result = $stmt->get_result();

$teachers = [];
while ($row = $result->fetch_assoc()) {
  $teachers[] = $row;
}

echo json_encode($teachers);
?>