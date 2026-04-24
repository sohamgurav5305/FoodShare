<?php
// classes/ContactMessage.php
class ContactMessage {
    private $conn;
    private $table_name = "contact_messages";

    public $id;
    public $name;
    public $email;
    public $phone;
    public $subject;
    public $message;
    public $status;
    public $priority;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, email=:email, phone=:phone, 
                      subject=:subject, message=:message, status=:status, priority=:priority";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->subject = htmlspecialchars(strip_tags($this->subject));
        $this->message = htmlspecialchars(strip_tags($this->message));
        $this->status = 'new';
        $this->priority = ($this->subject == 'emergency') ? 'urgent' : 'medium';

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":subject", $this->subject);
        $stmt->bindParam(":message", $this->message);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":priority", $this->priority);

        if($stmt->execute()) {
            // Send notification for emergency requests
            if($this->subject == 'emergency') {
                $this->sendEmergencyNotification();
            }
            return $this->conn->lastInsertId();
        }
        return false;
    }

    private function sendEmergencyNotification() {
        // Send SMS/Email notification to admin team
        // Implementation depends on your SMS/Email service
        error_log("EMERGENCY CONTACT: " . $this->name . " - " . $this->phone . " - " . $this->message);
    }

    public function readNew() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status='new' ORDER BY priority DESC, created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>