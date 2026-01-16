<?php
/* File: fetch_items.php (Merged: Your Custom Design + New Notify Button) */
include 'db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { exit(); }

// 1. SEARCH LOGIC
$search_query = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $s = $conn->real_escape_string($_GET['search']);
    // Search in Title, Description, or Reporter Name
    $search_query = "AND (items.title LIKE '%$s%' OR items.description LIKE '%$s%' OR users.full_name LIKE '%$s%')";
}

// 2. SQL QUERY (Preserving your specific fields: mobile, department, etc.)
$sql = "SELECT items.*, users.full_name, users.student_id as reporter_id, users.department, users.mobile 
        FROM items 
        JOIN users ON items.user_id = users.id 
        WHERE 1=1 $search_query 
        ORDER BY created_at DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $badge_class = ($row['type']=='lost') ? 'b-lost' : 'b-found';
        
        // Visibility Logic (Your Original Design)
        if ($row['is_sensitive']) {
            $img_html = "<div class='thumb-locked'>🔒</div>";
            $toggle_btn = "<a href='admin_dashboard.php?toggle_sensitive={$row['id']}' class='btn-toggle t-show'>🔓 Unlock</a>";
            $vis_status = "<span style='color:#d97706; font-weight:600; font-size:0.85rem;'>Hidden</span>";
        } else {
            $img_html = $row['image_path'] ? "<img src='{$row['image_path']}' class='thumb'>" : "<span style='color:#ccc; font-size:0.8rem;'>No Img</span>";
            $toggle_btn = "<a href='admin_dashboard.php?toggle_sensitive={$row['id']}' class='btn-toggle t-hide'>🔒 Lock</a>";
            $vis_status = "<span style='color:#059669; font-weight:600; font-size:0.85rem;'>Public</span>";
        }

        // Reporter Info Logic (Your Original Design)
        $dept_info = $row['department'] ? " <span style='color:#94a3b8; font-size:0.75rem;'>({$row['department']})</span>" : "";
        
        // Mobile Link (Your Original Design)
        $mobile_display = "";
        if (!empty($row['mobile'])) {
            $mobile_display = "<div style='margin-top:4px;'>
                                <a href='tel:{$row['mobile']}' style='color:#3b82f6; text-decoration:none; font-weight:600; font-size:0.85rem; display:inline-flex; align-items:center; gap:4px;'>
                                   📞 {$row['mobile']}
                                </a>
                               </div>";
        } else {
            $mobile_display = "<div style='color:#ef4444; font-size:0.8rem; margin-top:4px;'>No mobile</div>";
        }

        // Output Table Row
        echo "<tr>
                <td>#{$row['id']}</td>
                <td>$img_html</td>
                <td>$vis_status<br>$toggle_btn</td>
                <td><span class='badge $badge_class'>".strtoupper($row['type'])."</span></td>
                <td>
                    <strong style='color:#1e293b;'>".htmlspecialchars($row['title'])."</strong><br>
                    <small style='color:#64748b;'>".substr(htmlspecialchars($row['description']), 0, 30)."...</small>
                </td>
                <td>
                    <div style='font-weight:500; color:#1e293b;'>".htmlspecialchars($row['full_name'])."</div>
                    <div style='color:#64748b; font-size:0.8rem;'>ID: {$row['reporter_id']} $dept_info</div>
                    $mobile_display
                </td>
                <td><span style='font-weight:500;'>".ucfirst($row['status'])."</span></td>
                
                <td>";
        
        // --- NEW NOTIFY BUTTON LOGIC ---
        // Only show if item type is 'lost'
        if ($row['type'] == 'lost') {
            echo "<a href='admin_notify_match.php?item_id={$row['id']}' class='btn-notify' onclick=\"return confirm('Send email alert to {$row['full_name']}?');\" title='Notify student about a potential match'>🔔 Notify</a> ";
        }

        echo "<a href='?delete_id={$row['id']}' class='btn-del' onclick=\"return confirm('Delete this item completely?');\">Delete</a>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='8' style='text-align:center; padding:30px; color:#94a3b8;'>No matching items found.</td></tr>";
}
?>