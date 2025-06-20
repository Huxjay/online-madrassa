<?php
session_start();
include 'includes/db.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Check user in database
        $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            // Store user info in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            
            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header('Location: admin/index.php');
                    break;
                case 'teacher':
                    header('Location: dashboard/teacher.php');
                    break;
                case 'parent':
                    header('Location: dashboard/parent.php');
                    break;
                default:
                    $error = 'Invalid user role.';
            }
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Madrassa</title>
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="overlay"></div>
        <div class="login-box">
            <h1>Login</h1>
            <?php if (isset($error)): ?>
                <p style="color: #ff4d4d;">⚠️ <?php echo $error; ?></p>
            <?php endif; ?>
            <form action="" method="POST">
                <input type="email" name="email" placeholder="Enter your email" required>
                <input type="password" name="password" placeholder="Enter your password" required>
                <button type="submit">Login</button>
            </form>
            <p>Don’t have an account? <a href="register.php">Register</a></p>
        </div>
    </div>
    <footer>
        <p>© 2025 Online Madrassa. All rights reserved. | Designed with ❤️ by YourName</p>
    </footer>
    <script src="assets/js/scripts.js"></script>
</body>
</html>
