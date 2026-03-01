<?php
/* File: fetch_items.php (Merged: Custom Design + Notify + Location + Category) */
include 'db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { exit(); }

// 1. SEARCH LOGIC (Updated to include Category)
$search_query = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $s = $conn->real_escape_string($_GET['search']);
    // Search in Title, Description, Location, Category, or Reporter Name
    $search_query = "AND (items.title LIKE '%$s%' OR items.description LIKE '%$s%' OR items.location LIKE '%$s%' OR items.category LIKE '%$s%' OR users.full_name LIKE '%$s%')";
}

// 2. SQL QUERY
$sql = "SELECT items.*, users.full_name, users.student_id as reporter_id, users.department, users.mobile 
        FROM items 
        JOIN users ON items.user_id = users.id 
        WHERE 1=1 $search_query 
        ORDER BY created_at DESC";

$result = $conn->query($sql);

// Helper function for Icons
function getCategoryIcon($cat) {
    switch($cat) {
        case 'Electronics': return '📱';
        case 'ID Cards': return '💳';
        case 'Stationery': return '✏️';
        case 'Clothing': return '👕';
        case 'Keys': return '🔑';
        case 'Sports Gear': return '⚽';
        default: return '📦';
    }
}

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $badge_class = ($row['type']=='lost') ? 'b-lost' : 'b-found';
        
        // Visibility Logic
        if ($row['is_sensitive']) {
            $img_html = "<div class='thumb-locked'>🔒</div>";
            $toggle_btn = "<a href='admin_dashboard.php?toggle_sensitive={$row['id']}' class='btn-toggle t-show'>🔓 Unlock</a>";
            $vis_status = "<span style='color:#d97706; font-weight:600; font-size:0.85rem;'>Hidden</span>";
        } else {
            $img_html = $row['image_path'] ? "<img src='{$row['image_path']}' class='thumb'>" : "<span style='color:#ccc; font-size:0.8rem;'>No Img</span>";
            $toggle_btn = "<a href='admin_dashboard.php?toggle_sensitive={$row['id']}' class='btn-toggle t-hide'>🔒 Lock</a>";
            $vis_status = "<span style='color:#059669; font-weight:600; font-size:0.85rem;'>Public</span>";
        }

        // Reporter Info Logic
        $dept_info = $row['department'] ? " <span style='color:#94a3b8; font-size:0.75rem;'>({$row['department']})</span>" : "";
        
        // Mobile Link
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

        // Location & Category Display
        $loc_display = !empty($row['location']) ? $row['location'] : "General";
        $cat_display = !empty($row['category']) ? $row['category'] : "Other";
        $cat_icon = getCategoryIcon($cat_display);

        // Output Table Row
        echo "<tr>
                <td>#{$row['id']}</td>
                <td>$img_html</td>
                <td>$vis_status<br>$toggle_btn</td>
                <td><span class='badge $badge_class'>".strtoupper($row['type'])."</span></td>
                <td>
                    <strong style='color:#1e293b;'>".htmlspecialchars($row['title'])."</strong><br>
                    
                    <div style='margin: 4px 0; display:flex; gap:8px;'>
                        <span style='color:#6366f1; font-weight:600; font-size:0.8rem; background:#e0e7ff; padding:2px 6px; border-radius:4px;'>
                            📍 ".htmlspecialchars($loc_display)."
                        </span>
                        <span style='color:#0f766e; font-weight:600; font-size:0.8rem; background:#ccfbf1; padding:2px 6px; border-radius:4px;'>
                            $cat_icon ".htmlspecialchars($cat_display)."
                        </span>
                    </div>

                    <small style='color:#64748b;'>".substr(htmlspecialchars($row['description']), 0, 30)."...</small>
                </td>
                <td>
                    <div style='font-weight:500; color:#1e293b;'>".htmlspecialchars($row['full_name'])."</div>
                    <div style='color:#64748b; font-size:0.8rem;'>ID: {$row['reporter_id']} $dept_info</div>
                    $mobile_display
                </td>
                <td><span style='font-weight:500;'>".ucfirst($row['status'])."</span></td>
                
                <td>";
        
        // Notify Button Logic
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