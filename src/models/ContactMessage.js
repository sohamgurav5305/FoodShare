const { query } = require('../config/database');
const { sanitizeText, sanitizeEmail } = require('../utils/sanitize');

class ContactMessage {
  static async create(data) {
    const subject = sanitizeText(data.subject);
    const contact = {
      name: sanitizeText(data.name),
      email: sanitizeEmail(data.email),
      phone: sanitizeText(data.phone),
      subject,
      message: sanitizeText(data.message),
      status: 'new',
      priority: subject === 'emergency' ? 'urgent' : 'medium'
    };

    const result = await query(
      `INSERT INTO contact_messages
      (name, email, phone, subject, message, status, priority)
      VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [
        contact.name,
        contact.email,
        contact.phone,
        contact.subject,
        contact.message,
        contact.status,
        contact.priority
      ]
    );

    if (contact.subject === 'emergency') {
      console.error(`EMERGENCY CONTACT: ${contact.name} - ${contact.phone} - ${contact.message}`);
    }

    return result.insertId;
  }

  static async readNew() {
    return query(
      "SELECT * FROM contact_messages WHERE status = 'new' ORDER BY priority DESC, created_at DESC"
    );
  }
}

module.exports = ContactMessage;
