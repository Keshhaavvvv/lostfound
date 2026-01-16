<?php
/* File: forgot_password.php (Fixed Timezone Issue) */
include 'db.php';
include 'mail_config.php'; 

$message = "";
$msg_type = "";

if (isset($_GET['resend_email'])) {
    $_POST['email'] = $_GET['resend_email'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" || isset($_GET['resend_email'])) {
    $email = $conn->real_escape_string($_POST['email']);
    
    $sql = "SELECT id, full_name FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        $otp = rand(100000, 999999);
        
        // --- FIX: LET MYSQL HANDLE THE TIME ---
        // We do not calculate $expiry in PHP anymore.
        $conn->query("UPDATE users SET reset_token='$otp', token_expiry=DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE email='$email'");

        $subject = "Password Reset Code - Campus Lost & Found";
        $email_body = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f3f4f6;'>
                <div style='background-color: white; padding: 30px; border-radius: 10px; max-width: 500px; margin: 0 auto;'>
                    <h2 style='color: #4f46e5; margin-top: 0;'>Reset Your Password</h2>
                    <p>Hi {$row['full_name']},</p>
                    <p>Use the code below to reset your password. Valid for 15 minutes.</p>
                    <div style='background: #e0e7ff; color: #4338ca; font-size: 24px; font-weight: bold; text-align: center; padding: 15px; border-radius: 8px; letter-spacing: 5px; margin: 20px 0;'>
                        $otp
                    </div>
                </div>
            </div>
        ";

        if (sendEmail($email, $subject, $email_body)) {
            header("Location: reset_verify.php?email=" . urlencode($email));
            exit();
        } else {
            $message = "Error: Could not send email.";
            $msg_type = "error";
        }

    } else {
        $message = "We couldn't find an account with that email.";
        $msg_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1; --primary-hover: #818cf8;
            --bg-body: #0f172a; --bg-card: #1e293b;
            --text-main: #f1f5f9; --text-muted: #94a3b8;
            --border: #334155; --input-bg: #0f172a;
        }
        body { font-family: 'Inter', sans-serif; margin: 0; background-color: var(--bg-body); display: flex; height: 100vh; color: var(--text-main); }
        .left-panel { flex: 1; background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); display: flex; flex-direction: column; justify-content: center; align-items: center; color: white; padding: 40px; text-align: center; }
        .left-panel h1 { font-size: 2.5rem; margin-bottom: 10px; }
        .right-panel { flex: 1; display: flex; align-items: center; justify-content: center; padding: 20px; background: var(--bg-body); }
        .login-box { width: 100%; max-width: 400px; padding: 40px; background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5); }
        h2 { margin-top: 0; color: white; }
        p { color: var(--text-muted); }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 500; color: var(--text-main); margin-bottom: 8px; }
        input { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 1rem; box-sizing: border-box; background: var(--input-bg); color: white; }
        input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); }
        .btn-submit { width: 100%; padding: 12px; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .btn-submit:hover { background: var(--primary-hover); }
        .error-banner { background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 10px; border-radius: 6px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.4); text-align: center; }
        @media (max-width: 768px) { .left-panel { display: none; } }
    </style>
</head>
<body>
    <div class="left-panel">
        <h1>Forgot Password?</h1>
        <p>Enter your email and we'll send you a secure code.</p>
    </div>
    <div class="right-panel">
        <div class="login-box">
            <h2>Reset Password</h2>
            <?php if($message): ?><div class="error-banner"><?php echo $message; ?></div><?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="student@college.edu" required>
                </div>
                <button type="submit" class="btn-submit">Send OTP Code</button>
            </form>
            <div style="text-align:center; margin-top:20px;">
                <a href="login.php" style="color:var(--primary); text-decoration:none;">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>