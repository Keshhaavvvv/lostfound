<?php
/*
    File: index.php
    Purpose: Main Dashboard (Dark Theme + Stylish UI).
*/
include 'db.php';

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Search Logic
$search = "";
$search_sql = "";
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $search_sql = "AND (title LIKE '%$search%' OR description LIKE '%$search%')";
}

// Fetch Items
$sql_lost = "SELECT * FROM items WHERE type='lost' AND status='active' $search_sql ORDER BY created_at DESC";
$result_lost = $conn->query($sql_lost);

$sql_found = "SELECT * FROM items WHERE type='found' AND status='active' $search_sql ORDER BY created_at DESC";
$result_found = $conn->query($sql_found);

// Get first name
$first_name = explode(' ', $_SESSION['full_name'])[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Campus Lost & Found</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* DARK THEME PALETTE */
            --primary: #6366f1; /* Indigo-500 */
            --primary-hover: #818cf8;
            
            --bg-body: #0f172a; /* Slate-900 (Deep Dark Blue) */
            --bg-card: #1e293b; /* Slate-800 */
            --bg-card-hover: #334155;
            
            --text-main: #f1f5f9; /* Slate-100 */
            --text-muted: #94a3b8; /* Slate-400 */
            
            --border: #334155; /* Slate-700 */
            
            --tag-lost-bg: rgba(239, 68, 68, 0.2); --tag-lost-text: #fca5a5;
            --tag-found-bg: rgba(16, 185, 129, 0.2); --tag-found-text: #6ee7b7;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-body); 
            color: var(--text-main); 
            margin: 0; 
            padding-bottom: 60px; 
            -webkit-font-smoothing: antialiased;
        }

        /* --- HEADER & NAV --- */
        .header { 
            background: rgba(30, 41, 59, 0.85); /* Semi-transparent */
            backdrop-filter: blur(12px); 
            border-bottom: 1px solid var(--border); 
            padding: 0 2rem; 
            height: 70px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            position: sticky; 
            top: 0; 
            z-index: 100; 
        }
        .brand { 
            font-weight: 800; 
            font-size: 1.4rem; 
            color: white; 
            text-decoration: none; 
            letter-spacing: -0.5px;
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        /* Desktop Nav */
        .nav-actions { display: flex; align-items: center; gap: 0.8rem; }
        
        /* Mobile Menu */
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; color: var(--text-main); cursor: pointer; }
        
        .mobile-dropdown {
            display: none; position: fixed; top: 70px; left: 0; width: 100%;
            background: var(--bg-card); border-bottom: 1px solid var(--border);
            flex-direction: column; padding: 10px 0; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
            z-index: 99;
        }
        .mobile-dropdown.open { display: flex; }
        .mobile-dropdown a {
            padding: 15px 25px; text-decoration: none; color: var(--text-main);
            font-weight: 500; border-bottom: 1px solid var(--border);
        }
        .mobile-dropdown a:last-child { border-bottom: none; }
        .mobile-dropdown a:hover { background: var(--bg-card-hover); color: var(--primary); }

        /* Buttons */
        .btn-small { 
            padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; 
            text-decoration: none; transition: all 0.2s; white-space: nowrap; 
        }
        .btn-outline { 
            border: 1px solid var(--border); color: var(--text-main); background: transparent; 
        }
        .btn-outline:hover { 
            border-color: var(--primary); color: white; background: rgba(99, 102, 241, 0.1); 
        }
        .btn-admin { background: rgba(220, 38, 38, 0.2); color: #fca5a5; border: 1px solid rgba(220, 38, 38, 0.5); }
        .btn-admin:hover { background: rgba(220, 38, 38, 0.3); }

        .nav-link { text-decoration: none; color: var(--text-muted); font-size: 0.9rem; font-weight: 500; transition: 0.2s; }
        .nav-link:hover { color: white; }

        /* --- HERO SECTION (Stylish) --- */
        .hero { 
            max-width: 800px; margin: 3rem auto; padding: 0 1.5rem; text-align: center; 
            position: relative;
        }
        /* Glow Effect behind text */
        .hero::before {
            content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 300px; height: 300px; background: var(--primary); opacity: 0.15; filter: blur(80px); z-index: -1;
            border-radius: 50%;
        }

        .hero h2 { font-size: 2.2rem; margin-bottom: 0.5rem; color: white; letter-spacing: -1px; }
        .hero p { color: var(--text-muted); font-size: 1.1rem; margin-bottom: 2.5rem; }

        /* Search Bar */
        .search-box { position: relative; max-width: 500px; margin: 0 auto 2.5rem auto; }
        .search-input {
            width: 100%; padding: 18px 25px; padding-right: 60px;
            border-radius: 50px; border: 1px solid var(--border);
            background: var(--bg-card); color: white;
            font-size: 1rem; outline: none; transition: 0.3s; box-sizing: border-box;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }
        .search-input:focus { 
            border-color: var(--primary); 
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2); 
        }
        .search-btn {
            position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
            background: var(--primary); color: white; border: none;
            width: 42px; height: 42px; border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
            transition: 0.2s;
        }
        .search-btn:hover { background: var(--primary-hover); transform: translateY(-50%) scale(1.05); }

        /* --- ACTION GRID (Stylish Cards) --- */
        .action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; max-width: 600px; margin: 0 auto 3rem auto; }
        .action-card {
            padding: 1.8rem; border-radius: 16px;
            text-decoration: none; color: white;
            transition: all 0.3s ease; display: flex; flex-direction: column; align-items: center;
            border: 1px solid var(--border);
            position: relative; overflow: hidden;
            background: var(--bg-card);
        }
        
        /* Lost Button Style */
        .action-card:first-child:hover { border-color: #f87171; background: rgba(239, 68, 68, 0.1); box-shadow: 0 0 20px rgba(239, 68, 68, 0.2); }
        /* Found Button Style */
        .action-card:last-child:hover { border-color: #34d399; background: rgba(16, 185, 129, 0.1); box-shadow: 0 0 20px rgba(16, 185, 129, 0.2); }

        .action-card:hover { transform: translateY(-5px); }
        .icon-large { font-size: 2.2rem; margin-bottom: 12px; }
        .act-title { font-weight: 700; font-size: 1.1rem; }

        /* --- STICKY TABS --- */
        .sticky-tabs-wrapper {
            position: sticky; top: 80px; z-index: 30; /* Below Header */
            padding: 0 1rem; margin-bottom: 1.5rem; display: flex; justify-content: center;
        }
        .tab-container { 
            display: flex; background: rgba(30, 41, 59, 0.9); 
            backdrop-filter: blur(10px);
            border: 1px solid var(--border);
            border-radius: 50px; padding: 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            width: 100%; max-width: 400px;
        }
        .tab { 
            padding: 10px 0; cursor: pointer; font-weight: 600; color: var(--text-muted); 
            border-radius: 40px; transition: 0.3s; flex: 1; text-align: center; font-size: 0.95rem;
        }
        .tab:hover { color: white; }
        .tab.active { 
            color: white; background: var(--primary); 
            box-shadow: 0 2px 10px rgba(99, 102, 241, 0.4);
        }

        /* --- ITEM GRID --- */
        .container { max-width: 1100px; margin: 0 auto; padding: 0 1.5rem; }
        .item-grid { display: none; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
        .item-grid.active { display: grid; }

        .card { 
            background: var(--bg-card); border: 1px solid var(--border); 
            border-radius: 16px; overflow: hidden; transition: 0.3s; 
            display: flex; flex-direction: column; 
        }
        .card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5); 
            border-color: var(--primary);
        }
        
        .card-img-wrapper { height: 200px; background: #020617; position: relative; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .card-img-wrapper img { width: 100%; height: 100%; object-fit: cover; opacity: 0.9; transition: 0.3s; }
        .card:hover .card-img-wrapper img { opacity: 1; transform: scale(1.05); }
        
        .sensitive-overlay { width: 100%; height: 100%; background: rgba(15, 23, 42, 0.95); display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text-muted); text-align: center; }
        
        .card-body { padding: 1.25rem; flex: 1; display: flex; flex-direction: column; }
        .card-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.8rem; }
        .card-title { font-size: 1.1rem; font-weight: 700; margin: 0; color: white; line-height: 1.4; }
        
        .tag { font-size: 0.7rem; padding: 4px 10px; border-radius: 6px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .tag-lost { background: var(--tag-lost-bg); color: var(--tag-lost-text); border: 1px solid rgba(239, 68, 68, 0.2); }
        .tag-found { background: var(--tag-found-bg); color: var(--tag-found-text); border: 1px solid rgba(16, 185, 129, 0.2); }
        
        .card-desc { font-size: 0.9rem; color: var(--text-muted); margin: 0 0 1.2rem 0; line-height: 1.6; flex-grow: 1; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        
        .card-footer { margin-top: auto; border-top: 1px solid var(--border); padding-top: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .btn-claim { 
            background: var(--primary); color: white; padding: 8px 18px; border-radius: 8px; 
            text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: 0.2s; 
        }
        .btn-claim:hover { background: var(--primary-hover); box-shadow: 0 0 15px rgba(99, 102, 241, 0.4); }

        /* --- MOBILE RESPONSIVE --- */
        @media (max-width: 768px) { 
            .header { padding: 0 1rem; } 
            .nav-actions { display: none; } 
            .mobile-menu-btn { display: block; } 
            
            .hero { margin: 1.5rem auto; padding: 0 1rem; }
            .hero h2 { font-size: 1.8rem; }
            
            .action-grid { grid-template-columns: 1fr; gap: 15px; margin-bottom: 2rem; }
            .action-card { padding: 1.2rem; flex-direction: row; justify-content: center; gap: 20px; }
            .icon-large { font-size: 1.5rem; margin: 0; }
            
            .sticky-tabs-wrapper { top: 70px; margin-bottom: 20px; } 
            
            .container { padding: 0 1rem; }
            .item-grid { grid-template-columns: 1fr; }
            .card-img-wrapper { height: 220px; }
        }
    </style>
    
    <script>
        function openTab(tabName) {
            document.querySelectorAll('.item-grid').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
            document.getElementById('grid-' + tabName).classList.add('active');
            document.getElementById('tab-' + tabName).classList.add('active');
        }

        function toggleMenu() {
            document.getElementById('mobileDropdown').classList.toggle('open');
        }
    </script>
</head>
<body>

    <header class="header">
        <a href="index.php" class="brand">𝐋𝐨𝐬𝐭&𝐅𝐨𝐮𝐧𝐝</a>
        
        <nav class="nav-actions">
            <a href="leaderboard.php" class="btn-small btn-outline">🏆 Leaderboard</a>
            
            <a href="profile.php" class="btn-small btn-outline">Profile</a>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin_dashboard.php" class="btn-small btn-admin">Admin Panel</a>
            <?php endif; ?>
            <a href="my_uploads.php" class="btn-small btn-outline">My Reports</a>
            <a href="my_claims.php" class="btn-small btn-outline">My Claims</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </nav>

        <button class="mobile-menu-btn" onclick="toggleMenu()">☰</button>
    </header>

    <div id="mobileDropdown" class="mobile-dropdown">
        <a href="profile.php">👤 My Profile</a>
        
        <a href="leaderboard.php">🏆 Leaderboard</a>
        
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <a href="admin_dashboard.php" style="color:#fca5a5;">🛡️ Admin Panel</a>
        <?php endif; ?>
        <a href="my_uploads.php">📢 My Reports</a>
        <a href="my_claims.php">✋ My Claims</a>
        <a href="logout.php" style="color:#94a3b8;">🚪 Logout</a>
    </div>

    <div class="hero">
        <h2>Hello, <?php echo htmlspecialchars($first_name); ?> 👋</h2>
        <p>Find what you lost, or help others find theirs.</p>
        
        <form action="" method="GET" class="search-box">
            <input type="text" name="search" class="search-input" placeholder="Search for items..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="search-btn">🔍</button>
        </form>

        <div class="action-grid">
            <a href="report.php?type=lost" class="action-card">
                <div class="icon-large">📢</div>
                <div class="act-title">I Lost Something</div>
            </a>
            <a href="report.php?type=found" class="action-card">
                <div class="icon-large">🔍</div>
                <div class="act-title">I Found Something</div>
            </a>
        </div>
    </div>

    <div class="sticky-tabs-wrapper">
        <div class="tab-container">
            <div id="tab-lost" class="tab active" onclick="openTab('lost')">Lost Items</div>
            <div id="tab-found" class="tab" onclick="openTab('found')">Found Items</div>
        </div>
    </div>

    <div class="container">
        <div id="grid-lost" class="item-grid active">
            <?php if ($result_lost->num_rows > 0): ?>
                <?php while($row = $result_lost->fetch_assoc()): ?>
                    <div class="card">
                        <div class="card-img-wrapper">
                            <?php if($row['image_path']): ?>
                                <img src="<?php echo $row['image_path']; ?>" alt="Item">
                            <?php else: ?>
                                <span style="color:#64748b; font-size:0.9rem;">No Image</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="card-header">
                                <h3 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                                <span class="tag tag-lost">LOST</span>
                            </div>
                            <p class="card-desc"><?php echo htmlspecialchars($row['description']); ?></p>
                            <div class="card-footer">
                                <span style="font-size:0.8rem; color:#94a3b8;">
                                    Reported: <?php echo date('M d', strtotime($row['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #94a3b8;">
                    <?php echo $search ? "No lost items match your search." : "No lost items reported recently."; ?>
                </div>
            <?php endif; ?>
        </div>

        <div id="grid-found" class="item-grid">
            <?php if ($result_found->num_rows > 0): ?>
                <?php while($row = $result_found->fetch_assoc()): ?>
                    <div class="card">
                        <div class="card-img-wrapper">
                            <?php if($row['is_sensitive']): ?>
                                <div class="sensitive-overlay">
                                    <div style="font-size:1.5rem; margin-bottom:5px;">🔒</div>
                                    <small>Protected</small>
                                </div>
                            <?php elseif($row['image_path']): ?>
                                <img src="<?php echo $row['image_path']; ?>" alt="Item">
                            <?php else: ?>
                                <span style="color:#64748b; font-size:0.9rem;">No Image</span>
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <div class="card-header">
                                <h3 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                                <span class="tag tag-found">FOUND</span>
                            </div>
                            <p class="card-desc">
                                <?php echo $row['is_sensitive'] ? "<em>Sensitive item. Details hidden.</em>" : htmlspecialchars($row['description']); ?>
                            </p>
                            <div class="card-footer">
                                <span style="font-size:0.8rem; color:#94a3b8;">
                                    Reported: <?php echo date('M d', strtotime($row['created_at'])); ?>
                                </span>
                                <a href="claim.php?item_id=<?php echo $row['id']; ?>" class="btn-claim">Claim</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #94a3b8;">
                    <?php echo $search ? "No found items match your search." : "No found items reported recently."; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer style="text-align: center; padding: 40px 20px; color: #64748b; font-size: 0.9rem; border-top: 1px solid var(--border); margin-top: 40px;">
        <p>&copy; <?php echo date('Y'); ?> Campus Lost & Found System.</p>
    </footer>

</body>
</html>