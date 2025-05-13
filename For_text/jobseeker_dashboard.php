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

// Fetch accurate counts for quick stats
// Applications count
$applicationsCount = 0;
$applicationsQuery = "SELECT COUNT(*) as count FROM jobapplications WHERE user_Id = ? AND (isHidden IS NULL OR isHidden = 0)";
if ($stmt = $conn->prepare($applicationsQuery)) {
    $stmt->bind_param("i", $user_Id);
    $stmt->execute();
    $stmt->bind_result($applicationsCount);
    $stmt->fetch();
    $stmt->close();
}

// Interviews count
$interviewsCount = 0;
$interviewsCountQuery = "SELECT COUNT(*) as count FROM interview i
                         JOIN jobapplications ja ON i.applicationID = ja.applicationID
                         WHERE ja.user_Id = ?";
if ($stmt = $conn->prepare($interviewsCountQuery)) {
    $stmt->bind_param("i", $user_Id);
    $stmt->execute();
    $stmt->bind_result($interviewsCount);
    $stmt->fetch();
    $stmt->close();
}

// Saved jobs count
$savedJobsCount = 0;
if (isset($_SESSION['saved_jobs']) && is_array($_SESSION['saved_jobs'])) {
    $savedJobsCount = count($_SESSION['saved_jobs']);
}
$notifications = [];
$unreadCount = 0;
$notifQuery = "SELECT n.notificationID, n.message, n.isRead, n.dateSent, i.interviewID, i.interviewDate, i.status, j.jobTitle, e.employerID
               FROM notifications n
               LEFT JOIN interview i ON (n.message LIKE CONCAT('%', i.interviewID, '%') OR n.message LIKE CONCAT('%', i.applicationID, '%'))
               LEFT JOIN jobapplications ja ON i.applicationID = ja.applicationID
               LEFT JOIN joblist j ON ja.jobID = j.jobID
               LEFT JOIN employers e ON j.employerID = e.employerID
               WHERE n.user_Id = ? AND (n.isHidden IS NULL OR n.isHidden = 0)
               AND (
                   (e.employerID IS NOT NULL AND n.message LIKE 'Your interview:%')
                   OR (n.receiverRole = 'jobseeker' AND (e.employerID IS NULL OR e.employerID IS NOT NULL))
               )
               ORDER BY dateSent DESC
               LIMIT 10";
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

    
// Delete old notifications older than 30 days
    $deleteOldNotifSql = "DELETE FROM notifications WHERE user_Id = ? AND dateSent < DATE_SUB(NOW(), INTERVAL 30 DAY)";
    if ($stmt = $conn->prepare($deleteOldNotifSql)) {
        $stmt->bind_param("i", $user_Id);
        $stmt->execute();
        $stmt->close();
    }

// Fetch interviews for the logged-in user
$interviews = [];
$interviewQuery = "SELECT i.interviewID, i.interviewDate, i.status, j.jobTitle, e.companyName
                   FROM interview i
                   JOIN jobapplications ja ON i.applicationID = ja.applicationID
                   JOIN joblist j ON ja.jobID = j.jobID
                   JOIN employers e ON j.employerID = e.employerID
                   WHERE ja.user_Id = ?
                   ORDER BY i.interviewDate DESC
                   LIMIT 5";
if ($stmt = $conn->prepare($interviewQuery)) {
    $stmt->bind_param("i", $user_Id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $interviews[] = $row;
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

// Fetch interviews for the logged-in user
$interviews = [];
$interviewQuery = "SELECT i.interviewID, i.interviewDate, i.status, j.jobTitle, e.companyName
                   FROM interview i
                   JOIN jobapplications ja ON i.applicationID = ja.applicationID
                   JOIN joblist j ON ja.jobID = j.jobID
                   JOIN employers e ON j.employerID = e.employerID
                   WHERE ja.user_Id = ?
                   ORDER BY i.interviewDate DESC
                   LIMIT 5";
if ($stmt = $conn->prepare($interviewQuery)) {
    $stmt->bind_param("i", $user_Id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $interviews[] = $row;
    }
    $stmt->close();
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

<body class="jobseeker-dashboard">
    <?php include 'feedback_popup.php'; ?>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-briefcase me-2"></i>Upwork.</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown position-relative">
                        <a class="nav-link dropdown-toggle" href="jobseeker_notifications.php" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell me-1"></i>
                            <?php if ($unreadCount > 0): ?>
                                <span class="notification-badge"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
                            <li class="dropdown-header">Notifications</li>
                            <?php if (count($notifications) > 0): ?>
                                <?php foreach ($notifications as $notif): ?>
                                <li class="notification-item position-relative" style="overflow: hidden;">
                                    <a class="dropdown-item<?php echo $notif['isRead'] == 0 ? ' fw-bold' : ''; ?>" href="jobseeker_notifications.php?notificationID=<?php echo $notif['notificationID']; ?>" style="display: block; padding-right: 40px;">
                                        <?php echo htmlspecialchars($notif['message']); ?><br>
                                        <small class="text-muted"><?php echo date('M j, Y H:i', strtotime($notif['dateSent'])); ?></small>
                                    </a>
                                    <form method="post" action="hide_notification.php" class="hide-form position-absolute top-0 end-0 m-1" style="display: none;" onsubmit="return confirm('Are you sure you want to hide this notification?');">
                                        <input type="hidden" name="notificationID" value="<?php echo $notif['notificationID']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger p-1" title="Hide notification" style="border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
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
                            <li><a class="dropdown-item" href="jobseeker_account_settings.php">Account Settings</a></li>
                            <li><a class="dropdown-item" href="#">Privacy</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
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
                            <span class="badge bg-primary rounded-pill"><?php echo $applicationsCount; ?></span>
                        </div>
                        <div class="info-item d-flex justify-content-between">
                            <span>Interviews:</span> 
                            <span class="badge bg-success rounded-pill"><?php echo $interviewsCount; ?></span>
                        </div>
                        <div class="info-item d-flex justify-content-between">
                            <span>Saved Jobs:</span> 
                            <span class="badge bg-warning rounded-pill"><?php echo $savedJobsCount; ?></span>
                        </div>
                    </div>
                </div>

              
                <div class="profile-card"> <div class="section-title">Notifications</div>
                <div class="profile-info">
                    <div class="info-item d-flex justify-content-between">
                        <span>Unread:</span>
                        <span class="badge bg-danger rounded-pill"><?php echo $unreadCount; ?></span>
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
                                $applicationsQuery = "SELECT ja.applicationID, ja.applicationDate, ja.Status1, j.jobTitle, e.companyName, ja.jobID
                                FROM jobapplications ja
                                JOIN joblist j ON ja.jobID = j.jobID
                                JOIN employers e ON j.employerID = e.employerID
                                WHERE ja.user_Id = ? AND (ja.isHidden IS NULL OR ja.isHidden = 0)
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
                                             echo "<td><a href='view_job.php?jobID=" . urlencode($row['jobID']) . "' class='btn btn-sm btn-outline-primary me-1'>View</a>";
                                             echo "<button type='button' class='btn btn-sm btn-outline-warning remove-application-btn' data-application-id='" . htmlspecialchars($row['applicationID']) . "'>Remove</button>";
                                             echo "</td>";
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
                <div class="profile-card mt-4">
                    <div class="section-title">Upcoming Interviews</div>
                    <?php if (count($interviews) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Position</th>
                                        <th>Company</th>
                                        <th>Interview Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($interviews as $interview): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($interview['jobTitle']) ?></td>
                                            <td><?= htmlspecialchars($interview['companyName']) ?></td>
                                            <td><?= date('M j, Y, g:i A', strtotime($interview['interviewDate'])) ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php 
                                                        echo $interview['status'] === 'scheduled' ? 'bg-primary' : 
                                                             ($interview['status'] === 'completed' ? 'bg-success' : 'bg-danger'); 
                                                    ?>">
                                                    <?= ucfirst($interview['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view_interview_details.php?interviewID=<?= urlencode($interview['interviewID']) ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No upcoming interviews found.</p>
                    <?php endif; ?>
                </div>

                <div class="profile-card mt-4">
                    <div class="section-title">Job Offers</div>
                    <?php
                    $offers = [];
                    $offersQuery = "SELECT o.offerID, o.salary, o.startDate, o.offerStatus, j.jobTitle, e.companyName
                                    FROM job_offers o
                                    JOIN jobapplications ja ON o.applicationID = ja.applicationID
                                    JOIN joblist j ON ja.jobID = j.jobID
                                    JOIN employers e ON j.employerID = e.employerID
                                    WHERE ja.user_Id = ?
                                    ORDER BY o.offerID DESC
                                    LIMIT 5";
                    if ($stmt = $conn->prepare($offersQuery)) {
                        $stmt->bind_param("i", $user_Id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            $offers[] = $row;
                        }
                        $stmt->close();
                    }
                    ?>
                    <?php if (count($offers) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Position</th>
                                        <th>Company</th>
                                        <th>Salary</th>
                                        <th>Start Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($offers as $offer): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($offer['jobTitle']) ?></td>
                                            <td><?= htmlspecialchars($offer['companyName']) ?></td>
                                            <td><?= htmlspecialchars(number_format($offer['salary'], 2)) ?></td>
                                            <td><?= htmlspecialchars($offer['startDate']) ?></td>
                                            <td><?= htmlspecialchars(ucfirst($offer['offerStatus'])) ?></td>
                                            <td>
                                                <a href="respond_offer.php?offerID=<?= urlencode($offer['offerID']) ?>" class="btn btn-sm btn-primary">Respond</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="my_offers.php" class="btn btn-link text-primary float-end">View all offers</a>
                    <?php else: ?>
                        <p>No job offers found.</p>
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

        // Show delete button on hover for notifications
        document.querySelectorAll('.notification-item').forEach(item => {
            const hideForm = item.querySelector('.hide-form');
            if (hideForm) {
                item.addEventListener('mouseenter', () => {
                    hideForm.style.display = 'flex';
                });
                item.addEventListener('mouseleave', () => {
                    hideForm.style.display = 'none';
                });
            }
        });

        // Hide notification badge and close dropdown after 3 seconds
        setTimeout(() => {
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                badge.style.display = 'none';
            }
            const dropdown = document.getElementById('notificationDropdown');
            if (dropdown && dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
                const menu = dropdown.nextElementSibling;
                if (menu && menu.classList.contains('dropdown-menu')) {
                    menu.classList.remove('show');
                }
            }
        }, 3000);

        // Add remove application button functionality - hide application on dashboard via backend call
        document.querySelectorAll('.remove-application-btn').forEach(button => {
            button.addEventListener('click', function() {
                const applicationId = this.getAttribute('data-application-id');
                if (confirm('Are you sure you want to remove this application from the dashboard view?')) {
                    fetch('hide_application.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'applicationID=' + encodeURIComponent(applicationId)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the row from the table
                            const row = this.closest('tr');
                            if (row) {
                                row.remove();
                            }
                        } else {
                            alert(data.message || 'Failed to remove application from dashboard. Please try again.');
                        }
                    })
                    .catch(error => {
                        alert('Failed to remove application from dashboard. Please try again.');
                        console.error('Error:', error);
                    });
                }
            });
        });
    </script>
    <footer class="footer">
    <div class="credit">&copy; copyright @ 2025 by <span>mr. web designer</span> | All rights reserved!</div>
</footer>

<script src="../Functions/script.js"></script>
</body>
</html>
        

