<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jobID'])) {
    $jobID = $_POST['jobID'];

    if (isset($_SESSION['saved_jobs']) && is_array($_SESSION['saved_jobs'])) {
        $key = array_search($jobID, $_SESSION['saved_jobs']);
        if ($key !== false) {
            unset($_SESSION['saved_jobs'][$key]);
            // Reindex the array to prevent gaps in keys
            $_SESSION['saved_jobs'] = array_values($_SESSION['saved_jobs']);
        }
    }
}

header("Location: jobseeker_dashboard.php");
exit;
?>
