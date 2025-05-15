<?php
session_start();
include("../Functions/db_connection.php");

// Fetch categories for job posting
$categories = [];
$categoryQuery = "SELECT categoryID, categoryName FROM job_categories ORDER BY categoryName ASC";
if ($stmt = $conn->prepare($categoryQuery)) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    $stmt->close();
} else {
    // Handle query preparation error if needed
}

include_once "../Functions/employer_search_functions.php";

$searchResults = [
    'jobs' => [],
    'applicants' => [],
    'offers' => [],
];

$searchErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_submit'])) {
    $employerID = $_SESSION['employerID'];
    $searchQuery = $_POST['search_query'] ?? '';

    include_once "../Functions/employer_search_functions.php";
    // Use the new search functions related to employer with filters
    $filters = ['title' => $searchQuery];
    $searchResults['jobs'] = searchJobsByEmployer($conn, $employerID, $filters);
    $filters = ['fullName' => $searchQuery];
    $searchResults['applicants'] = searchApplicantsByEmployer($conn, $employerID, $filters);
    $filters = ['jobTitle' => $searchQuery];
    $searchResults['offers'] = searchOffersByEmployer($conn, $employerID, $filters);

    // Define hasResults for use in HTML
    $hasResults = !empty($searchResults['jobs']) || !empty($searchResults['applicants']) || !empty($searchResults['offers']);
}

// Check if employer is logged in
if (!isset($_SESSION['employerID'])) {
    header('location:login.php');
    exit();
}

// Handle delete job request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_job']) && isset($_POST['delete_jobID'])) {
    $delete_jobID = intval($_POST['delete_jobID']);
    $employerID = $_SESSION['employerID'];

    // Delete job only if it belongs to the logged-in employer
    $stmt = $conn->prepare("DELETE FROM joblist WHERE jobID = ? AND employerID = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $delete_jobID, $employerID);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Job deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete job.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Failed to prepare delete statement.";
    }
    header("Location: employer_dashboard.php");
    exit();
}

// Get employer details
$employer_id = $_SESSION['employerID'];
$select_employer = "SELECT * FROM employers WHERE employerID = ?";
if ($stmt = $conn->prepare($select_employer)) {
    $stmt->bind_param("i", $employer_id);
    $stmt->execute();
    $result_employer = $stmt->get_result();
    $employer = $result_employer->fetch_assoc();
    $stmt->close();
} else {
    $employer = null;
}

// Fetch notifications for the logged-in employer
$notifications = [];
$unreadCount = 0;
$notifQuery = "SELECT notificationID, message, isRead, dateSent
               FROM notifications
               WHERE user_Id = ? AND receiverRole = 'employer' AND (isHidden IS NULL OR isHidden = 0)
               ORDER BY dateSent DESC
               LIMIT 10";
if ($stmt = $conn->prepare($notifQuery)) {
    $stmt->bind_param("i", $employer_id);
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard</title>
    <link rel="stylesheet" href="../For_design/New_employer_dash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'feedback_popup.php'; ?>
    <?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['success'])) {
        echo '<div class="alert success">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert error">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    ?>
    <div class="sidebar">
        <h2><i class="fas fa-briefcase"></i> Employer Dashboard</h2>
        <ul>
            <li><a href="employer_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="recruitment.php"><i class="fas fa-users"></i> Recruitment</a></li>
            <li><a href="view_interview.php"><i class="fas fa-comments"></i> Interview</a></li>
            <li><a href="offers_log.php"><i class="fas fa-user-plus"></i> Offers</a></li>
            <li><a href="employer_notifications.php"><i class="fas fa-inbox"></i> Messages
                    <?php if ($unreadCount > 0): ?>
                        <span class="notification-badge"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </a></li>
            <li><a href="application_review.php?showAccepted=1"><i class="fas fa-calendar-check"></i> Appointments</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            <li style="margin-top: 5px;">
                <form action="logout.php" method="post" class="sidebar-logout-form" style="margin: 0;">
                    <button class="logout"><i class="fas fa-right-from-bracket"></i> Logout</button>
                </form>
            </li>
        </ul>

    </div>

    <div class="main">
        <header class="search-header">
            <form method="POST" class="search-form live-search" onsubmit="return validateSearchForm();">
                <input type="text" id="live_search" name="search_query" placeholder="Search jobs, applicants, offers..." autocomplete="off">
                <button type="submit" name="search_submit"><i class="fas fa-search"></i></button>
            </form>
            <div id="live_search_results" class="search-results-box"></div>
        </header>


        
        
        <button class="add-new">Add New</button>
        
        </header>
        
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_submit'])): ?>
            <section class="search-results">
                <h4>Search Results</h4>
                <?php if ($hasResults): ?>
                    <div>
                        <h5>Jobs</h5>
                        <ul>
                            <?php foreach ($searchResults['jobs'] as $job): ?>
                                <li><a href="view_job.php?jobID=<?= $job['jobID'] ?>"><?= htmlspecialchars($job['jobTitle']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>

                        <h5>Applicants</h5>
                        <ul>
                            <?php foreach ($searchResults['applicants'] as $app): ?>
                                <li><a href="view_jobseeker_profile.php?user_Id=<?= $app['user_Id'] ?>"><?= htmlspecialchars($app['fullName']) ?> — <?= $app['jobTitle'] ?></a></li>
                            <?php endforeach; ?>
                        </ul>

                        <h5>Offers</h5>
                        <ul>
                            <?php foreach ($searchResults['offers'] as $offer): ?>
                                <li><a href="offers_log.php?offerID=<?= $offer['offerID'] ?>"><?= $offer['jobTitle'] ?> — <?= $offer['offerStatus'] ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <p>No results found.</p>
                <?php endif; ?>
            </section>

        <?php endif; ?>

        <div class="job-modal" id="jobModal">

            <div class="job-modal-content">
                <span class="close-btn" id="closeJobModal">&times;</span>
                <h3><i class="fas fa-briefcase"></i> Post a New Job</h3>

                <form id="jobForm" action="post_job.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="employerID" value="<?php echo htmlspecialchars($employer['employerID']); ?>">

                    <label for="jobTitle"><i class="fas fa-heading"></i> Job Title</label>
                    <input type="text" id="jobTitle" name="jobTitle" placeholder="e.g. Frontend Developer" required>

                    <label for="categoryID"><i class="fas fa-list"></i> Category</label>
                    <select name="categoryID" id="categoryID" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['categoryID'] ?>"><?= htmlspecialchars($category['categoryName']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="description"><i class="fas fa-align-left"></i> Job Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Describe the role in detail..." required></textarea>

                    <label for="salary"><i class="fas fa-dollar-sign"></i> Salary</label>
                    <input type="text" id="salary" name="salary" placeholder="e.g. 60,000 - 80,000" pattern="^\$\d{1,3}(,\d{3})*(\s*-\s*\$\d{1,3}(,\d{3})*)?$" required>

                    <label for="benefits"><i class="fas fa-gift"></i> Benefits</label>
                    <textarea id="benefits" name="benefits" rows="2" placeholder="e.g. Health insurance, Paid leave" required></textarea>

                    <label for="schedule"><i class="fas fa-calendar-alt"></i> Schedule</label>
                    <textarea id="schedule" name="schedule" rows="2" placeholder="e.g. Full-time, Mon–Fri" required></textarea>

                    <label for="requirements"><i class="fas fa-check-circle"></i> Requirements</label>
                    <textarea id="requirements" name="requirements" rows="3" placeholder="Minimum qualifications..." required></textarea>

                    <label for="skills"><i class="fas fa-tools"></i> Skills</label>
                    <textarea id="skills" name="skills" rows="3" placeholder="e.g. React, Problem-solving" required></textarea>

                    <label for="experience"><i class="fa-solid fa-user-check"></i> Experience</label>
                    <textarea id="experience" name="experience" rows="4" placeholder="e.g. 1 - 2 years," required></textarea>

                    <label for="photo"><i class="fas fa-image"></i> Upload Photo</label>
                    <input type="file" id="photo" name="photo" accept="image/*">

                    <label for="location"><i class="fas fa-map-marker-alt"></i> Location</label>
                    <input type="text" id="location" name="location" placeholder="e.g. New York, Remote" required>

                    <label for="education"><i class="fas fa-graduation-cap"></i> Education</label>
                    <input type="text" id="education" name="education" placeholder="e.g. Bachelor's Degree" required>

                    <label for="posted_date"><i class="fas fa-calendar-day"></i> Posted Date</label>
                    <input type="date" id="posted_date" name="posted_date" required>

                    <label for="status"><i class="fas fa-toggle-on"></i> Status</label>
                    <select id="status" name="status" required>
                        <option value="">-- Select Status --</option>
                        <option value="Open">Open</option>
                        <option value="Closed">Closed</option>
                    </select>

                    <button type="submit" class="submit-job"><i class="fas fa-paper-plane"></i> Post Job</button>
                </form>
            </div>
        </div>

<?php
// Count new applications for the logged-in employer
$newApplicationsCount = 0;
$employerID = $_SESSION['employerID'];
$queryNewApps = "SELECT COUNT(*) FROM jobapplications a JOIN joblist j ON a.jobID = j.jobID WHERE j.employerID = ? AND (a.Status1 IS NULL OR a.Status1 NOT IN ('accepted', 'rejected'))";
if ($stmtNewApps = $conn->prepare($queryNewApps)) {
    $stmtNewApps->bind_param("i", $employerID);
    $stmtNewApps->execute();
    $stmtNewApps->bind_result($newApplicationsCount);
    $stmtNewApps->fetch();
    $stmtNewApps->close();
}
?>
<section class="welcome">
    <h3>Good Morning <?php echo htmlspecialchars($employer['Emp_email']); ?></h3>
    <p>You have <?php echo $newApplicationsCount > 0 ? $newApplicationsCount : 'no'; ?> new application<?php echo $newApplicationsCount == 1 ? '' : 's'; ?>. A lot of work for today!</p>
    <button onclick="window.location.href='Employer_explore.php'">Explore</button>
</section>

        <section class="hire-needs">
            <h4>You Need to hire</h4>
            <div class="cards">
                <?php
                $roles = ["Content Developer", "Full Developer", "UI/UX Designer", "iOS Developer", "Android Developer"];
                foreach ($roles as $role) {
                    echo "<div class='card'>$role</div>";
                }
                ?>
            </div>
        </section>

        <section class="posted-jobs">
            <h4>Your Posted Jobs</h4>
            <table>
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Status</th>
                        <th>Posted Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $employerID = $_SESSION['employerID'];
                    $query = "SELECT * FROM joblist WHERE employerID = ? ORDER BY posted_date DESC";
                    if ($stmt = $conn->prepare($query)) {
                        $stmt->bind_param("i", $employerID);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result && $result->num_rows > 0) {
                            while ($job = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($job['jobTitle']) . "</td>";
                                echo "<td>" . htmlspecialchars($job['status']) . "</td>";
                                echo "<td>" . htmlspecialchars(date('F j, Y', strtotime($job['posted_date']))) . "</td>";
                                echo "<td>
                                    <a href='post_job.php?jobID=" . $job['jobID'] . "' class='btn update-btn'>Update</a>
                                    <form action='employer_dashboard.php' method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this job?\");'>
                                        <input type='hidden' name='delete_jobID' value='" . $job['jobID'] . "'>
                                        <button type='submit' name='delete_job' class='btn delete-btn'>Delete</button>
                                    </form>
                                </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No jobs posted yet.</td></tr>";
                        }
                        $stmt->close();
                    } else {
                        echo "<tr><td colspan='4'>Failed to load jobs.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>

    <script src="script.js"></script>
    <script src="../For_design/jv/dashboard.js"></script>
    <script>
        // Check if URL has openPostJob=1 to open the post job modal automatically
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('openPostJob') === '1') {
                const jobModal = document.getElementById('jobModal');
                const addNewBtn = document.querySelector('.add-new');
                if (jobModal) {
                    jobModal.style.display = 'block';
                }
            }
        });

        // Close modal functionality
        const closeBtn = document.getElementById('closeJobModal');
        const jobModal = document.getElementById('jobModal');
        if (closeBtn && jobModal) {
            closeBtn.addEventListener('click', function() {
                jobModal.style.display = 'none';
                // Remove the query parameter from URL without reloading
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('openPostJob');
                    window.history.replaceState({}, document.title, url.toString());
                }
            });
        }

        function validateSearchForm() {
            var searchInput = document.getElementById('live_search').value.trim();
            if (searchInput === '') {
                alert('Please fill the search bar before submitting.');
                return false;
            }
            return true;
        }

            document.addEventListener('DOMContentLoaded', function() {
                const liveSearchInput = document.getElementById('live_search');
                const resultsBox = document.getElementById('live_search_results');

                liveSearchInput.addEventListener('input', function() {
                    const query = this.value.trim();
                    if (query.length === 0) {
                        resultsBox.innerHTML = '';
                        resultsBox.style.display = 'none';
                        return;
                    }

                    fetch(`live_search_handler.php?q=${encodeURIComponent(query)}`)
                        .then(response => response.text())
                        .then(data => {
                            resultsBox.innerHTML = data;
                            resultsBox.style.display = 'block';
                        })
                        .catch(error => {
                            console.error('Error fetching live search results:', error);
                            resultsBox.innerHTML = '<li>Error loading results.</li>';
                            resultsBox.style.display = 'block';
                        });
                });
                

                // Optional: Hide results when clicking outside
                document.addEventListener('click', function(event) {
                    if (!resultsBox.contains(event.target) && event.target !== liveSearchInput) {
                        resultsBox.style.display = 'none';
                    }
                });
            });
        </script>
</body>

</html>