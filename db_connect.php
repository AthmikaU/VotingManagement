<!-- db_connect.php -->
<?php
// Replace the credentials with your database details
$servername = "localhost";
$username = "root";
$password = "password";
$dbname = "voting_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
