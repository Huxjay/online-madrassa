<?php
include 'includes/db.php';

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $gender = $_POST['gender'];
    $age = $_POST['age'];
    $phone = $_POST['phone'];
    $qualification = $_POST['qualification'];
    $specialization = $_POST['specialization'];
    $location_name = $_POST['location'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // 1. Insert location
    $stmt = $conn->prepare("INSERT INTO locations (name, latitude, longitude) VALUES (?, ?, ?)");
    $stmt->bind_param("sdd", $location_name, $latitude, $longitude);
    $stmt->execute();
    $location_id = $stmt->insert_id;
    $stmt->close();

    // 2. Insert into users
    $role = 'teacher';
    $approved = 0;
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, location_id, approved) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssii', $name, $email, $password, $role, $location_id, $approved);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();

    // 3. Insert into teachers
    $stmt = $conn->prepare("INSERT INTO teachers (user_id, gender, age, phone, qualification, specialization) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isisss", $user_id, $gender, $age, $phone, $qualification, $specialization);
    $stmt->execute();
    $stmt->close();

    $success = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register Teacher - Online Madrassa</title>
  <link rel="stylesheet" href="assets/css/register.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .modal {
      position: fixed;
      z-index: 9999;
      top: 0; left: 0;
      width: 100%; height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      background: rgba(0, 0, 0, 0.6);
      animation: fadeIn 0.4s ease-in-out;
    }

    .modal-content {
      background: white;
      padding: 30px;
      border-radius: 12px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      animation: popUp 0.3s ease;
      max-width: 400px;
      width: 90%;
    }

    .modal-content h2 {
      margin-top: 0;
      color: #27ae60;
    }

    .modal-content button {
      padding: 10px 20px;
      border: none;
      background: #27ae60;
      color: white;
      font-weight: bold;
      border-radius: 8px;
      margin-top: 20px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .modal-content button:hover {
      background: #219150;
    }

    @keyframes popUp {
      from { transform: scale(0.8); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
  </style>
</head>
<body>
  <div class="register-container">
    <div class="form-box">
      <h2><i class="fas fa-chalkboard-teacher"></i> Register as Teacher</h2>

      <form method="POST" action="">
        <div class="input-group">
          <i class="fas fa-user"></i>
          <input type="text" name="name" placeholder="Full Name" required>
        </div>

        <div class="input-group">
          <i class="fas fa-envelope"></i>
          <input type="email" name="email" placeholder="Email Address" required>
        </div>

        <div class="input-group">
          <i class="fas fa-lock"></i>
          <input type="password" name="password" placeholder="Password" required>
        </div>

        <div class="input-group">
          <i class="fas fa-map-marker-alt"></i>
          <input type="text" name="location" id="location" placeholder="Detecting location..." readonly required>
          <input type="hidden" name="latitude" id="latitude">
          <input type="hidden" name="longitude" id="longitude">
        </div>

        <div class="input-group">
          <i class="fas fa-venus-mars"></i>
          <select name="gender" required>
            <option value="">Select Gender</option>
            <option>Male</option>
            <option>Female</option>
          </select>
        </div>

        <div class="input-group">
          <i class="fas fa-hourglass-half"></i>
          <input type="number" name="age" placeholder="Age" required>
        </div>

        <div class="input-group">
          <i class="fas fa-phone"></i>
          <input type="text" name="phone" placeholder="Phone Number" required>
        </div>

        <div class="input-group">
          <i class="fas fa-certificate"></i>
          <input type="text" name="qualification" placeholder="Qualification" required>
        </div>

        <div class="input-group">
          <i class="fas fa-book"></i>
          <input type="text" name="specialization" placeholder="Specialization" required>
        </div>

        <button type="submit" class="submit-btn">Register</button>
      </form>
    </div>
  </div>

  <?php if ($success): ?>
  <div id="successModal" class="modal">
    <div class="modal-content">
      <h2>✅ Registration Successful</h2>
      <p>Please wait for admin approval before logging in.</p>
      <button onclick="closeModal()">OK</button>
    </div>
  </div>
  <?php endif; ?>

  <script>
    function closeModal() {
      document.getElementById("successModal").style.display = "none";
    }

    // Geolocation and reverse geocoding
    window.onload = function () {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
          const lat = position.coords.latitude;
          const lon = position.coords.longitude;

          document.getElementById('latitude').value = lat;
          document.getElementById('longitude').value = lon;

          fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`)
            .then(res => res.json())
            .then(data => {
              document.getElementById('location').value = data.display_name || 'Location Found';
            })
            .catch(() => {
              document.getElementById('location').value = 'Could not fetch location';
            });
        }, () => {
          document.getElementById('location').value = 'Location permission denied';
        });
      } else {
        document.getElementById('location').value = 'Geolocation not supported';
      }
    };
  </script>
</body>
</html>