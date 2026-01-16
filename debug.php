<?php
include 'db.php';

echo "<h1>Login Debugger</h1>";

if (isset($_SESSION['user_id'])) {
    echo "<h2 style='color:green'>✅ You are LOGGED IN!</h2>";
    echo "User ID: " . $_SESSION['user_id'];
} else {
    echo "<h2 style='color:red'>❌ You are LOGGED OUT.</h2>";
    echo "The Session array is empty or 'user_id' is missing.<br>";
    echo "Current Session Data: ";
    print_r($_SESSION);
}
?>