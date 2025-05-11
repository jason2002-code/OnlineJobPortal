<?php
session_start();
include("../Functions/db_connection.php");

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
$select_employer = "SELECT * FROM employers WHERE employerID = '$employer_id'";
$result_employer = mysqli_query($conn, $select_employer);
$employer = mysqli_fetch_assoc($result_employer);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard</title>
    <link rel="stylesheet" href="../For_design/style.css">
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
            <li><a href="#"><i class="fas fa-user-plus"></i> Onboarding</a></li>
            <li><a href="#"><i class="fas fa-tasks"></i> Interview Task</a></li>
            <li><a href="application_review.php?showAccepted=1"><i class="fas fa-calendar-check"></i> Appointments</a></li>
            <li><a href="#"><i class="fas fa-chalkboard-teacher"></i> Training</a></li>
        </ul>
        <form action="logout.php" method="post" class="sidebar-logout-form">
            <button class="logout"><i class="fas fa-right-from-bracket"></i> Logout</button>
        </form>
    </div>

    <div class="main">
        <header>
            <input type="text" placeholder="Search something...">
            <button class="add-new">Add New</button>
        </header>
        <div class="job-modal" id="jobModal">
            <div class="job-modal-content">
                <span class="close-btn" id="closeJobModal">&times;</span>
                <h3><i class="fas fa-briefcase"></i> Post a New Job</h3>

                <form id="jobForm" action="post_job.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="employerID" value="<?php echo htmlspecialchars($employer['employerID']); ?>">

                    <label for="jobTitle"><i class="fas fa-heading"></i> Job Title</label>
                    <input type="text" id="jobTitle" name="jobTitle" placeholder="e.g. Frontend Developer" required>

                    <label for="description"><i class="fas fa-align-left"></i> Job Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Describe the role in detail..." required></textarea>

                    <label for="salary"><i class="fas fa-dollar-sign"></i> Salary</label>
                    <input type="text" id="salary" name="salary" placeholder="e.g. $60,000 - $80,000" pattern="^\$\d{1,3}(,\d{3})*(\s*-\s*\$\d{1,3}(,\d{3})*)?$" required>

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

        <section class="welcome">
            <h3>Good Morning <?php echo htmlspecialchars($employer['Emp_email']); ?></h3>
            <p>You have 75+ new applications. A lot of work for today!</p>
            <button>Explore</button>
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
                    $query = "SELECT * FROM joblist WHERE employerID = '$employerID' ORDER BY posted_date DESC";
                    $result = mysqli_query($conn, $query);
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($job = mysqli_fetch_assoc($result)) {
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
                    ?>
                </tbody>
            </table>
        </section>

        <section class="recruitment-progress">
            <h4>Recruitment Progress</h4>
            <table>
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Description</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $candidates = [
                        ["John Doe", "UI/UX Designer", "Tech Interview"],
                        ["Sam Emmanuel", "UI/UX Designer", "Resume Review"],
                        ["John Samuel", "Content Developer", "Final Interview"]
                    ];
                    foreach ($candidates as $index => $c) {
                        $class = $index === 1 ? 'class="highlight"' : '';
                        echo "<tr $class><td>{$c[0]}</td><td>{$c[1]}</td><td>{$c[2]}</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </div>

    <div class="sidebar-right">
        <div class="profile">
            <p><?php echo htmlspecialchars($employer['Emp_email']); ?></p>
        </div>
        <div class="calendar">
            <h4>Schedule Calendar</h4>
            <div class="dates">
                <?php foreach ([24, 25, 26, 27, 28] as $day) echo "<span>$day</span>"; ?>
            </div>
        </div>

        <div class="new-applicants p-4 bg-white shadow rounded">
    <h4 class="mb-4">New Applicants</h4>
    <ul class="list-group list-group-flush">
        <?php
        $employerID = $_SESSION['employerID'];
        $query = "SELECT a.applicationID, u.fullName, j.jobTitle, a.applicationDate 
                  FROM jobapplications a
                  JOIN joblist j ON a.jobID = j.jobID
                  JOIN jobseeker_profiles u ON a.user_Id = u.user_Id
                  WHERE j.employerID = ? 
                  ORDER BY a.applicationDate DESC
                  LIMIT 5";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $employerID);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<li class='list-group-item d-flex justify-content-between align-items-start'>";
                echo "<div class='ms-2 me-auto'>";
                echo "<div class='fw-bold'>" . htmlspecialchars($row['fullName']) . "</div>";
                echo "Applied for <strong>" . htmlspecialchars($row['jobTitle']) . "</strong> on " . date('M j, Y', strtotime($row['applicationDate']));
                echo "</div>";
                echo "<a href='application_review.php?applicationID=" . urlencode($row['applicationID']) . "' class='btn btn-outline-primary btn-sm'>Review</a>";
                echo "</li>";
            }
        } else {
            echo "<li class='list-group-item'>No new applications</li>";
        }
        $stmt->close();
        ?>
    </ul>
</div>


        <div class="training">
            <h4>Ready For Training</h4>
            <div class="trainees">
                <?php
                $trainees = ["Alex", "Sam", "Maria"];
                foreach ($trainees as $trainee) echo "<div>$trainee</div>";
                ?>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script src="../For_design/jv/dashboard.js"></script>
    <script>
        // Check if URL has openPostJob=1 to open the post job modal automatically
        document.addEventListener('DOMContentLoaded', function () {
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
            closeBtn.addEventListener('click', function () {
                jobModal.style.display = 'none';
                // Remove the query parameter from URL without reloading
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('openPostJob');
                    window.history.replaceState({}, document.title, url.toString());
                }
            });
        }
    </script>
</body>

</html>
