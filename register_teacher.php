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
    $specializations = implode(',', $_POST['specialization']);
    $location_name = $_POST['location'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $district = $_POST['district'];
    $street = $_POST['street'];

    // Insert location
    $stmt = $conn->prepare("INSERT INTO locations (name, latitude, longitude) VALUES (?, ?, ?)");
    $stmt->bind_param("sdd", $location_name, $latitude, $longitude);
    $stmt->execute();
    $location_id = $stmt->insert_id;
    $stmt->close();

    // Insert into users
    $role = 'teacher';
    $approved = 0;
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, location_id, approved) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssii', $name, $email, $password, $role, $location_id, $approved);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();

    // Image upload
    $upload_dir = 'uploads/teachers/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $profile_picture = 'default.png';

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['profile_picture']['tmp_name'];
        $originalName = basename($_FILES['profile_picture']['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $allowed_ext)) {
            $newName = uniqid('teacher_') . '.' . $ext;
            $target_path = $upload_dir . $newName;

            if (move_uploaded_file($tmp, $target_path)) {
                $profile_picture = $newName;
            }
        }
    }

    // Insert into teachers
    $stmt = $conn->prepare("INSERT INTO teachers (user_id, gender, age, phone, qualification, specialization, district, street, profile_picture)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isissssss", $user_id, $gender, $age, $phone, $qualification, $specializations, $district, $street, $profile_picture);
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
    .modal-content h2 { color: #27ae60; }
    .modal-content button {
      padding: 10px 20px;
      background: #27ae60;
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      margin-top: 20px;
      cursor: pointer;
    }
    .modal-content button:hover { background: #219150; }
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

      <form method="POST" action="" enctype="multipart/form-data">
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
          <input type="hidden" name="district" id="district">
          <input type="hidden" name="street" id="street">
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

        <h3><i class="fas fa-certificate"></i> Qualifications</h3>
        <div class="input-group" style="display:block;">
          <?php
          $qualifications = [
            "Maktab Certificate", "Hifdh al-Qur'an", "Tajweed Certification", "Alimiyyah (Aalim/Aalimah)",
            "Fazil / Dars-e-Nizami", "Ijazah (in Hadith/Quran)", "Mufti Course (Takhassus fi al-Fiqh)",
            "Bachelor in Islamic Studies (BA)", "Masters in Islamic Studies (MA)", "PhD in Islamic Sciences"
          ];
          foreach ($qualifications as $q):
          ?>
          <label><input type="radio" name="qualification" value="<?= $q ?>" required> <?= $q ?></label><br>
          <?php endforeach; ?>
        </div>

        <h3><i class="fas fa-book-open"></i> Areas of Specialization</h3>
        <div class="input-group" style="display:block;">
          <?php
          $subjects = ["Tajweed", "Hifdh", "Tilawah", "Hadith", "Fiqh", "Aqidah", "Seerah", "Tafsir"];
          foreach ($subjects as $s):
          ?>
          <label><input type="checkbox" name="specialization[]" value="<?= $s ?>"> <?= $s ?></label><br>
          <?php endforeach; ?>
        </div>

        <div class="input-group">
          <i class="fas fa-image"></i>
          <input type="file" name="profile_picture" accept="image/*" required />
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
  window.location.href = "login.php"; // or "index.html" based on your system
}
    // Geolocation
    window.onload = function () {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
          const lat = position.coords.latitude;
          const lon = position.coords.longitude;
          document.getElementById("latitude").value = lat;
          document.getElementById("longitude").value = lon;

          fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`)
            .then(res => res.json())
            .then(data => {
              const addr = data.address || {};
              document.getElementById("location").value = data.display_name || "Location found";
              document.getElementById("district").value = addr.county || addr.state_district || "";
              document.getElementById("street").value = addr.road || addr.suburb || "";
            })
            .catch(() => {
              document.getElementById("location").value = "Could not fetch location";
            });
        }, () => {
          document.getElementById("location").value = "Location permission denied";
        });
      } else {
        document.getElementById("location").value = "Geolocation not supported";
      }
    };
  </script>
</body>
</html>