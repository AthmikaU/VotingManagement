<?php
// Include database connection file
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
    // Reset votes in candidates table
    $resetVotesQuery = "UPDATE candidates SET votes_received = 0";
    if ($conn->query($resetVotesQuery) === TRUE) {
        echo "<div class='alert alert-success'>Votes have been reset successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error resetting votes: " . $conn->error . "</div>";
    }

    // Reset has_voted flag in voters table
    $resetVotersQuery = "UPDATE voters SET has_voted = 0";
    if ($conn->query($resetVotersQuery) === TRUE) {
        echo "<div class='alert alert-success'>Voter statuses have been reset!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error resetting voters: " . $conn->error . "</div>";
    }

    // Delete all entries in winners table to reset results
    $resetWinnersQuery = "DELETE FROM winners";
    if ($conn->query($resetWinnersQuery) === TRUE) {
        echo "<div class='alert alert-success'>Winners table has been cleared!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error clearing winners: " . $conn->error . "</div>";
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
    <style>
        /* Center the buttons on the page */
        .center-buttons {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full viewport height */
            flex-direction: column;
        }

        .btn-custom {
            width: 250px;
            padding: 15px;
            font-size: 18px;
            transition: all 0.3s ease; /* Smooth transition effect */
        }

        .btn-green {
            background-color: #28a745;
            border: none;
        }

        .btn-green:hover {
            background-color: #218838;
            transform: scale(1.1); /* Enlarge the button on hover */
        }

        .btn-warning {
            background-color: #ffc107;
            border: none;
        }

        .btn-warning:hover {
            background-color: #e0a800;
            transform: scale(1.1); /* Enlarge the button on hover */
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c82333;
            transform: scale(1.1); /* Enlarge the button on hover */
        }

        .alert {
            width: 80%;
            max-width: 600px;
            margin: 15px auto;
        }
    </style>
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
