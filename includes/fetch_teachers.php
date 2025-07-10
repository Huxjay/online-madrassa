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

// Get parent's district
$districtQuery = $conn->query("
  SELECT l.district
  FROM users u
  JOIN locations l ON u.location_id = l.id
  WHERE u.id = $parent_id
");
$districtData = $districtQuery->fetch_assoc();
$district = $districtData['district'] ?? null;

// Base SQL
$sql = "
  SELECT u.id AS id, u.name AS name, t.specialization AS specialization
  FROM users u
  JOIN teachers t ON u.id = t.user_id
  JOIN locations l ON u.location_id = l.id
  WHERE u.role = 'teacher'
    AND u.approved = 1
    AND t.specialization LIKE CONCAT('%', ?, '%')
";

// If mode is not online, filter by district
$params = [$specialization];
$types = "s";

if ($mode !== 'online' && $district) {
  $sql .= " AND l.district = ?";
  $params[] = $district;
  $types .= "s";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$teachers = [];
while ($row = $result->fetch_assoc()) {
  $teachers[] = $row;
}

echo json_encode($teachers);
?>