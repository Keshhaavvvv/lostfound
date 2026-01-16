<?php
/* File: leaderboard.php (Dark Theme + Stylish UI) */
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 1. STATS
try {
    $stats['lost'] = $conn->query("SELECT COUNT(*) FROM items WHERE type='lost'")->fetch_row()[0];
    $stats['found'] = $conn->query("SELECT COUNT(*) FROM items WHERE type='found'")->fetch_row()[0];
    $stats['pending'] = $conn->query("SELECT COUNT(*) FROM claims WHERE status='pending'")->fetch_row()[0];
    $stats['approved'] = $conn->query("SELECT COUNT(*) FROM claims WHERE status='approved'")->fetch_row()[0];
} catch (Exception $e) {
    $stats = ['lost'=>0, 'found'=>0, 'pending'=>0, 'approved'=>0];
}

// 2. LEADERBOARD FUNCTION
function getLeaderboard($conn, $timeframe) {
    $date_sql = "";
    if ($timeframe == 'month') {
        $date_sql = "AND MONTH(i.created_at) = MONTH(CURRENT_DATE()) AND YEAR(i.created_at) = YEAR(CURRENT_DATE())";
    } elseif ($timeframe == 'year') {
        $date_sql = "AND YEAR(i.created_at) = YEAR(CURRENT_DATE())";
    } elseif ($timeframe == 'prev_month') {
        $date_sql = "AND MONTH(i.created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(i.created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)";
    } elseif ($timeframe == 'prev_year') {
        $date_sql = "AND YEAR(i.created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 YEAR)";
    }

    $sql = "SELECT u.id as uid, u.full_name, u.department, COUNT(i.id) as score 
            FROM items i 
            JOIN users u ON i.user_id = u.id 
            WHERE i.type = 'found' $date_sql
            GROUP BY u.id 
            ORDER BY score DESC 
            LIMIT 5";
    return $conn->query($sql);
}

$top_month = getLeaderboard($conn, 'month');
$top_year  = getLeaderboard($conn, 'year');
$prev_month_winner = getLeaderboard($conn, 'prev_month')->fetch_assoc();
$prev_year_winner  = getLeaderboard($conn, 'prev_year')->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --gold: #fbbf24;
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

        /* --- LEADERBOARD SPECIFIC STYLES --- */
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        
        .page-title { font-size: 1.8rem; margin-bottom: 20px; color: white; text-align: center; }

        /* Award Banner */
        .award-banner { 
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); 
            color: white; border-radius: 16px; padding: 30px; margin-bottom: 30px; 
            position: relative; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
        }
        .award-banner h2 { margin: 0 0 10px 0; font-size: 1.6rem; }
        .award-banner p { margin: 0; opacity: 0.9; line-height: 1.6; max-width: 85%; }
        .award-icon { position: absolute; right: 20px; bottom: -10px; font-size: 6rem; opacity: 0.2; transform: rotate(-10deg); }

        /* Hall of Fame Grid */
        .hof-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .hof-card { 
            background: var(--bg-card); padding: 25px; border-radius: 16px; 
            display: flex; align-items: center; gap: 20px; text-decoration: none; 
            color: inherit; transition: 0.3s; border: 1px solid var(--border); 
            position: relative; overflow: hidden;
        }
        .hof-card:hover { 
            border-color: var(--gold); transform: translateY(-5px); 
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.3); 
            background: linear-gradient(to right, #1e293b, #0f172a);
        }
        .crown { font-size: 2.5rem; background: rgba(251, 191, 36, 0.1); padding: 12px; border-radius: 50%; border: 1px solid rgba(251, 191, 36, 0.2); }
        .hof-info small { color: var(--text-muted); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
        .hof-info strong { display: block; font-size: 1.2rem; color: white; margin-top: 4px; }

        /* Chart Section */
        .chart-section { 
            background: var(--bg-card); padding: 25px; border-radius: 16px; 
            margin-bottom: 30px; border: 1px solid var(--border); 
        }
        .chart-wrapper { max-width: 400px; margin: 0 auto; position: relative; }
        
        /* Leaderboard Tables */
        .lb-container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .lb-box { 
            background: var(--bg-card); border-radius: 16px; overflow: hidden; 
            border: 1px solid var(--border); 
        }
        .lb-header { 
            background: rgba(15, 23, 42, 0.5); padding: 15px 20px; 
            border-bottom: 1px solid var(--border); font-weight: 700; color: var(--text-main); 
            display: flex; justify-content: space-between; 
        }
        
        .list-item { 
            padding: 15px 20px; border-bottom: 1px solid var(--border); 
            display: flex; align-items: center; text-decoration: none; 
            color: inherit; transition: 0.2s; 
        }
        .list-item:last-child { border-bottom: none; }
        .list-item:hover { background: var(--bg-card-hover); cursor: pointer; }

        .rank { width: 30px; font-weight: 800; color: var(--text-muted); font-size: 1.1rem; }
        .rank-1 { color: #fbbf24; text-shadow: 0 0 10px rgba(251, 191, 36, 0.4); } 
        .rank-2 { color: #94a3b8; } 
        .rank-3 { color: #b45309; }
        
        .user-details { flex: 1; padding: 0 15px; }
        .user-name { font-weight: 600; display: block; color: var(--text-main); }
        .user-dept { font-size: 0.8rem; color: var(--text-muted); }
        .score-badge { 
            background: rgba(99, 102, 241, 0.1); color: #818cf8; 
            padding: 5px 12px; border-radius: 20px; font-weight: 700; font-size: 0.85rem; 
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        @media (max-width: 768px) { 
            .lb-container, .hof-grid { grid-template-columns: 1fr; } 
            .header { padding: 0 1rem; }
            .nav-actions { display: none; }
            .mobile-menu-btn { display: block; }
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
    <a href="index.php" class="brand">𝐋𝐨𝐬𝐭&𝐅𝐨𝐮𝐧𝐝</a>
    
    <nav class="nav-actions">
        <a href="index.php" class="btn-small btn-outline">Home</a>
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
    <a href="index.php">🏠 Home</a>
    <a href="profile.php">👤 My Profile</a>
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
        <a href="admin_dashboard.php" style="color:#fca5a5;">🛡️ Admin Panel</a>
    <?php endif; ?>
    <a href="my_uploads.php">📢 My Reports</a>
    <a href="my_claims.php">✋ My Claims</a>
    <a href="logout.php" style="color:#94a3b8;">🚪 Logout</a>
</div>

<div class="container">
    
    <h1 class="page-title">Campus Leaderboard 🏆</h1>

    <div class="award-banner">
        <h2>CS Department Award</h2>
        <p>The student who helps find the most items by the end of the year will be awarded by the Computer Science Department for their honesty.</p>
        <div class="award-icon">🎖️</div>
    </div>

    <div class="hof-grid">
        <?php if($prev_month_winner): ?>
            <a href="view_user_found.php?uid=<?php echo $prev_month_winner['uid']; ?>" class="hof-card">
                <div class="crown">👑</div>
                <div class="hof-info">
                    <small>Last Month's Hero</small>
                    <strong><?php echo htmlspecialchars($prev_month_winner['full_name']); ?></strong>
                    <span style="font-size:0.85rem; color:var(--text-muted);"><?php echo $prev_month_winner['score']; ?> Items Found</span>
                </div>
            </a>
        <?php else: ?>
            <div class="hof-card"><div class="crown">👑</div><div class="hof-info"><strong>No Data Yet</strong></div></div>
        <?php endif; ?>

        <?php if($prev_year_winner): ?>
            <a href="view_user_found.php?uid=<?php echo $prev_year_winner['uid']; ?>" class="hof-card">
                <div class="crown">🎓</div>
                <div class="hof-info">
                    <small>Last Year's Champion</small>
                    <strong><?php echo htmlspecialchars($prev_year_winner['full_name']); ?></strong>
                    <span style="font-size:0.85rem; color:var(--text-muted);"><?php echo $prev_year_winner['score']; ?> Items Found</span>
                </div>
            </a>
        <?php else: ?>
             <div class="hof-card"><div class="crown">🎓</div><div class="hof-info"><strong>No Data Yet</strong></div></div>
        <?php endif; ?>
    </div>

    <div class="chart-section">
        <h3 style="text-align:center; margin-top:0; color:white;">System Statistics</h3>
        <div class="chart-wrapper"><canvas id="statsChart"></canvas></div>
    </div>

    <div class="lb-container">
        <div class="lb-box">
            <div class="lb-header"><span>📅 This Month</span></div>
            <?php if($top_month->num_rows > 0): ?>
                <?php $rank=1; while($row = $top_month->fetch_assoc()): ?>
                    <a href="view_user_found.php?uid=<?php echo $row['uid']; ?>" class="list-item">
                        <div class="rank rank-<?php echo $rank; ?>">#<?php echo $rank; ?></div>
                        <div class="user-details">
                            <span class="user-name">
                                <?php echo htmlspecialchars($row['full_name']); ?>
                                <?php if($rank==1) echo '🥇'; elseif($rank==2) echo '🥈'; elseif($rank==3) echo '🥉'; ?>
                            </span>
                            <span class="user-dept"><?php echo htmlspecialchars($row['department']); ?></span>
                        </div>
                        <div class="score-badge"><?php echo $row['score']; ?> Found</div>
                    </a>
                <?php $rank++; endwhile; ?>
            <?php else: ?>
                <div style="padding:20px; text-align:center; color:var(--text-muted);">No activity yet.</div>
            <?php endif; ?>
        </div>

        <div class="lb-box">
            <div class="lb-header"><span>🌟 All Time</span></div>
            <?php if($top_year->num_rows > 0): ?>
                <?php $rank=1; while($row = $top_year->fetch_assoc()): ?>
                    <a href="view_user_found.php?uid=<?php echo $row['uid']; ?>" class="list-item">
                        <div class="rank rank-<?php echo $rank; ?>">#<?php echo $rank; ?></div>
                        <div class="user-details">
                            <span class="user-name">
                                <?php echo htmlspecialchars($row['full_name']); ?>
                                <?php if($rank==1) echo '🥇'; elseif($rank==2) echo '🥈'; elseif($rank==3) echo '🥉'; ?>
                            </span>
                            <span class="user-dept"><?php echo htmlspecialchars($row['department']); ?></span>
                        </div>
                        <div class="score-badge"><?php echo $row['score']; ?> Found</div>
                    </a>
                <?php $rank++; endwhile; ?>
            <?php else: ?>
                <div style="padding:20px; text-align:center; color:var(--text-muted);">No data yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('statsChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Lost', 'Found', 'Pending', 'Resolved'],
            datasets: [{
                data: [<?php echo $stats['lost']; ?>, <?php echo $stats['found']; ?>, <?php echo $stats['pending']; ?>, <?php echo $stats['approved']; ?>],
                backgroundColor: ['#ef4444', '#10b981', '#f59e0b', '#3b82f6'], 
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: { 
            responsive: true, 
            plugins: { 
                legend: { 
                    position: 'bottom',
                    labels: { color: '#94a3b8' } // Light text for Dark Mode
                } 
            } 
        }
    });
</script>
</body>
</html>