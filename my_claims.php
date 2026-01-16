<?php
/*
    File: my_claims.php
    Purpose: Student Claim History (Dark Theme + Stylish UI).
*/
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch claims made by THIS user
$sql = "SELECT claims.id as claim_id, claims.status as claim_status, claims.created_at,
               items.title, items.image_path, items.is_sensitive
        FROM claims
        JOIN items ON claims.item_id = items.id
        WHERE claims.claimant_id = $user_id
        ORDER BY claims.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Claims - Campus Lost & Found</title>
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
            
            /* Status Colors (Dark Mode Friendly) */
            --status-pending-bg: rgba(251, 191, 36, 0.1); --status-pending-text: #fbbf24;
            --status-approved-bg: rgba(16, 185, 129, 0.1); --status-approved-text: #34d399;
            --status-rejected-bg: rgba(239, 68, 68, 0.1); --status-rejected-text: #fca5a5;
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
        .mobile-dropdown a:hover { background: var(--bg-card-hover); color: var(--primary); }

        .btn-small { padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: 0.2s; white-space: nowrap; }
        .btn-outline { border: 1px solid var(--border); color: var(--text-main); background: transparent; }
        .btn-outline:hover { border-color: var(--primary); color: white; background: rgba(99, 102, 241, 0.1); }
        .btn-admin { background: rgba(220, 38, 38, 0.2); color: #fca5a5; border: 1px solid rgba(220, 38, 38, 0.5); }
        .nav-link { text-decoration: none; color: var(--text-muted); font-size: 0.9rem; font-weight: 500; }
        .nav-link:hover { color: white; }

        /* --- PAGE CONTENT --- */
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-header { margin-bottom: 2rem; }
        .page-title { margin: 0; font-size: 1.8rem; font-weight: 700; color: white; letter-spacing: -0.5px; }
        
        /* Claim Cards */
        .claim-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: 0.2s;
        }
        .claim-card:hover {
            transform: translateY(-3px);
            border-color: var(--primary);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.3);
        }

        /* Image Thumbnail */
        .thumb-box {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            overflow: hidden;
            flex-shrink: 0;
            background: #020617;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
        }
        .thumb-box img { width: 100%; height: 100%; object-fit: cover; opacity: 0.9; }
        .thumb-placeholder { color: var(--text-muted); font-size: 0.75rem; text-align: center; }
        .thumb-locked { background: #0f172a; color: var(--text-muted); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }

        /* Content */
        .info { flex: 1; }
        .item-title { margin: 0 0 5px 0; font-size: 1.1rem; font-weight: 600; color: white; }
        .claim-date { margin: 0; color: var(--text-muted); font-size: 0.85rem; }

        /* Status Badges */
        .status-badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            border: 1px solid transparent;
        }
        .status-pending { background: var(--status-pending-bg); color: var(--status-pending-text); border-color: rgba(251, 191, 36, 0.2); }
        .status-approved { background: var(--status-approved-bg); color: var(--status-approved-text); border-color: rgba(16, 185, 129, 0.2); }
        .status-rejected { background: var(--status-rejected-bg); color: var(--status-rejected-text); border-color: rgba(239, 68, 68, 0.2); }

        /* Button */
        .btn-chat {
            text-decoration: none;
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-hover);
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.2s;
            border: 1px solid var(--border);
            white-space: nowrap;
        }
        .btn-chat:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header { padding: 0 1rem; }
            .nav-actions { display: none; }
            .mobile-menu-btn { display: block; }
            
            .claim-card { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .thumb-box { width: 100%; height: 150px; }
            .status-badge { display: inline-block; margin-bottom: 10px; }
            .btn-chat { width: 100%; text-align: center; display: block; box-sizing: border-box; }
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
        
        <a href="my_claims.php" class="btn-small btn-outline" style="border-color:var(--primary); color:white; background:rgba(99, 102, 241, 0.1);">My Claims</a>
        
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
    <a href="my_claims.php" style="color:var(--primary);">✋ My Claims</a>
    
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
        <a href="admin_dashboard.php" style="color:#fca5a5;">🛡️ Admin Panel</a>
    <?php endif; ?>
    
    <a href="logout.php" style="color:#94a3b8;">🚪 Logout</a>
</div>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">My Claims</h1>
        <p style="color:var(--text-muted); margin:5px 0 0 0;">Track the status of items you are trying to claim.</p>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="claim-card">
                <div class="thumb-box">
                    <?php if($row['is_sensitive']): ?>
                        <div class="thumb-locked">🔒</div>
                    <?php elseif($row['image_path']): ?>
                        <img src="<?php echo $row['image_path']; ?>" alt="Item">
                    <?php else: ?>
                        <div class="thumb-placeholder">No Image</div>
                    <?php endif; ?>
                </div>

                <div class="info">
                    <h3 class="item-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p class="claim-date">Claimed on: <?php echo date("d M Y", strtotime($row['created_at'])); ?></p>
                    
                    <div style="margin-top:8px;">
                        <span class="status-badge status-<?php echo $row['claim_status']; ?>">
                            <?php echo ucfirst($row['claim_status']); ?>
                        </span>
                    </div>
                </div>

                <a href="claim_chat.php?claim_id=<?php echo $row['claim_id']; ?>" class="btn-chat">
                    Chat & Status &rarr;
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 60px; color: var(--text-muted); background:var(--bg-card); border-radius:16px; border:1px solid var(--border);">
            <div style="font-size: 3rem; margin-bottom: 10px;">📂</div>
            <p>You haven't claimed any items yet.</p>
            <br>
            <a href="index.php" class="btn-small btn-outline">Browse Found Items</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>