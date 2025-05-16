<?php
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

// Initialize variables
$role = $voter_id = $first_name = $last_name = $party_id = $constituency_id = $password = '';
$error = '';

// Start session
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'];

    // Admin login (hardcoded password)
    if ($role === 'admin') {
        $password = $conn->real_escape_string($_POST['password']);

        // Check the fixed admin password
        if ($password === 'admin123') { // Set your fixed admin password here
            $_SESSION['role'] = 'admin';
            header("Location: admin.php");
            exit;
        } else {
            $error = "Invalid Admin credentials.";
        }
    }

    // Voter login
    elseif ($role === 'voter') {
        $voter_id = $conn->real_escape_string($_POST['voter_id']);
        $first_name = $conn->real_escape_string($_POST['first_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $password = $conn->real_escape_string($_POST['password']);

        if ($voter_id && $first_name && $last_name && $password) {
            $query = "SELECT * FROM voters WHERE voter_id = ? AND first_name = ? AND last_name = ? AND password = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $voter_id, $first_name, $last_name, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $_SESSION['voter_id'] = $voter_id;
                header("Location: voter_dashboard.php");
                exit;
            } else {
                $error = "Invalid Voter credentials.";
            }
        } else {
            $error = "Please fill in all fields.";
        }
    }

    // Party login
    elseif ($role === 'party') {
        $party_id = $conn->real_escape_string($_POST['party_id']);
        $password = $conn->real_escape_string($_POST['password']);

        if ($party_id && $password) {
            $query = "SELECT * FROM parties WHERE party_id = ? AND password = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $party_id, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $_SESSION['party_id'] = $party_id;
                header("Location: party.php");
                exit;
            } else {
                $error = "Invalid Party credentials.";
            }
        } else {
            $error = "Please fill in all fields.";
        }
    }

    // Constituency login
    elseif ($role === 'constituency') {
        $constituency_id = $conn->real_escape_string($_POST['constituency_id']);
        $password = $conn->real_escape_string($_POST['password']);

        if ($constituency_id && $password) {
            $query = "SELECT * FROM constituencies WHERE constituency_id = ? AND password = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $constituency_id, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $_SESSION['constituency_id'] = $constituency_id;
                header("Location: constituency_admin.php");
                exit;
            } else {
                $error = "Invalid Constituency credentials.";
            }
        } else {
            $error = "Please fill in all fields.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/login.css">
</head>
<body>
    <div class="container">
        <div class="login-card">
            <h1 class="text-center mb-4">Login</h1>

            <!-- Nav Tabs for Role Selection -->
            <ul class="nav nav-pills mb-3">
                <li class="nav-item">
                    <a class="nav-link active" id="voter-tab" href="#voter" data-bs-toggle="pill">Voter</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="party-tab" href="#party" data-bs-toggle="pill">Party</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="constituency-tab" href="#constituency" data-bs-toggle="pill">Constituency</a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content mt-4">
                <!-- Voter Tab -->
                <div class="tab-pane fade show active" id="voter">
                    <form method="POST" action="">
                        <input type="hidden" name="role" value="voter">
                        <div class="mb-3">
                            <label for="voter_id" class="form-label">Voter ID</label>
                            <input type="text" class="form-control" id="voter_id" name="voter_id" required>
                        </div>
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>

                <!-- Party Tab -->
                <div class="tab-pane fade" id="party">
                    <form method="POST" action="">
                        <input type="hidden" name="role" value="party">
                        <div class="mb-3">
                            <label for="party_id" class="form-label">Party ID</label>
                            <input type="text" class="form-control" id="party_id" name="party_id" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>

                <!-- Constituency Tab -->
                <div class="tab-pane fade" id="constituency">
                    <form method="POST" action="">
                        <input type="hidden" name="role" value="constituency">
                        <div class="mb-3">
                            <label for="constituency_id" class="form-label">Constituency ID</label>
                            <input type="text" class="form-control" id="constituency_id" name="constituency_id" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="alert alert-danger mt-3">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Modal Button -->
        <div class="text-center mt-4">
            <div class="view-results-btn">
                <a href="results.php" class="btn">View Results</a>
            </div>
            <div class="admin-btn">
                <button class="btn" data-bs-toggle="modal" data-bs-target="#adminModal">Admin Login</button>
            </div>
        </div>

        <!-- Admin Modal -->
        <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel">Admin Login</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="">
                            <input type="hidden" name="role" value="admin">
                            <div class="mb-3">
                                <label for="admin-password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="admin-password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
