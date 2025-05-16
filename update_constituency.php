<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "password"; 
$dbname = "voting_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if necessary data is provided via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['candidate_id'], $_POST['constituency_id'], $_POST['experience'], $_POST['party_id'])) {
    $candidate_id = $_POST['candidate_id'];
    $constituency_id = $_POST['constituency_id'];
    $experience = $_POST['experience'];
    $party_id = $_POST['party_id'];

    // Update the candidate's party and experience in the candidates table
    $update_query = "UPDATE candidates SET party_id = ?, experience = ? WHERE candidate_id = ?";
    $stmt_update = $conn->prepare($update_query);
    $stmt_update->bind_param("sss", $party_id, $experience, $candidate_id);

    if ($stmt_update->execute()) {
        // Update the competes_in table based on constituency_id
        $update_competes_query = "UPDATE competes_in SET party_id = ? WHERE constituency_id = ?";
        $stmt_competes = $conn->prepare($update_competes_query);
        $stmt_competes->bind_param("ss", $party_id, $constituency_id);

        if ($stmt_competes->execute()) {
            // After successful update, redirect to constituency admin page
            header("Location: constituency_admin.php");
            exit(); // Make sure to exit to stop further script execution
        } else {
            echo "Error updating competes_in table: " . $conn->error;
        }
    } else {
        echo "Error updating candidate: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
