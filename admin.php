<?php
$servername = "localhost";
$username = "root";
$password = "password";
$dbname = "voting_system";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check for database connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start the session for logout functionality
session_start();

// Handle reset votes logic
if (isset($_POST['reset_votes'])) {
    // Begin a transaction to handle multiple updates
    $conn->begin_transaction();

    try {
        // Reset votes in candidates table
        $resetVotesQuery = "UPDATE candidates SET votes_received = 0";
        if ($conn->query($resetVotesQuery) === FALSE) {
            throw new Exception("Error resetting votes: " . $conn->error);
        }

        
        $resetVotersQuery = "UPDATE voters SET has_voted = 0";
        if ($conn->query($resetVotersQuery) === FALSE) {
            throw new Exception("Error resetting voters: " . $conn->error);
        }

        // Delete all entries in winners table to reset results
        $resetWinnersQuery = "DELETE FROM winners";
        if ($conn->query($resetWinnersQuery) === FALSE) {
            throw new Exception("Error clearing winners: " . $conn->error);
        }

        // Commit the transaction
        $conn->commit();

        echo "<div class='alert alert-success'>Votes, voters, and winners have been reset successfully!</div>";
    } catch (Exception $e) {
        // Rollback the transaction in case of any error
        $conn->rollback();
        echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
    }
}

// Handle publishing results logic using the stored procedure
if (isset($_POST['publish_results'])) {
    // Call the stored procedure to populate the winners table
    $result = $conn->query("CALL publish_results();");

    if ($result === TRUE) {
        echo "<div class='alert alert-success'>Results have been published successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error publishing results: " . $conn->error . "</div>";
    }
}

// Handle logout logic
if (isset($_POST['logout'])) {
    // Destroy the session to log the user out
    session_destroy();
    header("Location: login.php"); // Redirect to login page after logout
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Election</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/admin.css">
</head>
<body>
    <div class="container center-buttons">
        <h1 class="text-center mb-4">Admin Panel - Election</h1>

        <!-- Reset Votes Button -->
        <form method="POST" action="">
            <button type="submit" name="reset_votes" class="btn btn-warning btn-custom mb-3">Reset Votes</button>
        </form>

        <!-- Publish Results Button -->
        <form method="POST" action="">
            <button type="submit" name="publish_results" class="btn btn-green btn-custom mb-3">Publish Results</button>
        </form>

        <!-- Logout Button -->
        <form method="POST" action="">
            <button type="submit" name="logout" class="btn btn-danger btn-custom mb-3">Logout</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


<!-- Reset Votes Trigger -->
<!--  

DELIMITER $$

CREATE TRIGGER reset_votes_trigger 
AFTER UPDATE ON candidates
FOR EACH ROW
BEGIN
    -- Check if all candidates have their votes reset to 0
    IF NEW.votes_received = 0 THEN
        -- Reset has_voted flag for all voters
        UPDATE voters SET has_voted = 0;
    END IF;
END$$

DELIMITER ;

-->


<!-- Publish Results : Stored Procudeur:: -->
<!-- 
DELIMITER $$

CREATE PROCEDURE publish_results()
BEGIN
    -- Insert the winning candidate for each constituency into the winners table
    INSERT INTO winners (constituency_id, winner_name, party_name)
    SELECT 
        c.constituency_id, 
        CONCAT(v.first_name, ' ', v.last_name) AS winner_name, 
        p.party_name
    FROM 
        candidates c
        JOIN voters v ON c.voter_id = v.voter_id
        JOIN parties p ON c.party_id = p.party_id
    WHERE c.votes_received = (
        SELECT MAX(c2.votes_received) 
        FROM candidates c2 
        WHERE c2.constituency_id = c.constituency_id
    )
    GROUP BY c.constituency_id, p.party_name, v.first_name, v.last_name; 
END$$

DELIMITER ; 
-->
