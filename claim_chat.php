<?php
/* File: claim_chat.php (Dark Theme + Email Details + Notifications) */
include 'db.php';
include 'mail_config.php'; // Required for sending notifications

// 1. Basic Login Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$claim_id = intval($_GET['claim_id']);
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role']; 

// 2. Fetch Claim Data (Added users.email)
$sql_claim = "SELECT claims.status as claim_status, claims.claimant_id, 
              items.id as item_id, items.title, items.description, items.image_path, items.is_sensitive, 
              users.full_name, users.student_id, users.mobile, users.email,
              users.department, users.year_study, users.address 
              FROM claims 
              JOIN items ON claims.item_id = items.id 
              JOIN users ON claims.claimant_id = users.id 
              WHERE claims.id = $claim_id";
$claim_data = $conn->query($sql_claim)->fetch_assoc();

if (!$claim_data) { die("Claim not found."); }

// 3. SECURITY
if ($user_role != 'admin' && $user_id != $claim_data['claimant_id']) {
    die("⛔ Access Denied.");
}

// --- HANDLE ADMIN ACTIONS (With Email Notifications) ---
if ($user_role == 'admin' && isset($_GET['action'])) {
    $action = $_GET['action'];
    $item_id = intval($_GET['item_id']);
    $student_email = $claim_data['email'];
    $item_title = $claim_data['title'];
    
    if ($action == 'approve') {
        $conn->query("UPDATE claims SET status='approved' WHERE id=$claim_id");
        $conn->query("UPDATE items SET status='resolved' WHERE id=$item_id");
        
        $sys_msg = "System: Claim APPROVED. Please collect your item.";
        $conn->query("INSERT INTO messages (claim_id, sender_id, message_text) VALUES ($claim_id, $user_id, '$sys_msg')");

        // Send Approval Email
        sendEmail($student_email, "Claim Approved - $item_title", "
            <h3>Good News!</h3>
            <p>Your claim for <b>$item_title</b> has been APPROVED.</p>
            <p>Please visit the admin office/security desk to collect your item.</p>
        ");

    } elseif ($action == 'reject') {
        $conn->query("UPDATE claims SET status='rejected' WHERE id=$claim_id");
        
        $sys_msg = "System: Claim REJECTED.";
        $conn->query("INSERT INTO messages (claim_id, sender_id, message_text) VALUES ($claim_id, $user_id, '$sys_msg')");

        // Send Rejection Email
        sendEmail($student_email, "Claim Update - $item_title", "
            <h3>Claim Status Update</h3>
            <p>Your claim for <b>$item_title</b> has been REJECTED.</p>
            <p>You can log in to the portal to view the chat history for more details.</p>
        ");

    } elseif ($action == 'reset') {
        $conn->query("UPDATE claims SET status='pending' WHERE id=$claim_id");
        $conn->query("UPDATE items SET status='active' WHERE id=$item_id");
        $sys_msg = "System: Status reset to PENDING by Admin.";
        $conn->query("INSERT INTO messages (claim_id, sender_id, message_text) VALUES ($claim_id, $user_id, '$sys_msg')");
        
    } elseif ($action == 'toggle_sensitive') {
        $current_val = intval($_GET['current_val']);
        $new_val = ($current_val == 1) ? 0 : 1;
        $conn->query("UPDATE items SET is_sensitive=$new_val WHERE id=$item_id");
        $sys_msg = "System: Admin changed item visibility.";
        $conn->query("INSERT INTO messages (claim_id, sender_id, message_text) VALUES ($claim_id, $user_id, '$sys_msg')");
    }
    header("Location: claim_chat.php?claim_id=$claim_id");
    exit();
}

// --- HANDLE MESSAGE & FILE SEND ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $msg = $conn->real_escape_string($_POST['message']);
    $attachment_path = NULL;

    // Handle Image Upload
    if (!empty($_FILES['attachment']['name'])) {
        $target_dir = "uploads/chat/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $target_file = $target_dir . time() . "_" . basename($_FILES["attachment"]["name"]);
        if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file)) {
            $attachment_path = $target_file;
        }
    }

    if (!empty($msg) || $attachment_path) {
        
        // ** EMAIL NOTIFICATION LOGIC FOR FIRST ADMIN RESPONSE **
        if ($user_role == 'admin') {
            // Check if this is the first time the admin is messaging in this claim
            $check_sql = "SELECT id FROM messages WHERE claim_id = $claim_id AND sender_id = $user_id LIMIT 1";
            $previous_msgs = $conn->query($check_sql);

            if ($previous_msgs->num_rows == 0) {
                // This is the first response! Send Email.
                $item_title = $claim_data['title'];
                sendEmail($claim_data['email'], "Admin Responded - $item_title", "
                    <h3>New Message from Admin</h3>
                    <p>The Admin has responded to your claim for <b>$item_title</b>.</p>
                    <p>Please log in to the portal to reply and provide further proof if asked.</p>
                    <br>
                    <a href='http://{$_SERVER['HTTP_HOST']}/lostfound/login.php'>Go to Chat</a>
                ");
            }
        }

        // Use Prepared Statement for safety
        $stmt = $conn->prepare("INSERT INTO messages (claim_id, sender_id, message_text, attachment_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $claim_id, $user_id, $msg, $attachment_path);
        $stmt->execute();
    }
    header("Location: claim_chat.php?claim_id=$claim_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Case #<?php echo $claim_id; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            /* DARK THEME PALETTE */
            --primary: #6366f1; /* Indigo-500 */
            --primary-hover: #818cf8;
            
            --bg-body: #0f172a; /* Slate-900 */
            --bg-chat: #0f172a; /* Dark Background */
            --bg-panel: #1e293b; /* Slate-800 */
            --bg-header: rgba(30, 41, 59, 0.9); /* Glassy Header */
            --bg-input: #1e293b;
            
            --text-main: #f1f5f9; /* Slate-100 */
            --text-muted: #94a3b8; /* Slate-400 */
            --border: #334155; /* Slate-700 */
            
            /* Chat Bubbles */
            --msg-me: #6366f1; --msg-me-text: #ffffff;
            --msg-other: #334155; --msg-other-text: #f1f5f9;
            
            /* Status Colors */
            --status-pending: #fbbf24; 
            --status-approved: #34d399; 
            --status-rejected: #f87171;
        }

        * { box-sizing: border-box; }

        body { 
            font-family: 'Inter', sans-serif; margin: 0; 
            background-color: var(--bg-chat); color: var(--text-main);
            height: 100vh; height: 100dvh; display: flex; flex-direction: column; overflow: hidden; 
        }

        /* HEADER */
        .chat-header {
            background: var(--bg-header); backdrop-filter: blur(10px);
            padding: 15px 20px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; z-index: 10;
        }
        .header-title { font-weight: 600; color: white; font-size: 1rem; margin: 0; }
        .header-subtitle { font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 2px; }
        
        .header-actions { display: flex; gap: 10px; }
        .btn-details { 
            background: rgba(99, 102, 241, 0.1); color: #818cf8; 
            border: 1px solid rgba(99, 102, 241, 0.2); 
            padding: 6px 12px; border-radius: 6px; font-weight: 600; 
            font-size: 0.85rem; cursor: pointer; transition: 0.2s;
        }
        .btn-details:hover { background: var(--primary); color: white; }
        
        .btn-close { 
            text-decoration: none; color: var(--text-muted); 
            font-size: 0.9rem; padding: 6px 10px; 
            background: rgba(255,255,255,0.05); border-radius: 6px; 
            transition: 0.2s;
        }
        .btn-close:hover { background: rgba(255,255,255,0.1); color: white; }

        /* STATUS BANNER */
        .status-banner {
            text-align: center; font-size: 0.75rem; font-weight: 700; padding: 6px; 
            color: #1e293b; /* Dark text for contrast against bright banners */
            text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0;
        }
        .bg-pending { background-color: var(--status-pending); }
        .bg-approved { background-color: var(--status-approved); }
        .bg-rejected { background-color: var(--status-rejected); }

        /* CHAT BOX */
        .chat-box {
            flex-grow: 1; overflow-y: auto; padding: 20px; 
            display: flex; flex-direction: column; gap: 12px; scroll-behavior: smooth;
        }
        .message {
            max-width: 75%; padding: 10px 14px; border-radius: 12px; 
            font-size: 0.95rem; line-height: 1.4; word-wrap: break-word; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .msg-me { align-self: flex-end; background-color: var(--msg-me); color: var(--msg-me-text); border-bottom-right-radius: 2px; }
        .msg-other { align-self: flex-start; background-color: var(--msg-other); color: var(--msg-other-text); border: 1px solid var(--border); border-bottom-left-radius: 2px; }
        
        .msg-meta { display: block; font-size: 0.7rem; margin-bottom: 4px; opacity: 0.7; font-weight: 500; }
        
        /* Image in Chat Style */
        .chat-img { max-width: 100%; border-radius: 8px; margin-top: 5px; border: 1px solid rgba(255,255,255,0.1); cursor: pointer; }

        /* INPUT AREA */
        .input-area {
            background: var(--bg-panel); padding: 15px; border-top: 1px solid var(--border);
            flex-shrink: 0; display: flex; gap: 10px; align-items: center;
        }
        .input-field { 
            flex: 1; padding: 12px 15px; 
            border: 1px solid var(--border); border-radius: 25px; 
            font-size: 0.95rem; background: var(--bg-body); color: white;
            outline: none; transition: 0.2s;
        }
        .input-field:focus { border-color: var(--primary); }
        
        .btn-send { 
            background: var(--primary); color: white; border: none; 
            border-radius: 50%; width: 45px; height: 45px; cursor: pointer; 
            display: flex; align-items: center; justify-content: center; 
            font-size: 1.2rem; transition: 0.2s;
        }
        .btn-send:hover { background: var(--primary-hover); transform: scale(1.05); }
        
        /* File Upload Button */
        .file-upload-label {
            cursor: pointer; padding: 8px; border-radius: 50%; color: var(--text-muted); transition: 0.2s;
        }
        .file-upload-label:hover { background: rgba(255,255,255,0.1); color: white; }
        #file-input { display: none; }

        /* SLIDE-OUT PANEL */
        .slide-panel {
            position: fixed; top: 0; right: 0; width: 100%; max-width: 350px; height: 100%;
            background: var(--bg-panel); border-left: 1px solid var(--border);
            box-shadow: -10px 0 25px rgba(0,0,0,0.5); z-index: 50;
            transform: translateX(100%); transition: transform 0.3s ease-in-out;
            display: flex; flex-direction: column;
        }
        .slide-panel.open { transform: translateX(0); }
        .panel-header { 
            padding: 15px; border-bottom: 1px solid var(--border); 
            display: flex; justify-content: space-between; align-items: center; 
            background: rgba(30, 41, 59, 0.5); 
        }
        .panel-header h3 { color: white; margin: 0; }
        
        .panel-content { flex: 1; overflow-y: auto; padding: 20px; }
        .overlay { 
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); 
            z-index: 40; display: none; backdrop-filter: blur(2px);
        }
        .overlay.open { display: block; }

        /* Panel Components */
        .panel-img { width: 100%; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 15px; }
        .info-group { margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border); }
        .info-label { font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px; }
        .info-value { font-size: 0.95rem; color: white; font-weight: 500; word-break: break-word; }
        
        .admin-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
        .btn-action { padding: 10px; border-radius: 6px; font-weight: 600; text-align: center; text-decoration: none; font-size: 0.9rem; cursor: pointer; border: none; width: 100%; }
        
        .btn-approve { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.4); }
        .btn-reject { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4); }
        .btn-reset { background: rgba(148, 163, 184, 0.2); color: #cbd5e1; grid-column: 1 / -1; border: 1px solid var(--border); }
        .btn-toggle-sens { background: rgba(251, 191, 36, 0.1); color: #fbbf24; grid-column: 1 / -1; margin-top:5px; font-size: 0.8rem; border: 1px solid rgba(251, 191, 36, 0.3); }

        .closed-notice { 
            background: var(--bg-panel); color: var(--text-muted); 
            text-align: center; padding: 20px; border-top: 1px solid var(--border); font-size: 0.9rem; 
        }
    </style>
</head>
<body>

<div class="chat-header">
    <div>
        <h2 class="header-title"><?php echo htmlspecialchars($claim_data['title']); ?></h2>
        <span class="header-subtitle">Case #<?php echo $claim_id; ?></span>
    </div>
    <div class="header-actions">
        <button class="btn-details" onclick="toggleDetails()">ⓘ Details</button>
        <a href="<?php echo ($user_role == 'admin') ? 'admin_claims.php' : 'index.php'; ?>" class="btn-close">Close</a>
    </div>
</div>

<div class="status-banner bg-<?php echo $claim_data['claim_status']; ?>">
    <?php echo ucfirst($claim_data['claim_status']); ?>
</div>

<div class="chat-box" id="chatBox"></div>

<?php if($claim_data['claim_status'] == 'pending'): ?>
    <form class="input-area" method="POST" action="" enctype="multipart/form-data">
        <label for="file-input" class="file-upload-label">
            📎
        </label>
        <input id="file-input" type="file" name="attachment" accept="image/*" onchange="alert('Image selected! Press Send.');">
        
        <input type="text" name="message" class="input-field" placeholder="Type a message..." autocomplete="off">
        <button type="submit" class="btn-send">➤</button>
    </form>
<?php else: ?>
    <div class="closed-notice">Case Closed. Re-open to chat.</div>
<?php endif; ?>


<div class="overlay" id="overlay" onclick="toggleDetails()"></div>
<div class="slide-panel" id="sidePanel">
    <div class="panel-header">
        <h3 style="margin:0; font-size:1.1rem;">Case Details</h3>
        <button onclick="toggleDetails()" style="background:none; border:none; color:white; font-size:1.5rem; cursor:pointer;">&times;</button>
    </div>
    
    <div class="panel-content">
        <?php if ($user_role == 'admin'): ?>
            <div class="info-label">Admin Actions</div>
            <div class="admin-actions">
                <?php if($claim_data['claim_status'] == 'pending'): ?>
                    <a href="?claim_id=<?php echo $claim_id; ?>&item_id=<?php echo $claim_data['item_id']; ?>&action=approve" class="btn-action btn-approve" onclick="return confirm('Approve?');">✓ Approve</a>
                    <a href="?claim_id=<?php echo $claim_id; ?>&item_id=<?php echo $claim_data['item_id']; ?>&action=reject" class="btn-action btn-reject" onclick="return confirm('Reject?');">✗ Reject</a>
                <?php else: ?>
                    <a href="?claim_id=<?php echo $claim_id; ?>&item_id=<?php echo $claim_data['item_id']; ?>&action=reset" class="btn-action btn-reset" onclick="return confirm('Reset?');">↺ Re-open Case</a>
                <?php endif; ?>
                
                <a href="?claim_id=<?php echo $claim_id; ?>&item_id=<?php echo $claim_data['item_id']; ?>&action=toggle_sensitive&current_val=<?php echo $claim_data['is_sensitive']; ?>" class="btn-action btn-toggle-sens">
                    <?php echo $claim_data['is_sensitive'] ? '🔓 Make Public' : '🔒 Make Sensitive'; ?>
                </a>
            </div>
        <?php endif; ?>

        <div class="info-label">Item Image</div>
        <?php if($claim_data['image_path'] && (!$claim_data['is_sensitive'] || $user_role == 'admin')): ?>
            <img src="<?php echo $claim_data['image_path']; ?>" class="panel-img">
        <?php else: ?>
            <div style="padding:20px; background:rgba(255,255,255,0.05); text-align:center; border-radius:8px; margin-bottom:15px; font-size:0.9rem; color:var(--text-muted); border:1px solid var(--border);">
                <?php echo $claim_data['is_sensitive'] ? '🔒 Hidden (Sensitive)' : 'No Image Uploaded'; ?>
            </div>
        <?php endif; ?>

        <div class="info-group">
            <div class="info-label">Description</div>
            <div class="info-value"><?php echo htmlspecialchars($claim_data['description']); ?></div>
        </div>

        <?php if ($user_role == 'admin'): ?>
            <div style="margin-top:20px; padding-top:15px; border-top:2px dashed var(--border);">
                <h4 style="margin:0 0 10px 0; color:#818cf8;">Claimant Info</h4>
                
                <div class="info-group">
                    <div class="info-label">Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($claim_data['full_name']); ?></div>
                </div>

                <div class="info-group">
                    <div class="info-label">Student ID</div>
                    <div class="info-value"><?php echo htmlspecialchars($claim_data['student_id']); ?></div>
                </div>

                <div class="info-group">
                    <div class="info-label">Email</div>
                    <div class="info-value">
                        <a href="mailto:<?php echo htmlspecialchars($claim_data['email']); ?>" style="color:#818cf8; text-decoration:none;">
                            <?php echo htmlspecialchars($claim_data['email']); ?>
                        </a>
                    </div>
                </div>

                <div class="info-group">
                    <div class="info-label">Mobile</div>
                    <div class="info-value">
                        <a href="tel:<?php echo htmlspecialchars($claim_data['mobile']); ?>" style="color:#818cf8; text-decoration:none; font-weight:600;">
                            <?php echo htmlspecialchars($claim_data['mobile']); ?>
                        </a>
                    </div>
                </div>

                <div class="info-group">
                    <div class="info-label">Department</div>
                    <div class="info-value"><?php echo htmlspecialchars($claim_data['department']); ?></div>
                </div>

                <div class="info-group">
                    <div class="info-label">Year of Study</div>
                    <div class="info-value"><?php echo htmlspecialchars($claim_data['year_study']); ?></div>
                </div>

                <div class="info-group">
                    <div class="info-label">Address / Hostel</div>
                    <div class="info-value"><?php echo htmlspecialchars($claim_data['address']); ?></div>
                </div>

            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleDetails() {
        document.getElementById('sidePanel').classList.toggle('open');
        document.getElementById('overlay').classList.toggle('open');
    }

    let firstLoad = true;
    function loadMessages() {
        fetch('fetch_chat.php?claim_id=<?php echo $claim_id; ?>')
        .then(response => response.text())
        .then(data => {
            const chatBox = document.getElementById("chatBox");
            if (chatBox.innerHTML !== data) {
                const isAtBottom = chatBox.scrollHeight - chatBox.scrollTop <= chatBox.clientHeight + 100;
                chatBox.innerHTML = data;
                if(firstLoad || isAtBottom) { chatBox.scrollTop = chatBox.scrollHeight; firstLoad = false; }
            }
        });
    }
    loadMessages();
    setInterval(loadMessages, 2000);
</script>

</body>
</html>