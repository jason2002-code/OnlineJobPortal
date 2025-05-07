<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['user_Id'])) {
    header("Location: login.php");
    exit;
}

$user_Id = $_SESSION['user_Id'];

$sql = "SELECT a.applicationID, a.applicationDate, 
        COALESCE(ar.decision, a.Status1) AS status, 
        j.jobTitle, e.companyName
        FROM jobapplications a
        JOIN joblist j ON a.jobID = j.jobID
        JOIN employers e ON j.employerID = e.employerID
        LEFT JOIN application_review ar ON a.applicationID = ar.applicationID
        WHERE a.user_Id = ?
        ORDER BY a.applicationDate DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_Id);
$stmt->execute();
$result = $stmt->get_result();

$applications = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }
}
$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Applications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../For_design/Jobseekstyle.css" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="jobseeker_dashboard.php"><i class="fas fa-briefcase me-2"></i>Upwork.</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item position-relative">
                        <a class="nav-link" href="#"><i class="fas fa-bell me-1"></i><span class="notification-badge">3</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="jobseeker_dashboard.php"><i class="fas fa-user me-1"></i>Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="jobs.php"><i class="fas fa-search me-1"></i>Job Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="jobseeker_applications.php"><i class="fas fa-file-alt me-1"></i>Applications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="saved_jobs.php"><i class="fas fa-bookmark me-1"></i>Saved Jobs</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog me-1"></i>Settings
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="#">Account Settings</a></li>
                            <li><a class="dropdown-item" href="#">Privacy</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    
    <div class="container mt-5 mb-5">
        <h2>My Job Applications</h2>
        <?php if (count($applications) === 0): ?>
            <p>You have not applied to any jobs yet.</p>
        <?php else: ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th>Applied On</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($app['jobTitle']); ?></td>
                            <td><?php echo htmlspecialchars($app['companyName']); ?></td>
                            <td><?php echo date('F j, Y', strtotime($app['applicationDate'])); ?></td>
                            <td><?php echo htmlspecialchars($app['status']); ?></td>
                            <td><a href="view_job.php?jobID=<?php echo urlencode($app['applicationID']); ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="jobseeker_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
</body>
</html>
