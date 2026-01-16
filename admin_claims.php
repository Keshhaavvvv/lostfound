<?php
/* File: admin_claims.php (With Dynamic Search) */
include 'db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="favicon.png">
    <meta charset="UTF-8">
    <title>Manage Claims - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-bg: #1e293b;
            --sidebar-text: #f1f5f9;
            --primary: #4f46e5;
            --bg-body: #f3f4f6;
        }
        body { font-family: 'Inter', sans-serif; margin: 0; display: flex; height: 100vh; background: var(--bg-body); }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--sidebar-bg); color: var(--sidebar-text); display: flex; flex-direction: column; flex-shrink: 0; }
        .brand { padding: 25px; font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid #334155; }
        .menu { flex: 1; padding: 20px 0; }
        .menu a {
            display: block; padding: 12px 25px; color: #94a3b8; text-decoration: none; font-weight: 500; transition: 0.2s;
        }
        .menu a:hover, .menu a.active { background: #334155; color: white; border-left: 4px solid var(--primary); }
        
        .user-info { padding: 20px; border-top: 1px solid #334155; font-size: 0.9rem; color: #94a3b8; }

        /* Content */
        .content { flex: 1; overflow-y: auto; padding: 30px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h1 { font-size: 1.8rem; color: #111827; margin: 0; }
        
        /* Dynamic Search Bar */
        .search-bar {
            padding: 10px 15px; width: 300px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; outline: none; transition: 0.2s; background: white;
        }
        .search-bar:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

        /* Table Styles */
        .table-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; text-align: left; padding: 15px 20px; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
        td { padding: 15px 20px; border-bottom: 1px solid #e2e8f0; color: #334155; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        
        /* Images & Badges */
        .thumb { width: 40px; height: 40px; border-radius: 6px; object-fit: cover; border: 1px solid #e2e8f0; }
        .thumb-lock { width: 40px; height: 40px; border-radius: 6px; background: #334155; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .s-pending { background: #fef3c7; color: #92400e; }
        .s-approved { background: #dcfce7; color: #166534; }
        .s-rejected { background: #fee2e2; color: #991b1b; }
        
        .btn-view {
            background: var(--primary); color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 500; transition: 0.2s;
        }
        .btn-view:hover { background: #4338ca; }

    </style>
</head>
<body>

<div class="sidebar">
    <div class="brand">Admin Panel</div>
    <div class="menu">
        <a href="admin_dashboard.php">Manage Items</a>
        <a href="admin_claims.php" class="active">Manage Claims</a>
        <a href="admin_users.php">Manage Students</a> <a href="index.php" target="_blank">View Live Site &nearr;</a>
    </div>
    <div class="user-info">
        Logged in as<br><strong style="color:white;"><?php echo $_SESSION['full_name']; ?></strong><br>
        <a href="logout.php" style="padding:0; margin-top:5px; color:#ef4444;">Logout</a>
    </div>
</div>

<div class="content">
    <div class="page-header">
        <div>
            <h1>Claim Requests</h1>
            <div style="color:#64748b; font-size:0.9rem; margin-top:5px;">Real-time search & auto-refresh</div>
        </div>
        
        <input type="text" id="searchInput" class="search-bar" placeholder="Search Item Name, Student or ID..." autocomplete="off">
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Case ID</th>
                    <th>Item</th>
                    <th>Claimant</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="claimsTableBody">
                <tr><td colspan="6" style="text-align:center;">Loading data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    function loadClaims() {
        // 1. Get search term
        const search = document.getElementById('searchInput').value;
        
        // 2. Fetch data from backend
        fetch('fetch_claims.php?search=' + encodeURIComponent(search))
        .then(response => response.text())
        .then(data => {
            document.getElementById("claimsTableBody").innerHTML = data;
        });
    }

    // Load initially
    loadClaims();
    
    // Refresh every 3 seconds to check for new claims
    setInterval(loadClaims, 3000);

    // Instant search on typing
    document.getElementById('searchInput').addEventListener('keyup', loadClaims);
</script>

</body>
</html>