<?php
/*
    File: report.php
    Purpose: Report an item (Dark Theme + Stylish UI).
*/
include 'db.php';

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Determine Type (Lost or Found) from URL
$type = isset($_GET['type']) ? $_GET['type'] : 'lost';
$page_title = ($type == 'lost') ? "Report Lost Item" : "Report Found Item";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $date_incident = $conn->real_escape_string($_POST['date_incident']); 
    $user_id = $_SESSION['user_id'];
    $type = $_POST['type']; 
    
    // Sensitive Logic
    $is_sensitive = (isset($_POST['is_sensitive']) && $type == 'found') ? 1 : 0;

    // Image Upload Logic
    $image_path = "";
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $target_file = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
        
        if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    // Insert Query
    $sql = "INSERT INTO items (user_id, title, description, date_incident, type, image_path, is_sensitive, status) 
            VALUES ('$user_id', '$title', '$description', '$date_incident', '$type', '$image_path', '$is_sensitive', 'active')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Report submitted successfully!'); window.location.href='index.php';</script>";
        exit();
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Campus Lost & Found</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
            --danger: #ef4444;
            
            /* Warning/Sensitive Colors */
            --warning-bg: rgba(251, 191, 36, 0.1); 
            --warning-border: rgba(251, 191, 36, 0.3);
            --warning-text: #fbbf24;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body); color: var(--text-main);
            margin: 0; padding-bottom: 40px;
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

        /* --- FORM CONTAINER --- */
        .container {
            background: var(--bg-card); width: 100%; max-width: 600px;
            border-radius: 16px; padding: 2.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border);
            margin: 40px auto;
            box-sizing: border-box;
        }

        .form-header { margin-bottom: 2rem; }
        .form-header h2 { margin: 0 0 0.5rem 0; font-size: 1.8rem; color: white; letter-spacing: -0.5px; }
        .form-header p { margin: 0; color: var(--text-muted); font-size: 1rem; }
        
        .back-link {
            display: inline-block; margin-bottom: 1.5rem;
            text-decoration: none; color: var(--text-muted);
            font-size: 0.9rem; font-weight: 600; transition: 0.2s;
        }
        .back-link:hover { color: white; transform: translateX(-3px); }

        .form-group { margin-bottom: 1.5rem; }
        label { display: block; font-weight: 500; font-size: 0.9rem; margin-bottom: 0.6rem; color: var(--text-main); }
        
        /* Dark Inputs */
        input[type="text"], input[type="date"], textarea {
            width: 100%; padding: 0.85rem; 
            border: 1px solid var(--border);
            border-radius: 8px; font-family: inherit; font-size: 0.95rem;
            color: white; background: var(--bg-input);
            transition: 0.2s; box-sizing: border-box;
        }

        input:focus, textarea:focus {
            outline: none; border-color: var(--primary); 
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        
        textarea { resize: vertical; min-height: 120px; }

        /* File Input */
        .file-input-wrapper {
            position: relative; border: 2px dashed var(--border);
            border-radius: 12px; padding: 2rem; text-align: center;
            background: rgba(15, 23, 42, 0.5); transition: 0.2s;
        }
        .file-input-wrapper:hover { border-color: var(--primary); background: rgba(99, 102, 241, 0.05); }
        input[type="file"] { width: 100%; color: var(--text-muted); }

        /* Sensitive Checkbox Style (Dark Mode) */
        .checkbox-group {
            display: flex; align-items: flex-start; gap: 15px;
            background: var(--warning-bg); border: 1px solid var(--warning-border);
            padding: 1.2rem; border-radius: 12px; margin-top: 10px;
        }
        .checkbox-group input { margin-top: 5px; accent-color: #d97706; width: 18px; height: 18px; }
        .checkbox-group small { display: block; color: var(--warning-text); margin-top: 4px; line-height: 1.5; opacity: 0.9; }

        .btn-submit {
            width: 100%; padding: 1rem; background-color: var(--primary);
            color: white; border: none; border-radius: 8px;
            font-size: 1rem; font-weight: 600; cursor: pointer;
            transition: 0.2s; margin-top: 1.5rem;
        }
        .btn-submit:hover { background-color: var(--primary-hover); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }
        
        .error-msg { color: #fca5a5; font-size: 0.9rem; margin-bottom: 1rem; background: rgba(239, 68, 68, 0.2); padding: 12px; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.4); text-align: center; }

        @media (max-width: 768px) {
            .header { padding: 0 1rem; }
            .nav-actions { display: none; }
            .mobile-menu-btn { display: block; }
            .container { padding: 1.5rem; margin: 20px; width: auto; }
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
        <a href="profile.php" class="btn-small btn-outline">Profile</a>
        <a href="my_uploads.php" class="btn-small btn-outline">My Reports</a>
        <a href="my_claims.php" class="btn-small btn-outline">My Claims</a>
        
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
    <a href="profile.php">👤 My Profile</a>
    <a href="my_uploads.php">📢 My Reports</a>
    <a href="my_claims.php">✋ My Claims</a>
    
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
        <a href="admin_dashboard.php" style="color:#fca5a5;">🛡️ Admin Panel</a>
    <?php endif; ?>
    
    <a href="logout.php" style="color:#94a3b8;">🚪 Logout</a>
</div>

<div class="container">
    <a href="index.php" class="back-link">&larr; Back to Dashboard</a>

    <div class="form-header">
        <h2><?php echo $page_title; ?></h2>
        <p>Please provide as many details as possible to help identify the item.</p>
    </div>

    <?php if($message): ?>
        <div class="error-msg"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="type" value="<?php echo $type; ?>">

        <div class="form-group">
            <label>Item Name</label>
            <input type="text" name="title" placeholder="e.g. Blue Dell Laptop" required>
        </div>

        <div class="form-group">
            <label>Date of Incident</label>
            <input type="date" name="date_incident" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" placeholder="Describe color, brand, unique marks, location found..." required></textarea>
        </div>

        <div class="form-group">
            <label>Upload Image (Optional)</label>
            <div class="file-input-wrapper">
                <input type="file" name="image" accept="image/*">
            </div>
        </div>

        <?php if($type == 'found'): ?>
        <div class="form-group checkbox-group">
            <input type="checkbox" name="is_sensitive" id="sens">
            <div>
                <label for="sens" style="margin:0; color:#fbbf24; font-weight:700; cursor:pointer;">Mark as Sensitive / Hidden?</label>
                <small>Check this for IDs, Debit Cards, or Keys. The photo will be hidden from the public feed to prevent misuse.</small>
            </div>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn-submit">Submit Report</button>
    </form>
</div>

</body>
</html>