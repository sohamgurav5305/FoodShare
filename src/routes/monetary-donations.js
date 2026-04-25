const express = require('express');

const MonetaryDonation = require('../models/MonetaryDonation');

const router = express.Router();

router.post('/', async (req, res) => {
  const { name, email, phone, amount, payment_method: paymentMethod } = req.body || {};

  if (!name || !email || !phone || !amount || !paymentMethod) {
    return res.status(400).json({ error: 'Missing required fields' });
  }

  try {
    const transactionId = `TXN_${Date.now()}_${Math.floor(1000 + Math.random() * 9000)}`;
    const donation = await MonetaryDonation.create({
      ...req.body,
      payment_status: 'pending',
      transaction_id: transactionId
    });

    return res.status(201).json({
      success: true,
      donation_id: donation.id,
      transaction_id: donation.transactionId,
      message: 'Thank you for your generous donation!'
    });
  } catch (error) {
    console.error('Donation insert failed:', error.message);
    return res.status(500).json({ error: 'Failed to process donation' });
  }
});

module.exports = router;
