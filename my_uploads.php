<?php
/*
    File: my_uploads.php
    Purpose: Manage items I reported (Dark Theme + Stylish UI).
*/
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle Delete
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    // Security: Ensure this item belongs to the logged-in user!
    $conn->query("DELETE FROM items WHERE id=$del_id AND user_id=$user_id");
    header("Location: my_uploads.php");
    exit();
}

// Fetch My Items
$sql = "SELECT * FROM items WHERE user_id = $user_id ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports - Campus Lost & Found</title>
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
            
            --tag-lost-bg: rgba(239, 68, 68, 0.2); --tag-lost-text: #fca5a5;
            --tag-found-bg: rgba(16, 185, 129, 0.2); --tag-found-text: #6ee7b7;
            --danger-bg: rgba(239, 68, 68, 0.1); --danger-text: #fca5a5; --danger-border: rgba(239, 68, 68, 0.5);
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
        .container { max-width: 800px; margin: 30px auto; padding: 0 20px; }

        .page-header { margin-bottom: 2rem; }
        .page-title { margin: 0; font-size: 1.8rem; font-weight: 700; color: white; letter-spacing: -0.5px; }

        .card {
            background: var(--bg-card); 
            border: 1px solid var(--border); 
            border-radius: 16px; 
            padding: 1.5rem; 
            margin-bottom: 1rem;
            display: flex; align-items: center; gap: 1.5rem; 
            transition: 0.2s;
        }
        .card:hover { 
            transform: translateY(-3px); 
            border-color: var(--primary);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.3);
        }

        .thumb { 
            width: 70px; height: 70px; border-radius: 12px; object-fit: cover; 
            background: #020617; border: 1px solid var(--border); 
        }
        .info { flex: 1; }
        .item-title { margin: 0 0 6px 0; font-size: 1.1rem; font-weight: 600; color: white; }
        .meta { font-size: 0.85rem; color: var(--text-muted); }

        .tag { 
            font-size: 0.7rem; padding: 3px 8px; border-radius: 6px; font-weight: 700; 
            text-transform: uppercase; margin-right: 10px; letter-spacing: 0.5px;
        }
        .tag-lost { background: var(--tag-lost-bg); color: var(--tag-lost-text); border: 1px solid rgba(239, 68, 68, 0.2); }
        .tag-found { background: var(--tag-found-bg); color: var(--tag-found-text); border: 1px solid rgba(16, 185, 129, 0.2); }

        .btn-del {
            padding: 8px 16px; border-radius: 8px; font-size: 0.9rem; font-weight: 600;
            background: var(--danger-bg); color: var(--danger-text); border: 1px solid var(--danger-border);
            text-decoration: none; transition: 0.2s;
        }
        .btn-del:hover { background: rgba(220, 38, 38, 0.3); }

        @media (max-width: 768px) {
            .header { padding: 0 1rem; }
            .nav-actions { display: none; }
            .mobile-menu-btn { display: block; }
            .card { flex-direction: column; align-items: flex-start; }
            .thumb { width: 100%; height: 150px; }
            .btn-del { width: 100%; text-align: center; margin-top: 10px; }
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
        
        <a href="my_uploads.php" class="btn-small btn-outline" style="border-color:var(--primary); color:white; background:rgba(99, 102, 241, 0.1);">My Reports</a>
        
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
    <a href="my_uploads.php" style="color:var(--primary);">📢 My Reports</a>
    <a href="my_claims.php">✋ My Claims</a>
    
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
        <a href="admin_dashboard.php" style="color:#fca5a5;">🛡️ Admin Panel</a>
    <?php endif; ?>
    
    <a href="logout.php" style="color:#94a3b8;">🚪 Logout</a>
</div>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">My Reports</h1>
        <p style="color:var(--text-muted); margin:5px 0 0 0;">Items you have posted as Lost or Found.</p>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="card">
                <?php if($row['image_path']): ?>
                    <img src="<?php echo $row['image_path']; ?>" class="thumb">
                <?php else: ?>
                    <div class="thumb" style="display:flex; align-items:center; justify-content:center; color:#64748b; font-size:0.8rem;">No Img</div>
                <?php endif; ?>

                <div class="info">
                    <h3 class="item-title">
                        <span class="tag tag-<?php echo $row['type']; ?>"><?php echo strtoupper($row['type']); ?></span>
                        <?php echo htmlspecialchars($row['title']); ?>
                    </h3>
                    <p class="meta">Posted on <?php echo date("d M Y", strtotime($row['created_at'])); ?> • Status: <?php echo ucfirst($row['status']); ?></p>
                </div>

                <a href="?delete_id=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('Delete this report? This cannot be undone.');">
                    🗑 Delete
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 60px; color: var(--text-muted); background:var(--bg-card); border-radius:16px; border:1px solid var(--border);">
            <div style="font-size: 3rem; margin-bottom: 10px;">📝</div>
            <p>You haven't reported any items yet.</p>
            <br>
            <a href="index.php" class="btn-small btn-outline">Report an Item</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>