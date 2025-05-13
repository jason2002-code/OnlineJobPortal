<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['user_Id'])) {
    header("Location: login.php");
    exit;
}

$user_Id = $_SESSION['user_Id'];

// Handle removal of saved job
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_jobID'])) {
    $removeJobID = intval($_POST['remove_jobID']);
    if (isset($_SESSION['saved_jobs']) && is_array($_SESSION['saved_jobs'])) {
        $index = array_search($removeJobID, $_SESSION['saved_jobs']);
        if ($index !== false) {
            unset($_SESSION['saved_jobs'][$index]);
            $_SESSION['saved_jobs'] = array_values($_SESSION['saved_jobs']); // reindex array
            $message = "Job removed from saved jobs.";
        }
    }
}

$savedJobs = [];
if (isset($_SESSION['saved_jobs']) && is_array($_SESSION['saved_jobs']) && count($_SESSION['saved_jobs']) > 0) {
    // Filter out any saved job IDs that no longer exist in joblist
    $validJobIDs = [];
    $placeholders = implode(',', array_fill(0, count($_SESSION['saved_jobs']), '?'));
    $types = str_repeat('i', count($_SESSION['saved_jobs']));
    $checkJobsQuery = "SELECT jobID FROM joblist WHERE jobID IN ($placeholders)";
    if ($stmt = $conn->prepare($checkJobsQuery)) {
        $bind_names_check[] = $types;
        for ($i = 0; $i < count($_SESSION['saved_jobs']); $i++) {
            $bind_name_check = 'bind_check' . $i;
            $$bind_name_check = $_SESSION['saved_jobs'][$i];
            $bind_names_check[] = &$$bind_name_check;
        }
        call_user_func_array(array($stmt, 'bind_param'), $bind_names_check);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $validJobIDs[] = $row['jobID'];
        }
        $stmt->close();
    }
    // Update session saved_jobs to only valid job IDs
    $_SESSION['saved_jobs'] = $validJobIDs;

    if (count($validJobIDs) > 0) {
        $placeholders = implode(',', array_fill(0, count($validJobIDs), '?'));
        $types = str_repeat('i', count($validJobIDs));
        $savedJobsQuery = "SELECT j.jobID, j.jobTitle, j.location, j.Salary, j.Schedule, j.posted_date, e.companyName
                           FROM joblist j
                           JOIN employers e ON j.employerID = e.employerID
                           WHERE j.jobID IN ($placeholders)
                           ORDER BY j.posted_date DESC";
        if ($stmt = $conn->prepare($savedJobsQuery)) {
            $bind_names[] = $types;
            for ($i = 0; $i < count($validJobIDs); $i++) {
                $bind_name = 'bind' . $i;
                $$bind_name = $validJobIDs[$i];
                $bind_names[] = &$$bind_name;
            }
            call_user_func_array(array($stmt, 'bind_param'), $bind_names);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $savedJobs[] = $row;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Saved Jobs - Jobseeker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../For_design/Jobseekstyle.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="jobseeker-dashboard">
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="jobseeker_dashboard.php"><i class="fas fa-briefcase me-2"></i>Upwork.</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item position-relative">
                        <a class="nav-link" href="jobseeker_notifications.php"><i class="fas fa-bell me-1"></i><span class=""></span></a>
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
                            <li><a class="dropdown-item" href="jobseeker_account_settings.php">Account Settings</a></li>
                            <li><a class="dropdown-item" href="#">Privacy</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Saved Jobs</h2>
        <?php if (count($savedJobs) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Position</th>
                            <th>Company</th>
                            <th>Location</th>
                            <th>Salary</th>
                            <th>Schedule</th>
                            <th>Posted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($savedJobs as $savedJob): ?>
                            <tr>
                                <td><?= htmlspecialchars($savedJob['jobTitle']) ?></td>
                                <td><?= htmlspecialchars($savedJob['companyName']) ?></td>
                                <td><?= htmlspecialchars($savedJob['location']) ?></td>
                                <td><?= htmlspecialchars($savedJob['Salary']) ?></td>
                                <td><?= htmlspecialchars($savedJob['Schedule']) ?></td>
                                <td><?= date('M j, Y', strtotime($savedJob['posted_date'])) ?></td>
                                <td>
                                    <a href="view_job.php?jobID=<?= urlencode($savedJob['jobID']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                                    <form method="post" action="saved_jobs.php" style="display:inline;">
                                        <input type="hidden" name="remove_jobID" value="<?= htmlspecialchars($savedJob['jobID']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this job from saved jobs?');">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No saved jobs found.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
