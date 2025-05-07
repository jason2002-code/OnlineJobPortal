<?php
include("../Functions/db_connection.php");
?>


<?php
$employerID = isset($_GET['employerID']) ? intval($_GET['employerID']) : 0;

if ($employerID <= 0) {
    echo "<p>Invalid employer ID.</p>";
    exit();
}

// Fetch employer info
$employerQuery = "SELECT * FROM employers WHERE employerID = ?";
$employerStmt = $conn->prepare($employerQuery);
$employerStmt->bind_param("i", $employerID);
$employerStmt->execute();
$employerResult = $employerStmt->get_result();

if ($employerResult->num_rows === 0) {
    echo "<p>Employer not found.</p>";
    exit();
}

$employer = $employerResult->fetch_assoc();

// Count jobs
$jobCountQuery = "SELECT COUNT(*) as jobCount FROM joblist WHERE employerID = ?";
$jobCountStmt = $conn->prepare($jobCountQuery);
$jobCountStmt->bind_param("i", $employerID);
$jobCountStmt->execute();
$jobCountResult = $jobCountStmt->get_result()->fetch_assoc();
$jobCount = $jobCountResult['jobCount'];

// Count employees (fake number for now if no employee table)
$employeeCount = 253; // Replace with a query if you have an `employees` table
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

   
   
    <title>View Company</title>

    <link rel="stylesheet" href="../For_design/design.css">
    

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
            <?php if (isset($_SESSION['employerID'])): ?>
                <a href="employer_dashboard.php?openPostJob=1" class="btn" style="margin-top: 0;">post job</a>
            <?php else: ?>
                <a href="login.php" class="btn" style="margin-top: 0;">post job</a>
            <?php endif; ?>
        </section>


    </header>

      
    <section class="view-company">

        <h1 class="heading">company details</h1>

        <div class="details">
        <div class="info">
        <?php
        $logoPath = !empty($employer['Logo']) ? 'uploads/' . basename($employer['Logo']) : 'uploads/default-logo.png';
        ?>
        <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Company Logo" style="max-width: 300px; height: auto;" onerror="this.onerror=null;this.src='uploads/default-logo.png';" />
    <h3><?= htmlspecialchars($employer['companyName']) ?></h3>
    <?php // Removed undefined $row['Logo'] block ?>

    <p><i class="fas fa-map-marker-alt"></i>Tagbilaran, Bohol</p>
</div>

<div class="description">
    <h3>about company</h3>
    <p><?= nl2br(htmlspecialchars($employer['empdescription'])) ?></p>
</div>

<ul>
    <li><?= $jobCount ?> jobs posted</li>
    <li>established at <?= htmlspecialchars($employer['DateStab']) ?></li>
    <li><?= $employeeCount ?> working employees</li>
</ul>

        
        </div>
    </section>

    <section class="jobs-container">
        <h1 class="heading">jobs they Offer</h1>
    
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