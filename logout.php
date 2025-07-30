<?php
session_start();

// Store the current page URL before logging out
$redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'HomePage.php';

// Check if logout was confirmed via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    // Destroy the session
    session_unset();
    session_destroy();
    
    // Redirect to homepage
    header('Location: HomePage.php');
    exit;
}

// If not confirmed via POST, show confirmation page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .logout-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .btn-container {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <h2>Logout Confirmation</h2>
        <p>Are you sure you want to logout?</p>
        
        <div class="btn-container">
            <form method="POST" action="logout.php">
                <button type="submit" name="confirm_logout" class="btn btn-danger">Yes, Logout</button>
            </form>
            <a href="<?php echo htmlspecialchars($redirect_url); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</body>
</html>