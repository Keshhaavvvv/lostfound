<?php
/* File: admin_dashboard.php (With Dynamic Search + Notify Style) */
include 'db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// 1. Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM items WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit();
}

// 2. Handle Sensitivity Toggle
if (isset($_GET['toggle_sensitive'])) {
    $id = intval($_GET['toggle_sensitive']);
    $conn->query("UPDATE items SET is_sensitive = NOT is_sensitive WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
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
        .menu a { display: block; padding: 12px 25px; color: #94a3b8; text-decoration: none; font-weight: 500; transition: 0.2s; }
        .menu a:hover, .menu a.active { background: #334155; color: white; border-left: 4px solid var(--primary); }
        .user-info { padding: 20px; border-top: 1px solid #334155; font-size: 0.9rem; color: #94a3b8; }

        /* Content */
        .content { flex: 1; overflow-y: auto; padding: 30px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h1 { font-size: 1.8rem; color: #111827; margin: 0; }
        
        /* Search Bar Style */
        .search-bar {
            padding: 10px 15px;
            width: 300px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.95rem;
            outline: none;
            transition: 0.2s;
            background: white;
        }
        .search-bar:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Table Styles */
        .table-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; text-align: left; padding: 15px 20px; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
        td { padding: 15px 20px; border-bottom: 1px solid #e2e8f0; color: #334155; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        
        /* Thumbnails & Badges */
        .thumb { width: 40px; height: 40px; border-radius: 6px; object-fit: cover; background: #eee; vertical-align: middle; }
        .thumb-locked { width: 40px; height: 40px; border-radius: 6px; background: #334155; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: white; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .b-lost { background: #fee2e2; color: #991b1b; }
        .b-found { background: #d1fae5; color: #065f46; }
        
        /* Buttons */
        .btn-toggle { font-size: 0.75rem; text-decoration: none; padding: 4px 8px; border-radius: 4px; margin-left: 10px; font-weight: 600; border: 1px solid transparent; }
        .t-hide { color: #d97706; border-color: #fcd34d; background: #fffbeb; }
        .t-show { color: #059669; border-color: #6ee7b7; background: #ecfdf5; }
        .t-hide:hover { background: #fef3c7; }
        .t-show:hover { background: #d1fae5; }

        .btn-del { color: #ef4444; text-decoration: none; font-weight: 600; font-size: 0.9rem; }
        .btn-del:hover { text-decoration: underline; }

        /* --- NEW BUTTON STYLE FOR NOTIFY --- */
        .btn-notify { 
            background: rgba(245, 158, 11, 0.1); 
            color: #d97706; 
            border: 1px solid rgba(245, 158, 11, 0.3); 
            margin-right: 10px;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        .btn-notify:hover { 
            background: rgba(245, 158, 11, 0.2); 
            color: #b45309;
        }

    </style>
</head>
<body>

<div class="sidebar">
    <div class="brand">Admin Panel</div>
    <div class="menu">
        <a href="admin_dashboard.php" class="active">Manage Items</a>
        <a href="admin_claims.php">Manage Claims</a>
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
            <h1>Reported Items</h1>
            <div style="color:#64748b; font-size:0.9rem; margin-top:5px;">Auto-refreshing every 3s</div>
        </div>
        
        <input type="text" id="searchInput" class="search-bar" placeholder="Search by item name..." autocomplete="off">
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Visibility</th>
                    <th>Type</th>
                    <th>Details</th>
                    <th>Reporter</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="itemTableBody">
                <tr><td colspan="8" style="text-align:center;">Loading live data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    function loadItems() {
        const search = document.getElementById('searchInput').value;
        fetch('fetch_items.php?search=' + encodeURIComponent(search))
        .then(response => response.text())
        .then(data => {
            document.getElementById("itemTableBody").innerHTML = data;
        });
    }

    loadItems();
    setInterval(loadItems, 3000);
    document.getElementById('searchInput').addEventListener('keyup', loadItems);
</script>

</body>
</html>