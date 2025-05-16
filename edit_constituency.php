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

// Check if candidate_id is provided in the URL
if (!isset($_GET['candidate_id'])) {
    echo "Invalid request.";
    exit();
}

$candidate_id = $_GET['candidate_id'];

// Fetch current candidate details, including experience and current party
$query = "SELECT candidates.candidate_id, voters.first_name, voters.last_name, candidates.constituency_id, constituencies.constituency_name, voters.address, candidates.experience, candidates.party_id 
          FROM candidates
          JOIN constituencies ON candidates.constituency_id = constituencies.constituency_id
          JOIN voters ON candidates.voter_id = voters.voter_id
          WHERE candidates.candidate_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $candidate = $result->fetch_assoc();
    $first_name = $candidate['first_name'];
    $last_name = $candidate['last_name'];
    $constituency_id = $candidate['constituency_id'];
    $constituency_name = $candidate['constituency_name'];
    $address = $candidate['address'];
    $experience = $candidate['experience'];
    $party_id = $candidate['party_id'];
} else {
    echo "Candidate not found.";
    exit();
}

// Fetch parties excluding those already registered in the same constituency
$parties_query = "SELECT party_id, party_name 
                  FROM parties
                  WHERE party_id NOT IN (
                      SELECT party_id 
                      FROM candidates 
                      WHERE constituency_id = ?
                  )";
$stmt_parties = $conn->prepare($parties_query);
$stmt_parties->bind_param("s", $constituency_id);
$stmt_parties->execute();
$parties_result = $stmt_parties->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Candidate</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/edit_candidate.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Online Voting Management System</a>
    </nav>

    <div class="container form-container">
        <h1>Edit Candidate: <?php echo htmlspecialchars($first_name . " " . $last_name); ?></h1>
        
        <!-- Display candidate's photo or a default icon -->
        <div class="text-center mb-4">
            <div class="candidate-photo">
                <i class="bi bi-person-fill"></i> <!-- Default user icon from Bootstrap Icons -->
            </div>
        </div>

        <form action="update_constituency.php" method="POST">
            <input type="hidden" name="candidate_id" value="<?php echo htmlspecialchars($candidate_id); ?>">
            <input type="hidden" name="constituency_id" value="<?php echo htmlspecialchars($constituency_id); ?>">

            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" disabled>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" disabled>
            </div>

            <div class="form-group">
                <label for="party">Party</label>
                <select class="form-control" id="party" name="party_id" required>
                    <?php while($party = $parties_result->fetch_assoc()): ?>
                        <option value="<?php echo $party['party_id']; ?>" <?php echo ($party['party_id'] == $party_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($party['party_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Experience field as text input -->
            <div class="form-group">
                <label for="experience">Experience</label>
                <input type="number" class="form-control" id="experience" name="experience" value="<?php echo htmlspecialchars($experience); ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Update Candidate</button>
            <button type="button" class="btn btn-secondary ml-2" onclick="window.location.href='constituency_admin.php'">Cancel</button>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 
