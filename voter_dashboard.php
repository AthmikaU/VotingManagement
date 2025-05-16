<!-- voter_dashboard.php -->
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

if (!isset($_SESSION['voter_id'])) {
    header("Location: login.php");
    exit();
}

$voter_id = $_SESSION['voter_id'];

$query = "SELECT first_name, last_name, address, phone FROM voters WHERE voter_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $voter_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $voter = $result->fetch_assoc();
    $first_name = $voter['first_name'];
    $last_name = $voter['last_name'];
    $address = $voter['address'];
    $phone = $voter['phone'];
} else {
    echo "Voter not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/voter_dashboard.css">
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
        <h1 class="text-center">Welcome, <?php echo htmlspecialchars("$first_name $last_name"); ?>!</h1>
        <div class="profile-container">
            <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons/icons/person-circle.svg" alt="Profile Icon">
            <div class="profile-details">
                <p><strong>Voter ID:</strong> <?php echo htmlspecialchars($voter_id); ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars("$first_name $last_name"); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($address); ?></p>
                <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($phone); ?></p>
                <button class="btn btn-primary btn-edit-profile" onclick="location.href='edit_profile.php'">Edit Profile</button>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-success" onclick="location.href='ballot.php'">Vote Now</button>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
