<?php
session_start();
include("../Functions/db_connection.php");

// Check if employer is logged in
if (!isset($_SESSION['employerID'])) {
    header('location:login.php');
    exit();
}

$employer_id = $_SESSION['employerID'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Home</title>
    <link rel="stylesheet" href="../For_design/empdash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
    <div class="main">
        <h2>Welcome, Employer!</h2>

        <section class="latest-jobs">
            <h4><i class="fas fa-briefcase"></i> Latest Jobs</h4>
            <form method="GET" style="margin-bottom: 1rem;">
                <label>
                    <input type="checkbox" name="open_only" value="1" <?php if (isset($_GET['open_only'])) echo 'checked'; ?>>
                    Show Open Only
                </label>
                <button type="submit">Apply</button>
            </form>
            <div class="job-listings">
                <?php
                $query = "SELECT jobID, jobTitle, posted_date, status FROM joblist WHERE employerID = '$employer_id'";
                if (isset($_GET['open_only'])) {
                    $query .= " AND status = 'Open'";
                }
                $query .= " ORDER BY posted_date DESC";

                $result = mysqli_query($conn, $query);

                if (mysqli_num_rows($result) > 0) {
                    while ($job = mysqli_fetch_assoc($result)) {
                        echo "
                            <div class='job-card'>
                                <h5>" . htmlspecialchars($job['jobTitle']) . "</h5>
                                <p>Status: <strong>" . htmlspecialchars($job['status']) . "</strong></p>
                                <p>Posted: " . htmlspecialchars($job['posted_date']) . "</p>
                                <a href='view_job.php?jobID=" . $job['jobID'] . "' class='view-btn'>View Details</a>
                            </div>
                        ";
                    }
                } else {
                    echo "<p>No jobs posted yet.</p>";
                }
                ?>
            </div>
        </section>
    </div>

</body>
</html>
