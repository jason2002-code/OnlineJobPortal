<?php
include_once "db_connection.php";

/**
 * Fetch job offers for a given jobseeker user ID.
 *
 * @param int $userId The user ID of the jobseeker.
 * @return array An array of offers.
 */
function getOffersByUserId($userId) {
    global $conn;

    $sql = "SELECT o.offerID, o.applicationID, o.salary, o.startDate, o.offerStatus, j.jobTitle, e.companyName
            FROM job_offers o
            JOIN jobapplications ja ON o.applicationID = ja.applicationID
            JOIN joblist j ON ja.jobID = j.jobID
            JOIN employers e ON j.employerID = e.employerID
            WHERE ja.user_Id = ?
            ORDER BY o.offerID DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $offers = [];
    while ($row = $result->fetch_assoc()) {
        $offers[] = $row;
    }
    $stmt->close();

    return $offers;
}
?>
