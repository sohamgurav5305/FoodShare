<?php
class MonetaryDonation {
    private $conn;
    private $table_name = "monetary_donations";

    public $id;
    public $donor_name;
    public $email;
    public $phone;
    public $amount;
    public $payment_method;
    public $payment_status;
    public $transaction_id;
    public $notes;
    public $created_at;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new donation
  public function create() {
    try {
        $query = "INSERT INTO " . $this->table_name . " 
                  (donor_name, email, phone, amount, payment_method, 
                   payment_status, transaction_id, notes, created_at) 
                  VALUES 
                  (:donor_name, :email, :phone, :amount, :payment_method, 
                   :payment_status, :transaction_id, :notes, NOW())";

        $stmt = $this->conn->prepare($query);

        $this->donor_name     = htmlspecialchars(strip_tags($this->donor_name));
        $this->email          = htmlspecialchars(strip_tags($this->email));
        $this->phone          = htmlspecialchars(strip_tags($this->phone));
        $this->amount         = htmlspecialchars(strip_tags($this->amount));
        $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));
        $this->payment_status = htmlspecialchars(strip_tags($this->payment_status));
        $this->transaction_id = htmlspecialchars(strip_tags($this->transaction_id));
        $this->notes          = htmlspecialchars(strip_tags($this->notes));

        $stmt->bindParam(':donor_name', $this->donor_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':payment_method', $this->payment_method);
        $stmt->bindParam(':payment_status', $this->payment_status);
        $stmt->bindParam(':transaction_id', $this->transaction_id);
        $stmt->bindParam(':notes', $this->notes);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        // Log SQL error
        $errorInfo = $stmt->errorInfo();
        error_log("Donation insert failed: " . implode(" | ", $errorInfo));

        return false;

    } catch (PDOException $e) {
        error_log("Donation insert exception: " . $e->getMessage());
        return false;
    }
}

}
?>
