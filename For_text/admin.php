<?php
include("../Functions/db_connection.php");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../For_design/adminS.css">
    <style>
       
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Admin Dashboard</h1>
        <nav>
            <ul>
                <li><a href="#" onclick="showSection('employers')">Employers</a></li>
                <li><a href="#" onclick="showSection('jobseekers')">Jobseekers</a></li>
                <li><a href="#" onclick="showSection('joblistings')">Job Listings</a></li>
                <li><a href="#" onclick="showSection('notifications')">Notifications</a></li>
                <li><a href="logout.php" class="logout">Logout</a></li>
            </ul>
        </nav>
    </div>
    <div class="dashboard">
        <div id="employers" class="section active">
            <h2>Employers</h2>
            <ul id="employers-list">
                <!-- Dummy data to be replaced with real data -->
                <li>Acme Corp<button onclick="viewEmployerDetails('Acme Corp')">View</button></li>
                <li>Widget Inc<button onclick="viewEmployerDetails('Widget Inc')">View</button></li>
            </ul>
        </div>
        <div id="jobseekers" class="section">
            <h2>Jobseekers</h2>
            <ul id="jobseekers-list">
                <!-- Dummy data to be replaced with real data -->
                <li>Jane Doe<button onclick="viewJobseekerDetails('Jane Doe')">View</button></li>
                <li>John Smith<button onclick="viewJobseekerDetails('John Smith')">View</button></li>
            </ul>
        </div>
        <div id="joblistings" class="section">
            <h2>Job Listings</h2>
            <ul id="job-listings">
                <!-- Dummy data to be replaced with real data -->
                <li>Software Engineer<button onclick="editJobListing('Software Engineer')">Edit</button></li>
                <li>Product Manager<button onclick="editJobListing('Product Manager')">Edit</button></li>
            </ul>
        </div>
        <div id="notifications" class="section">
            <h2>Notifications</h2>
            <ul id="notifications">
                <!-- Dummy data to be replaced with real data -->
                <li>New Employer Registered</li>
                <li>New Jobseeker Applied</li>
            </ul>
        </div>
    </div>
    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });
            document.getElementById(sectionId).classList.add('active');
        }

        function viewEmployerDetails(name) {
            alert(`Showing details for ${name}`);
            // Implement API call to fetch and display employer details
        }

        function viewJobseekerDetails(name) {
            alert(`Showing details for ${name}`);
            // Implement API call to fetch and display jobseeker details
        }

        function editJobListing(title) {
            alert(`Editing job listing: ${title}`);
            // Implement API call to fetch and display job listing details for editing
        }
    </script>
</body>
</html>

