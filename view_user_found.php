<?php
/* File: view_user_found.php (Dark Theme + Stylish UI) */
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['uid'])) {
    die("Invalid User ID");
}

$target_uid = intval($_GET['uid']);

// 1. Fetch User Info
$user_sql = "SELECT full_name, department, student_id FROM users WHERE id = $target_uid";
$user_res = $conn->query($user_sql);
if ($user_res->num_rows == 0) { die("User not found"); }
$user_info = $user_res->fetch_assoc();

// 2. Fetch Items Found by this User
$item_sql = "SELECT * FROM items WHERE user_id = $target_uid AND type = 'found' ORDER BY created_at DESC";
$items = $conn->query($item_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items Found by <?php echo htmlspecialchars($user_info['full_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* DARK THEME PALETTE */
            --primary: #6366f1; /* Indigo-500 */
            --primary-hover: #818cf8;
            
            --bg-body: #0f172a; /* Slate-900 */
            --bg-card: #1e293b; /* Slate-800 */
            --bg-card-hover: #334155;
            
            --text-main: #f1f5f9; /* Slate-100 */
            --text-muted: #94a3b8; /* Slate-400 */
            
            --border: #334155; /* Slate-700 */
            --success-bg: rgba(16, 185, 129, 0.2); --success-text: #6ee7b7;
            --info-bg: rgba(59, 130, 246, 0.2); --info-text: #93c5fd;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background: var(--bg-body); 
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
        .mobile-dropdown a:hover { background: var(--bg-card-hover); color: var(--primary); }

        .btn-small { padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: 0.2s; white-space: nowrap; }
        .btn-outline { border: 1px solid var(--border); color: var(--text-main); background: transparent; }
        .btn-outline:hover { border-color: var(--primary); color: white; background: rgba(99, 102, 241, 0.1); }
        .btn-admin { background: rgba(220, 38, 38, 0.2); color: #fca5a5; border: 1px solid rgba(220, 38, 38, 0.5); }
        .nav-link { text-decoration: none; color: var(--text-muted); font-size: 0.9rem; font-weight: 500; }
        .nav-link:hover { color: white; }

        /* --- PAGE CONTENT STYLES --- */
        .container { max-width: 800px; margin: 30px auto; padding: 0 20px; }
        
        /* User Profile Card */
        .header-card { 
            background: var(--bg-card); 
            padding: 30px; 
            border-radius: 16px; 
            margin-bottom: 30px; 
            text-align: center; 
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        }
        
        /* Decorative gradient line at top */
        .header-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(to right, #34d399, #10b981);
        }

        .header-card h1 { margin: 0; color: white; font-size: 1.8rem; letter-spacing: -0.5px; }
        .header-card p { color: var(--text-muted); margin: 8px 0 0 0; font-size: 1rem; }
        
        .contribution-badge {
            margin-top: 20px; display: inline-block;
            background: rgba(16, 185, 129, 0.1); color: #34d399;
            padding: 8px 16px; border-radius: 50px;
            font-weight: 700; border: 1px solid rgba(16, 185, 129, 0.2);
        }

        /* Item Grid */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        
        .card { 
            background: var(--bg-card); border-radius: 12px; overflow: hidden; 
            border: 1px solid var(--border); transition: 0.3s;
            display: flex; flex-direction: column;
        }
        .card:hover { 
            transform: translateY(-5px); 
            border-color: var(--primary); 
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.3); 
        }

        .card-img { height: 180px; background: #020617; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; }
        .card-img img { width: 100%; height: 100%; object-fit: cover; opacity: 0.9; transition: 0.3s; }
        .card:hover .card-img img { opacity: 1; transform: scale(1.05); }

        .card-body { padding: 18px; display: flex; flex-direction: column; flex: 1; }
        .card-title { font-weight: 700; margin-bottom: 8px; display: block; color: white; font-size: 1.05rem; }
        .card-desc { font-size: 0.9rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 15px; }
        
        .status-pill { 
            display: inline-block; padding: 5px 10px; border-radius: 8px; font-size: 0.75rem; 
            font-weight: 700; margin-top: auto; text-transform: uppercase; letter-spacing: 0.5px; align-self: flex-start;
        }
        .s-active { background: var(--success-bg); color: var(--success-text); border: 1px solid rgba(16, 185, 129, 0.2); } 
        .s-resolved { background: var(--info-bg); color: var(--info-text); border: 1px solid rgba(59, 130, 246, 0.2); } 

        .back-link { 
            display: inline-flex; align-items: center; margin-bottom: 20px; 
            text-decoration: none; color: var(--text-muted); font-weight: 600; 
            transition: 0.2s; font-size: 0.9rem;
        }
        .back-link:hover { color: white; transform: translateX(-3px); }

        @media (max-width: 768px) { 
            .header { padding: 0 1rem; }
            .nav-actions { display: none; }
            .mobile-menu-btn { display: block; }
            .grid { grid-template-columns: 1fr; }
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
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
        <a href="admin_dashboard.php" style="color:#fca5a5;">🛡️ Admin Panel</a>
    <?php endif; ?>
    <a href="logout.php" style="color:#94a3b8;">🚪 Logout</a>
</div>

<div class="container">
    <a href="leaderboard.php" class="back-link">&larr; Back to Leaderboard</a>

    <div class="header-card">
        <h1><?php echo htmlspecialchars($user_info['full_name']); ?></h1>
        <p><?php echo htmlspecialchars($user_info['department']); ?> • <?php echo htmlspecialchars($user_info['student_id']); ?></p>
        <div class="contribution-badge">
            🌟 Contribution: <?php echo $items->num_rows; ?> Items Found
        </div>
    </div>

    <div class="grid">
        <?php if($items->num_rows > 0): ?>
            <?php while($row = $items->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-img">
                        <?php if($row['image_path']): ?>
                            <img src="<?php echo $row['image_path']; ?>">
                        <?php else: ?>
                            <span style="color:#64748b;">No Image</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <span class="card-title"><?php echo htmlspecialchars($row['title']); ?></span>
                        <div class="card-desc"><?php echo htmlspecialchars($row['description']); ?></div>
                        
                        <?php if($row['status'] == 'resolved'): ?>
                            <span class="status-pill s-resolved">✅ Returned to Owner</span>
                        <?php else: ?>
                            <span class="status-pill s-active">🔍 Currently Active</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column:1/-1; text-align:center; color:var(--text-muted); padding:40px;">
                No items found by this student yet.
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>