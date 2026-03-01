<?php
/* File: verify_otp.php (Auto-Login after Verification) */
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
        
        // Escape data again just to be safe before query
        $full_name = $conn->real_escape_string($u['full_name']);
        $student_id = $conn->real_escape_string($u['student_id']);
        $gender = $conn->real_escape_string($u['gender']);
        $email = $conn->real_escape_string($u['email']);
        $mobile = $conn->real_escape_string($u['mobile']);
        $password = $u['password']; // Already hashed

        $sql = "INSERT INTO users (full_name, student_id, gender, email, mobile, password, role) 
                VALUES ('$full_name', '$student_id', '$gender', '$email', '$mobile', '$password', 'student')";

        if ($conn->query($sql)) {
            // --- NEW: AUTO-LOGIN LOGIC ---
            $new_user_id = $conn->insert_id; // Get the new ID created by database

            // Set Session Variables (Log them in immediately)
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['role'] = 'student'; 
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;

            // Clear temp data
            unset($_SESSION['temp_user']);

            // Redirect directly to Dashboard
            echo "<script>
                alert('✅ Verification Successful! Welcome to Campus Lost & Found.');
                window.location.href = 'index.php';
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
        body { 
            font-family: 'Inter', sans-serif; 
            background: #f3f4f6; 
            display: flex; align-items: center; justify-content: center; 
            height: 100vh; margin: 0; 
        }
        .box { 
            background: white; padding: 40px; border-radius: 12px; 
            width: 100%; max-width: 400px; text-align: center; 
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); 
        }
        h2 { color: #1f2937; margin-top: 0; }
        p { color: #6b7280; margin-bottom: 20px; font-size: 0.95rem; }
        
        /* OTP Input Styling */
        input { 
            width: 100%; padding: 15px; font-size: 1.5rem; letter-spacing: 8px; 
            text-align: center; border: 1px solid #d1d5db; border-radius: 8px; 
            margin-bottom: 20px; box-sizing: border-box; 
            transition: 0.2s;
        }
        input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2); }
        
        .btn { 
            width: 100%; padding: 12px; background: #4f46e5; color: white; 
            border: none; border-radius: 8px; font-weight: 600; cursor: pointer; 
            transition: 0.2s; font-size: 1rem;
        }
        .btn:hover { background: #4338ca; }
        
        .error { 
            color: #ef4444; background: #fee2e2; padding: 10px; 
            border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem; 
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>

    <div class="box">
        <h2>Verify Email</h2>
        <p>We sent a 6-digit code to <br><strong><?php echo $_SESSION['temp_user']['email']; ?></strong></p>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="otp" placeholder="000000" maxlength="6" required pattern="\d{6}" autocomplete="off" autofocus>
            <button type="submit" class="btn">Verify & Enter</button>
        </form>
        
        <br>
        <a href="register.php" style="color: #6b7280; font-size: 0.85rem; text-decoration: none;">&larr; Wrong email? Go back</a>
    </div>

</body>
</html>