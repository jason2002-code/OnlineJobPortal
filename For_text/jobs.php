<?php
include("../Functions/db_connection.php");

// Fetch distinct Schedule values for job_type dropdown
$schedule_result = $conn->query("SELECT DISTINCT Schedule FROM joblist ORDER BY Schedule ASC");
$schedules = [];
if ($schedule_result) {
    while ($row = $schedule_result->fetch_assoc()) {
        if (!empty($row['Schedule'])) {
            $schedules[] = $row['Schedule'];
        }
    }
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
            <a href="#" class="btn" style="margin-top: 0;">post job</a>
        </section>
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
            $title = $location = $date = $salary = $job_type = $education = $work_shift = "";
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
                $title = isset($_POST['title']) ? $conn->real_escape_string($_POST['title']) : '';
                $location = isset($_POST['location']) ? $conn->real_escape_string($_POST['location']) : '';
                $date = isset($_POST['date']) ? $conn->real_escape_string($_POST['date']) : '';
                $salary = isset($_POST['salary']) ? $conn->real_escape_string($_POST['salary']) : '';
                $job_type = isset($_POST['job_type']) ? $conn->real_escape_string($_POST['job_type']) : '';
                $education = isset($_POST['education']) ? $conn->real_escape_string($_POST['education']) : '';

                $conditions = [];

                if (!empty($title) && !empty($location)) {
                    $title_esc = $conn->real_escape_string($title);
                    $location_esc = $conn->real_escape_string($location);
                    $conditions[] = "(jobTitle LIKE '%$title_esc%' OR location LIKE '%$location_esc%')";
                } else {
                    if (!empty($title)) {
                        $conditions[] = "jobTitle LIKE '%$title%'";
                    }
                    if (!empty($location)) {
                        $conditions[] = "location LIKE '%$location%'";
                    }
                }

                // Date posted filter: calculate date range based on selected option
                if (!empty($date)) {
                    $days_ago = 0;
                    switch (strtolower($date)) {
                        case 'today':
                            $days_ago = 0;
                            break;
                        case '3 days ago':
                            $days_ago = 3;
                            break;
                        case '7 days ago':
                            $days_ago = 7;
                            break;
                        case '10 days ago':
                            $days_ago = 10;
                            break;
                        case '15 days ago':
                            $days_ago = 15;
                            break;
                        case '30 days ago':
                            $days_ago = 30;
                            break;
                        default:
                            $days_ago = 0;
                    }
                    $date_limit = date('Y-m-d', strtotime("-$days_ago days"));
                    $conditions[] = "posted_date >= '$date_limit'";
                }

                // Salary filter: parse min and max salary from string like "55k - 80k"
                if (!empty($salary)) {
                $salary = strtolower(str_replace(' ', '', $salary));
                if (strpos($salary, 'or more') !== false) {
                    // Extract the number before 'k or more'
                    preg_match('/(\d+)k/', $salary, $matches);
                    $min_salary = isset($matches[1]) ? intval($matches[1]) * 1000 : 0;
                    $conditions[] = "Salary >= $min_salary";
                } elseif (strpos($salary, '-') !== false) {
                    list($min_salary, $max_salary) = explode('-', $salary);
                    $min_salary = intval(str_replace('k', '000', $min_salary));
                    $max_salary = intval(str_replace('k', '000', $max_salary));
                    $conditions[] = "Salary BETWEEN $min_salary AND $max_salary";
                }
                }

                // Job type filter (Schedule column)
                if (!empty($job_type)) {
                    $conditions[] = "Schedule = '$job_type'";
                }

                // Education filter
                if (!empty($education)) {
                    $conditions[] = "education = '$education'";
                }

                $where_clause = "";
                if (count($conditions) > 0) {
                    $where_clause = "WHERE " . implode(" AND ", $conditions);
                }

                $sql = "SELECT * FROM joblist $where_clause ORDER BY posted_date DESC";
            } else {
                $sql = "SELECT * FROM joblist ORDER BY posted_date DESC";
            }
            $result = $conn->query($sql);
            if (!$result) {
                echo "<p style='color:red; text-align:center;'>Database query error: " . htmlspecialchars($conn->error) . "</p>";
            } else {
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        $imagePath = !empty($row['Photo']) ? $row['Photo'] : 'https://cdn-icons-png.freepik.com/256/3291/3291670.png';
                ?>
                <div class="box">
                    <div class="company">
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Job Image" />
                        <div>
                            <h3><?= htmlspecialchars($row['companyName'] ?? 'Company') ?></h3>
                            <p><?= date('F j, Y', strtotime($row['posted_date'])) ?></p>
                        </div>
                    </div>
                    <div class="job-info">
                        <h3 class="job-title"><?= htmlspecialchars($row['jobTitle']) ?></h3>
                        <p class="location"><i class="fas fa-map-marker-alt"></i> <span><?= htmlspecialchars($row['location']) ?></span></p>
                    </div>
                    <div class="tags">
                        <p><i class="fas fa-solid fa-peso-sign"></i><span><?= htmlspecialchars($row['Salary']) ?></span></p>
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
