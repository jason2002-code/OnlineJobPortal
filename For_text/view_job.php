<?php
include("../Functions/db_connection.php");

if (!isset($_GET['jobID'])) {
    echo "No job selected.";
    exit();
}

$jobID = $_GET['jobID'];

$query = "SELECT * FROM joblist WHERE jobID = '$jobID'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    echo "Job not found.";
    exit();
}

$job = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">



    <title>View job</title>

    <link rel="stylesheet" href="../For_design/design.css">
    <link rel="stylesheet" href="../For_design/save_job_button.css">


</head>

<body>


    <header class="header">
        <section class="flex">
            <div id="menu-btn" class="fas fa-bars-staggered"></div>


            <a href="home.php" class="logo"><i class="fas fa-briefcase"></i>
                Upwork.</a>


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

    <section class="job-details">

        <h1 class="heading">job details</h1>

        <div class="details">
            <div class="job-info">
                <h3><?= htmlspecialchars($job['jobTitle']) ?></h3>
                <a href="view_company.php?employerID=<?= $job['employerID'] ?>">Company Profile</a>
                <p><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($job['location']) ?></p>


            </div>
            <div class="basic-details">
                <h3>salary</h3>
                <p><?= htmlspecialchars($job['Salary']) ?></p>
                <h3>benefits</h3>
                <p><?= htmlspecialchars($job['Benefits']) ?></p>
                <h3>schedule</h3>
                <p><?= htmlspecialchars($job['Schedule']) ?></p>

            </div>
            <ul>
                <h3>requirements</h3>
                <li>education : <span><?= htmlspecialchars($job['education']) ?></span></li>
                <li>Skills : <span><?= htmlspecialchars($job['Skills']) ?></span></li>
                <li>experience : <span><?= htmlspecialchars($job['Experience']) ?></span></li>

            </ul>
           
            <ul>
                <h3>description</h3>
                <p><?= nl2br(htmlspecialchars($job['description'])) ?></p>
                <ul>
                    <li>Posted on <?= htmlspecialchars(date('F j, Y', strtotime($job['posted_date']))) ?></li>
                </ul>
                </ul>
            </div>
        <form action="" method="post" class="flex-btn" id="applyForm">
            <input type="submit" value="apply now" name="apply" class="btn" id="applyBtn">
<button type="submit" class="save save-job-btn">
<input type="submit" value="save job" name="apply" class="btn" id="applyBtn">
</button>
        </form>

        <?php
        session_start();
        if (isset($_SESSION['employerID']) && $_SESSION['employerID'] == $job['employerID']) {
           
        }

        // Handle delete job request from this page
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_job']) && isset($_POST['delete_jobID'])) {
            $delete_jobID = intval($_POST['delete_jobID']);
            $employerID = $_SESSION['employerID'];

            if ($delete_jobID === $job['jobID'] && $employerID === $job['employerID']) {
                include("../Functions/db_connection.php");
                $stmt = $conn->prepare("DELETE FROM joblist WHERE jobID = ? AND employerID = ?");
                if ($stmt) {
                    $stmt->bind_param("ii", $delete_jobID, $employerID);
                    if ($stmt->execute()) {
                        $_SESSION['success'] = "Job deleted successfully.";
                        header("Location: employer_dashboard.php");
                        exit();
                    } else {
                        echo "<p class='error'>Failed to delete job.</p>";
                    }
                    $stmt->close();
                } else {
                    echo "<p class='error'>Failed to prepare delete statement.</p>";
                }
            } else {
                echo "<p class='error'>Unauthorized delete attempt.</p>";
            }
        }
        ?>

            <script>
                document.getElementById('applyForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    // Check if user is logged in (this would typically check a session/cookie)
                    const isLoggedIn = checkLoginStatus(); // Function from script.js

                    if (!isLoggedIn) {
                        // Store the current job URL to redirect back after login
                        fetch('set_redirect.php?url=' + encodeURIComponent(window.location.href))
                            .then(() => {
                                window.location.href = 'login.php';
                            });
                    } else {
                        // If logged in, submit the form normally
                        this.submit();
                    }
                });
            </script>
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


    <body>

</html>