<?php
/*
    File: fetch_chat.php
    Purpose: Loads messages + images dynamically (Compatible with Mobile UI)
*/
include 'db.php';

if (!isset($_SESSION['user_id'])) { exit(); }

$claim_id = intval($_GET['claim_id']);
$viewer_id = $_SESSION['user_id']; 

// Fetch Messages with Sender Name
$sql = "SELECT messages.*, users.full_name 
        FROM messages 
        JOIN users ON messages.sender_id = users.id 
        WHERE claim_id = $claim_id 
        ORDER BY created_at ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($msg = $result->fetch_assoc()) {
        
        // 1. Determine Class (Me vs Them)
        // The new UI uses Flexbox, so we use classes 'msg-me' or 'msg-other'
        $is_me = ($msg['sender_id'] == $viewer_id);
        $msg_class = $is_me ? 'msg-me' : 'msg-other';
        $sender_label = $is_me ? 'You' : htmlspecialchars($msg['full_name']);

        echo '<div class="message ' . $msg_class . '">';
        
        // 2. Meta Data (Name • Time)
        echo '<span class="msg-meta">' . $sender_label . ' • ' . date('H:i', strtotime($msg['created_at'])) . '</span>';
        
        // 3. SHOW IMAGE (If exists)
        if (!empty($msg['attachment_path'])) {
            echo '<a href="' . htmlspecialchars($msg['attachment_path']) . '" target="_blank">';
            echo '<img src="' . htmlspecialchars($msg['attachment_path']) . '" class="chat-img">';
            echo '</a>';
        }

        // 4. SHOW TEXT (If exists)
        if (!empty($msg['message_text'])) {
            echo '<div>' . htmlspecialchars($msg['message_text']) . '</div>';
        }

        echo '</div>';
    }
} else {
    // Empty State
    echo '<div style="text-align:center; color:#9ca3af; margin-top:20px; font-size:0.9rem;">No messages yet. Start the conversation!</div>';
}
?>