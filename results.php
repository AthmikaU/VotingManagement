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

// Fetch constituencies with error checking
$query = "SELECT * FROM constituencies";
$result = $conn->query($query);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/results.css">
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center mb-4">Election Results</h1>

        <?php if ($result->num_rows > 0): ?>
            <div class="row">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card constituency-card">
                            <div class="card-body">
                                <h5 class="card-title">Constituency: <?php echo $row['constituency_name']; ?></h5>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#resultModal<?php echo $row['constituency_id']; ?>">View Results</button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for displaying results -->
                    <div class="modal fade" id="resultModal<?php echo $row['constituency_id']; ?>" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="resultModalLabel">Results for <?php echo $row['constituency_name']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <?php
                                        // Fetch winner for the constituency from the winners table
                                        $winnerQuery = "SELECT winner_name, party_name 
                                                        FROM winners 
                                                        WHERE constituency_id = ?";
                                        $stmt = $conn->prepare($winnerQuery);
                                        $stmt->bind_param("i", $row['constituency_id']);
                                        $stmt->execute();
                                        $winnerResult = $stmt->get_result();
                                        $winner = $winnerResult->fetch_assoc();
                                    ?>

                                    <?php if ($winner): ?>
                                        <?php if ($winner['winner_name'] === 'No election conducted here'): ?>
                                            <div class="no-election">
                                                <?php echo $winner['winner_name']; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="winner-info">
                                                <h4>Winner:</h4>
                                                <p><strong>Name:</strong> <?php echo $winner['winner_name']; ?></p>
                                                <p><strong>Party:</strong> <?php echo $winner['party_name']; ?></p>
                                            </div>

                                            <h4 class="mt-4">Participating Parties:</h4>
                                            <?php
                                                // Fetch all parties for the constituency from the winners table
                                                $partiesQuery = "SELECT DISTINCT party_name 
                                                                FROM winners 
                                                                WHERE constituency_id = ?";
                                                $partiesStmt = $conn->prepare($partiesQuery);
                                                $partiesStmt->bind_param("i", $row['constituency_id']);
                                                $partiesStmt->execute();
                                                $partiesResult = $partiesStmt->get_result();
                                            ?>
                                            <ul>
                                                <?php while ($party = $partiesResult->fetch_assoc()): ?>
                                                    <li><?php echo $party['party_name']; ?></li>
                                                <?php endwhile; ?>
                                            </ul>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p>No results have been published yet or no candidates participated.</p>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">No constituencies available.</div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
