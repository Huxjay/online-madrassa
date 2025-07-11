<?php
include('db.php');
session_start();
header('Content-Type: application/json');

// Debugging
// ini_set('display_errors', 1); error_reporting(E_ALL);

$specialization = trim($_GET['specialization'] ?? '');
$mode = trim($_GET['mode'] ?? '');
$parent_id = $_SESSION['user_id'] ?? null;

if (!$parent_id || $specialization === '' || $mode === '') {
  echo json_encode([]);
  exit;
}

// === Get Parent District ===
$district = null;
$districtStmt = $conn->prepare("
  SELECT l.district
  FROM users u
  JOIN locations l ON u.location_id = l.id
  WHERE u.id = ?
");
$districtStmt->bind_param("i", $parent_id);
$districtStmt->execute();
$districtResult = $districtStmt->get_result();
if ($row = $districtResult->fetch_assoc()) {
  $district = $row['district'];
}
$districtStmt->close();

// === Build SQL Query ===
$sql = "
  SELECT u.id, u.name, t.specialization
  FROM users u
  JOIN teachers t ON u.id = t.user_id
  JOIN locations l ON u.location_id = l.id
  WHERE u.role = 'teacher'
    AND u.approved = 1
    AND FIND_IN_SET(?, t.specialization)
";

$params = [$specialization];
$types = "s";

// Add district filter if NOT online mode
if ($mode !== 'online' && $district) {
  $sql .= " AND LOWER(l.district) = LOWER(?)";
  $params[] = $district;
  $types .= "s";
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
  echo json_encode(["error" => "Failed to prepare SQL"]);
  exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$teachers = [];
while ($teacher = $result->fetch_assoc()) {
  $teachers[] = [
    'id' => $teacher['id'],
    'name' => $teacher['name'],
    'specialization' => $teacher['specialization']
  ];
}

echo json_encode($teachers);