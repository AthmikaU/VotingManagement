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

if (!isset($_SESSION['party_id'])) {
    header("Location: login.php");
    exit();
}

$party_id = $_SESSION['party_id'];
$query = "SELECT party_name, party_image FROM parties WHERE party_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $party_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $party = $result->fetch_assoc();
    $party_name = $party['party_name'];
    $party_image = $party['party_image']; // Fetch the party image path
} else {
    echo "Party not found.";
    exit();
}

// Fetch candidates for the party, including experience instead of address
$candidates_query = "SELECT candidates.candidate_id, voters.first_name, voters.last_name, candidates.constituency_id, constituencies.constituency_name, candidates.experience 
                     FROM candidates
                     JOIN constituencies ON candidates.constituency_id = constituencies.constituency_id
                     JOIN voters ON candidates.voter_id = voters.voter_id
                     WHERE candidates.party_id = ?";
$candidates_stmt = $conn->prepare($candidates_query);
$candidates_stmt->bind_param("i", $party_id);
$candidates_stmt->execute();
$candidates_result = $candidates_stmt->get_result();

// Fetch constituencies where the party has not yet participated
$constituencies_query = "SELECT constituency_id, constituency_name 
                         FROM constituencies 
                         WHERE constituency_id NOT IN (
                             SELECT constituency_id FROM candidates WHERE party_id = ?
                         )";
$constituencies_stmt = $conn->prepare($constituencies_query);
$constituencies_stmt->bind_param("i", $party_id);
$constituencies_stmt->execute();
$constituencies_result = $constituencies_stmt->get_result();

// Handle adding a new candidate
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_candidate'])) {
    $voter_id = $_POST['voter_id'];
    $constituency_id = $_POST['constituency_id'];
    $experience = $_POST['experience'];

    // Check if the party has already a candidate in the chosen constituency
    $check_query = "SELECT * FROM candidates WHERE voter_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $voter_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>alert('This party/candidate has already participated in this constituency.');</script>";
    } else {
        // Insert new candidate
        $insert_query = "INSERT INTO candidates (voter_id, party_id, constituency_id, experience) 
                         VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("ssss", $voter_id, $party_id, $constituency_id, $experience);
        $insert_stmt->execute();

        // Insert into competes_in table
        // $competes_query = "INSERT INTO competes_in (constituency_id, party_id) VALUES (?, ?)";
        // $competes_stmt = $conn->prepare($competes_query);
        // $competes_stmt->bind_param("ii", $constituency_id, $party_id);
        // $competes_stmt->execute();

        echo "<script>alert('Candidate added successfully.');</script>";
    }
}

// Handle deleting a candidate
if (isset($_GET['delete_candidate_id'])) {
    $candidate_id = $_GET['delete_candidate_id'];

    // Delete the candidate from the database
    $delete_query = "DELETE FROM candidates WHERE candidate_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("s", $candidate_id);
    $delete_stmt->execute();
    echo "<script>alert('Candidate deleted successfully.'); window.location.href = 'party.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Party Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/party.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" style="font-size: 24px; font-weight: bold;">Online Voting Management System</a>
        <div class="ml-auto">
            <form action="logout.php" method="POST">
                <button type="submit" class="btn btn-danger"><i class="bi bi-box-arrow-right"></i> Logout</button>
            </form>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Party Info Section -->
        <h1 class="text-center">Welcome, <?php echo htmlspecialchars($party_name); ?> !</h1>
        <div class="profile-container">
            <!-- Display party-specific image or default BI icon -->
            <?php if ($party_image): ?>
                <img src="<?php echo htmlspecialchars($party_image); ?>" alt="Party Icon">
            <?php else: ?>
                <i class="bi bi-house-door"></i> <!-- Default BI Icon if no image is set -->
            <?php endif; ?>
            <div class="profile-details">
                <p><strong>Party ID:</strong> <?php echo htmlspecialchars($party_id); ?></p>
                <p><strong>Party Name:</strong> <?php echo htmlspecialchars($party_name); ?></p>
            </div>
        </div>

        <!-- Candidates Table Section -->
        <div class="candidate-table">
            <h3>List of Candidates:</h3>
            <?php if ($candidates_result->num_rows > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Candidate ID</th>
                            <th>Name</th>
                            <th>Constituency</th>
                            <th>Experience (Years)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($candidate = $candidates_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($candidate['candidate_id']); ?></td>
                                <td><?php echo htmlspecialchars($candidate['first_name'] . " " . $candidate['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($candidate['constituency_name']); ?></td>
                                <td><?php echo htmlspecialchars($candidate['experience']); ?> years</td>
                                <td>
                                    <a href="edit_candidate.php?candidate_id=<?php echo htmlspecialchars($candidate['candidate_id']); ?>" class="btn btn-warning btn-sm">
                                        Edit
                                    </a>
                                    <a href="?delete_candidate_id=<?php echo htmlspecialchars($candidate['candidate_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this candidate?')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-warning">No candidates found for this party.</div>
            <?php endif; ?>
        </div>

        <!-- Add Candidate Button -->
        <div class="mt-4 text-center">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCandidateModal">
                Add Candidate
            </button>
        </div>

        <!-- Add Candidate Modal -->
        <div class="modal fade" id="addCandidateModal" tabindex="-1" role="dialog" aria-labelledby="addCandidateModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCandidateModalLabel">Add Candidate</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="voter_id">Voter ID</label>
                                <input type="text" class="form-control" id="voter_id" name="voter_id" required>
                            </div>
                            <div class="form-group">
                                <label for="constituency_id">Constituency</label>
                                <select class="form-control" id="constituency_id" name="constituency_id" required>
                                    <?php while ($constituency = $constituencies_result->fetch_assoc()): ?>
                                        <option value="<?php echo $constituency['constituency_id']; ?>"><?php echo $constituency['constituency_name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="experience">Experience (Years)</label>
                                <input type="number" class="form-control" id="experience" name="experience" required>
                            </div>
                            <button type="submit" name="add_candidate" class="btn btn-success">Add Candidate</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
