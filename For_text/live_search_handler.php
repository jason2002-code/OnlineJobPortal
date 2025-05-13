<?php
session_start();
include("../Functions/db_connection.php");
include_once("../Functions/employer_search_functions.php");

$employerID = $_SESSION['employerID'] ?? 0;
$query = trim($_GET['q'] ?? '');

if (!$employerID || $query === '') {
    exit;
}

echo "<ul>";

$jobs = searchJobsByEmployer($conn, $employerID, ['title' => $query]);
$applicants = searchApplicantsByEmployer($conn, $employerID, ['fullName' => $query]);
$offers = searchOffersByEmployer($conn, $employerID, ['jobTitle' => $query]);

if ($jobs) {
    echo "<li><strong>Jobs</strong></li>";
    foreach ($jobs as $job) {
        echo "<li><a href='view_job.php?jobID={$job['jobID']}'>" . htmlspecialchars($job['jobTitle']) . "</a></li>";
    }
}

if ($applicants) {
    echo "<li><strong>Applicants</strong></li>";
    foreach ($applicants as $app) {
        echo "<li><a href='view_jobseeker_profile.php?user_Id={$app['user_Id']}'>" . htmlspecialchars($app['fullName']) . " - " . htmlspecialchars($app['jobTitle']) . "</a></li>";
    }
}

if ($offers) {
    echo "<li><strong>Offers</strong></li>";
    foreach ($offers as $offer) {
        echo "<li><a href='offers_log.php?offerID={$offer['offerID']}'>" . htmlspecialchars($offer['jobTitle']) . " - " . htmlspecialchars($offer['offerStatus']) . "</a></li>";
    }
}

if (!$jobs && !$applicants && !$offers) {
    echo "<li>No results found!!.</li>";
}

echo "</ul>";
?>
