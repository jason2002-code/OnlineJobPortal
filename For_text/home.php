<?php
include("../Functions/db_connection.php");

$categoryID = isset($_GET['categoryID']) ? intval($_GET['categoryID']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlineJobs.ph</title>
    <link rel="stylesheet" href="../For_design/Stylehome.css">
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
</header>


<div class="home-container">
    <section class="home">
        <form action="jobs.php" method="post">
            <h3>Find your next job</h3>
            <p>Job title<span>*</span></p>
            <input type="text" name="title" placeholder="keyword, category or company" required maxlength="20" class="input">
            <p>Job location</p>
            <input type="text" name="location" placeholder="city, state or country" required maxlength="50" class="input">
            <input type="submit" value="Search job" name="search" class="btn">
        </form>
    </section>
</div>

<?php
include("../Functions/Category.php");
$categoryObj = new Category($conn);
$categories = $categoryObj->getAllCategories();
?>

<section class="category">
    <h1 class="heading">Job Categories</h1>
    <div class="box-container">
        <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $category): ?>
                <?php
                $jobCount = $categoryObj->getJobCountByCategory($category['categoryID']);
                // Map category icons based on category name or ID (example mapping)
                $iconMap = [
                    'Development' => 'fas fa-code',
                    'Designer' => 'fas fa-pen',
                    'Teacher' => 'fas fa-chalkboard-user',
                    'Service' => 'fas fa-headset',
                    'Engineer' => 'fas fa-wrench',
                    'Finance' => 'fas fa-hand-holding-dollar',
                    'Labour' => 'fas fa-hard-hat',
                ];
                $iconClass = $iconMap[$category['categoryName']] ?? 'fas fa-briefcase';
                ?>
                <a href="home.php?categoryID=<?= htmlspecialchars($category['categoryID']) ?>" class="box">
                    <i class="<?= htmlspecialchars($iconClass) ?>"></i>
                    <div>
                        <h3><?= htmlspecialchars($category['categoryName']) ?></h3>
                        <span><?= htmlspecialchars($jobCount) ?> jobs</span>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No categories found.</p>
        <?php endif; ?>
    </div>
</section>

<section class="jobs-container">
    <h1 class="heading">Latest Jobs</h1>
    <div class="box-container">
        <?php
        if ($categoryID > 0) {
            $stmt = $conn->prepare("SELECT * FROM joblist WHERE categoryID = ? ORDER BY posted_date DESC LIMIT 6");
            $stmt->bind_param("i", $categoryID);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql = "SELECT * FROM joblist ORDER BY posted_date DESC LIMIT 6";
            $result = $conn->query($sql);
        }

        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
$imagePath = !empty($row['Photo']) ? 
    ('uploads/' . basename($row['Photo'])) : 
    '../images/default-job.png';
        ?>
        <div class="box">
            <div class="company">
            <img src="<?php echo htmlspecialchars($row['Photo']); ?>" alt="Job Image">
                <div>
                    <h3>
                    <?php
                    $employerID = $row['employerID'];
                    $companyName = 'Company';
                    $employerQuery = "SELECT companyName FROM employers WHERE employerID = ?";
                    if ($stmt2 = $conn->prepare($employerQuery)) {
                        $stmt2->bind_param("i", $employerID);
                        $stmt2->execute();
                        $stmt2->bind_result($fetchedCompanyName);
                        if ($stmt2->fetch()) {
                            $companyName = $fetchedCompanyName;
                        }
                        $stmt2->close();
                    }
                    echo htmlspecialchars($companyName);
                    ?>
                    </h3>
                    <p><?= date('F j, Y', strtotime($row['posted_date'])) ?></p>
                </div>
            </div>
            <h3 class="job-title"><?= htmlspecialchars($row['jobTitle']) ?></h3>
            <p class="location"><i class="fas fa-map-marker-alt"></i> <span><?= htmlspecialchars($row['location']) ?></span></p>
            <div class="tags">
                <p><i class="fas fa-solid fa-peso-sign"></i><span><?= htmlspecialchars($row['Salary']) ?></span></p>
                <p><i class="fas fa-briefcase"></i><span><?= htmlspecialchars($row['status']) ?></span></p>
                <p><i class="fas fa-clock"></i><span><?= htmlspecialchars($row['Schedule']) ?></span></p>
            </div>
            <div class="flex-btn">
                <a href="view_job.php?jobID=<?= $row['jobID'] ?>" class="btn">View Details</a>
                <button type="submit" class="far fa-heart" name="save"></button>
            </div>
        </div>
        <?php
            endwhile;
        else:
            echo "<p style='text-align:center;'>No job postings found.</p>";
        endif;
        ?>
    </div>

    <div style="text-align:center; margin: 20px 0;">
        <a href="jobs.php" class="btn">View All Jobs</a>
    </div>
</section>

<footer class="footer">
    <section class="grid">
        <div class="box">
            <h3>Quick Links</h3>
            <a href="home.php"><i class="fas fa-angle-right"></i> Home</a>
            <a href="about.php"><i class="fas fa-angle-right"></i> About</a>
            <a href="jobs.php"><i class="fas fa-angle-right"></i> All Jobs</a>
            <a href="contact.php"><i class="fas fa-angle-right"></i> Contact Us</a>
        </div>
        <div class="box">
            <h3>Extra Links</h3>
            <a href="#"><i class="fas fa-angle-right"></i> Account</a>
            <a href="login.php"><i class="fas fa-angle-right"></i> Login</a>
            <a href="register.php"><i class="fas fa-angle-right"></i> Register</a>
            <a href="#"><i class="fas fa-angle-right"></i> Post Job</a>
            <a href="#"><i class="fas fa-angle-right"></i> Dashboard</a>
        </div>
        <div class="box">
            <h3>Follow Us</h3>
            <a href="#"><i class="fab fa-facebook"></i> Facebook</a>
            <a href="#"><i class="fab fa-twitter"></i> Twitter</a>
            <a href="#"><i class="fab fa-instagram"></i> Instagram</a>
            <a href="#"><i class="fab fa-linkedin"></i> LinkedIn</a>
            <a href="#"><i class="fab fa-youtube"></i> YouTube</a>
        </div>
    </section>
    <div class="credit">&copy; copyright @ 2025 by <span>mr. web designer</span> | All rights reserved!</div>
</footer>

<script src="../Functions/script.js"></script>
</body>
</html>
