<?php
/* File: admin_notify_match.php (Sends "Found" Alert to Student) */
include 'db.php';
include 'mail_config.php';

// 1. Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['item_id'])) {
    $item_id = intval($_GET['item_id']);

    // 2. Fetch Item & User Details
    // We need the email of the person who reported the item as LOST
    $sql = "SELECT items.title, users.full_name, users.email 
            FROM items 
            JOIN users ON items.user_id = users.id 
            WHERE items.id = $item_id";
    
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row['email'];
        $name = $row['full_name'];
        $item_title = $row['title'];

        // 3. Prepare Email Content
        $subject = "Good News! Potential Match for your $item_title";
        
        $email_body = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f3f4f6;'>
                <div style='background-color: white; padding: 30px; border-radius: 10px; max-width: 500px; margin: 0 auto; border: 1px solid #e5e7eb;'>
                    <h2 style='color: #4f46e5; margin-top: 0;'>Potential Match Found!</h2>
                    <p style='color: #374151;'>Hi $name,</p>
                    
                    <p style='color: #374151; line-height: 1.6;'>
                        We noticed that a <strong>Found Item</strong> has been reported recently that might match your lost item: 
                        <strong>$item_title</strong>.
                    </p>

                    <div style='background: #ecfdf5; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0; color: #065f46;'>
                        Please log in to the <strong>Campus Lost & Found</strong> portal and check the 'Found Items' list immediately.
                    </div>

                    <div style='text-align: center; margin-top: 25px;'>
                        <a href='http://{$_SERVER['HTTP_HOST']}/lostfound/login.php' style='background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;'>Check Now</a>
                    </div>
                    
                    <hr style='border: 0; border-top: 1px solid #e5e7eb; margin: 25px 0;'>
                    <p style='color: #9ca3af; font-size: 12px;'>Sent by Campus Admin</p>
                </div>
            </div>
        ";

        // 4. Send Email
        if (sendEmail($email, $subject, $email_body)) {
            echo "<script>
                alert('✅ Notification Email Sent to $name!');
                window.location.href = 'admin_dashboard.php';
            </script>";
        } else {
            echo "<script>
                alert('❌ Error: Could not send email.');
                window.location.href = 'admin_dashboard.php';
            </script>";
        }

    } else {
        echo "<script>alert('Item or User not found.'); window.location.href='admin_dashboard.php';</script>";
    }
} else {
    header("Location: admin_dashboard.php");
}
?>