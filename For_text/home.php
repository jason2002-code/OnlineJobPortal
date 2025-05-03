<?php
include("../Functions/db_connection.php");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlineJobs.ph</title>
    <link rel="stylesheet" href="../For_design/design.css">
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

<section class="category">
    <h1 class="heading">Job Categories</h1>
    <div class="box-container">
        <a href="#" class="box">
            <i class="fas fa-code"></i>
            <div>
                <h3>Development</h3>
                <span>2200 jobs</span>
            </div>
        </a>
        <a href="#" class="box">
            <i class="fas fa-pen"></i>
            <div>
                <h3>Designer</h3>
                <span>500 jobs</span>
            </div>
        </a>
        <a href="#" class="box">
            <i class="fas fa-chalkboard-user"></i>
            <div>
                <h3>Teacher</h3>
                <span>1500 jobs</span>
            </div>
        </a>
        <a href="#" class="box">
            <i class="fas fa-headset"></i>
            <div>
                <h3>Service</h3>
                <span>3100 jobs</span>
            </div>
        </a>
        <a href="#" class="box">
            <i class="fas fa-wrench"></i>
            <div>
                <h3>Engineer</h3>
                <span>400 jobs</span>
            </div>
        </a>
        <a href="#" class="box">
            <i class="fas fa-hand-holding-dollar"></i>
            <div>
                <h3>Finance</h3>
                <span>1000 jobs</span>
            </div>
        </a>
        <a href="#" class="box">
            <i class="fas fa-wrench"></i>
            <div>
                <h3>Labour</h3>
                <span>4000 jobs</span>
            </div>
        </a>
    </div>
</section>

<section class="jobs-container">
    <h1 class="heading">Latest Jobs</h1>
    <div class="box-container">
        <?php
        $sql = "SELECT * FROM joblist ORDER BY posted_date DESC LIMIT 6";
        $result = $conn->query($sql);

        if ($result->num_rows > 0):
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
                    if ($stmt = $conn->prepare($employerQuery)) {
                        $stmt->bind_param("i", $employerID);
                        $stmt->execute();
                        $stmt->bind_result($fetchedCompanyName);
                        if ($stmt->fetch()) {
                            $companyName = $fetchedCompanyName;
                        }
                        $stmt->close();
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
        