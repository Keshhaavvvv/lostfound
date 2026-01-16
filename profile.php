<?php
/* File: profile.php (Dark Theme + Strict Rules) */
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = ""; 

// Fetch current data
$u = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updates = [];
    $errors = []; // To track validation issues

    // 1. Password Update (Strict Validation)
    if (!empty($_POST['password'])) {
        $pwd = $_POST['password'];
        
        if (strlen($pwd) > 15) {
            $errors[] = "Password too long (Max 15 chars).";
        } elseif (!preg_match('/[A-Za-z]/', $pwd) || !preg_match('/[0-9]/', $pwd) || !preg_match('/[\W]/', $pwd)) {
            $errors[] = "Password must have Letters, Numbers & Special Chars.";
        } else {
            $hash = password_hash($pwd, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hash' WHERE id=$user_id");
            $updates[] = "Password";
        }
    }

    // 2. Name Update (Strict 3-Word Validation)
    if (isset($_POST['full_name']) && $u['name_updated'] == 0) {
        $new_name = trim($conn->real_escape_string($_POST['full_name']));
        
        if (!empty($new_name) && $new_name != $u['full_name']) {
            if (str_word_count($new_name) < 3) {
                $errors[] = "Full Name must include First, Middle, and Last Name.";
            } else {
                $conn->query("UPDATE users SET full_name='$new_name', name_updated=1 WHERE id=$user_id");
                $_SESSION['full_name'] = $new_name;
                $updates[] = "Name";
                $u['name_updated'] = 1;
                $u['full_name'] = $new_name;
            }
        }
    }

    // 3. Gender Update (Lock after setting)
    if (isset($_POST['gender']) && empty($u['gender'])) {
        $new_gender = $conn->real_escape_string($_POST['gender']);
        if (!empty($new_gender)) {
            $conn->query("UPDATE users SET gender='$new_gender' WHERE id=$user_id");
            $updates[] = "Gender";
            $u['gender'] = $new_gender;
        }
    }

    // 4. Mobile Update (Lock after setting)
    if (isset($_POST['mobile']) && empty($u['mobile'])) {
        $new_mobile = $conn->real_escape_string($_POST['mobile']);
        if (!empty($new_mobile)) {
            $conn->query("UPDATE users SET mobile='$new_mobile' WHERE id=$user_id");
            $updates[] = "Mobile";
            $u['mobile'] = $new_mobile;
        }
    }

    // 5. Extra Info (Always Editable)
    $dept = $conn->real_escape_string($_POST['department']);
    $year = $conn->real_escape_string($_POST['year_study']);
    $addr = $conn->real_escape_string($_POST['address']);
    
    $conn->query("UPDATE users SET department='$dept', year_study='$year', address='$addr' WHERE id=$user_id");
    
    if($dept != $u['department'] || $year != $u['year_study'] || $addr != $u['address']) {
        $updates[] = "Details";
        $u['department'] = $dept; $u['year_study'] = $year; $u['address'] = $addr;
    }

    // FINAL MESSAGE LOGIC
    if (count($errors) > 0) {
        $msg = implode("<br>", $errors);
        $msg_type = "error";
    } elseif (count($updates) > 0) {
        $msg = "Updated: " . implode(", ", $updates);
        $msg_type = "success";
    } else {
        $msg = "No changes made.";
        $msg_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* DARK THEME PALETTE */
            --primary: #6366f1; /* Indigo-500 */
            --primary-hover: #818cf8;
            
            --bg-body: #0f172a; /* Slate-900 */
            --bg-card: #1e293b; /* Slate-800 */
            --bg-input: #0f172a; /* Darker than card */
            
            --text-main: #f1f5f9; /* Slate-100 */
            --text-muted: #94a3b8; /* Slate-400 */
            
            --border: #334155; /* Slate-700 */
            --success-bg: rgba(16, 185, 129, 0.2); --success-text: #6ee7b7;
            --error-bg: rgba(239, 68, 68, 0.2); --error-text: #fca5a5;
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-body); 
            color: var(--text-main); 
            margin: 0; 
            padding-bottom: 40px; 
            -webkit-font-smoothing: antialiased;
        }

        /* --- HEADER STYLES --- */
        .header { 
            background: rgba(30, 41, 59, 0.85); 
            backdrop-filter: blur(12px); 
            border-bottom: 1px solid var(--border); 
            padding: 0 2rem; 
            height: 70px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            position: sticky; top: 0; z-index: 100; 
        }
        .brand { 
            font-weight: 800; font-size: 1.4rem; color: white; text-decoration: none; letter-spacing: -0.5px;
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .nav-actions { display: flex; align-items: center; gap: 0.8rem; }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; color: var(--text-main); cursor: pointer; }
        
        .mobile-dropdown {
            display: none; position: fixed; top: 70px; left: 0; width: 100%;
            background: var(--bg-card); border-bottom: 1px solid var(--border);
            flex-direction: column; padding: 10px 0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.5); z-index: 99;
        }
        .mobile-dropdown.open { display: flex; }
        .mobile-dropdown a { padding: 15px 25px; text-decoration: none; color: var(--text-main); font-weight: 500; border-bottom: 1px solid var(--border); }
        .mobile-dropdown a:hover { background: var(--border); color: var(--primary); }

        .btn-small { padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: 0.2s; white-space: nowrap; }
        .btn-outline { border: 1px solid var(--border); color: var(--text-main); background: transparent; }
        .btn-outline:hover { border-color: var(--primary); color: white; background: rgba(99, 102, 241, 0.1); }
        .btn-admin { background: rgba(220, 38, 38, 0.2); color: #fca5a5; border: 1px solid rgba(220, 38, 38, 0.5); }
        .nav-link { text-decoration: none; color: var(--text-muted); font-size: 0.9rem; font-weight: 500; }
        .nav-link:hover { color: white; }

        /* --- PROFILE FORM STYLES --- */
        .container { 
            background: var(--bg-card); width: 100%; max-width: 600px; 
            border-radius: 16px; padding: 2.5rem; 
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); 
            border: 1px solid var(--border); margin: 40px auto; 
        }
        
        .page-header { margin-bottom: 2rem; text-align: center; }
        h1 { margin: 0 0 0.5rem 0; font-size: 1.8rem; color: white; letter-spacing: -0.5px; }
        
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; font-weight: 500; font-size: 0.9rem; margin-bottom: 0.6rem; color: var(--text-main); }
        
        /* Dark Inputs */
        input, select, textarea { 
            width: 100%; padding: 0.85rem; 
            border: 1px solid var(--border); 
            border-radius: 8px; font-family: inherit; font-size: 0.95rem; 
            background: var(--bg-input); color: white;
            box-sizing: border-box; transition: 0.2s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none; border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        textarea { resize: vertical; min-height: 80px; }
        
        /* Locked Fields */
        .input-locked-wrapper { position: relative; }
        input:disabled, select:disabled { 
            background: rgba(30, 41, 59, 0.5); /* Semi transparent */
            color: var(--text-muted); 
            cursor: not-allowed; 
            border-color: rgba(51, 65, 85, 0.5);
        }
        .lock-icon { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); opacity: 0.7; }

        .btn-save { 
            width: 100%; padding: 1rem; background-color: var(--primary); 
            color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; 
            transition: 0.2s; font-size: 1rem; margin-top: 10px;
        }
        .btn-save:hover { background-color: var(--primary-hover); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }
        
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 25px; text-align: center; font-size: 0.9rem; font-weight: 500; }
        .success { background: var(--success-bg); color: var(--success-text); border: 1px solid rgba(16, 185, 129, 0.2); }
        .error { background: var(--error-bg); color: var(--error-text); border: 1px solid rgba(239, 68, 68, 0.2); }
        
        .hint { font-size: 0.8rem; color: var(--text-muted); margin-top: 6px; display: block; }
        
        @media (max-width: 768px) { 
            .header { padding: 0 1rem; }
            .nav-actions { display: none; }
            .mobile-menu-btn { display: block; }
            .container { padding: 1.5rem; margin: 20px; width: auto; }
            .form-row { grid-template-columns: 1fr; gap: 0; }
        }
    </style>
    <script>
        function toggleMenu() {
            document.getElementById('mobileDropdown').classList.toggle('open');
        }
    </script>
</head>
<body>

<header class="header">
    <a href="index.php" class="brand">Lost&Found</a>
    
    <nav class="nav-actions">
        <a href="index.php" class="btn-small btn-outline">Home</a>
        <a href="leaderboard.php" class="btn-small btn-outline">🏆 Leaderboard</a>
        
        <a href="profile.php" class="btn-small btn-outline" style="border-color:var(--primary); color:white; background:rgba(99, 102, 241, 0.1);">Profile</a>
        
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <a href="admin_dashboard.php" class="btn-small btn-admin">Admin Panel</a>
        <?php endif; ?>
        
        <a href="logout.php" class="nav-link">Logout</a>
    </nav>

    <button class="mobile-menu-btn" onclick="toggleMenu()">☰</button>
</header>

<div id="mobileDropdown" class="mobile-dropdown">
    <a href="index.php">🏠 Home</a>
    <a href="leaderboard.php">🏆 Leaderboard</a>
    <a href="profile.php" style="color:var(--primary);">👤 My Profile</a>
    
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
        <a href="admin_dashboard.php" style="color:#fca5a5;">🛡️ Admin Panel</a>
    <?php endif; ?>
    
    <a href="logout.php" style="color:#94a3b8;">🚪 Logout</a>
</div>

<div class="container">
    <div class="page-header">
        <h1>Student Profile</h1>
        <p style="color:var(--text-muted); margin:0;">Keep your details updated for verification.</p>
    </div>

    <?php if($msg): ?><div class="alert <?php echo $msg_type; ?>"><?php echo $msg; ?></div><?php endif; ?>

    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Full Name</label>
                <?php if($u['name_updated'] == 0): ?>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($u['full_name']); ?>" placeholder="First Middle Last" required>
                <?php else: ?>
                    <div class="input-locked-wrapper"><input type="text" value="<?php echo htmlspecialchars($u['full_name']); ?>" disabled><span class="lock-icon">🔒</span></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Student ID</label>
                <div class="input-locked-wrapper"><input type="text" value="<?php echo htmlspecialchars($u['student_id']); ?>" disabled><span class="lock-icon">🔒</span></div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Email</label>
                <div class="input-locked-wrapper"><input type="text" value="<?php echo htmlspecialchars($u['email']); ?>" disabled><span class="lock-icon">🔒</span></div>
            </div>
            <div class="form-group">
                <label>Mobile Number</label>
                <?php if(empty($u['mobile'])): ?>
                    <input type="tel" name="mobile" placeholder="Required" pattern="[0-9]{10}" required>
                <?php else: ?>
                    <div class="input-locked-wrapper"><input type="tel" value="<?php echo htmlspecialchars($u['mobile']); ?>" disabled><span class="lock-icon">🔒</span></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Gender</label>
                <?php if(empty($u['gender'])): ?>
                    <select name="gender" required>
                        <option value="">Select</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                <?php else: ?>
                    <div class="input-locked-wrapper">
                        <input type="text" value="<?php echo htmlspecialchars($u['gender']); ?>" disabled>
                        <span class="lock-icon">🔒</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Department</label>
                <select name="department">
                    <option value="">Select Dept</option>
                    <option value="Computer Science" <?php if($u['department']=='Computer Science') echo 'selected'; ?>>Computer Science</option>
                    <option value="Information Tech" <?php if($u['department']=='Information Tech') echo 'selected'; ?>>Information Tech</option>
                    <option value="Electronics" <?php if($u['department']=='Electronics') echo 'selected'; ?>>Electronics</option>
                    <option value="Civil" <?php if($u['department']=='Civil') echo 'selected'; ?>>Civil</option>
                    <option value="Mechanical" <?php if($u['department']=='Mechanical') echo 'selected'; ?>>Mechanical</option>
                    <option value="Biotech" <?php if($u['department']=='Biotech') echo 'selected'; ?>>Biotech</option>
                    <option value="Other" <?php if($u['department']=='Other') echo 'selected'; ?>>Other</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Year of Study</label>
                <select name="year_study">
                    <option value="">Select Year</option>
                    <option value="1st Year" <?php if($u['year_study']=='1st Year') echo 'selected'; ?>>1st Year</option>
                    <option value="2nd Year" <?php if($u['year_study']=='2nd Year') echo 'selected'; ?>>2nd Year</option>
                    <option value="3rd Year" <?php if($u['year_study']=='3rd Year') echo 'selected'; ?>>3rd Year</option>
                    <option value="4th Year" <?php if($u['year_study']=='4th Year') echo 'selected'; ?>>4th Year</option>
                </select>
            </div>
            <div class="form-group">
                <label>Address / Hostel</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($u['address']); ?>" placeholder="Room No, Hostel/Flat">
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--border); margin: 10px 0 25px 0;">

        <div class="form-group">
            <label>New Password (Optional)</label>
            <input type="password" name="password" placeholder="••••••••" maxlength="15">
            <span class="hint">Strict: Letters, Numbers, Special Chars. Max 15 chars.</span>
        </div>

        <button type="submit" class="btn-save">Save Profile Details</button>
    </form>
</div>

</body>
</html>