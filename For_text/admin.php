<?php
session_start();
include("../Functions/db_connection.php");

// Get recent job applications
$appQuery = "SELECT a.applicationID, u.fullName, j.jobTitle, a.applicationDate
             FROM jobapplications a
             LEFT JOIN joblist j ON a.jobID = j.jobID
             LEFT JOIN jobseeker_profiles u ON a.user_ID = u.user_ID
             ORDER BY a.applicationDate DESC
             LIMIT 5";
$appResult = $conn->query($appQuery);

// Get recent job postings
$jobQuery = "SELECT j.jobID, j.jobTitle, e.companyName, j.posted_date
             FROM joblist j
             LEFT JOIN employers e ON j.employerID = e.employerID
             ORDER BY j.posted_date DESC
             LIMIT 5";
$jobResult = $conn->query($jobQuery);

// Get recent employers
$employerQuery = "SELECT employerID, companyName, DateStab
                  FROM employers
                  ORDER BY DateStab DESC
                  LIMIT 5";
$employerResult = $conn->query($employerQuery);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Modern Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

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
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : '' ?>" href="manage_users.php">
                            <i class="fas fa-users-cog me-1"></i> Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_jobs.php"><i class="fas fa-briefcase me-1"></i> Manage Jobs</a>
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

    <!-- Dashboard Content -->
    <div class="container mt-5">
        <h1 class="mb-4">Admin Dashboard</h1>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Recent Job Applications</h5>
            </div>
            <ul class="list-group list-group-flush">
                <?php if ($appResult->num_rows > 0): ?>
                    <?php while ($row = $appResult->fetch_assoc()): ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($row['fullName']) ?></strong> applied for
                            <em><?= htmlspecialchars($row['jobTitle']) ?></em> on
                            <?= date('M d, Y', strtotime($row['applicationDate'])) ?>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="list-group-item">No recent applications.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Recent Job Postings</h5>
            </div>
            <ul class="list-group list-group-flush">
                <?php if ($jobResult->num_rows > 0): ?>
                    <?php while ($row = $jobResult->fetch_assoc()): ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($row['jobTitle']) ?></strong> posted by
                            <em><?= htmlspecialchars($row['companyName']) ?></em> on
                            <?= date('M d, Y', strtotime($row['posted_date'])) ?>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="list-group-item">No recent job posts.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Recent Employers</h5>
            </div>
            <ul class="list-group list-group-flush">
                <?php if ($employerResult->num_rows > 0): ?>
                    <?php while ($row = $employerResult->fetch_assoc()): ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($row['companyName']) ?></strong> (ID: <?= htmlspecialchars($row['employerID']) ?>) registered on
                            <?= date('M d, Y', strtotime($row['DateStab'])) ?>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="list-group-item">No recent employers.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

</body>

</html>