<?php
session_start();
include("../Functions/db_connection.php");

// Check login
if (!isset($_SESSION['employerID'])) {
    header("Location: login.php");
    exit();
}

$employerID = $_SESSION['employerID'];

// Fetch applications
$sql = "SELECT a.applicationID, js.fullName, j.jobTitle, a.Status1, a.applicationDate
        FROM jobapplications a
        JOIN joblist j ON a.jobID = j.jobID
        JOIN jobseeker_profiles js ON a.user_Id = js.user_Id
        WHERE j.employerID = ?
        ORDER BY a.applicationDate DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employerID);
$stmt->execute();
$result = $stmt->get_result();
$applications = [];
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}
$stmt->close();

// Stats
function getCount($conn, $query, $id) {
    $count = 0;
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count;
}

$totalJobs = getCount($conn, "SELECT COUNT(*) FROM joblist WHERE employerID = ?", $employerID);
$openJobs = getCount($conn, "SELECT COUNT(*) FROM joblist WHERE employerID = ? AND status = 'Open'", $employerID);
$closedJobs = getCount($conn, "SELECT COUNT(*) FROM joblist WHERE employerID = ? AND status = 'Closed'", $employerID);
$accepted = getCount($conn, "SELECT COUNT(*) FROM jobapplications a JOIN joblist j ON a.jobID = j.jobID WHERE j.employerID = ? AND a.Status1 = 'accepted'", $employerID);
$rejected = getCount($conn, "SELECT COUNT(*) FROM jobapplications a JOIN joblist j ON a.jobID = j.jobID WHERE j.employerID = ? AND a.Status1 = 'rejected'", $employerID);
$pending = getCount($conn, "SELECT COUNT(*) FROM jobapplications a JOIN joblist j ON a.jobID = j.jobID WHERE j.employerID = ? AND (a.Status1 IS NULL OR a.Status1 NOT IN ('accepted', 'rejected'))", $employerID);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Applications</title>
    <link rel="stylesheet" href="../For_design/explore.style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <h1>Application Overview</h1>

        <div class="stats">
            <div class="stat-card">Total Jobs: <strong><?= $totalJobs ?></strong></div>
            <div class="stat-card">Open Jobs: <strong><?= $openJobs ?></strong></div>
            <div class="stat-card">Closed Jobs: <strong><?= $closedJobs ?></strong></div>
            <div class="stat-card">Accepted Apps: <strong><?= $accepted ?></strong></div>
            <div class="stat-card">Rejected Apps: <strong><?= $rejected ?></strong></div>
            <div class="stat-card">Pending Apps: <strong><?= $pending ?></strong></div>
        </div>

        <canvas id="appChart" height="100"></canvas>

        <h2>All Applications</h2>
        <table>
            <thead>
                <tr>
                    <th>Applicant</th>
                    <th>Job Title</th>
                    <th>Status</th>
                    <th>Applied On</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= htmlspecialchars($app['fullName']) ?></td>
                        <td><?= htmlspecialchars($app['jobTitle']) ?></td>
                        <td><?= htmlspecialchars($app['Status1'] ?? 'Pending') ?></td>
                        <td><?= date('M d, Y', strtotime($app['applicationDate'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        const ctx = document.getElementById('appChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Accepted', 'Rejected', 'Pending'],
                datasets: [{
                    label: 'Application Status',
                    data: [<?= $accepted ?>, <?= $rejected ?>, <?= $pending ?>],
                    backgroundColor: ['#4CAF50', '#F44336', '#FFC107']
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    </script>
</body>
</html>
