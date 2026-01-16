<?php
/* File: fetch_claims.php (Backend for Admin Claims Search) */
include 'db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { exit(); }

// Search Logic
$search_sql = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $s = $conn->real_escape_string($_GET['search']);
    $search_sql = "AND (items.title LIKE '%$s%' OR users.full_name LIKE '%$s%' OR users.student_id LIKE '%$s%')";
}

// Fetch Query
$sql = "SELECT claims.id as claim_id, claims.status as claim_status, claims.created_at,
               items.title, items.image_path, items.is_sensitive,
               users.full_name, users.student_id
        FROM claims
        JOIN items ON claims.item_id = items.id
        JOIN users ON claims.claimant_id = users.id
        WHERE 1=1 $search_sql
        ORDER BY 
            CASE WHEN claims.status = 'pending' THEN 0 ELSE 1 END, 
            claims.created_at DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Thumbnail Logic
        if($row['is_sensitive']) {
            $thumb = '<div class="thumb-lock">🔒</div>';
            $sensitive_tag = '<small style="color:#d97706;">Sensitive Item</small>';
        } elseif($row['image_path']) {
            $thumb = '<img src="'.$row['image_path'].'" class="thumb">';
            $sensitive_tag = '';
        } else {
            $thumb = '<div class="thumb" style="background:#f1f5f9; display:flex; align-items:center; justify-content:center; color:#ccc;">?</div>';
            $sensitive_tag = '';
        }

        echo '<tr>
            <td><strong>#'.$row['claim_id'].'</strong></td>
            <td>
                <div style="display:flex; align-items:center; gap:10px;">
                    '.$thumb.'
                    <div>
                        <div style="font-weight:500;">'.htmlspecialchars($row['title']).'</div>
                        '.$sensitive_tag.'
                    </div>
                </div>
            </td>
            <td>
                '.htmlspecialchars($row['full_name']).'<br>
                <small style="color:#94a3b8;">'.$row['student_id'].'</small>
            </td>
            <td>'.date("M d, Y", strtotime($row['created_at'])).'</td>
            <td>
                <span class="badge s-'.$row['claim_status'].'">
                    '.ucfirst($row['claim_status']).'
                </span>
            </td>
            <td>
                <a href="claim_chat.php?claim_id='.$row['claim_id'].'" class="btn-view">
                    View Case &rarr;
                </a>
            </td>
        </tr>';
    }
} else {
    echo '<tr><td colspan="6" style="text-align:center; padding:30px; color:#94a3b8;">No claims found.</td></tr>';
}
?>