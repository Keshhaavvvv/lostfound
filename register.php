<?php
/* File: register.php (Dark Theme + Real Email OTP) */
include 'db.php';
include 'mail_config.php'; // Include the mailer configuration

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($conn->real_escape_string($_POST['full_name']));
    $student_id = $conn->real_escape_string($_POST['student_id']);
    $gender = $conn->real_escape_string($_POST['gender']); 
    $email = $conn->real_escape_string($_POST['email']);
    $mobile = $conn->real_escape_string($_POST['mobile']); 
    $password_raw = $_POST['password'];

    // 1. VALIDATION: Name Format (First Middle Last)
    if (str_word_count($full_name) < 3) {
        $error = "Please enter your Full Name (First Name + Middle Name + Last Name).";
    }
    
    // 2. VALIDATION: Gender
    elseif (empty($gender)) {
        $error = "Please select your gender.";
    }

    // 3. VALIDATION: Password Complexity & Length
    elseif (strlen($password_raw) > 15) {
        $error = "Password is too long. Maximum 15 characters allowed.";
    }
    elseif (!preg_match('/[A-Za-z]/', $password_raw) || 
            !preg_match('/[0-9]/', $password_raw) || 
            !preg_match('/[\W]/', $password_raw)) {
        $error = "Password must include Letters, Numbers, and a Special Character (@, #, etc).";
    }

    // 4. Check if Email Exists
    elseif ($conn->query("SELECT id FROM users WHERE email='$email'")->num_rows > 0) {
        $error = "An account with this email already exists.";
    } 
    
    // 5. Check if Mobile Exists
    elseif ($conn->query("SELECT id FROM users WHERE mobile='$mobile'")->num_rows > 0) {
        $error = "This mobile number is already registered.";
    } 
    
    else {
        // Validation Passed
        $password = password_hash($password_raw, PASSWORD_DEFAULT);
        $otp = rand(100000, 999999);
        
        // Store Data in Session
        $_SESSION['temp_user'] = [
            'full_name' => $full_name,
            'student_id' => $student_id,
            'gender' => $gender,
            'email' => $email,
            'mobile' => $mobile,
            'password' => $password,
            'otp' => $otp
        ];

        // --- SEND REAL EMAIL ---
        $subject = "Verify Your Account - Campus Lost & Found";
        $message = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f3f4f6;'>
                <div style='background-color: white; padding: 30px; border-radius: 10px; max-width: 500px; margin: 0 auto;'>
                    <h2 style='color: #4f46e5; margin-top: 0;'>Welcome, $full_name!</h2>
                    <p style='color: #374151; font-size: 16px;'>You are one step away from joining the Campus Lost & Found community.</p>
                    <p style='color: #6b7280;'>Here is your verification code:</p>
                    <div style='background-color: #e0e7ff; color: #4338ca; font-size: 24px; font-weight: bold; text-align: center; padding: 15px; border-radius: 8px; letter-spacing: 5px; margin: 20px 0;'>
                        $otp
                    </div>
                    <p style='font-size: 12px; color: #9ca3af; text-align: center;'>This code is valid for 15 minutes. Do not share it with anyone.</p>
                </div>
            </div>
        ";

        if (sendEmail($email, $subject, $message)) {
            echo "<script>
                    alert('✅ Verification code sent to $email. Please check your inbox. Please check your spam section for the otp, the Email might be Flagged as Harmful!');
                    window.location.href = 'verify_otp.php';
                  </script>";
            exit();
        } else {
            $error = "⚠️ Could not send email. Please check your internet connection or try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
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
            --warning-bg: rgba(251, 191, 36, 0.1); --warning-text: #fbbf24; --warning-border: rgba(251, 191, 36, 0.3);
        }

        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            background-color: var(--bg-body); 
            display: flex; 
            height: 100vh; 
            color: var(--text-main);
        }

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

        /* Right Panel - Form */
        .right-panel { 
            flex: 1; 
            display: flex; align-items: center; justify-content: center; 
            padding: 20px; background: var(--bg-body); 
            overflow-y: auto; 
        }
        
        .auth-box { 
            width: 100%; max-width: 450px; padding: 40px; 
            background: var(--bg-card); 
            border-radius: 16px; 
            border: 1px solid var(--border);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
            margin: 20px 0;
        }
        
        .auth-header h2 { font-size: 2rem; color: white; margin: 0 0 8px 0; letter-spacing: -0.5px; }
        .auth-header p { color: var(--text-muted); margin: 0 0 30px 0; font-size: 0.95rem; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: 500; color: var(--text-main); margin-bottom: 8px; font-size: 0.9rem; }
        
        /* Dark Inputs */
        input, select { 
            width: 100%; padding: 12px; border: 1px solid var(--border); 
            border-radius: 8px; font-size: 1rem; box-sizing: border-box; 
            transition: 0.2s; background: var(--bg-input); color: white;
        }
        input:focus, select:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2); }
        
        /* Button */
        .btn-primary { 
            width: 100%; padding: 12px; background: var(--primary); 
            color: white; border: none; border-radius: 8px; 
            font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 15px; 
            transition: 0.2s;
        }
        .btn-primary:hover { background: var(--primary-hover); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }
        
        .error-banner { 
            background: var(--error-bg); color: var(--error-text); 
            padding: 12px; border-radius: 8px; margin-bottom: 20px; 
            font-size: 0.9rem; text-align: center; border: 1px solid var(--error-border); 
        }
        
        .footer-link { text-align: center; margin-top: 25px; font-size: 0.9rem; color: var(--text-muted); }
        .footer-link a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .footer-link a:hover { color: var(--primary-hover); }
        
        .hint-text { font-size: 0.75rem; color: var(--text-muted); margin-top: 6px; display: block; }
        
        .strict-note { 
            font-size: 0.8rem; color: var(--warning-text); 
            background: var(--warning-bg); padding: 10px; 
            border-radius: 6px; border: 1px solid var(--warning-border); 
            margin-top: 8px; line-height: 1.4; 
        }

        @media (max-width: 768px) { .left-panel { display: none; } .auth-box { padding: 25px; margin: 0; border: none; box-shadow: none; background: transparent; } }
    </style>
</head>
<body>

    <div class="left-panel">
        <h1>Join the Community</h1>
        <p>Create an account to report lost items, help others find their belongings, and make our campus a better place.</p>
    </div>

    <div class="right-panel">
        <div class="auth-box">
            <div class="auth-header">
                <h2>Create Account</h2>
                <p>It only takes a minute to get started.</p>
            </div>

            <?php if($error): ?>
                <div class="error-banner"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="First Middle Last" required>
                    <span class="hint-text">Format: First Name + Middle Name + Last Name</span>
                </div>

                <div class="form-group">
                    <label>Student ID (or Staff ID)</label>
                    <input type="text" name="student_id" placeholder="e.g. 2023CS001" required>
                </div>

                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Mobile Number</label>
                    <input type="tel" name="mobile" placeholder="e.g. 9876543210" pattern="[0-9]{10}" title="10 digit mobile number" required>
                    <div class="strict-note">
                        ⚠️ <b>Important:</b> If you provide an invalid or fake number, the Admin will mark your profile as suspicious.
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="student@college.edu" required>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Mix of chars, #, and @" maxlength="15" required>
                    <span class="hint-text">Requirements: Letters, Numbers, Special Char. Max 15 chars.</span>
                </div>

                <button type="submit" class="btn-primary">Verify & Register</button>
            </form>

            <div class="footer-link">
                Already have an account? <a href="login.php">Log In</a>
            </div>
        </div>
    </div>

</body>
</html>