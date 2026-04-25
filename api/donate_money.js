const MonetaryDonation = require('../src/models/MonetaryDonation');
const { handleOptions, methodNotAllowed, sendJson } = require('../src/vercel/http');

module.exports = async (req, res) => {
  if (handleOptions(req, res, 'POST, OPTIONS')) {
    return;
  }

  if (req.method !== 'POST') {
    return methodNotAllowed(res);
  }

  const data = req.body || {};

  if (!data.name || !data.email || !data.phone || !data.amount || !data.payment_method) {
    return sendJson(res, 400, { error: 'Missing required fields' });
  }

  try {
    const transactionId = `TXN_${Date.now()}_${Math.floor(1000 + Math.random() * 9000)}`;
    const donation = await MonetaryDonation.create({
      ...data,
      payment_status: 'pending',
      transaction_id: transactionId
    });

    return sendJson(res, 201, {
      success: true,
      donation_id: donation.id,
      transaction_id: donation.transactionId,
      message: 'Thank you for your generous donation!'
    });
  } catch (error) {
    console.error('Donation insert failed:', error.message);
    return sendJson(res, 500, { error: 'Failed to process donation' });
  }
};
