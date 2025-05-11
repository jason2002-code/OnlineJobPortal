<?php
session_start();
include("../Functions/db_connection.php");

session_start();
// Check if employer is logged in (assuming employer session variable)
if (!isset($_SESSION['employerID'])) {
    header("Location: login.php");
    exit;
}





// Fetch offers for this employer's jobs
$sql = "SELECT o.offerID, o.applicationID, o.salary, o.startDate, o.offerStatus,
        ja.user_Id, js.fullName AS jobseekerName,
        j.jobTitle,
        e.companyName
        FROM job_offers o
        JOIN jobapplications ja ON o.applicationID = ja.applicationID
        JOIN jobseeker_profiles js ON ja.user_Id = js.user_Id
        JOIN joblist j ON ja.jobID = j.jobID
        JOIN employers e ON j.employerID = e.employerID
        WHERE e.employerID = ?
        ORDER BY o.offerID DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employerID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Offers Log - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../For_design/Jobseekstyle.css" />
</head>
<body>
<div class="container mt-5">
    <h2>All Job Offers</h2>
    <?php if ($result && $result->num_rows > 0): ?>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Offer ID</th>
                    <th>Job Title</th>
                    <th>Company</th>
                    <th>Jobseeker</th>
                    <th>Salary</th>
                    <th>Start Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($offer = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($offer['offerID']); ?></td>
                    <td><?php echo htmlspecialchars($offer['jobTitle']); ?></td>
                    <td><?php echo htmlspecialchars($offer['companyName']); ?></td>
                    <td><?php echo htmlspecialchars($offer['jobseekerName']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($offer['salary'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($offer['startDate']); ?></td>
                    <td><?php echo htmlspecialchars($offer['offerStatus']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No offers found.</p>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
