<?php
/* File: fetch_users.php (Backend for Student Search) */
include 'db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { exit(); }

// Search Logic
$search_sql = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $s = $conn->real_escape_string($_GET['search']);
    // Search by Name, Email, Student ID, or Mobile
    $search_sql = "AND (full_name LIKE '%$s%' OR email LIKE '%$s%' OR student_id LIKE '%$s%' OR mobile LIKE '%$s%')";
}

// Main Query
$sql = "SELECT * FROM users WHERE role != 'admin' $search_sql ORDER BY id DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $uid = $row['id'];
        
        // 1. Fetch Items Reported
        $items_res = $conn->query("SELECT title, type FROM items WHERE user_id=$uid");
        $items_html = "";
        if($items_res->num_rows > 0) {
            while($item = $items_res->fetch_assoc()) {
                $tag_class = ($item['type']=='lost') ? 'tag-lost' : 'tag-found';
                $items_html .= "<span class='item-tag $tag_class'>".htmlspecialchars($item['title'])."</span>";
            }
        } else {
            $items_html = "<span style='color:#cbd5e1; font-size:0.85rem;'>No reports filed.</span>";
        }

        // 2. Fetch Claims Made
        $claims_res = $conn->query("SELECT c.id, c.status, i.title FROM claims c JOIN items i ON c.item_id = i.id WHERE c.claimant_id=$uid");
        $claims_html = "";
        if($claims_res->num_rows > 0) {
            while($claim = $claims_res->fetch_assoc()) {
                $claims_html .= "<a href='claim_chat.php?claim_id={$claim['id']}' style='text-decoration:none;' title='View Chat'>
                                    <span class='item-tag tag-claim'>
                                        ".htmlspecialchars($claim['title'])." (".ucfirst($claim['status']).") ↗
                                    </span>
                                 </a>";
            }
        } else {
            $claims_html = "<span style='color:#cbd5e1; font-size:0.85rem;'>No claims made.</span>";
        }

        // 3. Format Profile Data
        $mobile = $row['mobile'] ? $row['mobile'] : '<span style="color:#ccc">-</span>';
        $dept = $row['department'] ? htmlspecialchars($row['department']) : '<span style="color:#ccc">-</span>';
        $year = $row['year_study'] ? htmlspecialchars($row['year_study']) : '<span style="color:#ccc">-</span>';
        $addr = $row['address'] ? htmlspecialchars($row['address']) : '<span style="color:#ccc">-</span>';
        
        // 4. Status Badge & Button
        if ($row['is_banned']) {
            $row_style = "background:#fef2f2;";
            $status_badge = "<span class='badge b-banned'>BANNED</span>";
            $action_btn = "<a href='?unban_id={$row['id']}' class='btn btn-unban' onclick=\"return confirm('Restore user?');\">Unban</a>";
        } else {
            $row_style = "";
            $status_badge = "<span class='badge b-active'>ACTIVE</span>";
            $action_btn = "<a href='?ban_id={$row['id']}' class='btn btn-ban' onclick=\"return confirm('Ban this user?');\">Ban</a>";
        }

        // OUTPUT ROW
        echo "<tr style='$row_style'>
            <td>#{$row['id']}</td>
            <td>
                <div style='font-size:1.1rem; font-weight:600; color:#1e293b; margin-bottom:5px;'>
                    ".htmlspecialchars($row['full_name'])."
                </div>
                <div class='detail-row'><span class='detail-label'>Email:</span> ".htmlspecialchars($row['email'])."</div>
                <div class='detail-row'><span class='detail-label'>Mobile:</span> $mobile</div>
                <div class='detail-row'><span class='detail-label'>ID:</span> ".htmlspecialchars($row['student_id'])."</div>
                <div style='margin-top:8px; padding-top:8px; border-top:1px dashed #e2e8f0;'>
                    <div class='detail-row'><span class='detail-label'>Dept:</span> $dept</div>
                    <div class='detail-row'><span class='detail-label'>Year:</span> $year</div>
                    <div class='detail-row'><span class='detail-label'>Addr:</span> $addr</div>
                </div>
            </td>
            <td>
                <div class='activity-box'>
                    <div class='act-header'>Reported Items ({$items_res->num_rows})</div>
                    $items_html
                </div>
                <div class='activity-box'>
                    <div class='act-header'>Claims Made ({$claims_res->num_rows})</div>
                    $claims_html
                </div>
            </td>
            <td>$status_badge</td>
            <td>$action_btn</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='5' style='text-align:center; padding:30px; color:#94a3b8;'>No students found matching your search.</td></tr>";
}
?>