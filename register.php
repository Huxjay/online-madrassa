<?php
require_once("includes/db.php");

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parentName = $_POST['parent_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $parentPhone = $_POST['parent_phone'];

    // Validate password
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%?&])[A-Za-z\d@$!%?&]{8,}$/', $password)) {
        $error = "Password must be at least 8 characters, include uppercase, lowercase, number, and special character.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (!preg_match('/^255\d{9}$|^254\d{9}$/', $parentPhone)) {
        $error = "Phone number must start with 255 or 254 and be 12 digits long.";
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $locationName = $_POST['location'];
        $district = $_POST['district'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        $childNames = $_POST['child_name'] ?? [];
        $childAges = $_POST['child_age'] ?? [];
        $childGenders = $_POST['child_gender'] ?? [];

        $parentGender = $_POST['parent_gender'];
        $parentAge = $_POST['parent_age'];

        // Insert location
        $stmt = $conn->prepare("INSERT INTO locations (name, district, latitude, longitude) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdd", $locationName, $district, $latitude, $longitude);
        $stmt->execute();
        $location_id = $stmt->insert_id;
        $stmt->close();

        // Insert into users
        $role = 'parent';
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, location_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $parentName, $email, $passwordHash, $role, $location_id);
        $stmt->execute();
        $user_id = $stmt->insert_id;
        $stmt->close();

        // Insert into parents
        $stmt = $conn->prepare("INSERT INTO parents (user_id, gender, age, phone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $user_id, $parentGender, $parentAge, $parentPhone);
        $stmt->execute();
        $stmt->close();

        // Insert children
        if (!empty($childNames) && !empty($childAges)) {
            $stmt = $conn->prepare("INSERT INTO students (parent_id, name, age, gender, location_id) VALUES (?, ?, ?, ?, ?)");
            for ($i = 0; $i < count($childNames); $i++) {
                if (trim($childNames[$i]) !== '') {
                    $stmt->bind_param("isisi", $user_id, $childNames[$i], $childAges[$i], $childGenders[$i], $location_id);
                    $stmt->execute();
                }
            }
            $stmt->close();
        }

        echo "<script>alert('🎉 Registration successful! You can now log in.'); window.location.href = 'login.php';</script>";
        exit;
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

      <?php if ($error): ?>
        <p class="error" style="color:red;"><?= $error ?></p>
      <?php endif; ?>

      <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="parent_name" placeholder="Parent Name" required />
      </div>

      <div class="input-group">
        <i class="fas fa-envelope"></i>
        <input type="email" name="email" placeholder="Email" required />
      </div>

      <div class="input-group password-toggle">
  <i class="fas fa-lock"></i>
  <input type="password" id="password" name="password" placeholder="Password (Strong)" required />
  <span class="toggle-password" onclick="togglePassword('password', this)">
    <i class="fas fa-eye"></i>
  </span>
</div>

<div class="input-group password-toggle">
  <i class="fas fa-lock"></i>
  <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required />
  <span class="toggle-password" onclick="togglePassword('confirm_password', this)">
    <i class="fas fa-eye"></i>
  </span>
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
        <input type="text" name="parent_phone" placeholder="Phone (e.g. 2557XXXXXXX)" required />
      </div>

      <div class="input-group">
        <i class="fas fa-map-marker-alt"></i>
        <input type="text" id="location" name="location" placeholder="Detecting location..." readonly required />
        <input type="hidden" name="latitude" id="latitude" />
        <input type="hidden" name="longitude" id="longitude" />
        <input type="hidden" name="district" id="district" />
      </div>

      <h3><i class="fas fa-children"></i> Child Information <small>(Optional)</small></h3>

      <div id="children-wrapper">
        <div class="child-block">
          <div class="input-group">
            <i class="fas fa-child"></i>
            <input type="text" name="child_name[]" placeholder="Child Name" />
          </div>
          <div class="input-group">
            <i class="fas fa-birthday-cake"></i>
            <input type="number" name="child_age[]" placeholder="Child Age" />
          </div>
          <div class="input-group">
            <i class="fas fa-venus-mars"></i>
            <select name="child_gender[]">
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
    // Detect location
    window.onload = function () {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
          const lat = position.coords.latitude;
          const lon = position.coords.longitude;

          document.getElementById("latitude").value = lat;
          document.getElementById("longitude").value = lon;

          fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`)
            .then(response => response.json())
            .then(data => {
              document.getElementById("location").value = data.display_name || "Unknown";
              document.getElementById("district").value = data.address.county || data.address.state_district || "";
            })
            .catch(() => {
              document.getElementById("location").value = "Location unavailable";
            });
        }, function () {
          document.getElementById("location").value = "Location permission denied";
        });
      } else {
        document.getElementById("location").value = "Geolocation not supported";
      }
    };

    // Add more children fields
    document.getElementById('add-child').addEventListener('click', () => {
      const wrapper = document.getElementById('children-wrapper');
      const newBlock = wrapper.firstElementChild.cloneNode(true);
      newBlock.querySelectorAll('input, select').forEach(el => el.value = '');
      wrapper.appendChild(newBlock);
    });


    
  function togglePassword(fieldId, toggleIcon) {
    const input = document.getElementById(fieldId);
    const icon = toggleIcon.querySelector('i');

    if (input.type === "password") {
      input.type = "text";
      icon.classList.remove("fa-eye");
      icon.classList.add("fa-eye-slash");
    } else {
      input.type = "password";
      icon.classList.remove("fa-eye-slash");
      icon.classList.add("fa-eye");
    }
  }

  </script>
</body>
</html>