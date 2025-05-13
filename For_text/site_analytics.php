<?php
include_once '../Functions/db_connection.php';

// Initialize counts
$userCount = $employerCount = $applicationCount = $jobCount = 0;

// Count queries
$userCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM jobseekers"))['count'];
$employerCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM employers"))['count'];
$applicationCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM jobapplications"))['count'];
$jobCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM joblist"))['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Site Analytics Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
         body {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    background-size: 400% 400%;
    animation: gradientBG 10s ease infinite;
    font-family: 'Poppins', sans-serif;
        }

        .navbar-nav .nav-link {
    color: #0c0c0c !important;
    transition: background-color 0.3s ease;
    border-radius: 4px;
    padding: 8px 12px;
}

.navbar-nav .nav-link:hover {
    background-color: rgba(78, 115, 223, 0.8);
    color: #f8f5f5 !important;
}

.navbar .nav-link {
    color: #fff !important;
    font-weight: 500;
}

.navbar .nav-link:hover {
    text-decoration: underline;
}

.navbar-brand {
    font-weight: 600;
}


/* Responsive adjustments */
@media (max-width: 768px) {
    .navbar-nav {
        flex-direction: column;
        width: 100%;
    }
    .navbar-nav .nav-link {
        padding: 10px;
        text-align: center;
    }
}

        @keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
        .analytics-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
        .card-box {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.05);
            padding: 30px;
            text-align: center;
            transition: transform 0.2s ease;
        }
        .card-box:hover {
            transform: translateY(-5px);
        }
        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #4e73df;
        }
        .card-title {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        .card-value {
            font-size: 2rem;
            font-weight: 700;
            color: #222;
        }
    </style>

<!-- Admin Navbar -->
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


</head>
<body>

<div class="analytics-container">
    <h2 class="text-center mb-4">📊 Site Analytics Dashboard</h2>
    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="card-box">
                <div class="card-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="card-title">Total Jobseekers</div>
                <div class="card-value"><?= $userCount ?></div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-box">
                <div class="card-icon"><i class="fas fa-building"></i></div>
                <div class="card-title">Total Employers</div>
                <div class="card-value"><?= $employerCount ?></div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-box">
                <div class="card-icon"><i class="fas fa-briefcase"></i></div>
                <div class="card-title">Jobs Posted</div>
                <div class="card-value"><?= $jobCount ?></div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-box">
                <div class="card-icon"><i class="fas fa-file-alt"></i></div>
                <div class="card-title">Applications</div>
                <div class="card-value"><?= $applicationCount ?></div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
