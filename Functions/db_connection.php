
<?php
// Use mysqli_connect with error handling
$conn = new mysqli("localhost", "root", "", "online_job_portal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>


