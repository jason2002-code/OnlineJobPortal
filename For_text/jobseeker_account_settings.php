<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['user_Id'])) {
    header("Location: login.php");
    exit;
}

$user_Id = $_SESSION['user_Id'];
$msg = '';
$success = '';

// Fetch current user data
$query = "SELECT username, User_email FROM jobseekers WHERE user_Id = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $user_Id);
    $stmt->execute();
    $stmt->bind_result($username, $email);
    $stmt->fetch();
    $stmt->close();
} else {
    die("Failed to fetch user data.");
}

if (isset($_POST['submit'])) {
    $new_username = trim($_POST['full_name']);
    $new_email = trim($_POST['email']);
    $current_password = $_POST['current_pass'];
    $new_password = $_POST['new_pass'];
    $confirm_password = $_POST['c_pass'];

    // Validate inputs
    if (empty($new_username) || empty($new_email)) {
        $msg = "Full name and email are required.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email format.";
    } else {
        // Check if email is used by another user
        $check_email_query = "SELECT user_Id FROM jobseekers WHERE User_email = ? AND user_Id != ?";
        if ($stmt = $conn->prepare($check_email_query)) {
            $stmt->bind_param("si", $new_email, $user_Id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $msg = "Email is already in use by another account.";
            }
            $stmt->close();
        }

        // If no error so far, proceed
        if (empty($msg)) {
            // If password fields are filled, verify current password and update
            if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
                if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                    $msg = "To change password, fill all password fields.";
                } elseif ($new_password !== $confirm_password) {
                    $msg = "New password and confirm password do not match.";
                } else {
                    // Verify current password
                    $pass_query = "SELECT User_Pass FROM jobseekers WHERE user_Id = ?";
                    if ($stmt = $conn->prepare($pass_query)) {
                        $stmt->bind_param("i", $user_Id);
                        $stmt->execute();
                        $stmt->bind_result($hashed_password);
                        $stmt->fetch();
                        $stmt->close();

                        if (!password_verify($current_password, $hashed_password)) {
                            $msg = "Current password is incorrect.";
                        }
                    } else {
                        $msg = "Failed to verify current password.";
                    }
                }
            }

            // If still no error, update user data
            if (empty($msg)) {
                if (!empty($new_password)) {
                    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE jobseekers SET username = ?, User_email = ?, User_Pass = ? WHERE user_Id = ?";
                    if ($stmt = $conn->prepare($update_query)) {
                        $stmt->bind_param("sssi", $new_username, $new_email, $new_hashed_password, $user_Id);
                        if ($stmt->execute()) {
                            $success = "Account updated successfully.";
                            $username = $new_username;
                            $email = $new_email;
                        } else {
                            $msg = "Failed to update account.";
                        }
                        $stmt->close();
                    }
                } else {
                    $update_query = "UPDATE jobseekers SET username = ?, User_email = ? WHERE user_Id = ?";
                    if ($stmt = $conn->prepare($update_query)) {
                        $stmt->bind_param("ssi", $new_username, $new_email, $user_Id);
                        if ($stmt->execute()) {
                            $success = "Account updated successfully.";
                            $username = $new_username;
                            $email = $new_email;
                        } else {
                            $msg = "Failed to update account.";
                        }
                        $stmt->close();
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Account Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../For_design/Jobseekstyle.css" />
</head>
<body class="jobseeker-dashboard">
    <div class="container mt-5">
        <h2>Account Settings</h2>
        <?php if (!empty($msg)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($msg); ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($username); ?>" required />
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required />
            </div>
            <hr />
            <h5>Change Password</h5>
            <div class="mb-3">
                <label for="current_pass" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="current_pass" name="current_pass" />
            </div>
            <div class="mb-3">
                <label for="new_pass" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_pass" name="new_pass" />
            </div>
            <div class="mb-3">
                <label for="c_pass" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="c_pass" name="c_pass" />
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Update Account</button>
            <a href="jobseeker_dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
