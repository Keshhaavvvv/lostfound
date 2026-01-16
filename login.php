<?php
/* File: login.php (Dark Theme + Ban Check Logic) */
include 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if (password_verify($password, $row['password'])) {
            
            // 🛑 BAN CHECK LOGIC
            if ($row['is_banned'] == 1) {
                $error = "🚫 Access Denied: Your account has been suspended by the Admin due to suspicious activity.";
            } else {
                // ✅ Not Banned - Set Session
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['full_name'] = $row['full_name'];
                $_SESSION['role'] = $row['role'];

                if ($row['role'] == 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            }

        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Campus Lost & Found</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* DARK THEME PALETTE */
            --primary: #6366f1; /* Indigo-500 */
            --primary-hover: #818cf8;
            
            --bg-body: #0f172a; /* Slate-900 */
            --bg-card: #1e293b; /* Slate-800 */
            --bg-input: #0f172a; 
            
            --text-main: #f1f5f9; /* Slate-100 */
            --text-muted: #94a3b8; /* Slate-400 */
            
            --border: #334155; /* Slate-700 */
            --error-bg: rgba(239, 68, 68, 0.2); --error-text: #fca5a5; --error-border: rgba(239, 68, 68, 0.4);
        }

        body { font-family: 'Inter', sans-serif; margin: 0; background-color: var(--bg-body); display: flex; height: 100vh; color: var(--text-main); }
        
        /* Left Panel - Gradient */
        .left-panel { 
            flex: 1; 
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); 
            display: flex; flex-direction: column; justify-content: center; align-items: center; 
            color: white; padding: 40px; text-align: center; 
            position: relative; overflow: hidden;
        }
        /* Subtle Pattern Overlay */
        .left-panel::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background-image: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), 
                              radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);
        }

        .left-panel h1 { font-size: 2.8rem; margin-bottom: 15px; z-index: 1; letter-spacing: -1px; }
        .left-panel p { font-size: 1.1rem; opacity: 0.9; max-width: 450px; line-height: 1.6; z-index: 1; }
        
        /* Right Panel - Login Form */
        .right-panel { flex: 1; display: flex; align-items: center; justify-content: center; padding: 20px; background: var(--bg-body); }
        
        .login-box { 
            width: 100%; max-width: 400px; padding: 40px; 
            background: var(--bg-card); 
            border-radius: 16px; 
            border: 1px solid var(--border);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
        }
        
        .login-header { margin-bottom: 30px; }
        .login-header h2 { font-size: 2rem; color: white; margin: 0 0 8px 0; letter-spacing: -0.5px; }
        .login-header p { color: var(--text-muted); margin: 0; font-size: 0.95rem; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 500; color: var(--text-main); margin-bottom: 8px; font-size: 0.9rem; }
        
        /* Dark Inputs */
        input { 
            width: 100%; padding: 12px; border: 1px solid var(--border); 
            border-radius: 8px; font-size: 1rem; box-sizing: border-box; 
            transition: 0.2s; background: var(--bg-input); color: white;
        }
        input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2); }
        
        /* Button */
        .btn-login { 
            width: 100%; padding: 12px; background: var(--primary); 
            color: white; border: none; border-radius: 8px; 
            font-size: 1rem; font-weight: 600; cursor: pointer; transition: 0.2s; 
        }
        .btn-login:hover { background: var(--primary-hover); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }
        
        .error-banner { 
            background: var(--error-bg); color: var(--error-text); 
            padding: 12px; border-radius: 8px; margin-bottom: 20px; 
            font-size: 0.9rem; text-align: center; border: 1px solid var(--error-border); 
        }
        
        .footer-link { text-align: center; margin-top: 25px; font-size: 0.9rem; color: var(--text-muted); }
        .footer-link a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .footer-link a:hover { color: var(--primary-hover); }
        
        /* Forgot Password Link */
        .forgot-link { display: block; text-align: right; margin-bottom: 25px; font-size: 0.85rem; }
        .forgot-link a { color: var(--primary); text-decoration: none; font-weight: 500; }
        .forgot-link a:hover { text-decoration: underline; color: var(--primary-hover); }

        @media (max-width: 768px) { .left-panel { display: none; } }
    </style>
</head>
<body>

    <div class="left-panel">
        <h1>Welcome Back!</h1>
        <p>Join the community in keeping our campus secure. Report lost items or claim what you found.</p>
    </div>

    <div class="right-panel">
        <div class="login-box">
            <div class="login-header">
                <h2>Sign In</h2>
                <p>Enter your credentials to access your account.</p>
            </div>

            <?php if($error): ?>
                <div class="error-banner"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="student@college.edu" required>
                </div>
                
                <div class="form-group" style="margin-bottom: 5px;">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="forgot-link">
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <div class="footer-link">
                Don't have an account? <a href="register.php">Create Account</a>
            </div>
        </div>
    </div>

</body>
</html>