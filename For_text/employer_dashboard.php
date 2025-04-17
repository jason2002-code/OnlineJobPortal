<?php
session_start();
include("../Functions/db_connection.php");

// Check if employer is logged in
if (!isset($_SESSION['employerID'])) {
    header('location:login.php');
    exit();
}

// Get employer details
$employer_id = $_SESSION['employerID'];
$select_employer = "SELECT * FROM employers WHERE employerID = '$employer_id'";
$result_employer = mysqli_query($conn, $select_employer);
$employer = mysqli_fetch_assoc($result_employer);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard</title>
    <link rel="stylesheet" href="../For_design/empdash.css">
</head>
<body>
    <div class="sidebar">
        <h2>Employer Dashboard</h2>
        <ul>
            <li class="active">Dashboard</li>
            <li>Recruitment</li>
            <li>Interview</li>
            <li>Onboarding</li>
            <li>Interview Task</li>
            <li>Appointments</li>
            <li>Training</li>
        </ul>
        <form action="logout.php" method="post">
            <button class="logout">Logout</button>
        </form>
    </div>

    <div class="main">
        <header>
            <input type="text" placeholder="Search something...">
            <button class="add-new">Add New</button>
        </header>

        <section class="welcome">
            <h3>Good Morning <?php echo 'Sara'; ?></h3>
            <p>You have 75+ new applications. A lot of work for today!</p>
            <button>Explore</button>
        </section>

        <section class="hire-needs">
            <h4>You Need to hire</h4>
            <div class="cards">
                <?php
                $roles = ["Content Developer", "Full Developer", "UI/UX Designer", "iOS Developer", "Android Developer"];
                foreach ($roles as $role) {
                    echo "<div class='card'>$role</div>";
                }
                ?>
            </div>
        </section>

        <section class="recruitment-progress">
            <h4>Recruitment Progress</h4>
            <table>
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Description</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $candidates = [
                        ["John Doe", "UI/UX Designer", "Tech Interview"],
                        ["Sam Emmanuel", "UI/UX Designer", "Resume Review"],
                        ["John Samuel", "Content Developer", "Final Interview"]
                    ];
                    foreach ($candidates as $index => $c) {
                        $class = $index === 1 ? 'class="highlight"' : '';
                        echo "<tr $class><td>{$c[0]}</td><td>{$c[1]}</td><td>{$c[2]}</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </div>

    <div class="sidebar-right">
        <div class="profile">
            <p><?php echo 'Sara Abraham'; ?></p>
        </div>
        <div class="calendar">
            <h4>Schedule Calendar</h4>
            <div class="dates">
                <?php foreach ([24, 25, 26, 27, 28] as $day) echo "<span>$day</span>"; ?>
            </div>
        </div>

        <div class="new-applicants">
            <h4>New Applicants</h4>
            <ul>
                <?php
                $applicants = ["Mike Tyson", "Zann Thomas", "Neeru Abraham", "John Samuel"];
                foreach ($applicants as $name) echo "<li>$name</li>";
                ?>
            </ul>
        </div>

        <div class="training">
            <h4>Ready For Training</h4>
            <div class="trainees">
                <?php
                $trainees = ["Alex", "Sam", "Maria"];
                foreach ($trainees as $trainee) echo "<div>$trainee</div>";
                ?>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
