<?php
/* File: claim.php (With Strict Warning Notice) */
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['item_id'])) {
    header("Location: index.php");
    exit();
}

$item_id = intval($_GET['item_id']);
$user_id = $_SESSION['user_id'];

// Check if already claimed by this user
$check = $conn->query("SELECT id FROM claims WHERE item_id=$item_id AND claimant_id=$user_id");
if ($check->num_rows > 0) {
    // Redirect to the chat if already claimed
    $claim_row = $check->fetch_assoc();
    header("Location: claim_chat.php?claim_id=" . $claim_row['id']);
    exit();
}

// Handle New Claim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->query("INSERT INTO claims (item_id, claimant_id, status) VALUES ($item_id, $user_id, 'pending')");
    $claim_id = $conn->insert_id;
    
    // Auto-send first message
    $msg = "System: New claim started. Please provide proof of ownership.";
    $conn->query("INSERT INTO messages (claim_id, sender_id, message_text) VALUES ($claim_id, $user_id, '$msg')"); // Sender is user initially
    
    header("Location: claim_chat.php?claim_id=$claim_id");
    exit();
}

$item = $conn->query("SELECT * FROM items WHERE id=$item_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Claim Item</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; padding: 20px; display: flex; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: white; width: 100%; max-width: 500px; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); height: fit-content; }
        
        .item-preview { display: flex; gap: 15px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #e5e7eb; }
        .thumb { width: 80px; height: 80px; border-radius: 8px; object-fit: cover; background: #f1f5f9; }
        .item-info h2 { margin: 0 0 5px 0; font-size: 1.1rem; color: #111827; }
        .item-info p { margin: 0; color: #6b7280; font-size: 0.9rem; }

        .warning-box {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #ef4444;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
        }
        .warning-title { color: #991b1b; font-weight: 700; display: flex; align-items: center; gap: 8px; margin-bottom: 5px; font-size: 0.95rem; }
        .warning-text { color: #7f1d1d; font-size: 0.85rem; line-height: 1.5; margin: 0; }

        .btn-claim { width: 100%; padding: 12px; background: #4f46e5; color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: 0.2s; }
        .btn-claim:hover { background: #4338ca; }
        
        .cancel-link { display: block; text-align: center; margin-top: 15px; color: #6b7280; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="card">
    <h1 style="margin-top:0;">Claim Item</h1>
    
    <div class="item-preview">
        <?php if($item['is_sensitive']): ?>
            <div class="thumb" style="display:flex; align-items:center; justify-content:center; font-size:1.5rem;">🔒</div>
        <?php elseif($item['image_path']): ?>
            <img src="<?php echo $item['image_path']; ?>" class="thumb">
        <?php else: ?>
            <div class="thumb" style="display:flex; align-items:center; justify-content:center; color:#ccc;">No Img</div>
        <?php endif; ?>
        
        <div class="item-info">
            <h2><?php echo htmlspecialchars($item['title']); ?></h2>
            <p><?php echo $item['is_sensitive'] ? "Sensitive Item (Hidden)" : htmlspecialchars($item['description']); ?></p>
        </div>
    </div>

    <div class="warning-box">
        <div class="warning-title">
            <span>⚠️</span> WARNING: OFFICIAL NOTICE
        </div>
        <p class="warning-text">
            By proceeding, you declare that you are the rightful owner of this item.
            <br><br>
            <strong>False claims are a violation of Campus Conduct Rules.</strong>
            <br>
            If you are caught filing a fake claim, your Student ID will be reported to the Disciplinary Committee for immediate suspension or strict action.
        </p>
    </div>

    <form method="POST">
        <button type="submit" class="btn-claim">I Understand & Claim Item</button>
    </form>
    
    <a href="index.php" class="cancel-link">Cancel</a>
</div>

</body>
</html>