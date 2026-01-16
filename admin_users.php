<?php
/* File: admin_users.php (With Dynamic Search) */
include 'db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php"); exit();
}

// 1. Handle Ban Action
if (isset($_GET['ban_id'])) {
    $id = intval($_GET['ban_id']);
    $conn->query("UPDATE users SET is_banned=1 WHERE id=$id");
    header("Location: admin_users.php"); exit();
}

// 2. Handle Unban Action
if (isset($_GET['unban_id'])) {
    $id = intval($_GET['unban_id']);
    $conn->query("UPDATE users SET is_banned=0 WHERE id=$id");
    header("Location: admin_users.php"); exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-bg: #1e293b; --sidebar-text: #f1f5f9; --primary: #4f46e5; --bg-body: #f3f4f6; }
        body { font-family: 'Inter', sans-serif; margin: 0; display: flex; height: 100vh; background: var(--bg-body); }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--sidebar-bg); color: var(--sidebar-text); display: flex; flex-direction: column; flex-shrink: 0; }
        .brand { padding: 25px; font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid #334155; }
        .menu { flex: 1; padding: 20px 0; }
        .menu a { display: block; padding: 12px 25px; color: #94a3b8; text-decoration: none; font-weight: 500; transition: 0.2s; }
        .menu a:hover, .menu a.active { background: #334155; color: white; border-left: 4px solid var(--primary); }
        .user-info { padding: 20px; border-top: 1px solid #334155; font-size: 0.9rem; color: #94a3b8; }

        /* Content */
        .content { flex: 1; overflow-y: auto; padding: 30px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h1 { font-size: 1.8rem; color: #111827; margin: 0; }
        
        /* Search Bar */
        .search-bar { padding: 10px 15px; width: 300px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; outline: none; background: white; }
        .search-bar:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

        /* Table */
        .table-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; text-align: left; padding: 15px 20px; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
        td { padding: 15px 20px; border-bottom: 1px solid #e2e8f0; color: #334155; vertical-align: top; }
        
        /* Profile Details Style */
        .detail-row { margin-bottom: 4px; font-size: 0.9rem; }
        .detail-label { color: #94a3b8; font-size: 0.8rem; font-weight: 600; width: 60px; display: inline-block; }
        
        /* Activity Lists */
        .activity-box { margin-bottom: 15px; }
        .act-header { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 5px; }
        
        .item-tag { display: inline-block; padding: 4px 10px; border-radius: 4px; background: #f1f5f9; color: #334155; font-size: 0.85rem; margin-right: 5px; margin-bottom: 5px; border: 1px solid #e2e8f0; transition: 0.2s; }
        
        .tag-lost { border-left: 3px solid #ef4444; }
        .tag-found { border-left: 3px solid #10b981; }
        
        /* Interactive Claim Tag */
        .tag-claim { border-left: 3px solid #3b82f6; cursor: pointer; }
        .tag-claim:hover { background: #e0e7ff; border-left-color: #4f46e5; color: #3730a3; transform: translateY(-1px); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        
        /* Badges & Buttons */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .b-active { background: #dcfce7; color: #166534; }
        .b-banned { background: #fee2e2; color: #991b1b; }
        
        .btn { padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: inline-block; }
        .btn-ban { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .btn-ban:hover { background: #fca5a5; }
        .btn-unban { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .btn-unban:hover { background: #86efac; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="brand">Admin Panel</div>
    <div class="menu">
        <a href="admin_dashboard.php">Manage Items</a>
        <a href="admin_claims.php">Manage Claims</a>
        <a href="admin_users.php" class="active">Manage Students</a>
        <a href="index.php" target="_blank">View Live Site &nearr;</a>
    </div>
    <div class="user-info">
        Logged in as<br><strong style="color:white;"><?php echo $_SESSION['full_name']; ?></strong><br>
        <a href="logout.php" style="padding:0; margin-top:5px; color:#ef4444;">Logout</a>
    </div>
</div>

<div class="content">
    <div class="page-header">
        <div>
            <h1>Student Directory</h1>
            <div style="color:#64748b; font-size:0.9rem; margin-top:5px;">Real-time search & auto-refresh</div>
        </div>
        
        <input type="text" id="searchInput" class="search-bar" placeholder="Search Name, Email, ID or Mobile..." autocomplete="off">
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="35%">Profile Details</th>
                    <th width="40%">Activity History</th>
                    <th width="10%">Status</th>
                    <th width="10%">Action</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <tr><td colspan="5" style="text-align:center; padding:30px;">Loading student data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    function loadUsers() {
        const search = document.getElementById('searchInput').value;
        fetch('fetch_users.php?search=' + encodeURIComponent(search))
        .then(response => response.text())
        .then(data => {
            document.getElementById("userTableBody").innerHTML = data;
        });
    }

    loadUsers(); // Initial Load
    setInterval(loadUsers, 3000); // Auto Refresh
    document.getElementById('searchInput').addEventListener('keyup', loadUsers); // Instant Search
</script>

</body>
</html>