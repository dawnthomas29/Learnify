<?php
ob_start();
require_once 'Databaseconnection.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'learnify';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->error);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    if (empty($errors)) {
        try {
            $query = "SELECT u.user_id, u.name, u.email, u.password, u.is_blocked, r.role_name 
                     FROM users u
                     JOIN roles r ON u.role_id = r.role_id
                     WHERE u.email = ?";
            
            $stmt = $conn->prepare($query);
            
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            if (!$stmt->bind_param("s", $email)) {
                throw new Exception("Bind failed: " . $stmt->error);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // ✅ Block check inserted here
                if (!empty($user['is_blocked']) && $user['is_blocked']) {
                    $errors[] = "Your account has been blocked by the admin.";
                } elseif (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role_name'];
                    
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $cookie_value = base64_encode($user['user_id'] . ':' . $token);
                        
                        $update_query = "UPDATE users SET remember_token = ? WHERE user_id = ?";
                        $update_stmt = $conn->prepare($update_query);
                        
                        if ($update_stmt && 
                            $update_stmt->bind_param("ss", $token, $user['user_id']) && 
                            $update_stmt->execute()) {
                            setcookie('remember_me', $cookie_value, time() + (86400 * 30), "/");
                        }
                        if ($update_stmt) $update_stmt->close();
                    }
                    
                    $redirect = strtolower($user['role_name']) . '_dashboard.php';
                    if ($user['role_name'] === 'student') {
                        $redirect = 'student_dashboard.php';
                    }
                    elseif($user['role_name'] === 'admin') {
                        $redirect = 'Admin/admin_dashboard.php';
                    }
                    header("Location: $redirect");
                    exit;
                } else {
                    $errors[] = "Invalid email or password";
                }
            } else {
                $errors[] = "Invalid email or password";
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Login failed. Please try again.";
        }
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learnify | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Login_Register.css">
    <style>
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .alert-danger {
            margin-bottom: 1rem;
        }
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            font-size: 0.75rem;
            color: #6c757d;
            background-color: white;
            padding: 2px 6px;
            border-radius: 4px;
            z-index: 5;
        }
        .password-toggle:hover {
            color: var(--primary-color);
        }
        .password-toggle .toggle-text {
            font-size: 0.75rem;
        }
        .form-control[type="password"],
        .form-control[type="text"] {
            padding-right: 80px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="inner">
            <div class="image-holder">
                <div class="logo-container">
                    <div class="logo animate__animated animate__fadeInDown">
                        <i class="fas fa-graduation-cap animate__animated animate__bounceIn"></i>
                        <span class="logo-text animate__animated animate__fadeIn">Learnify</span>
                    </div>
                    <p class="tagline animate__animated animate__fadeInUp animate__delay-1s">
                        <span class="path animate__animated animate__fadeInLeft animate__delay-2s">Path to</span> 
                        <span class="clear-understanding animate__animated animate__fadeInRight animate__delay-2s">Clear Understanding</span>
                    </p>
                </div>
            </div>

            <div class="form-container">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="login.php" id="loginForm">
                    <h3>Welcome Back</h3>

                    <div class="form-group">
                        <input type="email" class="form-control" id="login-email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder=" " required>
                        <label for="login-email" class="label">Email Address</label>
                    </div>

                    <div class="form-group">
                        <div class="password-container">
                            <input type="password" class="form-control" id="login-password" name="password" placeholder=" " required>
                            <label for="login-password" class="label">Password</label>
                            <span class="password-toggle" onclick="togglePassword('login-password', this)">
                                <i class="far fa-eye"></i>
                                <span class="toggle-text">Show</span>
                            </span>
                        </div>

                    <div class="form-check mb-4">
                        <br>
                        <input class="form-check-input" type="checkbox" id="remember-me" name="remember" <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="remember-me">Remember me</label>
                    </div>

                    <button type="submit" class="btn btn-primary">Sign In</button>
                    <br><br>
                   <div class="d-flex justify-content-between align-items-start mt-3">
    <div class="small">
        Don't have an account? 
        <a href="Register.php" class="toggle-link mt-4" style="color: var(--primary-color);">Register here</a>
    </div>
    <a href="forgot_password.php" class="small text-decoration-none mt-4" style="color: var(--primary-color);"><b>Forgot Password?</b></a>
</div>



                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('loginForm');
            const emailInput = document.getElementById('login-email');

            emailInput.addEventListener('blur', function () {
                if (!this.value.includes('@')) {
                    this.setCustomValidity('Email must contain @ symbol');
                } else {
                    this.setCustomValidity('');
                }
            });

            form.addEventListener('submit', function (e) {
                const email = emailInput.value;
                const password = document.getElementById('login-password').value;

                if (!email || !password) {
                    e.preventDefault();
                    alert('Please fill in all required fields');
                }
            });
        });

        function togglePassword(inputId, toggleElement) {
            const passwordInput = document.getElementById(inputId);
            const icon = toggleElement.querySelector('i');
            const toggleText = toggleElement.querySelector('.toggle-text');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
                icon.style.color = "var(--primary-color)";
                toggleText.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
                icon.style.color = "#6c757d";
                toggleText.textContent = 'Show';
            }
        }
    </script>
</body>
</html>
