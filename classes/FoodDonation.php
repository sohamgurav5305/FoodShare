<?php
// classes/FoodDonation.php
class FoodDonation {
    private $conn;
    private $table_name = "food_donations";

    public $id;
    public $contact_name;
    public $contact_phone;
    public $contact_email;
    public $food_type;
    public $description;
    public $estimated_quantity;
    public $pickup_date;
    public $pickup_time;
    public $pickup_address;
    public $pickup_latitude;
    public $pickup_longitude;
    public $location_source;
    public $special_instructions;
    public $status;

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

        if (!in_array('pickup_latitude', $columns, true)) {
            $alterStatements[] = "ADD COLUMN pickup_latitude DECIMAL(10, 7) DEFAULT NULL";
        }

        if (!in_array('pickup_longitude', $columns, true)) {
            $alterStatements[] = "ADD COLUMN pickup_longitude DECIMAL(10, 7) DEFAULT NULL";
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
                  SET contact_name=:contact_name, contact_phone=:contact_phone, 
                      contact_email=:contact_email, food_type=:food_type, 
                      description=:description, estimated_quantity=:estimated_quantity,
                      pickup_date=:pickup_date, pickup_time=:pickup_time,
                      pickup_address=:pickup_address, pickup_latitude=:pickup_latitude,
                      pickup_longitude=:pickup_longitude, location_source=:location_source,
                      special_instructions=:special_instructions,
                      status=:status";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->contact_name = htmlspecialchars(strip_tags($this->contact_name));
        $this->contact_phone = htmlspecialchars(strip_tags($this->contact_phone));
        $this->contact_email = filter_var($this->contact_email, FILTER_SANITIZE_EMAIL);
        $this->food_type = htmlspecialchars(strip_tags($this->food_type));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->estimated_quantity = htmlspecialchars(strip_tags($this->estimated_quantity));
        $this->pickup_date = htmlspecialchars(strip_tags($this->pickup_date));
        $this->pickup_time = htmlspecialchars(strip_tags($this->pickup_time));
        $this->pickup_address = htmlspecialchars(strip_tags($this->pickup_address));
        $this->pickup_latitude = is_numeric($this->pickup_latitude) ? (float)$this->pickup_latitude : null;
        $this->pickup_longitude = is_numeric($this->pickup_longitude) ? (float)$this->pickup_longitude : null;
        $this->location_source = htmlspecialchars(strip_tags($this->location_source ?? ''));
        $this->special_instructions = htmlspecialchars(strip_tags($this->special_instructions));
        $this->status = 'pending';

        // Bind values
        $stmt->bindParam(":contact_name", $this->contact_name);
        $stmt->bindParam(":contact_phone", $this->contact_phone);
        $stmt->bindParam(":contact_email", $this->contact_email);
        $stmt->bindParam(":food_type", $this->food_type);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":estimated_quantity", $this->estimated_quantity);
        $stmt->bindParam(":pickup_date", $this->pickup_date);
        $stmt->bindParam(":pickup_time", $this->pickup_time);
        $stmt->bindParam(":pickup_address", $this->pickup_address);
        $stmt->bindParam(":pickup_latitude", $this->pickup_latitude);
        $stmt->bindParam(":pickup_longitude", $this->pickup_longitude);
        $stmt->bindParam(":location_source", $this->location_source);
        $stmt->bindParam(":special_instructions", $this->special_instructions);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function readPending() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status='pending' ORDER BY pickup_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status=:status WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
}
?>
