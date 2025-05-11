<?php
session_start();
include("../Functions/offer_functions.php");

if (!isset($_SESSION['user_Id'])) {
    header("Location: login.php");
    exit;
}

$user_Id = $_SESSION['user_Id'];

$offers = getOffersByUserId($user_Id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Offers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../For_design/Jobseekstyle.css" />
</head>
<body>
<div class="container mt-5">
    <h2>My Job Offers</h2>
    <?php if (count($offers) === 0): ?>
        <p>No offers found.</p>
    <?php else: ?>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Offer ID</th>
                    <th>Job Title</th>
                    <th>Company</th>
                    <th>Salary</th>
                    <th>Start Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($offers as $offer): ?>
                <tr>
                    <td><?php echo htmlspecialchars($offer['offerID']); ?></td>
                    <td><?php echo htmlspecialchars($offer['jobTitle']); ?></td>
                    <td><?php echo htmlspecialchars($offer['companyName']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($offer['salary'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($offer['startDate']); ?></td>
                    <td><?php echo htmlspecialchars($offer['offerStatus']); ?></td>
                    <td>
                        <?php if ($offer['offerStatus'] === 'pending'): ?>
                            <a href="respond_offer.php?offerID=<?php echo urlencode($offer['offerID']); ?>" class="btn btn-sm btn-primary">Respond</a>
                        <?php else: ?>
                            <span class="text-muted">No action</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
