<?php
require_once 'Databaseconnection.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection (using MySQLi)
$host = 'localhost';
$dbname = 'learnify';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Initialize variables
$errors = [];
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process registration form
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $agree_terms = isset($_POST['agree_terms']);

    // Simple validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    // Email validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    // Password validation
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    } elseif (!preg_match('/[0-9]{2,}/', $password)) {
        $errors[] = "Password must contain at least two digits";
    } elseif (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Role validation - only allow student or teacher
    $allowed_roles = ['student', 'teacher'];
    if (empty($role)) {
        $errors[] = "Role is required";
    } elseif (!in_array($role, $allowed_roles)) {
        $errors[] = "Invalid role selected";
    }
    
    if (!isset($_POST['agree_terms'])) {
        $errors[] = "You must agree to the terms";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        if ($stmt === false) {
            $errors[] = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $errors[] = "Email already registered";
            }
            $stmt->close();
        }
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // Get role_id from roles table
            $role_stmt = $conn->prepare("SELECT role_id FROM roles WHERE role_name = ?");
            if ($role_stmt === false) {
                throw new Exception("Role query prepare failed: " . $conn->error);
            }
            
            $role_stmt->bind_param("s", $role);
            if (!$role_stmt->execute()) {
                throw new Exception("Role query execute failed: " . $role_stmt->error);
            }
            
            $role_result = $role_stmt->get_result();
            if ($role_result->num_rows === 0) {
                throw new Exception("Role '$role' not found in database");
            }
            
            $role_data = $role_result->fetch_assoc();
            $role_id = $role_data['role_id'];
            $role_stmt->close();
            
            // Insert into users table
            $user_stmt = $conn->prepare("INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)");
            if ($user_stmt === false) {
                throw new Exception("User insert prepare failed: " . $conn->error);
            }
            
            $user_stmt->bind_param("sssi", $name, $email, $hashed_password, $role_id);
            if (!$user_stmt->execute()) {
                throw new Exception("User insert execute failed: " . $user_stmt->error);
            }
            
            $user_stmt->close();
            
            // Success message and redirect
            $success_message = "Registration successful! Redirecting to login page...";
            $_SESSION['registration_success'] = true;
            
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 3000);
            </script>";
            
        } catch (Exception $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
            error_log("Registration Error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learnify | Register</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="Register.css"> 
    <style>
        .password-requirements {
            font-size: 0.8rem;
            margin-top: 5px;
        }
        .requirement {
            color: #dc3545;
        }
        .requirement.valid {
            color: #28a745;
        }
        #password-strength {
            height: 5px;
            margin-top: 5px;
            background-color: #e9ecef;
        }
        #password-strength .progress-bar {
            transition: width 0.3s;
        }
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
            border-color: #badbcc;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
            border-color: #f5c2c7;
        }
    .password-container {
        position: relative;
    }
    .password-toggle {
        position: absolute;
        top: 23%;
        right: 12px;
        transform: translateY(-50%);
        cursor: pointer;
        color: #6c757d;
        z-index: 2;
    }
    .form-control[type="password"],
    .form-control[type="text"] {
        padding-right: 40px;
    }

    </style>
</head>
<body>
    <div class="wrapper">
        <div class="inner">
            <!-- Image Holder (on left side for register) -->
            <div class="image-holder" style="right: auto; left: 0;">
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
            
            <!-- Register Form -->
           <div class="form-container">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($success_message)): ?>
        <div class="alert alert-success d-flex align-items-center justify-content-between">
            <div>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
            <div class="spinner-border spinner-border-sm ms-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <script>
            // Optional: redirect to login after 2 seconds
            setTimeout(function () {
                window.location.href = 'login.php'; // change if needed
            }, 2000);
        </script>
    <?php endif; ?>

                
                <form method="POST" action="" id="registrationForm">
                    <h3>Create Account</h3>
                    
                    <div class="form-group">
                        <input type="text" class="form-control" id="register-name" name="name" placeholder=" " 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                        <label for="register-name" class="label">Full Name</label>
                    </div>
                    
                    <div class="form-group">
                        <input type="email" class="form-control" id="register-email" name="email" placeholder=" " 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        <label for="register-email" class="label">Email Address</label>
                    </div>
                    
                    <div class="form-group">
                        <select class="form-control" id="register-role" name="role" required>
                            <option value="" disabled selected></option>
                            <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                            <option value="teacher" <?php echo (isset($_POST['role']) && $_POST['role'] === 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                        </select>
                        <label for="register-role" class="label">Select Role</label>
                    </div>
                    
               

<div class="form-group password-container">
    <input type="password" class="form-control" id="register-password" name="password" placeholder=" " required>
    <label for="register-password" class="label">Password</label>

    <!-- Show/Hide Icon -->
    <span class="password-toggle" onclick="togglePassword('register-password', this)">
        <i class="far fa-eye"></i>
    </span>

    <!-- Password strength progress -->
    <div id="password-strength" class="progress mt-2">
        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
    </div>

    <!-- Password requirements (2 per line) -->
    <div class="password-requirements d-flex flex-wrap gap-3 mt-2">
        <div id="length" class="requirement" style="width: 48%;">At least 6 characters</div>
        <div id="uppercase" class="requirement" style="width: 48%;">At least 1 uppercase letter</div>
        <div id="digits" class="requirement" style="width: 48%;">At least 2 digits</div>
        <div id="special" class="requirement" style="width: 48%;">At least 1 special character</div>
    </div>
</div>

                    
                    <div class="form-group">
                        <input type="password" class="form-control" id="register-confirm" name="confirm_password" placeholder=" " required>
                        <label for="register-confirm" class="label">Confirm Password</label>
                        <div id="password-match" class="text-danger small"></div>
                    </div>
                    
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="agree-terms" name="agree_terms" <?php echo isset($_POST['agree_terms']) ? 'checked' : ''; ?> required>
                        <label class="form-check-label" for="agree-terms">
                            I agree to the <a href="#" style="color: var(--primary-color);">Terms</a> and <a href="#" style="color: var(--primary-color);">Privacy Policy</a>
                        </label>
                    </div>
                    
                    <button type="submit" id="submit-btn" class="btn btn-primary">Register</button>
                    
                    <div class="form-footer">
                        Already have an account? <a href="login.php" class="toggle-link"><b>Sign in here</b></a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
              <script>
                function togglePassword(inputId, toggleElement) {
                    const input = document.getElementById(inputId);
                    const icon = toggleElement.querySelector('i');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.replace('fa-eye', 'fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.replace('fa-eye-slash', 'fa-eye');
                    }
                }


        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('register-password');
            const confirmInput = document.getElementById('register-confirm');
            const passwordMatch = document.getElementById('password-match');
            const submitBtn = document.getElementById('submit-btn');
            const form = document.getElementById('registrationForm');
            
            // Password validation requirements
            const requirements ={
                length: document.getElementById('length'),
                uppercase: document.getElementById('uppercase'),
                digits: document.getElementById('digits'),
                special: document.getElementById('special')
            };
            
            // Password strength meter
            const strengthMeter = document.querySelector('#password-strength .progress-bar');
            
            // Validate password on input
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Validate length
                if (password.length >= 6) {
                    requirements.length.classList.add('valid');
                    strength += 25;
                } else {
                    requirements.length.classList.remove('valid');
                }
                
                // Validate uppercase
                if (/[A-Z]/.test(password)) {
                    requirements.uppercase.classList.add('valid');
                    strength += 25;
                } else {
                    requirements.uppercase.classList.remove('valid');
                }
                
                // Validate digits (at least 2)
                if (/\d.*\d/.test(password)) {
                    requirements.digits.classList.add('valid');
                    strength += 25;
                } else {
                    requirements.digits.classList.remove('valid');
                }
                
                // Validate special character
                if (/[^a-zA-Z0-9]/.test(password)) {
                    requirements.special.classList.add('valid');
                    strength += 25;
                } else {
                    requirements.special.classList.remove('valid');
                }
                
                // Update strength meter
                strengthMeter.style.width = strength + '%';
                
                // Change color based on strength
                if (strength < 50) {
                    strengthMeter.classList.remove('bg-warning', 'bg-success');
                    strengthMeter.classList.add('bg-danger');
                } else if (strength < 100) {
                    strengthMeter.classList.remove('bg-danger', 'bg-success');
                    strengthMeter.classList.add('bg-warning');
                } else {
                    strengthMeter.classList.remove('bg-danger', 'bg-warning');
                    strengthMeter.classList.add('bg-success');
                }
                
                // Check password match
                checkPasswordMatch();
            });
            
            // Check password match
            confirmInput.addEventListener('input', checkPasswordMatch);
            
            function checkPasswordMatch() {
                if (passwordInput.value !== confirmInput.value) {
                    passwordMatch.textContent = 'Passwords do not match';
                    return false;
                } else {
                    passwordMatch.textContent = '';
                    return true;
                }
            }
            
            // Form validation before submission
            form.addEventListener('submit', function(e) {
                const password = passwordInput.value;
                
                // Check all requirements
                const isLengthValid = password.length >= 6;
                const hasUppercase = /[A-Z]/.test(password);
                const hasDigits = /\d.*\d/.test(password);
                const hasSpecial = /[^a-zA-Z0-9]/.test(password);
                const passwordsMatch = checkPasswordMatch();
                
                if (!isLengthValid || !hasUppercase || !hasDigits || !hasSpecial || !passwordsMatch) {
                    e.preventDefault();
                    alert('Please ensure your password meets all requirements and matches the confirmation.');
                }
            });
            
            // Email validation
            const emailInput = document.getElementById('register-email');
            emailInput.addEventListener('blur', function() {
                if (!this.value.includes('@')) {
                    this.setCustomValidity('Email must contain @ symbol');
                } else {
                    this.setCustomValidity('');
                }
            });
        });
    </script>
</body>
</html>