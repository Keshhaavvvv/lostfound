<?php
/* File: reset_password.php (Verify Token & Set New Password) */
include 'db.php';

$message = "";
$valid_token = false;

// 1. Check Token in URL
if (isset($_GET['token'])) {
    $token = $conn->real_escape_string($_GET['token']);
    
    // Validate Token & Expiry
    $sql = "SELECT id FROM users WHERE reset_token='$token' AND token_expiry > NOW()";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $valid_token = true;
    } else {
        $message = "Invalid or expired link. Please request a new one.";
    }
} else {
    header("Location: login.php");
    exit();
}

// 2. Handle New Password Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $valid_token) {
    $password_raw = $_POST['password'];
    $confirm_pwd  = $_POST['confirm_password'];
    $token = $conn->real_escape_string($_POST['token']);

    if ($password_raw !== $confirm_pwd) {
        $message = "Passwords do not match.";
    } elseif (strlen($password_raw) > 15 || !preg_match('/[A-Za-z]/', $password_raw) || !preg_match('/[0-9]/', $password_raw)) {
        $message = "Password does not meet complexity requirements.";
    } else {
        // Update Password & Clear Token
        $hash = password_hash($password_raw, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$hash', reset_token=NULL, token_expiry=NULL WHERE reset_token='$token'");
        
        echo "<script>
            alert('✅ Password Reset Successfully! Login with your new password.');
            window.location.href = 'login.php';
        </script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { margin-top: 0; color: #111827; text-align: center; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #d1d5db; border-radius: 8px; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; background: #4f46e5; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn:hover { background: #4338ca; }
        .alert { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>

    <div class="box">
        <?php if ($valid_token): ?>
            <h2>Set New Password</h2>
            <?php if($message): ?><div class="alert"><?php echo $message; ?></div><?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                
                <label>New Password</label>
                <input type="password" name="password" placeholder="New Password" required>
                
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                
                <button type="submit" class="btn">Update Password</button>
            </form>
        <?php else: ?>
            <div class="alert"><?php echo $message; ?></div>
            <a href="forgot_password.php" class="btn" style="display:block; text-align:center; text-decoration:none;">Request New Link</a>
        <?php endif; ?>
    </div>

</body>
</html>