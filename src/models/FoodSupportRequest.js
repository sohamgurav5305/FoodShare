const { query } = require('../config/database');
const { sanitizeText, sanitizeEmail, sanitizeInteger, sanitizeNumber } = require('../utils/sanitize');

class FoodSupportRequest {
  static async ensureTable() {
    await query(`CREATE TABLE IF NOT EXISTS food_support_requests (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4`);
  }

  static async create(data) {
    const result = await query(
      `INSERT INTO food_support_requests
      (applicant_type, applicant_name, organization_name, phone, email, address, city,
      latitude, longitude, location_source, people_count, food_needed, preferred_date,
      additional_notes, status)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        sanitizeText(data.applicant_type),
        sanitizeText(data.applicant_name),
        sanitizeText(data.organization_name),
        sanitizeText(data.phone),
        sanitizeEmail(data.email),
        sanitizeText(data.address),
        sanitizeText(data.city),
        sanitizeNumber(data.latitude),
        sanitizeNumber(data.longitude),
        sanitizeText(data.location_source),
        sanitizeInteger(data.people_count),
        sanitizeText(data.food_needed),
        data.preferred_date ? sanitizeText(data.preferred_date) : null,
        sanitizeText(data.additional_notes),
        'pending'
      ]
    );

    return result.insertId;
  }
}

module.exports = FoodSupportRequest;
