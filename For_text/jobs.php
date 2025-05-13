<?php
include("../Functions/db_connection.php");
session_start();

// Fetch distinct Schedule values for job_type dropdown
$schedule_result = $conn->query("SELECT DISTINCT Schedule FROM joblist ORDER BY Schedule ASC");
$schedules = [];
if ($schedule_result) {
    while ($row = $schedule_result->fetch_assoc()) {
        if (!empty($row['Schedule'])) {
            // Normalize Schedule value by trimming whitespace
            $schedule = trim($row['Schedule']);
            // Remove first and last character if they are the same
            if (strlen($schedule) > 1 && $schedule[0] === $schedule[strlen($schedule) - 1]) {
                $schedule = substr($schedule, 1, -1);
            }
            $schedules[] = $schedule;
        }
    }
    // Remove duplicates after normalization
    $schedules = array_unique($schedules);
    // Optional: reindex array
    $schedules = array_values($schedules);
}

// Fetch distinct education values for education dropdown
$education_result = $conn->query("SELECT DISTINCT education FROM joblist ORDER BY education ASC");
$educations = [];
if ($education_result) {
    while ($row = $education_result->fetch_assoc()) {
        if (!empty($row['education'])) {
            $educations[] = $row['education'];
        }
    }
}

if (isset($_GET['categoryID']) && is_numeric($_GET['categoryID'])) {
    $categoryID = intval($_GET['categoryID']);
    $stmt = $conn->prepare("SELECT * FROM joblist WHERE categoryID = ? ORDER BY posted_date DESC");
    $stmt->bind_param("i", $categoryID);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM joblist ORDER BY posted_date DESC");
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>All jobs</title>
    <link rel="stylesheet" href="../For_design/design.css" />
</head>
<body>
    <header class="header">
        <section class="flex">
            <div id="menu-btn" class="fas fa-bars-staggered"></div>
            <a href="home.php" class="logo"><i class="fas fa-briefcase"></i> Upwork.</a>
            <nav class="navbar">
                <a href="home.php">home</a>
                <a href="about.php">about us</a>
                <a href="jobs.php">all jobs</a>
                <a href="contact.php">contact us</a>
                <a href="login.php">account</a>
            </nav>
            <?php if (isset($_SESSION['employerID'])): ?>
                <a href="employer_dashboard.php?openPostJob=1" class="btn" style="margin-top: 0;">post job</a>
            <?php else: ?>
                <a href="login.php" class="btn" style="margin-top: 0;">post job</a>
            <?php endif; ?>
        </section>
    <?php if (isset($_SESSION['user_Id'])): ?>
        <div style="padding: 10px; text-align: right;">
            <a href="jobseeker_dashboard.php" class="btn back-to-dashboard-btn" style="padding: 8px 12px; border-radius: 4px; text-decoration: none;">Back to Dashboard</a>
        </div>
    <?php endif; ?>
    </header>

    <section class="job-filter">
        <h1 class="heading">filter jobs</h1>
        <form action="" method="post" id="jobFilterForm">
            <div class="flex">
                <div class="box">
                    <p>job title<span>*</span></p>
                    <input type="text" name="title" placeholder="keyword, category or company" required maxlength="20" class="input" />
                </div>
                <div class="box">
                    <p> job location</p>
                    <input type="text" name="location" placeholder=" city,  state or country" required maxlength="50" class="input" />
                </div>
            </div>
            <div class="dropdown-container">
                <div class="dropdown">
                    <input type="text" readonly placeholder="date posted" maxlength="20" name="date" class="output" />
                    <div class="lists">
                        <p class="items">today</p>
                        <p class="items">3 days ago</p>
                        <p class="items">7 days ago</p>
                        <p class="items">10 days ago</p>
                        <p class="items">15 days ago</p>
                        <p class="items">30 days ago</p>
                    </div>
                </div>
                <div class="dropdown">
                    <input type="text" readonly name="salary" placeholder="estimated" maxlength="20" class="output" />
                    <div class="lists">
                        <p class="items">55k - 80k</p>
                        <p class="items">50k - 75k</p>
                        <p class="items">45k - 65k</p>
                        <p class="items">40k - 60k</p>
                        <p class="items">35k - 55k</p>
                        <p class="items">30k - 50k</p>
                        <p class="items">25k - 45k</p>
                        <p class="items">20k - 40k</p>
                        <p class="items">15k - 30k</p>
                        <p class="items">12k - 25k</p>
                        <p class="items">10k - 22k</p>
                        <p class="items">9k - 20k</p>
                        <p class="items">7k - 18k</p>
                        <p class="items">4k - 16k</p>
                        <p class="items">5k - 10k</p>
                        <p class="items">1k or more</p>
                    </div>
                </div>
                <div class="dropdown">
                    <input type="text" readonly name="job_type" placeholder="job type" maxlength="20" class="output" />
                    <div class="lists">
                        <?php foreach ($schedules as $schedule): ?>
                            <p class="items"><?= htmlspecialchars($schedule) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="dropdown">
                    <input type="text" readonly name="education" placeholder="education level" maxlength="20" class="output" />
                    <div class="lists">
                        <?php foreach ($educations as $edu): ?>
                            <p class="items"><?= htmlspecialchars($edu) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="flex-btn">
                <button type="submit" name="search" class="btn">Search</button>
            </div>
        </form>
    </section>

    <section class="jobs-container">
        <h1 class="heading">all jobs</h1>
        <div class="box-container">
            <?php
          $title = $location = $date = $salary = $job_type = $education = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $title = trim($_POST['title'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $salary = trim($_POST['salary'] ?? '');
    $job_type = trim($_POST['job_type'] ?? '');
    $education = trim($_POST['education'] ?? '');

    $conditions = [];

    // Job title and Location combined with OR logic
    if (!empty($title) && !empty($location)) {
        $safe_title = $conn->real_escape_string($title);
        $safe_location = $conn->real_escape_string($location);
        $conditions[] = "(LOWER(jobTitle) LIKE '%" . strtolower($safe_title) . "%' OR LOWER(location) LIKE '%" . strtolower($safe_location) . "%')";
    } else {
        if (!empty($title)) {
            $safe_title = $conn->real_escape_string($title);
            $conditions[] = "LOWER(jobTitle) LIKE '%" . strtolower($safe_title) . "%'";
        }
        if (!empty($location)) {
            $safe_location = $conn->real_escape_string($location);
            $conditions[] = "LOWER(location) LIKE '%" . strtolower($safe_location) . "%'";
        }
    }

    // Date Posted, Salary, Job Type, Education combined with OR logic
    $or_conditions = [];

    if (!empty($date)) {
        $daysAgo = 0;
        switch (strtolower($date)) {
            case 'today': $daysAgo = 0; break;
            case '3 days ago': $daysAgo = 3; break;
            case '7 days ago': $daysAgo = 7; break;
            case '10 days ago': $daysAgo = 10; break;
            case '15 days ago': $daysAgo = 15; break;
            case '30 days ago': $daysAgo = 30; break;
        }

        if ($daysAgo >= 0) {
            $or_conditions[] = "posted_date >= CURDATE() - INTERVAL $daysAgo DAY";
        }
    }

    if (!empty($salary)) {
        $salary_clean = strtolower(str_replace([' ', 'k'], ['', '000'], $salary));

        if (strpos($salary, 'or more') !== false) {
            preg_match('/(\d+)/', $salary_clean, $match);
            $min_salary = isset($match[1]) ? intval($match[1]) : 0;
            $or_conditions[] = "Salary >= $min_salary";
        } elseif (strpos($salary_clean, '-') !== false) {
            [$min, $max] = explode('-', $salary_clean);
            $min_salary = intval($min);
            $max_salary = intval($max);
            $or_conditions[] = "Salary BETWEEN $min_salary AND $max_salary";
        }
    }

    if (!empty($job_type)) {
        $safe_type = $conn->real_escape_string($job_type);
        $or_conditions[] = "LOWER(Schedule) LIKE '%" . strtolower($safe_type) . "%'";
    }

    if (!empty($education)) {
        $safe_edu = $conn->real_escape_string($education);
        $or_conditions[] = "LOWER(education) LIKE '%" . strtolower($safe_edu) . "%'";
    }

    if (count($or_conditions) > 0) {
        $conditions[] = "(" . implode(" OR ", $or_conditions) . ")";
    }

    // Always exclude closed jobs
    $conditions[] = "LOWER(status) != 'closed'";

    $where_clause = "WHERE " . implode(" AND ", $conditions);

    $sql = "SELECT * FROM joblist $where_clause ORDER BY posted_date DESC";

} else {
    // No filters applied — show all open jobs
    $sql = "SELECT * FROM joblist WHERE LOWER(status) != 'closed' ORDER BY posted_date DESC";
}

            $result = $conn->query($sql);
            if (!$result) {
                echo "<p style='color:red; text-align:center;'>Database query error: " . htmlspecialchars($conn->error) . "</p>";
            } else {
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        $imagePath = !empty($row['Photo']) ? $row['Photo'] : 'https://cdn-icons-png.freepik.com/256/3291/3291670.png';

                        // Fetch company name from employers table using employerID
                        $employerID = $row['employerID'];
                        $companyName = 'Company';
                        $employerQuery = "SELECT companyName FROM employers WHERE employerID = ?";
                        if ($stmt = $conn->prepare($employerQuery)) {
                            $stmt->bind_param("i", $employerID);
                            $stmt->execute();
                            $stmt->bind_result($fetchedCompanyName);
                            if ($stmt->fetch()) {
                                $companyName = $fetchedCompanyName;
                            }
                            $stmt->close();
                        }
            ?>
                        <div class="box">
                            <div class="company">
                                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Job Image" />
                                <div>
                                    <h3><?= htmlspecialchars($companyName) ?></h3>
                                    <p><?= date('F j, Y', strtotime($row['posted_date'])) ?></p>
                                </div>
                            </div>
                            <div class="job-info">
                                <h3 class="job-title"><?= htmlspecialchars($row['jobTitle']) ?></h3>
                                <p class="location"><i class="fas fa-map-marker-alt"></i> <span><?= htmlspecialchars($row['location']) ?></span></p>
                            </div>
                            <div class="tags">
                                <p><i class="fas fa-solid fa-peso-sign"></i><span><?= htmlspecialchars(str_replace('$', '', $row['Salary'])) ?></span></p>
                                <p><i class="fas fa-briefcase"></i><span><?= htmlspecialchars($row['status']) ?></span></p>
                                <p><i class="fas fa-clock"></i><span><?= htmlspecialchars($row['Schedule']) ?></span></p>
                            </div>
                            <div class="flex-btn">
                                <a href="view_job.php?jobID=<?= $row['jobID'] ?>" class="btn">view details</a>
                                <button type="submit" class="far fa-heart" name="save"></button>
                            </div>
                        </div>
                        <?php
                    endwhile;
                else:
                    // No filtered jobs found, show message and other available jobs
                    echo "<p style='text-align:center;'>No job postings found.</p>";
                endif;
            }
            ?>
        </div>
    </section>

    <footer class="footer">
        <section class="grid">
            <div class="box">
                <h3>quick links</h3>
                <a href="home.php"><i class="fas fa-angle-right"></i> home </a>
                <a href="about.php"><i class="fas fa-angle-right"></i> about </a>
                <a href="jobs.php"><i class="fas fa-angle-right"></i> all jobs </a>
                <a href="contact.php"><i class="fas fa-angle-right"></i> contact us</a>
                <a href="#"><i class="fas fa-angle-right"></i> filter search </a>
            </div>
            <div class="box">
                <h3>extra links</h3>
                <a href="#"><i class="fas fa-angle-right"></i> account </a>
                <a href="login.php"><i class="fas fa-angle-right"></i> login</a>
                <a href="register.php"><i class="fas fa-angle-right"></i> register</a>
                <a href="#"><i class="fas fa-angle-right"></i> post job</a>
                <a href="#"><i class="fas fa-angle-right"></i> dashboard</a>
            </div>
            <div class="box">
                <h3>follow us</h3>
                <a href="#"><i class="fab fa-facebook"></i> facebook</a>
                <a href="#"><i class="fab fa-twitter"></i> twitter</a>
                <a href="#"><i class="fab fa-instagram"></i> instagram</a>
                <a href="#"><i class="fab fa-linkedin"></i> linkedin</a>
                <a href="#"><i class="fab fa-youtube"></i> youtube</a>
            </div>
        </section>

        <div class="credit">&copy; copyright @ 2025 by <span>mr. web designer
            </span> | all rights reserved!</div>
    </footer>

    <script src="../Functions/script.js"></script>

    <script>
        let dropdown_items = document.querySelectorAll('.job-filter form .dropdown-container .dropdown .lists .items');

        dropdown_items.forEach(items => {
            items.onclick = () => {
                items_parent = items.parentElement.parentElement;
                let output = items_parent.querySelector('.output');
                output.value = items.innerText;
            }
        });
    </script>

</body>

</html>