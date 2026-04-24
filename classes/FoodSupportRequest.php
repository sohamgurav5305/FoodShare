<?php
// classes/FoodSupportRequest.php
class FoodSupportRequest {
    private $conn;
    private $table_name = "food_support_requests";

    public $id;
    public $applicant_type;
    public $applicant_name;
    public $organization_name;
    public $phone;
    public $email;
    public $address;
    public $city;
    public $latitude;
    public $longitude;
    public $location_source;
    public $people_count;
    public $food_needed;
    public $preferred_date;
    public $additional_notes;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function ensureTable() {
        $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    applicant_type VARCHAR(50) NOT NULL,
                    applicant_name VARCHAR(150) NOT NULL,
                    organization_name VARCHAR(150) DEFAULT NULL,
                    phone VARCHAR(30) NOT NULL,
                    email VARCHAR(150) DEFAULT NULL,
                    address TEXT NOT NULL,
                    city VARCHAR(100) DEFAULT NULL,
                    latitude DECIMAL(10, 7) DEFAULT NULL,
                    longitude DECIMAL(10, 7) DEFAULT NULL,
                    location_source VARCHAR(30) DEFAULT NULL,
                    people_count INT NOT NULL,
                    food_needed TEXT NOT NULL,
                    preferred_date DATE DEFAULT NULL,
                    additional_notes TEXT DEFAULT NULL,
                    status VARCHAR(30) NOT NULL DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $created = $this->conn->exec($query) !== false;
        return $created && $this->ensureLocationColumns();
    }

    private function ensureLocationColumns() {
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
                  SET applicant_type=:applicant_type, applicant_name=:applicant_name,
                      organization_name=:organization_name, phone=:phone, email=:email,
                      address=:address, city=:city, latitude=:latitude, longitude=:longitude,
                      location_source=:location_source, people_count=:people_count,
                      food_needed=:food_needed, preferred_date=:preferred_date,
                      additional_notes=:additional_notes, status=:status";

        $stmt = $this->conn->prepare($query);

        $this->applicant_type = htmlspecialchars(strip_tags($this->applicant_type));
        $this->applicant_name = htmlspecialchars(strip_tags($this->applicant_name));
        $this->organization_name = htmlspecialchars(strip_tags($this->organization_name ?? ''));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->email = filter_var($this->email ?? '', FILTER_SANITIZE_EMAIL);
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->city = htmlspecialchars(strip_tags($this->city ?? ''));
        $this->latitude = is_numeric($this->latitude) ? (float)$this->latitude : null;
        $this->longitude = is_numeric($this->longitude) ? (float)$this->longitude : null;
        $this->location_source = htmlspecialchars(strip_tags($this->location_source ?? ''));
        $this->people_count = filter_var($this->people_count, FILTER_VALIDATE_INT);
        $this->food_needed = htmlspecialchars(strip_tags($this->food_needed));
        $this->preferred_date = !empty($this->preferred_date) ? htmlspecialchars(strip_tags($this->preferred_date)) : null;
        $this->additional_notes = htmlspecialchars(strip_tags($this->additional_notes ?? ''));
        $this->status = 'pending';

        $stmt->bindParam(":applicant_type", $this->applicant_type);
        $stmt->bindParam(":applicant_name", $this->applicant_name);
        $stmt->bindParam(":organization_name", $this->organization_name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);
        $stmt->bindParam(":location_source", $this->location_source);
        $stmt->bindParam(":people_count", $this->people_count);
        $stmt->bindParam(":food_needed", $this->food_needed);
        $stmt->bindParam(":preferred_date", $this->preferred_date);
        $stmt->bindParam(":additional_notes", $this->additional_notes);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }
}
?>
