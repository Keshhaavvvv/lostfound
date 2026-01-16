<?php
/* File: verify_otp.php (Final Step of Registration) */
include 'db.php';

// If no temp user data, redirect to register
if (!isset($_SESSION['temp_user'])) {
    header("Location: register.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = $_POST['otp'];
    $session_otp = $_SESSION['temp_user']['otp'];

    if ($entered_otp == $session_otp) {
        // --- OTP MATCHED! CREATE USER IN DB ---
        $u = $_SESSION['temp_user'];
        
        $sql = "INSERT INTO users (full_name, student_id, gender, email, mobile, password, role) 
                VALUES ('{$u['full_name']}', '{$u['student_id']}', '{$u['gender']}', '{$u['email']}', '{$u['mobile']}', '{$u['password']}', 'student')";

        if ($conn->query($sql)) {
            // Cleanup and Login
            unset($_SESSION['temp_user']);
            echo "<script>
                alert('✅ Account Verified! You can now login.');
                window.location.href = 'login.php';
            </script>";
            exit();
        } else {
            $error = "Database Error: " . $conn->error;
        }
    } else {
        $error = "Incorrect OTP. Please check your email and try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .box { background: white; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { color: #1f2937; margin-top: 0; }
        p { color: #6b7280; margin-bottom: 20px; }
        input { width: 100%; padding: 15px; font-size: 1.2rem; letter-spacing: 5px; text-align: center; border: 1px solid #d1d5db; border-radius: 8px; margin-bottom: 20px; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; background: #4f46e5; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .error { color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem; }
    </style>
</head>
<body>

    <div class="box">
        <h2>Verify Email</h2>
        <p>We sent a 6-digit code to <strong><?php echo $_SESSION['temp_user']['email']; ?></strong></p>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="otp" placeholder="000000" maxlength="6" required pattern="\d{6}">
            <button type="submit" class="btn">Verify & Create Account</button>
        </form>
        
        <br>
        <a href="register.php" style="color: #6b7280; font-size: 0.9rem; text-decoration: none;">&larr; Wrong email? Go back</a>
    </div>

</body>
</html>