<?php
include_once '../Functions/db_connection.php';


$startDate = $_GET['start_date'] ?? '2025-04-14';
$endDate = $_GET['end_date'] ?? '2025-05-14';


// Validate format
if (!preg_match('/\d{4}-\d{2}-\d{2}/', $startDate)) $startDate = date('Y-m-d', strtotime('-30 days'));
if (!preg_match('/\d{4}-\d{2}-\d{2}/', $endDate)) $endDate = date('Y-m-d');


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
            animation: gradientBG 12s ease infinite;
            font-family: 'Poppins', sans-serif;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .analytics-container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }

        .card-box {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
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
    </style>
</head>

<body>

    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: rgba(78, 115, 223, 0.95);">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php"><i class="fas fa-user-shield me-2"></i>Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="managementDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-briefcase me-1"></i> Management
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="admin_post_job.php">Manage Job</a></li>
                            <li><a class="dropdown-item" href="admin_add_category.php">Job Category</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="manage_users.php"><i class="fas fa-users-cog me-1"></i> Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="application_log.php"><i class="fas fa-file-alt me-1"></i> Application Activity</a></li>
                    <li class="nav-item"><a class="nav-link" href="notifications.php"><i class="fas fa-bell me-1"></i> Send Notifications</a></li>
                    <li class="nav-item"><a class="nav-link active" href="site_analytics.php"><i class="fas fa-chart-line me-1"></i> Analytics</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="export_analytics_excel.php?start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>"
            class="btn btn-success">
            <i class="fas fa-file-excel me-1"></i> Export Excel
        </a>

        <button onclick="window.print()" class="btn btn-danger"><i class="fas fa-file-pdf me-1"></i> Print PDF</button>
    </div>




    <!-- Dashboard Cards -->
    <div class="analytics-container">
        <form method="GET" class="mb-4 d-flex gap-3 align-items-end flex-wrap">
            <div>
                <label for="start_date" class="form-label text-white">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="form-control"
                    value="<?= htmlspecialchars($_GET['start_date'] ?? '2025-04-14') ?>">
            </div>
            <div>
                <label for="end_date" class="form-label text-white">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-control"
                    value="<?= htmlspecialchars($_GET['end_date'] ?? '2025-05-14') ?>">
            </div>
            <button type="submit" class="btn btn-light"><i class="fas fa-filter me-1"></i> Filter</button>
        </form>
        <h2 class="text-center mb-5 text-white">📊 Site Analytics Dashboard</h2>
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

        <div class="mt-5 bg-white p-4 rounded shadow">
            <h4 class="mb-4">Applications Overview Chart</h4>
            <canvas id="overviewChart" height="100"></canvas>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('overviewChart').getContext('2d');
            const overviewChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jobseekers', 'Employers', 'Jobs Posted', 'Applications'],
                    datasets: [{
                        label: 'Site Data Summary',
                        data: [<?= $userCount ?>, <?= $employerCount ?>, <?= $jobCount ?>, <?= $applicationCount ?>],
                        backgroundColor: ['#36b9cc', '#f6c23e', '#4e73df', '#1cc88a'],
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        </script>

    </div>




    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>