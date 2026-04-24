<?php
// classes/Statistics.php
class Statistics {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getDashboardStats() {
        $stats = [];
        
        // Total donations
        $query = "SELECT COUNT(*) as count, SUM(amount) as total FROM monetary_donations WHERE payment_status='completed'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $donation_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_donations'] = $donation_data['count'];
        $stats['total_amount'] = $donation_data['total'];

        // Food donations
        $query = "SELECT COUNT(*) as count FROM food_donations";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $food_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['food_donations'] = $food_data['count'];

        // People served (from distributions)
        $query = "SELECT SUM(family_size) as total FROM distributions";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $people_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['people_served'] = $people_data['total'] ?? 0;

        // Active volunteers
        $query = "SELECT COUNT(*) as count FROM volunteers WHERE status='active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $volunteer_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['active_volunteers'] = $volunteer_data['count'];

        // Meals distributed
        $query = "SELECT SUM(meals_distributed) as total FROM daily_stats";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $meals_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['meals_distributed'] = $meals_data['total'] ?? 0;

        return $stats;
    }

    public function updateDailyStats($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }

        // Calculate daily metrics
        $metrics = [
            'meals_distributed' => $this->getMealsDistributed($date),
            'people_served' => $this->getPeopleServed($date),
            'monetary_donations_received' => $this->getMonetaryDonations($date),
            'food_donations_received' => $this->getFoodDonations($date),
            'volunteers_active' => $this->getActiveVolunteers($date)
        ];

        // Insert or update daily stats
        $query = "INSERT INTO daily_stats (date, meals_distributed, people_served, monetary_donations_received, food_donations_received, volunteers_active)
                  VALUES (:date, :meals, :people, :money, :food, :volunteers)
                  ON DUPLICATE KEY UPDATE
                  meals_distributed = VALUES(meals_distributed),
                  people_served = VALUES(people_served),
                  monetary_donations_received = VALUES(monetary_donations_received),
                  food_donations_received = VALUES(food_donations_received),
                  volunteers_active = VALUES(volunteers_active)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":date", $date);
        $stmt->bindParam(":meals", $metrics['meals_distributed']);
        $stmt->bindParam(":people", $metrics['people_served']);
        $stmt->bindParam(":money", $metrics['monetary_donations_received']);
        $stmt->bindParam(":food", $metrics['food_donations_received']);
        $stmt->bindParam(":volunteers", $metrics['volunteers_active']);

        return $stmt->execute();
    }

    private function getMealsDistributed($date) {
        $query = "SELECT COUNT(*) as count FROM distributions WHERE distribution_date = :date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":date", $date);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    private function getPeopleServed($date) {
        $query = "SELECT SUM(family_size) as total FROM distributions WHERE distribution_date = :date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":date", $date);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    private function getMonetaryDonations($date) {
        $query = "SELECT SUM(amount) as total FROM monetary_donations WHERE DATE(donation_date) = :date AND payment_status='completed'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":date", $date);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    private function getFoodDonations($date) {
        $query = "SELECT COUNT(*) as count FROM food_donations WHERE DATE(created_at) = :date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":date", $date);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    private function getActiveVolunteers($date) {
        $query = "SELECT COUNT(*) as count FROM volunteers WHERE status='active' AND DATE(last_activity) = :date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":date", $date);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
}
?>