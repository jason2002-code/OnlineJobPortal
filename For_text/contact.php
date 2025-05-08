<?php
session_start();
include("../Functions/db_connection.php");

if (isset($_POST['send'])) {
    // Sanitize and validate inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $number = mysqli_real_escape_string($conn, $_POST['number']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Determine userID if logged in, else 0
    $userID = isset($_SESSION['user_Id']) ? intval($_SESSION['user_Id']) : 0;

    // Map role values to receiverRole enum values
    $receiverRole = '';
    if ($role === 'employee') {
        $receiverRole = 'jobseeker';
    } elseif ($role === 'employeer') {
        $receiverRole = 'employer';
    } else {
        $receiverRole = 'admin'; // Send to admin notifications by default if not jobseeker or employer
    }

    // Insert into notifications table
    $insertQuery = "INSERT INTO notifications (userID, receiverRole, message, isRead, dateSent) VALUES (?, ?, ?, 0, NOW())";
    if ($stmt = $conn->prepare($insertQuery)) {
        $stmt->bind_param("iss", $userID, $receiverRole, $message);
        if ($stmt->execute()) {
            $successMsg = "Message sent successfully.";
        } else {
            $errorMsg = "Failed to send message.";
        }
        $stmt->close();
    } else {
        $errorMsg = "Failed to prepare statement.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

   
   
    <title>Contact Us</title>

    <link rel="stylesheet" href="../For_design/design.css">
    

</head>

<body>


    <header class="header">
        <section class="flex">
            <div id="menu-btn" class="fas fa-bars-staggered"></div>
           
           
            <a href="home.html" class="logo"><i class="fas fa-briefcase"></i> 
                Upwork.</a>
            
            
            <nav class="navbar">
                <a href="home.php">home</a>
                <a href="about.php">about us</a>
                <a href="jobs.php">all jobs</a>
                <a href="contact.php">contact us</a>
                <a href="login.php">account</a>
            </nav>
            <?php if (isset($_SESSION['employerID'])): ?>
                <a href="employer_dashboard.php?openPostJob=1" class="btn" style="margin-top: 0;">post job</a>
            <?php else: ?>
                <a href="login.php" class="btn" style="margin-top: 0;">post job</a>
            <?php endif; ?>
        </section>


    </header>

<div class="section-title">contact us</div>

<section class="contact">

    <div class="box-container">
        <div class="box">
            <i class="fas fa-phone"></i>
            <a href="tel:09950687551">0995-0687-551</a>
            <a href="tel:09959949327">0995-9949-327</a>
        </div>

        <div class="box">
            <i class="fas fa-envelope"></i>
            <a href="Mail:markjason@gmail.com">markjason@gmail.com</a>
            <a href="Mail:markcamanzolorenzo@gmail.com">markcamanzolorenzo@gmail.com</a>
        </div>
 
    <div class="box">
        <i class="fas fa-map-marker-alt"></i>
        <a href="#">Bisu Balilihan, room 208, Magsija, balilihan, bohol - 728564</a>
    </div>

    </div>
    
    <form action="" method="post">
        <h3>Send your message</h3>
        <?php if (isset($successMsg)): ?>
            <p class="success-msg" style="color: green;"><?php echo $successMsg; ?></p>
        <?php elseif (isset($errorMsg)): ?>
            <p class="error-msg" style="color: red;"><?php echo $errorMsg; ?></p>
        <?php endif; ?>
        <div class="flex">
            <div class="box">
                <p>name<span>*</span></p>
                <input type="text" name="name" required maxlength="20"
                 placeholder="enter your name" class="input" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>
            <div class="box" id="email-box">
                <p>email<span>*</span></p>
                <input type="email" name="email" maxlength="50"
                 placeholder="enter your email" class="input" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="box" id="company-box" style="display:none;">
                <p>company name<span>*</span></p>
                <input type="text" name="companyName" maxlength="50"
                 placeholder="enter your company name" class="input" value="<?php echo isset($_POST['companyName']) ? htmlspecialchars($_POST['companyName']) : ''; ?>">
            </div>
            <div class="box">
                <p>number<span>*</span></p>
                <input type="number" name="number" required min="0" max="99999999999" maxlength="20"
                 placeholder="enter your number" class="input" value="<?php echo isset($_POST['number']) ? htmlspecialchars($_POST['number']) : ''; ?>">
            </div>
            <div class="box">
                <p>role<span>*</span></p>
               <select name="role" id="role-select" required class="input">
                <option value="employee" <?php echo (isset($_POST['role']) && $_POST['role'] === 'employee') ? 'selected' : ''; ?>>job seeker (employee)</option>
                <option value="employeer" <?php echo (isset($_POST['role']) && $_POST['role'] === 'employeer') ? 'selected' : ''; ?>>job provider (employeer)</option>
                <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Administrator (Admin)</option>
            </select>
            </div>
        </div>
        <p>message <span>*</span></p>
        <textarea name="message" class="input" required maxlength="100"
       placeholder="enter your message" cols="30" rows="10"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
       <input type="submit" value="send message" name="send" class="btn">
    </form>

    <script>
        document.getElementById('role-select').addEventListener('change', function() {
            var role = this.value;
            var emailBox = document.getElementById('email-box');
            var companyBox = document.getElementById('company-box');
            if (role === 'employeer') {
                companyBox.style.display = 'block';
                emailBox.style.display = 'none';
            } else if (role === 'employee') {
                emailBox.style.display = 'block';
                companyBox.style.display = 'none';
            } else if (role === 'admin') {
                emailBox.style.display = 'none';
                companyBox.style.display = 'none';
            } else {
                emailBox.style.display = 'block';
                companyBox.style.display = 'none';
            }
        });

        // Trigger change event on page load to set initial visibility
        document.getElementById('role-select').dispatchEvent(new Event('change'));
    </script>


</section>












       




<footer class="footer">

    <section class="grid">
        <div class="box">
            <h3>quick links</h3>
            <a href="home.php"><i class="fas fa-angle-right"></i> home </a>
            <a href="about.php"><i class="fas fa-angle-right"></i> about </a>
            <a href="jobs.php"><i class="fas fa-angle-right"></i> all jobs </a>   
            <a href="contact.php"><i class="fas fa-angle-right"></i> contact us</a>
            <a href="#"><i class="fas fa-angle-right"></i> filter search </a>
        </div>

        
            <div class="box">
                <h3>extra links</h3>
                <a href="#"><i class="fas fa-angle-right"></i> account </a>
                <a href="login.php"><i class="fas fa-angle-right"></i> login</a>
                <a href="register.php"><i class="fas fa-angle-right"></i> register</a>
                <a href="#"><i class="fas fa-angle-right"></i> post job</a>
                <a href="#"><i class="fas fa-angle-right"></i> dashboard</a>
            </div>
            <div class="box">
                <h3>follow us</h3>
                <a href="#"><i class="fab fa-facebook"></i> facebook</a>
                <a href="#"><i class="fab fa-twitter"></i> twitter</a>
                <a href="#"><i class="fab fa-instagram"></i> instagram</a>
                <a href="#"><i class="fab fa-linkedin"></i> linkedin</a>
                <a href="#"><i class="fab fa-youtube"></i> youtube</a>

            </div>

    </section>

    <div class="credit">&copy; copyright @ 2025 by <span>mr. web designer
    </span> | all rights reserved!</div>
</footer>






    <script src="../Functions/script.js"></script>
       

    <body>
    </html>