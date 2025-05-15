<?php
include_once '../Functions/db_connection.php';

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipient_type = $_POST['recipient_type'] ?? '';
    $userID = trim($_POST['userID'] ?? '');
    $message_body = trim($_POST['message_body'] ?? '');

    if (empty($recipient_type) || empty($userID) || empty($message_body)) {
        $error = "All fields are required.";
    } else {
        $recipient_type = mysqli_real_escape_string($conn, $recipient_type);
        $userID = mysqli_real_escape_string($conn, $userID);
        $message_body = mysqli_real_escape_string($conn, $message_body);
        $dateSent = date("Y-m-d H:i:s");

        $sql = "INSERT INTO notifications (user_Id, receiverRole, message, isRead, dateSent)
                VALUES ('$userID', '$recipient_type', '$message_body', 0, '$dateSent')";

        if (mysqli_query($conn, $sql)) {
            $message = "✅ Notification sent successfully to $recipient_type (ID: $userID)";
        } else {
            $error = "❌ Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Notification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
       body {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    background-size: 400% 400%;
    animation: gradientBG 10s ease infinite;
    font-family: 'Poppins', sans-serif;
}
  .navbar-brand {
            font-weight: 600;
            color: #fff !important;
        }

        .navbar-nav .nav-link {
            color: #fff !important;
            padding: 8px 14px;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }

        @media (max-width: 768px) {
            .navbar-nav {
                flex-direction: column;
                text-align: center;
            }
        }

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.notification-form {
    max-width: 600px;
    margin: 50px auto;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    padding: 30px;
    backdrop-filter: blur(10px);
}

        .form-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .form-header i {
            font-size: 2rem;
            color: #4e73df;
        }
        .alert {
            margin-bottom: 20px;
        }
        button[type="submit"] {
            width: 100%;
        }
    </style>
</head>
<body>
    
<nav class="navbar navbar-expand-lg navbar-light" style="background-color: rgba(78, 115, 223, 0.95);">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php"><i class="fas fa-user-shield me-2"></i>Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="managementDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true">
                            <i class="fas fa-briefcase me-1"></i> Management
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="managementDropdown">
                            <li><a class="dropdown-item" href="admin_post_job.php">Manage Job</a></li>
                            <li><a class="dropdown-item" href="admin_add_category.php">Job Category</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : '' ?>" href="manage_users.php">
                            <i class="fas fa-users-cog me-1"></i> Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="application_log.php"><i class="fas fa-file-alt me-1"></i> Application Activity</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notifications.php"><i class="fas fa-bell me-1"></i> Send Notifications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="site_analytics.php"><i class="fas fa-chart-line me-1"></i> Analytics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                    </li>

                </ul>

            </div>
        </div>
    </nav>

<div class="notification-form">
    <div class="form-header">
        <i class="fas fa-paper-plane"></i>
        <h4 class="mt-2">Send Notification</h4>
        <p class="text-muted">Send a message to jobseekers or employers.</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="notifications.php">
        <div class="mb-3">
            <label for="recipient_type" class="form-label">Recipient Type</label>
            <select class="form-select" id="recipient_type" name="recipient_type" required onchange="this.form.submit()">
                <option value="">-- Select Role --</option>
                <option value="jobseeker" <?= (isset($recipient_type) && $recipient_type == 'jobseeker') ? 'selected' : '' ?>>Jobseeker</option>
                <option value="employer" <?= (isset($recipient_type) && $recipient_type == 'employer') ? 'selected' : '' ?>>Employer</option>
            </select>

<?php
// Fetch users based on recipient_type if set
$users = [];
if (!empty($recipient_type)) {
    $recipient_type_escaped = mysqli_real_escape_string($conn, $recipient_type);
    if ($recipient_type_escaped === 'jobseeker') {
        // Adjusted to use user_Id and username from jobseekers table
        $user_query = "SELECT user_Id AS userID, username AS fullName FROM jobseekers ORDER BY username";
    } elseif ($recipient_type_escaped === 'employer') {
        // Use employerID and companyName from employers table
        $user_query = "SELECT employerID AS userID, companyName AS fullName FROM employers ORDER BY companyName";
    } else {
        $user_query = "";
    }
    if (!empty($user_query)) {
        $result_users = mysqli_query($conn, $user_query);
        if ($result_users && mysqli_num_rows($result_users) > 0) {
            while ($row = mysqli_fetch_assoc($result_users)) {
                $users[] = $row;
            }
        }
    }
}
// If no users found, add a default option to mimic category select behavior
if (empty($users)) {
    $users[] = ['userID' => '', 'fullName' => '-- No users available --'];
}
?>
        <div class="mb-3">
            <label for="userID" class="form-label">User ID</label>
            <select class="form-select" id="userID" name="userID" required>
                <option value="">-- Select User --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= htmlspecialchars($user['userID']) ?>" <?= (isset($userID) && $userID == $user['userID']) ? 'selected' : '' ?>><?= htmlspecialchars($user['fullName']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="message_body" class="form-label">Message</label>
            <textarea class="form-control" id="message_body" name="message_body" rows="5" required><?= isset($message_body) ? htmlspecialchars($message_body) : ''; ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Send Notification</button>
    </form>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <?php if ($message): ?>
        <div class="toast align-items-center text-bg-success show" role="alert">
            <div class="d-flex">
                <div class="toast-body"><?= htmlspecialchars($message); ?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    <?php elseif ($error): ?>
        <div class="toast align-items-center text-bg-danger show" role="alert">
            <div class="d-flex">
                <div class="toast-body"><?= htmlspecialchars($error); ?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../For_design/jv/toast-init.js"></script>

</div>

</body>
</html>
