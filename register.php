<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Parent Registration - Online Madrassa</title>

  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>
  <div class="form-container">
    <h2><i class="fas fa-user-plus"></i> Parent Registration</h2>

    <?php if (isset($error)): ?>
      <p class="error-msg">⚠ <?php echo $error; ?></p>
    <?php endif; ?>

    <form action="" method="POST">
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
        <i class="fas fa-phone"></i>
        <input type="text" name="phone" placeholder="Phone Number" required />
      </div>

      <div class="input-group">
        <i class="fas fa-map-marker-alt"></i>
        <input type="text" name="location" id="address" placeholder="Detecting location..." readonly required />
        <input type="hidden" name="latitude" id="latitude" />
        <input type="hidden" name="longitude" id="longitude" />
      </div>

      <div class="input-group">
        <i class="fas fa-user-clock"></i>
        <input type="number" name="parent_age" placeholder="Parent Age" required />
      </div>

      <div class="input-group">
        <i class="fas fa-venus-mars"></i>
        <select name="parent_gender" required>
          <option value="">Select Parent Gender</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
        </select>
      </div>

      <div class="input-group">
        <i class="fas fa-child"></i>
        <input type="text" name="child_name" placeholder="Child Name" required />
      </div>

      <div class="input-group">
        <i class="fas fa-birthday-cake"></i>
        <input type="number" name="child_age" placeholder="Child Age" required />
      </div>

      <div class="input-group">
        <i class="fas fa-venus-mars"></i>
        <select name="child_gender" required>
          <option value="">Select Child Gender</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
        </select>
      </div>

      <button type="submit"><i class="fas fa-paper-plane"></i> Register</button>
    </form>

    <div class="footer">
      <p>Already have an account? <a href="login.php">Login</a></p>
      <p>© 2025 Online Madrassa. All rights reserved. | Designed with ❤ by YourName</p>
    </div>
  </div>

  <!-- Location Detection Script -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const container = document.querySelector(".form-container");
      container.classList.add("visible");

      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(async function(position) {
          const lat = position.coords.latitude;
          const lon = position.coords.longitude;

          document.getElementById("latitude").value = lat;
          document.getElementById("longitude").value = lon;

          try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
            const data = await response.json();
            if (data && data.display_name) {
              document.getElementById("address").value = data.display_name;
            } else {
              document.getElementById("address").value = "Unknown location";
            }
          } catch (error) {
            console.error("Geocoding error:", error);
            document.getElementById("address").value = "Unknown location";
          }
        }, function(error) {
          alert("Unable to fetch location. Please allow location access.");
        });
      } else {
        alert("Geolocation is not supported by this browser.");
      }
    });
  </script>
</body>
</html>