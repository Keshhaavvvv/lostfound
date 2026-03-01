<?php
/*
    File: db.example.php
    Purpose: Database Connection Template
    Instructions: 
    1. Rename this file to 'db.php'
    2. Update the credentials below
*/

$servername = "localhost";
$username = "root";          // Default XAMPP username
$password = "YOUR_DB_PASSWORD_HERE"; // Leave empty for XAMPP default
$dbname = "campus_lostfound"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>