const { query } = require('../config/database');
const { sanitizeText, sanitizeEmail, sanitizeNumber } = require('../utils/sanitize');

class MonetaryDonation {
  static async create(data) {
    const donation = {
      donor_name: sanitizeText(data.name),
      email: sanitizeEmail(data.email),
      phone: sanitizeText(data.phone),
      amount: sanitizeNumber(data.amount),
      payment_method: sanitizeText(data.payment_method),
      payment_status: sanitizeText(data.payment_status || 'pending'),
      transaction_id: sanitizeText(data.transaction_id),
      notes: sanitizeText(data.notes)
    };

    const result = await query(
      `INSERT INTO monetary_donations
      (donor_name, email, phone, amount, payment_method, payment_status, transaction_id, notes, created_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())`,
      [
        donation.donor_name,
        donation.email,
        donation.phone,
        donation.amount,
        donation.payment_method,
        donation.payment_status,
        donation.transaction_id,
        donation.notes
      ]
    );

    return {
      id: result.insertId,
      transactionId: donation.transaction_id
    };
  }
}

module.exports = MonetaryDonation;
