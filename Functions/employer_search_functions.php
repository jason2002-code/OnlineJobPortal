<?php
// Function to search jobs posted by a specific employer with filters
function searchJobsByEmployer($conn, $employerID, $filters) {
    $query = "SELECT * FROM joblist WHERE employerID = ?";
    $params = [$employerID];
    $types = "i";

    $conditions = [];
    if (!empty($filters['title'])) {
        $conditions[] = "jobTitle LIKE ?";
        $params[] = '%' . $filters['title'] . '%';
        $types .= "s";
    }
    if (!empty($filters['location'])) {
        $conditions[] = "location LIKE ?";
        $params[] = '%' . $filters['location'] . '%';
        $types .= "s";
    }
    if (!empty($filters['status'])) {
        $conditions[] = "status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }
    if (!empty($filters['date_posted'])) {
        $conditions[] = "posted_date = ?";
        $params[] = $filters['date_posted'];
        $types .= "s";
    }
    if (!empty($filters['salary_min'])) {
        $conditions[] = "salary >= ?";
        $params[] = $filters['salary_min'];
        $types .= "s";
    }
    if (!empty($filters['salary_max'])) {
        $conditions[] = "salary <= ?";
        $params[] = $filters['salary_max'];
        $types .= "s";
    }
    if (!empty($filters['job_type'])) {
        $conditions[] = "jobType = ?";
        $params[] = $filters['job_type'];
        $types .= "s";
    }
    if (!empty($filters['education'])) {
        $conditions[] = "education = ?";
        $params[] = $filters['education'];
        $types .= "s";
    }

    if (count($conditions) > 0) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $jobs = [];
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
    $stmt->close();
    return $jobs;
}



// Function to search applicants for jobs posted by a specific employer with filters
function searchApplicantsByEmployer($conn, $employerID, $filters) {
    $query = "SELECT a.*, js.fullName, j.jobTitle 
              FROM jobapplications a
              JOIN jobseeker_profiles js ON a.user_Id = js.user_Id
              JOIN joblist j ON a.jobID = j.jobID
              WHERE j.employerID = ?";
    $params = [$employerID];
    $types = "i";

    $conditions = [];
    if (!empty($filters['fullName'])) {
        $conditions[] = "js.fullName LIKE ?";
        $params[] = '%' . $filters['fullName'] . '%';
        $types .= "s";
    }
    if (!empty($filters['jobTitle'])) {
        $conditions[] = "j.jobTitle LIKE ?";
        $params[] = '%' . $filters['jobTitle'] . '%';
        $types .= "s";
    }
    if (!empty($filters['applicationStatus'])) {
        $conditions[] = "a.Status1 = ?";
        $params[] = $filters['applicationStatus'];
        $types .= "s";
    }
    if (!empty($filters['date_applied'])) {
        $conditions[] = "a.applicationDate = ?";
        $params[] = $filters['date_applied'];
        $types .= "s";
    }

    if (count($conditions) > 0) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $applicants = [];
    while ($row = $result->fetch_assoc()) {
        $applicants[] = $row;
    }
    $stmt->close();
    return $applicants;
}

// Function to search offers made by a specific employer with filters
function searchOffersByEmployer($conn, $employerID, $filters) {
    $query = "SELECT o.*, j.jobTitle 
              FROM job_offers o
              JOIN joblist j ON o.jobID = j.jobID
              WHERE j.employerID = ?";
    $params = [$employerID];
    $types = "i";

    $conditions = [];
    if (!empty($filters['jobTitle'])) {
        $conditions[] = "j.jobTitle LIKE ?";
        $params[] = '%' . $filters['jobTitle'] . '%';
        $types .= "s";
    }
    if (!empty($filters['offerStatus'])) {
        $conditions[] = "o.offerStatus = ?";
        $params[] = $filters['offerStatus'];
        $types .= "s";
    }
    if (!empty($filters['startDate'])) {
        $conditions[] = "o.startDate = ?";
        $params[] = $filters['startDate'];
        $types .= "s";
    }

    if (count($conditions) > 0) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param($types, ...$params);
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
