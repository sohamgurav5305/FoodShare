<?php
// classes/EmergencyRequest.php
class EmergencyRequest {
    private $conn;
    private $table_name = "emergency_requests";

    public $requester_name;
    public $phone;
    public $email;
    public $address;
    public $latitude;
    public $longitude;
    public $location_source;
    public $family_size;
    public $urgent_reason;
    public $dietary_restrictions;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function ensureLocationColumns() {
        $columns = [];
        $stmt = $this->conn->query("SHOW COLUMNS FROM " . $this->table_name);

        if (!$stmt) {
            return false;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $row['Field'];
        }

        $alterStatements = [];

        if (!in_array('latitude', $columns, true)) {
            $alterStatements[] = "ADD COLUMN latitude DECIMAL(10, 7) DEFAULT NULL";
        }

        if (!in_array('longitude', $columns, true)) {
            $alterStatements[] = "ADD COLUMN longitude DECIMAL(10, 7) DEFAULT NULL";
        }

        if (!in_array('location_source', $columns, true)) {
            $alterStatements[] = "ADD COLUMN location_source VARCHAR(30) DEFAULT NULL";
        }

        if (empty($alterStatements)) {
            return true;
        }

        $alterQuery = "ALTER TABLE " . $this->table_name . " " . implode(", ", $alterStatements);
        return $this->conn->exec($alterQuery) !== false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET requester_name=:requester_name, phone=:phone, email=:email,
                      address=:address, latitude=:latitude, longitude=:longitude,
                      location_source=:location_source, family_size=:family_size, urgent_reason=:urgent_reason,
                      dietary_restrictions=:dietary_restrictions, status='pending'";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->requester_name = htmlspecialchars(strip_tags($this->requester_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->latitude = is_numeric($this->latitude) ? (float)$this->latitude : null;
        $this->longitude = is_numeric($this->longitude) ? (float)$this->longitude : null;
        $this->location_source = htmlspecialchars(strip_tags($this->location_source ?? ''));
        $this->family_size = filter_var($this->family_size, FILTER_VALIDATE_INT);
        $this->urgent_reason = htmlspecialchars(strip_tags($this->urgent_reason));
        $this->dietary_restrictions = htmlspecialchars(strip_tags($this->dietary_restrictions));

        // Bind values
        $stmt->bindParam(":requester_name", $this->requester_name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);
        $stmt->bindParam(":location_source", $this->location_source);
        $stmt->bindParam(":family_size", $this->family_size);
        $stmt->bindParam(":urgent_reason", $this->urgent_reason);
        $stmt->bindParam(":dietary_restrictions", $this->dietary_restrictions);

        if($stmt->execute()) {
            $this->sendEmergencyAlert();
            return $this->conn->lastInsertId();
        }
        return false;
    }

    private function sendEmergencyAlert() {
        // Send immediate notification to emergency response team
        error_log("EMERGENCY FOOD REQUEST: " . $this->requester_name . " - " . $this->phone);
        // You can integrate SMS API here for immediate alerts
    }
}
?>
