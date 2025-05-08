<?php
session_start();
include("../Functions/db_connection.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('location:login.php');
    exit();
}

// Get full job application history
$appQuery = "SELECT a.applicationID, u.fullName, j.jobTitle, a.applicationDate, a.Status1
             FROM jobapplications a
             LEFT JOIN joblist j ON a.jobID = j.jobID
             LEFT JOIN jobseeker_profiles u ON a.user_ID = u.user_ID
             ORDER BY a.applicationDate DESC";

$appResult = $conn->query($appQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application Log - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="../For_design/adminS.css" rel="stylesheet">
    <style>
       body {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    background-size: 400% 400%;
    animation: gradientBG 10s ease infinite;
    font-family: 'Poppins', sans-serif;
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

<!-- Admin Navbar -->
<nav class="navbar navbar-expand-lg navbar-light" style="background-color: rgba(78, 115, 223, 0.95);">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_dashboard.php"><i class="fas fa-user-shield me-2"></i>Admin Panel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="manage_users.php"><i class="fas fa-users-cog me-1"></i> Manage Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_jobs.php"><i class="fas fa-briefcase me-1"></i> Manage Jobs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="application_log.php"><i class="fas fa-file-alt me-1"></i> Application Activity</a>
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

<!-- Page Content -->
<div class="container mt-5">
    <h1 class="mb-4">Full Application History</h1>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Job Applications</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>Application ID</th>
                        <th>Jobseeker Name</th>
                        <th>Job Title</th>
                        <th>Application Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($appResult && $appResult->num_rows > 0): ?>
                        <?php while ($row = $appResult->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['applicationID']) ?></td>
                                <td><?= htmlspecialchars($row['fullName']) ?></td>
                                <td><?= htmlspecialchars($row['jobTitle']) ?></td>
                                <td><?= date('M d, Y', strtotime($row['applicationDate'])) ?></td>
                                <td><?= htmlspecialchars($row['Status1']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No applications found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
