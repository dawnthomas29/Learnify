<?php
session_start();
require '../Databaseconnection.php';

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];
$success = false;
$error = "";

// ✅ Fetch existing user data to prefill the form
$fetch = "SELECT u.name, u.email, pd.phone, pd.date_of_birth, pd.country, pd.city, pd.postal_code 
          FROM users u 
          LEFT JOIN profile_details pd ON u.user_id = pd.user_id 
          WHERE u.user_id = '$user_id'";
$result = $conn->query($fetch);
$data = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name         = trim($_POST['name']);
    $email        = trim($_POST['email']);
    $phone        = trim($_POST['phone']);
    $dob          = trim($_POST['date_of_birth']);
    $country      = trim($_POST['country']);
    $city         = trim($_POST['city']);
    $postal_code  = trim($_POST['postal_code']);

    // ✅ Email check (avoid duplicates)
    $email_check = "SELECT * FROM users WHERE email = '$email' AND user_id != '$user_id'";
    $email_result = $conn->query($email_check);

    if ($email_result && $email_result->num_rows > 0) {
        $error = "Email already in use by another user.";
    } else {
        // ✅ Always update name and email
        $update_user = "UPDATE users SET name = '$name', email = '$email' WHERE user_id = '$user_id'";
        if (!$conn->query($update_user)) {
            $error = "User update failed: " . $conn->error;
        }

        // ✅ Build dynamic profile update
        $fields = [];
        if (!empty($phone))        $fields[] = "phone = '$phone'";
        if (!empty($dob))          $fields[] = "date_of_birth = '$dob'";
        if (!empty($country))      $fields[] = "country = '$country'";
        if (!empty($city))         $fields[] = "city = '$city'";
        if (!empty($postal_code))  $fields[] = "postal_code = '$postal_code'";

        if (!empty($fields)) {
            $check_profile = "SELECT * FROM profile_details WHERE user_id = '$user_id'";
            $profile_result = $conn->query($check_profile);

            if ($profile_result && $profile_result->num_rows > 0) {
                $update_profile = "UPDATE profile_details SET " . implode(', ', $fields) . " WHERE user_id = '$user_id'";
            } else {
                $insert_cols = ['user_id'];
                $insert_vals = ["'$user_id'"];
                if (!empty($phone))        { $insert_cols[] = "phone";        $insert_vals[] = "'$phone'"; }
                if (!empty($dob))          { $insert_cols[] = "date_of_birth";$insert_vals[] = "'$dob'"; }
                if (!empty($country))      { $insert_cols[] = "country";      $insert_vals[] = "'$country'"; }
                if (!empty($city))         { $insert_cols[] = "city";         $insert_vals[] = "'$city'"; }
                if (!empty($postal_code))  { $insert_cols[] = "postal_code";  $insert_vals[] = "'$postal_code'"; }

                $update_profile = "INSERT INTO profile_details (" . implode(', ', $insert_cols) . ") VALUES (" . implode(', ', $insert_vals) . ")";
            }

            if (!$conn->query($update_profile)) {
                $error = "Profile update failed: " . $conn->error;
            } else {
                $success = true;
            }
        } else {
            // No profile data to update, but user update is successful
            $success = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="1" fill="white" opacity="0.1"/><circle cx="80" cy="40" r="1" fill="white" opacity="0.1"/><circle cx="40" cy="80" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 
                0 32px 64px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            padding: 48px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { background-position: 200% 0; }
            50% { background-position: -200% 0; }
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 12px 24px rgba(102, 126, 234, 0.3);
        }

        .header-icon i {
            font-size: 32px;
            color: white;
        }

        h2 {
            color: #2d3748;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #718096;
            font-size: 16px;
        }

        .form-grid {
            display: grid;
            gap: 24px;
            margin-bottom: 32px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            position: relative;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .required::after {
            content: ' *';
            color: #e53e3e;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 18px;
            z-index: 2;
        }

        input[type=text], input[type=email], input[type=date] {
            width: 100%;
            padding: 16px 16px 16px 50px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.9);
            color: #2d3748;
        }

        input[type=text]:focus, input[type=email]:focus, input[type=date]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: white;
            transform: translateY(-2px);
        }

        input::placeholder {
            color: #a0aec0;
        }

        .form-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-top: 40px;
        }

        button {
            padding: 16px 32px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            min-width: 140px;
        }

        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        button:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 2px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #edf2f7;
            border-color: #cbd5e0;
            transform: translateY(-2px);
        }

        .alert {
            padding: 20px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: none;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .alert::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: currentColor;
        }

        .alert-success {
            background: linear-gradient(135deg, #c6f6d5, #9ae6b4);
            color: #22543d;
            border: 1px solid #9ae6b4;
        }

        .alert-error {
            background: linear-gradient(135deg, #fed7d7, #feb2b2);
            color: #742a2a;
            border: 1px solid #feb2b2;
        }

        .alert i {
            margin-right: 12px;
            font-size: 18px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 32px 24px;
                margin: 10px;
                border-radius: 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            h2 {
                font-size: 28px;
            }

            .form-actions {
                flex-direction: column;
                align-items: center;
            }

            button {
                width: 100%;
                max-width: 280px;
            }

            .header-icon {
                width: 64px;
                height: 64px;
            }

            .header-icon i {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .form-container {
                padding: 24px 20px;
            }

            input[type=text], input[type=email], input[type=date] {
                padding: 14px 14px 14px 45px;
                font-size: 16px;
            }

            .input-icon {
                font-size: 16px;
                left: 14px;
            }
        }

        /* Loading animation for form submission */
        .btn-primary.loading {
            pointer-events: none;
        }

        .btn-primary.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <div class="header">
            <div class="header-icon">
                <i class="fas fa-user-edit"></i>
            </div>
            <h2>Update Profile</h2>
            <p class="subtitle">Keep your information current and secure</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="alert alert-success" id="successAlert">
            <i class="fas fa-check-circle"></i>
            Profile updated successfully! Redirecting...
        </div>

        <form method="POST" id="profileForm">
            <div class="form-grid">
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Full Name</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="name" value="<?= htmlspecialchars($data['name'] ?? '') ?>" placeholder="Enter your full name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="required">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>" placeholder="Enter your email" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Phone Number</label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="text" name="phone" value="<?= htmlspecialchars($data['phone'] ?? '') ?>" placeholder="Enter phone number">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <div class="input-wrapper">
                            <i class="fas fa-calendar input-icon"></i>
                            <input type="date" name="date_of_birth" value="<?= htmlspecialchars($data['date_of_birth'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Country</label>
                        <div class="input-wrapper">
                            <i class="fas fa-globe input-icon"></i>
                            <input type="text" name="country" value="<?= htmlspecialchars($data['country'] ?? '') ?>" placeholder="Enter your country">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <div class="input-wrapper">
                            <i class="fas fa-map-marker-alt input-icon"></i>
                            <input type="text" name="city" value="<?= htmlspecialchars($data['city'] ?? '') ?>" placeholder="Enter your city">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Postal Code</label>
                    <div class="input-wrapper">
                        <i class="fas fa-mail-bulk input-icon"></i>
                        <input type="text" name="postal_code" value="<?= htmlspecialchars($data['postal_code'] ?? '') ?>" placeholder="Enter postal code">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary" id="updateBtn">
                    <i class="fas fa-save" style="margin-right: 8px;"></i>
                    Update Profile
                </button>
                <button type="button" class="btn-secondary" onclick="window.location.href='adminprofile.php'">
                    <i class="fas fa-times" style="margin-right: 8px;"></i>
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($success): ?>
    <script>
        document.getElementById("successAlert").style.display = "block";
        setTimeout(() => {
            window.location.href = "adminprofile.php";
        }, 2000);
    </script>
<?php endif; ?>

</body>
</html>