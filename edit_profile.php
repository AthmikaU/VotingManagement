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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Update profile details (address and phone)
    $new_address = isset($_POST['address']) ? $_POST['address'] : null;
    $new_phone = isset($_POST['phone']) ? preg_replace('/[^0-9]/', '', $_POST['phone']) : null;

    if ($new_phone && strlen($new_phone) > 15) {
        die("Invalid phone number. Please enter a valid phone number with up to 15 digits.");
    }

    // Update query for address and phone
    $update_query = "UPDATE voters SET address = COALESCE(?, address), phone = COALESCE(?, phone) WHERE voter_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sss", $new_address, $new_phone, $voter_id);

    if ($stmt->execute()) {
        echo "Profile updated successfully.";
    } else {
        echo "Error updating profile: " . $conn->error;
    }

    // Reset password
    if (isset($_POST['current_password']) && isset($_POST['new_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];

        // Fetch current password hash from database
        $query = "SELECT password FROM voters WHERE voter_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $voter_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $voter = $result->fetch_assoc();
            $hashed_password = $voter['password'];
            
            // Verify current password
            if (password_verify($current_password, $hashed_password)) {
                // Update password if the current password is correct
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update_password_query = "UPDATE voters SET password = ? WHERE voter_id = ?";
                $stmt = $conn->prepare($update_password_query);
                $stmt->bind_param("ss", $new_password_hash, $voter_id);
                
                if ($stmt->execute()) {
                    echo "Password updated successfully.";
                } else {
                    echo "Error updating password: " . $conn->error;
                }
            } else {
                echo "Current password is incorrect.";
            }
        } else {
            echo "Voter not found.";
        }
    }
}

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
    <title>Edit Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/edit_profile.css">
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
        <h1 class="text-center">Edit Profile</h1>
        <div class="profile-container">
            <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons/icons/person-circle.svg" alt="Profile Icon">
        </div>

        <div class="info-container">
            <p><strong>Name:</strong> <?php echo htmlspecialchars("$first_name $last_name"); ?></p>
            <p><strong>Voter ID:</strong> <?php echo htmlspecialchars($voter_id); ?></p>
            <p>
                <strong>Address:</strong>
                <span class="editable-field">
                    <span id="address-display"><?php echo htmlspecialchars($address); ?></span>
                    <i class="bi bi-pencil-square ml-2" onclick="enableEdit('address')"></i>
                    <input type="text" id="address-input" class="editable-input" value="<?php echo htmlspecialchars($address); ?>">
                    <button class="btn btn-sm btn-success save-button" id="address-save" onclick="saveField('address')">Save</button>
                    <button class="btn btn-sm btn-danger cancel-button" id="address-cancel" onclick="cancelEdit('address')">X</button>
                </span>
            </p>
            <p>
                <strong>Phone Number:</strong>
                <span class="editable-field">
                    <span id="phone-display"><?php echo htmlspecialchars($phone); ?></span>
                    <i class="bi bi-pencil-square ml-2" onclick="enableEdit('phone')"></i>
                    <input type="text" id="phone-input" class="editable-input" value="<?php echo htmlspecialchars($phone); ?>">
                    <button class="btn btn-sm btn-success save-button" id="phone-save" onclick="saveField('phone')">Save</button>
                    <button class="btn btn-sm btn-danger cancel-button" id="phone-cancel" onclick="cancelEdit('phone')">X</button>
                </span>
            </p>
        </div>

        <div class="text-center mt-4">
            <!-- Button to open modal for resetting password -->
            <button class="btn btn-warning" data-toggle="modal" data-target="#resetPasswordModal">Reset Password</button>
        </div>

        <!-- Back to Dashboard button -->
        <div class="text-center mt-4">
            <a href="voter_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>

    <!-- Modal for password reset -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="current_password">Current Password:</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password:</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-warning">Reset Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function enableEdit(field) {
            document.getElementById(`${field}-display`).style.display = 'none';
            document.getElementById(`${field}-input`).style.display = 'inline-block';
            document.getElementById(`${field}-save`).style.display = 'inline-block';
            document.getElementById(`${field}-cancel`).style.display = 'inline-block';
        }

        function cancelEdit(field) {
            document.getElementById(`${field}-display`).style.display = 'inline';
            document.getElementById(`${field}-input`).style.display = 'none';
            document.getElementById(`${field}-save`).style.display = 'none';
            document.getElementById(`${field}-cancel`).style.display = 'none';
        }

        function saveField(field) {
            const value = document.getElementById(`${field}-input`).value;
            const form = new FormData();
            form.append(field, value);

            fetch("", { method: "POST", body: form })
                .then(response => response.text())
                .then(() => {
                    document.getElementById(`${field}-display`).innerText = value;
                    cancelEdit(field);
                    alert("Profile updated successfully.");
                })
                .catch(() => alert("Error updating profile."));
        }
    </script>
</body>
</html>
