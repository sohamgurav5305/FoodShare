const { query } = require('../config/database');
const { sanitizeText, sanitizeEmail, sanitizeNumber, sanitizeInteger } = require('../utils/sanitize');

class EmergencyRequest {
  static async ensureLocationColumns() {
    const columns = await query('SHOW COLUMNS FROM emergency_requests');
    const existing = new Set(columns.map((column) => column.Field));
    const alterStatements = [];

    if (!existing.has('latitude')) {
      alterStatements.push('ADD COLUMN latitude DECIMAL(10, 7) DEFAULT NULL');
    }

    if (!existing.has('longitude')) {
      alterStatements.push('ADD COLUMN longitude DECIMAL(10, 7) DEFAULT NULL');
    }

    if (!existing.has('location_source')) {
      alterStatements.push('ADD COLUMN location_source VARCHAR(30) DEFAULT NULL');
    }

    if (alterStatements.length > 0) {
      await query(`ALTER TABLE emergency_requests ${alterStatements.join(', ')}`);
    }
  }

  static async create(data) {
    const request = {
      requester_name: sanitizeText(data.requester_name),
      phone: sanitizeText(data.phone),
      email: sanitizeEmail(data.email),
      address: sanitizeText(data.address),
      latitude: sanitizeNumber(data.latitude),
      longitude: sanitizeNumber(data.longitude),
      location_source: sanitizeText(data.location_source),
      family_size: sanitizeInteger(data.family_size),
      urgent_reason: sanitizeText(data.urgent_reason),
      dietary_restrictions: sanitizeText(data.dietary_restrictions)
    };

    const result = await query(
      `INSERT INTO emergency_requests
      (requester_name, phone, email, address, latitude, longitude, location_source,
      family_size, urgent_reason, dietary_restrictions, status)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')`,
      [
        request.requester_name,
        request.phone,
        request.email,
        request.address,
        request.latitude,
        request.longitude,
        request.location_source,
        request.family_size,
        request.urgent_reason,
        request.dietary_restrictions
      ]
    );

    console.error(`EMERGENCY FOOD REQUEST: ${request.requester_name} - ${request.phone}`);
    return result.insertId;
  }
}

module.exports = EmergencyRequest;
