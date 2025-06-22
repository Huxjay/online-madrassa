<?php
include 'includes/db.php';

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
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, location_id) VALUES (?, ?, ?, 'teacher', ?)");
    $stmt->bind_param("sssi", $name, $email, $password, $location_id);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();

    // 3. Insert into teachers
    $stmt = $conn->prepare("INSERT INTO teachers (user_id, gender, age, phone, qualification, specialization) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isisss", $user_id, $gender, $age, $phone, $qualification, $specialization);
    $stmt->execute();
    $stmt->close();

    $success = "Teacher registered successfully!";
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
</head>
<body>
  <div class="register-container">
    <div class="form-box">
      <h2><i class="fas fa-chalkboard-teacher"></i> Register as Teacher</h2>

      <?php if (isset($success)): ?>
        <p class="success"><?php echo $success; ?></p>
      <?php endif; ?>

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

  <script>
    // Geolocation + OpenStreetMap reverse geocoding
    window.onload = () => {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(success => {
          const lat = success.coords.latitude;
          const lon = success.coords.longitude;

          document.getElementById('latitude').value = lat;
          document.getElementById('longitude').value = lon;

          fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`)
            .then(response => response.json())
            .then(data => {
              document.getElementById('location').value = data.display_name;
            })
            .catch(() => {
              document.getElementById('location').value = "Location unavailable";
            });
        }, error => {
          document.getElementById('location').value = "Permission denied or error fetching location.";
        });
      } else {
        document.getElementById('location').value = "Geolocation not supported";
      }
    };
  </script>
</body>
</html>