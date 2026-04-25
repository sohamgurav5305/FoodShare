const { query } = require('../config/database');
const { sanitizeText, sanitizeEmail, sanitizeNumber } = require('../utils/sanitize');

class FoodDonation {
  static async ensureLocationColumns() {
    const columns = await query('SHOW COLUMNS FROM food_donations');
    const existing = new Set(columns.map((column) => column.Field));
    const alterStatements = [];

    if (!existing.has('pickup_latitude')) {
      alterStatements.push('ADD COLUMN pickup_latitude DECIMAL(10, 7) DEFAULT NULL');
    }

    if (!existing.has('pickup_longitude')) {
      alterStatements.push('ADD COLUMN pickup_longitude DECIMAL(10, 7) DEFAULT NULL');
    }

    if (!existing.has('location_source')) {
      alterStatements.push('ADD COLUMN location_source VARCHAR(30) DEFAULT NULL');
    }

    if (alterStatements.length > 0) {
      await query(`ALTER TABLE food_donations ${alterStatements.join(', ')}`);
    }
  }

  static async create(data) {
    const result = await query(
      `INSERT INTO food_donations
      (contact_name, contact_phone, contact_email, food_type, description, estimated_quantity,
      pickup_date, pickup_time, pickup_address, pickup_latitude, pickup_longitude,
      location_source, special_instructions, status)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        sanitizeText(data.contact_name),
        sanitizeText(data.contact_phone),
        sanitizeEmail(data.contact_email),
        sanitizeText(data.food_type),
        sanitizeText(data.description),
        sanitizeText(data.estimated_quantity),
        sanitizeText(data.pickup_date),
        sanitizeText(data.pickup_time || '10:00'),
        sanitizeText(data.pickup_address),
        sanitizeNumber(data.pickup_latitude),
        sanitizeNumber(data.pickup_longitude),
        sanitizeText(data.location_source),
        sanitizeText(data.special_instructions),
        'pending'
      ]
    );

    return result.insertId;
  }

  static async readPending() {
    return query("SELECT * FROM food_donations WHERE status = 'pending' ORDER BY pickup_date ASC");
  }

  static async updateStatus(id, status) {
    const result = await query('UPDATE food_donations SET status = ? WHERE id = ?', [
      sanitizeText(status),
      id
    ]);

    return result.affectedRows > 0;
  }
}

module.exports = FoodDonation;
