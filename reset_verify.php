<?php
/* File: reset_verify.php (Fixed Timezone Issue) */
include 'db.php';

$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_post = $conn->real_escape_string($_POST['email']);
    $otp_input = trim($conn->real_escape_string($_POST['otp'])); // Trim spaces
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // --- DEBUGGING (Uncomment if still failing) ---
    // echo "Checking: $email_post with OTP: $otp_input"; 

    // Verify OTP (Using MySQL NOW() ensures timezone match)
    $sql = "SELECT id FROM users WHERE email='$email_post' AND reset_token='$otp_input' AND token_expiry > NOW()";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        if ($new_pass === $confirm_pass) {
            if (strlen($new_pass) > 15 || !preg_match('/[A-Za-z]/', $new_pass) || !preg_match('/[0-9]/', $new_pass)) {
                $message = "Password must be < 15 chars & contain letters/numbers.";
            } else {
                $hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $conn->query("UPDATE users SET password='$hash', reset_token=NULL, token_expiry=NULL WHERE email='$email_post'");
                echo "<script>alert('✅ Password Reset Success! Login now.'); window.location.href='login.php';</script>";
                exit();
            }
        } else {
            $message = "Passwords do not match.";
        }
    } else {
        $message = "Invalid or expired OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1; --primary-hover: #818cf8;
            --bg-body: #0f172a; --bg-card: #1e293b;
            --text-main: #f1f5f9; --text-muted: #94a3b8;
            --border: #334155; --input-bg: #0f172a;
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg-body); display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; color: var(--text-main); }
        .box { background: var(--bg-card); padding: 40px; border-radius: 16px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5); border: 1px solid var(--border); }
        h2 { margin-top: 0; color: white; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: 500; margin-bottom: 6px; color: var(--text-muted); }
        input { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box; font-size: 1rem; background: var(--input-bg); color: white; }
        input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); }
        .btn { width: 100%; padding: 12px; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px; transition: 0.2s; }
        .btn:hover { background: var(--primary-hover); }
        .error { background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; border: 1px solid rgba(239, 68, 68, 0.4); }
        .resend-box { margin-top: 20px; text-align: center; font-size: 0.9rem; color: var(--text-muted); }
        .resend-link { color: var(--primary); text-decoration: none; font-weight: 600; cursor: pointer; display: none; }
        .resend-link:hover { color: var(--primary-hover); }
    </style>
</head>
<body>
    <div class="box">
        <h2>Verify & Reset</h2>
        <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:20px;">Code sent to <strong><?php echo $email; ?></strong></p>
        <?php if($message): ?><div class="error"><?php echo $message; ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="email" value="<?php echo $email; ?>">
            <div class="form-group">
                <label>OTP Code</label>
                <input type="text" name="otp" placeholder="123456" maxlength="6" required style="letter-spacing: 5px; font-weight: bold; text-align:center;">
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="New Password" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <button type="submit" class="btn">Reset Password</button>
        </form>
        <div class="resend-box">
            <span id="timerText">Resend OTP in <span id="timer" style="color:var(--primary); font-weight:bold;">60</span>s</span>
            <a href="forgot_password.php?resend_email=<?php echo urlencode($email); ?>" id="resendLink" class="resend-link">Resend OTP</a>
        </div>
    </div>
    <script>
        let timeLeft = 60;
        const timerElem = document.getElementById('timer');
        const timerText = document.getElementById('timerText');
        const resendLink = document.getElementById('resendLink');
        const countdown = setInterval(() => {
            timeLeft--;
            timerElem.innerText = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(countdown);
                timerText.style.display = 'none';
                resendLink.style.display = 'inline-block';
            }
        }, 1000);
    </script>
</body>
</html>