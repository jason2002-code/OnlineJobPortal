<?php
include_once '../Functions/db_connection.php';

// Initialize counts
$userCount = $employerCount = $applicationCount = $jobCount = 0;

// Count queries
$userCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM jobseekers"))['count'];
$employerCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM employers"))['count'];
$applicationCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM jobapplications"))['count'];
$jobCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM joblist"))['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Site Analytics Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
         body {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    background-size: 400% 400%;
    animation: gradientBG 10s ease infinite;
    font-family: 'Poppins', sans-serif;
        }
        @keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
        .analytics-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
        .card-box {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.05);
            padding: 30px;
            text-align: center;
            transition: transform 0.2s ease;
        }
        .card-box:hover {
            transform: translateY(-5px);
        }
        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #4e73df;
        }
        .card-title {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        .card-value {
            font-size: 2rem;
            font-weight: 700;
            color: #222;
        }
    </style>
</head>
<body>

<div class="analytics-container">
    <h2 class="text-center mb-4">📊 Site Analytics Dashboard</h2>
    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="card-box">
                <div class="card-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="card-title">Total Jobseekers</div>
                <div class="card-value"><?= $userCount ?></div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-box">
                <div class="card-icon"><i class="fas fa-building"></i></div>
                <div class="card-title">Total Employers</div>
                <div class="card-value"><?= $employerCount ?></div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-box">
                <div class="card-icon"><i class="fas fa-briefcase"></i></div>
                <div class="card-title">Jobs Posted</div>
                <div class="card-value"><?= $jobCount ?></div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-box">
                <div class="card-icon"><i class="fas fa-file-alt"></i></div>
                <div class="card-title">Applications</div>
                <div class="card-value"><?= $applicationCount ?></div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
