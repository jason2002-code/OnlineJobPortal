<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['user_Id'])) {
    header("Location: login.php");
    exit;
}

$user_Id = $_SESSION['user_Id'];

// Fetch jobseeker profile data
$query = "SELECT * FROM jobseeker_profiles WHERE user_Id = $user_Id LIMIT 1";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $profile = mysqli_fetch_assoc($result);
} else {
    // Initialize empty profile if none found
    $profile = [
        'fullName' => '',
        'bio' => '',
        'skills' => '',
        'resume' => '',
        'profilePic' => '',
        'age' => '',
        'gender' => '',
        'address' => ''
    ];
}

// Fetch notifications for the logged-in user
$notifications = [];
$unreadCount = 0;
$notifQuery = "SELECT notificationID, message, isRead, dateSent FROM notifications WHERE userID = ? ORDER BY dateSent DESC LIMIT 10";
if ($stmt = $conn->prepare($notifQuery)) {
    $stmt->bind_param("i", $user_Id);
    $stmt->execute();
    $notifResult = $stmt->get_result();
    while ($notif = $notifResult->fetch_assoc()) {
        $notifications[] = $notif;
        if ($notif['isRead'] == 0) {
            $unreadCount++;
        }
    }
    $stmt->close();
}

// Parse skills into array
$skills = array_filter(array_map('trim', explode(',', $profile['skills'])));

$savedJobs = [];
if (isset($_SESSION['saved_jobs']) && is_array($_SESSION['saved_jobs']) && count($_SESSION['saved_jobs']) > 0) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['saved_jobs']), '?'));
    $types = str_repeat('i', count($_SESSION['saved_jobs']));
    $savedJobsQuery = "SELECT j.jobID, j.jobTitle, j.location, j.Salary, j.Schedule, j.posted_date, e.companyName
                       FROM joblist j
                       JOIN employers e ON j.employerID = e.employerID
                       WHERE j.jobID IN ($placeholders)
                       ORDER BY j.posted_date DESC";
    if ($stmt = $conn->prepare($savedJobsQuery)) {
        $bind_names[] = $types;
        for ($i = 0; $i < count($_SESSION['saved_jobs']); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $_SESSION['saved_jobs'][$i];
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

$showProfileReminder = empty($profile['fullName']) || empty($profile['bio']) || empty($profile['skills']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobseeker Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../For_design/Jobseekstyle.css">
    <style>
       
    </style>
</head>
<?php if ($showProfileReminder): ?>
    <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
        <strong>Reminder:</strong> Please complete your <a href="edit_profile.php" class="alert-link">profile summary</a> and details to improve your visibility to employers.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="jobseeker_dashboard.php"><i class="fas fa-briefcase me-2"></i>Upwork.</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown position-relative">
                        <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell me-1"></i>
                            <?php if ($unreadCount > 0): ?>
                                <span class="notification-badge"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
                            <li class="dropdown-header">Notifications</li>
                            <?php if (count($notifications) > 0): ?>
                                <?php foreach ($notifications as $notif): ?>
                                    <li>
                                        <a class="dropdown-item<?php echo $notif['isRead'] == 0 ? ' fw-bold' : ''; ?>" href="#">
                                            <?php echo htmlspecialchars($notif['message']); ?><br>
                                            <small class="text-muted"><?php echo date('M j, Y H:i', strtotime($notif['dateSent'])); ?></small>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><span class="dropdown-item-text">No notifications found.</span></li>
                            <?php endif; ?>
                        </ul>
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

    <div class="dashboard container-fluid">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="profile-card">
                    <div class="section-title">Profile Summary</div>
                    <div class="profile-summary">
                        <div class="profile-avatar">
                            <?php if (!empty($profile['profilePic'])): ?>
                                <img src="../For_text/uploads/<?php echo htmlspecialchars($profile['profilePic']); ?>" alt="Profile Picture" class="img-fluid rounded-circle" style="width: 80px; height: 80px;">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="profile-info">
                            <div class="info-item">
                                <span>Name:</span> <?php echo htmlspecialchars($profile['fullName']); ?>
                            </div>
                            <div class="info-item">
                                <span>Location:</span> <?php echo htmlspecialchars($profile['address']); ?>
                            </div>
                            <div class="info-item">
                                <span>Age:</span> <?php echo htmlspecialchars($profile['age']); ?>
                            </div>
                            <div class="info-item">
                                <span>Gender:</span> <?php echo htmlspecialchars($profile['gender']); ?>
                            </div>
                            <div class="info-item">
                                <span>Status:</span> <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                    </div>
                    <a href="edit_profile.php" class="btn btn-primary w-100 mt-3">Edit Profile</a>
                </div>
                
                <div class="profile-card">
                    <div class="section-title">Quick Stats</div>
                    <div class="profile-info">
                        <div class="info-item d-flex justify-content-between">
                            <span>Applications:</span> 
                            <span class="badge bg-primary rounded-pill">12</span>
                        </div>
                        <div class="info-item d-flex justify-content-between">
                            <span>Interviews:</span> 
                            <span class="badge bg-success rounded-pill">3</span>
                        </div>
                        <div class="info-item d-flex justify-content-between">
                            <span>Saved Jobs:</span> 
                            <span class="badge bg-warning rounded-pill"><?php echo count($savedJobs); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="profile-card">
                    <div class="section-title">Profile Details</div>
                    <div class="profile-info">
                        <div class="info-item">
                            <span>Bio:</span>
                            <p><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
                        </div>
                        <div class="info-item">
                            <span>Skills:</span>
                            <div class="skills-container mt-2">
                                <?php foreach ($skills as $skill): ?>
                                    <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="resume-section mt-4">
                        <div class="section-title">Resume</div>
                        <div class="resume-upload">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Drag & drop your resume here or click to browse</p>
                            <input type="file" accept=".pdf,.doc,.docx" class="form-control mt-3" style="display: none;">
                        </div>
                        <p class="info-item">
                            <span>Current Resume:</span> 
                            <?php if (!empty($profile['resume'])): ?>
                                <a href="../For_text/uploads/<?php echo htmlspecialchars($profile['resume']); ?>" class="text-primary" target="_blank"><?php echo htmlspecialchars($profile['resume']); ?></a>
                            <?php else: ?>
                                No resume uploaded
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="profile-card mt-4">
                    <div class="section-title">Recent Applications</div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Position</th>
                                    <th>Company</th>
                                    <th>Applied</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $applicationsQuery = "SELECT ja.applicationDate, ja.Status1, j.jobTitle, e.companyName, ja.jobID
                                FROM jobapplications ja
                                JOIN joblist j ON ja.jobID = j.jobID
                                JOIN employers e ON j.employerID = e.employerID
                                WHERE ja.user_Id = ?
                                ORDER BY ja.applicationDate DESC
                                LIMIT 5";
                            if ($stmt = $conn->prepare($applicationsQuery)) {
                                $stmt->bind_param("i", $user_Id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $appliedDate = date('M j, Y', strtotime($row['applicationDate']));
                                        $status = htmlspecialchars($row['Status1']);
                                        $statusClass = 'bg-secondary';
                                        if (strtolower($status) === 'pending') {
                                            $statusClass = 'bg-info';
                                        } elseif (strtolower($status) === 'interview scheduled') {
                                            $statusClass = 'bg-warning';
                                        } elseif (strtolower($status) === 'offer received') {
                                            $statusClass = 'bg-success';
                                        }
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['jobTitle']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['companyName']) . "</td>";
                                        echo "<td>" . $appliedDate . "</td>";
                                        echo "<td><span class='badge $statusClass'>" . $status . "</span></td>";
                                        echo "<td><a href='view_job.php?jobID=" . urlencode($row['jobID']) . "' class='btn btn-sm btn-outline-primary'>View</a></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>No recent applications found.</td></tr>";
                                }
                                $stmt->close();
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>Failed to load applications.</td></tr>";
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="#" class="btn btn-link text-primary float-end">View all applications</a>
                </div>

                <div class="profile-card mt-4">
                    <div class="section-title">Saved Jobs</div>
                    <?php if (count($savedJobs) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
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
                                                <form method="post" action="remove_saved_job.php" style="display:inline;">
                                                    <input type="hidden" name="jobID" value="<?= htmlspecialchars($savedJob['jobID']) ?>">
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

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Make resume upload area clickable
        document.querySelector('.resume-upload').addEventListener('click', function() {
            this.querySelector('input[type="file"]').click();
        });
        
        // Change resume upload area style when file is selected
        document.querySelector('.resume-upload input[type="file"]').addEventListener('change', function(e) {
            if (this.files.length > 0) {
                const uploadArea = this.closest('.resume-upload');
                uploadArea.innerHTML = '<i class="fas fa-check-circle text-success"></i>' +
                    '<p>' + this.files[0].name + ' selected</p>' +
                    '<p class="small text-muted">Click to upload a different file</p>';
                uploadArea.querySelector('i').style.fontSize = '2rem';
                uploadArea.querySelector('p').classList.add('mb-0');
                
                // Re-add the click event
                uploadArea.addEventListener('click', function() {
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.accept = '.pdf,.doc,.docx';
                    input.style.display = 'none';
                    uploadArea.appendChild(input);
                    input.click();
                    
                    input.addEventListener('change', function() {
                        if (this.files.length > 0) {
                            uploadArea.innerHTML = '<i class="fas fa-check-circle text-success"></i>' +
                                '<p>' + this.files[0].name + ' selected</p>' +
                                '<p class="small text-muted">Click to upload a different file</p>';
                            uploadArea.querySelector('i').style.fontSize = '2rem';
                            uploadArea.querySelector('p').classList.add('mb-0');
                        }
                    });
                });
            }
        });
    </script>
    <footer class="footer">
    <div class="credit">&copy; copyright @ 2025 by <span>mr. web designer</span> | All rights reserved!</div>
</footer>

<script src="../Functions/script.js"></script>
</body>
</html>
        

