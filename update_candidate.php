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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['candidate_id'], $_POST['constituency'], $_POST['experience'], $_POST['party_id'])) {
        echo "Invalid request.";
        exit();
    }

    $candidate_id = $_POST['candidate_id'];
    $constituency_id = $_POST['constituency'];
    $experience = $_POST['experience'];
    $party_id = $_POST['party_id'];

    // Check if the candidate is already participating with another party
    $check_query = "SELECT party_id FROM candidates WHERE candidate_id = ? AND party_id != ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("ss", $candidate_id, $party_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo "Error: This candidate is already participating with another party.";
        exit();
    }

    // Update the candidate's constituency and experience in the candidates table
    $update_candidate_query = "UPDATE candidates SET constituency_id = ?, experience = ? WHERE candidate_id = ?";
    $stmt_update = $conn->prepare($update_candidate_query);
    $stmt_update->bind_param("sss", $constituency_id, $experience, $candidate_id);

    if ($stmt_update->execute()) {
        // Update the competes_in table with the new party_id and constituency_id
        $update_competes_query = "UPDATE competes_in SET party_id = ?, constituency_id = ? WHERE party_id = ?";
        $stmt_competes = $conn->prepare($update_competes_query);
        $stmt_competes->bind_param("sss", $party_id, $constituency_id, $party_id);

        if ($stmt_competes->execute()) {
            // Redirect to party dashboard after successful update
            header("Location: party.php");
            exit();
        } else {
            echo "Error updating competes_in table: " . $conn->error;
        }
    } else {
        echo "Error updating candidate: " . $conn->error;
    }
}

$conn->close();
?>
