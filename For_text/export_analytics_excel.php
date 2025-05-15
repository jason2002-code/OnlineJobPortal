<?php
include_once '../Functions/db_connection.php';

$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Validate format
if (!preg_match('/\d{4}-\d{2}-\d{2}/', $startDate)) $startDate = date('Y-m-d', strtotime('-30 days'));
if (!preg_match('/\d{4}-\d{2}-\d{2}/', $endDate)) $endDate = date('Y-m-d');


// Jobseekers count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM jobseekers");
$row = mysqli_fetch_assoc($result);
$userCount = $row['count'] ?? 0;

// Employers count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM employers");
$row = mysqli_fetch_assoc($result);
$employerCount = $row['count'] ?? 0;

// Jobs count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM joblist");
$row = mysqli_fetch_assoc($result);
$jobCount = $row['count'] ?? 0;

// Applications count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM jobapplications");
$row = mysqli_fetch_assoc($result);
$applicationCount = $row['count'] ?? 0;


// Set headers for download
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="site_analytics_' . date('Y-m-d') . '.csv"');

// Output CSV
$output = fopen('php://output', 'w');
fputcsv($output, ['Metric', 'Count']);
fputcsv($output, ['Start Date', $startDate]);
fputcsv($output, ['End Date', $endDate]);
fputcsv($output, ['Total Jobseekers', $userCount]);
fputcsv($output, ['Total Employers', $employerCount]);
fputcsv($output, ['Jobs Posted', $jobCount]);
fputcsv($output, ['Applications Submitted', $applicationCount]);
fclose($output);
exit;
?>
