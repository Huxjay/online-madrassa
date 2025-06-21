<?php
require_once("includes/db.php");

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parentName = $_POST['parent_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $locationName = $_POST['location'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $childNames = $_POST['child_name'];
    $childAges = $_POST['child_age'];
    $childGenders = $_POST['child_gender'];

    // New parent details
    $parentGender = $_POST['parent_gender'];
    $parentAge = $_POST['parent_age'];
    $parentPhone = $_POST['parent_phone'];

    // Step 1: Insert into locations table
    $stmt = $conn->prepare("INSERT INTO locations (name, latitude, longitude) VALUES (?, ?, ?)");
    $stmt->bind_param("sdd", $locationName, $latitude, $longitude);
    if (!$stmt->execute()) {
        $error = "Failed to insert location.";
    }
    $location_id = $stmt->insert_id;
    $stmt->close();

    // Step 2: Insert into users table (role = parent)
    $role = 'parent';
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, location_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $parentName, $email, $password, $role, $location_id);
    if (!$stmt->execute()) {
        $error = "Failed to insert user.";
    }
    $user_id = $stmt->insert_id;
    $stmt->close();

    // Step 3: Insert into parents table (link with users.id)
    $stmt = $conn->prepare("INSERT INTO parents (user_id, gender, age, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $user_id, $parentGender, $parentAge, $parentPhone);
    if (!$stmt->execute()) {
        $error = "Failed to insert parent details.";
    }
    $parent_id = $stmt->insert_id;
    $stmt->close();

    // Step 4: Insert children
    $stmt = $conn->prepare("INSERT INTO students (parent_id, name, age, gender, location_id) VALUES (?, ?, ?, ?, ?)");
    $allChildrenSaved = true;

    for ($i = 0; $i < count($childNames); $i++) {
        $stmt->bind_param("isisi", $user_id, $childNames[$i], $childAges[$i], $childGenders[$i], $location_id);
        if (!$stmt->execute()) {
            $allChildrenSaved = false;
            break;
        }
    }
    $stmt->close();

    if ($allChildrenSaved) {
        $success = "🎉 Registration successful! You can now log in.";
    } else {
        $error = "Failed to save one or more children.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Parent Registration</title>
  <link rel="stylesheet" href="assets/css/register.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
  <div class="register-container fade-in">
    <form method="POST" action="register.php">
      <h2><i class="fas fa-user-plus"></i> Parent Registration</h2>

      <?php if ($success): ?>
        <p class="success"><?= $success ?></p>
      <?php endif; ?>
      <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
      <?php endif; ?>

      <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="parent_name" placeholder="Parent Name" required />
      </div>

      <div class="input-group">
        <i class="fas fa-envelope"></i>
        <input type="email" name="email" placeholder="Email" required />
      </div>

      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" placeholder="Password" required />
      </div>
<div class="input-group">
  <i class="fas fa-venus-mars"></i>
  <select name="parent_gender" required>
    <option value="">Select Gender</option>
    <option value="Male">Male</option>
    <option value="Female">Female</option>
  </select>
</div>

<div class="input-group">
  <i class="fas fa-birthday-cake"></i>
  <input type="number" name="parent_age" placeholder="Parent Age" required />
</div>

<div class="input-group">
  <i class="fas fa-phone"></i>
  <input type="text" name="parent_phone" placeholder="Phone Number" required />
</div>
      <div class="input-group">
        <i class="fas fa-map-marker-alt"></i>
        <input type="text" id="location" name="location" placeholder="Detecting location..." readonly required />
         <input type="hidden" name="latitude" id="latitude" />
        <input type="hidden" name="longitude" id="longitude" />
      </div>
     
      <h3><i class="fas fa-children"></i> Child Information</h3>

      <div id="children-wrapper">
        <div class="child-block">
          <div class="input-group">
            <i class="fas fa-child"></i>
            <input type="text" name="child_name[]" placeholder="Child Name" required />
          </div>

          <div class="input-group">
            <i class="fas fa-birthday-cake"></i>
            <input type="number" name="child_age[]" placeholder="Child Age" required />
          </div>

          <div class="input-group">
            <i class="fas fa-venus-mars"></i>
            <select name="child_gender[]" required>
              <option value="">Select Gender</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
        </div>
      </div>

      <button type="button" id="add-child"><i class="fas fa-plus-circle"></i> Add Another Child</button>

      <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Register</button>
    </form>
  </div>
<script>
  window.onload = function () {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function (position) {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;

        // Set hidden fields for latitude and longitude
        document.getElementById("latitude").value = lat;
        document.getElementById("longitude").value = lon;

        // Fetch address from OpenStreetMap
        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`)
          .then(response => response.json())
          .then(data => {
            const locationInput = document.getElementById("location");
            locationInput.value = data.display_name || "Unknown location";
          })
          .catch(() => {
            document.getElementById("location").value = "Location unavailable";
          });
      }, function (error) {
        document.getElementById("location").value = "Location permission denied";
      });
    } else {
      document.getElementById("location").value = "Geolocation not supported";
    }
  };

  // Handle adding dynamic child blocks
  document.getElementById('add-child').addEventListener('click', () => {
    const wrapper = document.getElementById('children-wrapper');
    const newBlock = wrapper.firstElementChild.cloneNode(true);
    newBlock.querySelectorAll('input, select').forEach(el => el.value = '');
    wrapper.appendChild(newBlock);
  });
</script>
</body>
</html>




