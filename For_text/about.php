<?php
include("../Functions/db_connection.php");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">



    <title>about</title>

    <link rel="stylesheet" href="../For_design/design.css">


</head>

</body>


    <header class="header">
        <section class="flex">
            <div id="menu-btn" class="fas fa-bars-staggered"></div>


            <a href="home.html" class="logo"><i class="fas fa-briefcase"></i>
                Upwork.</a>


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

    <?php include 'feedback_popup.php'; ?>

    <div class="section-title">about us</div>


    <section class="about">
        <img src="https://i.pinimg.com/736x/51/11/db/5111db85e1ed27212a67f6d7146b5b2e.jpg" alt="">
        <div class="box">

            <h3>why choose us?</h3>
            <p>In the competitive landscape of job portals, we stand out by prioritizing both talent and opportunity.
                For job seekers, we offer a curated selection of diverse roles, personalized matching, and resources to empower career growth.
                For employers, we provide access to a pool of vetted professionals, streamlined hiring tools, and dedicated support to find the perfect fit.
                Our platform fosters genuine connections, ensuring a seamless experience for all, making us the premier choice for your career or hiring needs.</p>

            <p> "Whether you're a skilled freelancer seeking rewarding projects or a business in need of top-tier talent, our platform is designed for your success.
                We offer a seamless experience, connecting you with vetted professionals and diverse opportunities globally.
                With secure payments, efficient project management tools, and a commitment to fostering productive collaborations,
                we empower you to achieve your professional goals. Join our community and experience the difference of a platform built on trust, efficiency, and growth."
            </p>
            <a href="contact.php" class="btn">contact us</a>
        </div>

    </section>


    <?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    include("../Functions/db_connection.php");

    // Fetch feedback with reviewer info
$sql = "SELECT f.message, f.dateSubmitted, f.reviewerRole, f.rate,
        e.companyName, e.Logo,
        js.fullName, js.profilePic
        FROM feedback f
        LEFT JOIN employers e ON f.reviewerRole = 'employer' AND f.reviewerID = e.employerID
        LEFT JOIN jobseeker_profiles js ON f.reviewerRole = 'jobseeker' AND f.reviewerID = js.user_Id
        ORDER BY f.dateSubmitted DESC
        LIMIT 10";
    $result = mysqli_query($conn, $sql);
    ?>

    <div class="section-title">top reviews</div>

    <section class="reviews">
        <div class="box-container">
            <?php
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $name = $row['reviewerRole'] === 'employer' ? $row['companyName'] : $row['fullName'];
$photo = $row['reviewerRole'] === 'employer' ? (isset($row['Logo']) ? $row['Logo'] : '') : $row['profilePic'];
if (!empty($photo)) {
    if ($row['reviewerRole'] === 'employer') {
        // Check if $photo already contains 'uploads/' prefix
        if (strpos($photo, 'uploads/') === 0) {
            $photoPath = "../For_text/" . htmlspecialchars($photo);
        } else {
            $photoPath = "../For_text/uploads/" . htmlspecialchars($photo);
        }
    } else {
        $photoPath = "../For_text/uploads/" . htmlspecialchars($photo);
    }
} else {
    $photoPath = "https://via.placeholder.com/150";
}

// Fix for employer logo path if not displaying
if ($row['reviewerRole'] === 'employer' && (!file_exists($photoPath) || empty($photo))) {
    $photoPath = "../For_text/uploads/default_company_logo.png";
}
            ?>
                    <div class="box">
                        <div class="stars">
                            <?php
$rating = isset($row['rate']) ? (int)$row['rate'] : 0;
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                        <h3 class="title"><?php echo htmlspecialchars($name); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                        <div class="user">
                            <img src="<?php echo $photoPath; ?>" alt="<?php echo htmlspecialchars($name); ?>">
                            <div>
                                <h3><?php echo htmlspecialchars($name); ?></h3>
                                <span><?php echo htmlspecialchars($row['reviewerRole']); ?></span>
                            </div>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo "<p>No reviews found.</p>";
            }
            ?>
        </div>
    </section>

    <div class="section-title">Submit Your Review</div>

    <?php if (isset($_SESSION['feedback_success'])): ?>
        <div id="feedbackSuccess" class="alert success"><?php echo htmlspecialchars($_SESSION['feedback_success']); ?></div>
        <?php unset($_SESSION['feedback_success']); ?>
        <script>
            setTimeout(function() {
                var feedbackDiv = document.getElementById('feedbackSuccess');
                if (feedbackDiv) {
                    feedbackDiv.style.display = 'none';
                }
            }, 3000);
        </script>
    <?php endif; ?>

    <?php if (isset($_SESSION['feedback_error'])): ?>
        <div class="alert error"><?php echo htmlspecialchars($_SESSION['feedback_error']); ?></div>
        <?php unset($_SESSION['feedback_error']); ?>
    <?php endif; ?>

    <!-- Removed the review submission form as per user request -->
    <?php /* if (isset($_SESSION['employerID']) || isset($_SESSION['user_Id'])): ?>
    <section class="feedback-form">
        <form action="submit_feedback.php" method="POST">
            <textarea name="message" rows="4" placeholder="Write your review here..." required></textarea>
            <button type="submit" class="btn">Submit Review</button>
        </form>
    </section>
<?php else: ?>
    <p>Please <a href="login.php">log in</a> to submit a review.</p>
<?php endif; */ ?>







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

</body>
</html>