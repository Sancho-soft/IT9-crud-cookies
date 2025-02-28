<?php
session_start();
include 'db.php';

// Auto-login if "remember_user" cookie exists
if (isset($_COOKIE['remember_user']) && !isset($_SESSION['user_id'])) {
    $user_id = $_COOKIE['remember_user'];

    $stmt = $conn->prepare("SELECT id, email, name FROM registered_users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['name'] = $user['name'];

        // Redirect to dashboard (index.php)
        header("Location: index.php");
        exit();
    } else {
        // Remove invalid cookie
        setcookie("remember_user", "", time() - 3600, "/");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $remember_me = isset($_POST['remember_me']); // Check if "Remember Me" is checked

    $stmt = $conn->prepare("SELECT id, password, name FROM registered_users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $user['name'];

            // Set cookie if "Remember Me" is checked
            if ($remember_me) {
                setcookie("remember_user", $user['id'], time() + (86400 * 30), "/"); // 30 days
            }

            header("Location: index.php");
            exit();
        } else {
            echo "<script>alert('Invalid password!');</script>";
        }
    } else {
        echo "<script>alert('User not found!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #6a11cb;
            color: white;
            text-align: center;
            border-radius: 15px 15px 0 0;
        }
        .btn-success {
            background-color: #6a11cb;
            border: none;
        }
        .btn-success:hover {
            background-color: #2575fc;
        }
        .social-login .btn {
            width: 100%;
            margin: 5px 0;
        }
        .social-login .btn i {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h3>Login</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" class="form-control" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" name="password" class="form-control" required autocomplete="off">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember_me" class="form-check-input">
                    <label class="form-check-label">Remember Me</label>
                </div>
                <button type="submit" class="btn btn-success w-100">Login</button>
            </form>

            <!-- Social Login Buttons -->
            <div class="social-login mt-4">
                <p class="text-center">Or login with:</p>
                <a href="https://www.facebook.com" target="_blank" class="btn btn-primary" style="background-color: #1877F2;">
                    <i class="fab fa-facebook"></i> Facebook
                </a>
                <a href="https://discord.gg/Hv5hMUzh" target="_blank" class="btn btn-primary" style="background-color: #5865F2;">
                    <i class="fab fa-discord"></i> Discord
                </a>
                <a href="https://www.x.com" target="_blank" class="btn btn-info" style="background-color: #1DA1F2;">
                    <i class="fab fa-twitter"></i> Twitter (X)
                </a>
            </div>

            <p class="text-center mt-3">Don't have an account? <a href="register.php" class="text-decoration-none">Register</a></p>
        </div>
    </div>
</body>
</html>