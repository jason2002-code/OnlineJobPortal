<?php
class Category {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Fetch all categories
    public function getAllCategories() {
        $categories = [];
        $sql = "SELECT * FROM job_categories ORDER BY categoryName ASC";
        $result = $this->conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Add iconClass with default if not set
                $row['iconClass'] = $row['iconClass'] ?? 'fas fa-briefcase';
                $categories[] = $row;
            }
        }
        return $categories;
    }

    // Get job count for a category
    public function getJobCountByCategory($categoryId) {
        $count = 0;
        $sql = "SELECT COUNT(*) as jobCount FROM joblist WHERE categoryID = ?";
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("i", $categoryId);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();
        }
        return $count;
    }
}
?>
