const express = require('express');

const FoodDonation = require('../models/FoodDonation');
const { isTodayOrFuture } = require('../utils/dates');

const router = express.Router();

router.post('/', async (req, res) => {
  const data = req.body || {};

  if (
    !data.contact_name ||
    !data.contact_phone ||
    !data.food_type ||
    !data.pickup_date ||
    !data.pickup_address
  ) {
    return res.status(400).json({ error: 'Missing required fields' });
  }

  if (!isTodayOrFuture(data.pickup_date)) {
    return res.status(400).json({ error: 'Invalid pickup date' });
  }

  try {
    await FoodDonation.ensureLocationColumns();
    const donationId = await FoodDonation.create(data);

    const message = [
      'New food donation scheduled:',
      `Contact: ${data.contact_name}`,
      `Phone: ${data.contact_phone}`,
      `Food Type: ${data.food_type}`,
      `Pickup Date: ${data.pickup_date}`,
      `Address: ${data.pickup_address}`
    ].join('\n');
    console.error(`FOOD PICKUP NOTIFICATION: ${message}`);

    return res.status(201).json({
      success: true,
      donation_id: donationId,
      message: "Food donation scheduled successfully! We'll contact you soon.",
      pickup_date: data.pickup_date,
      notification_sent: true
    });
  } catch (error) {
    console.error('Food donation insert failed:', error.message);
    return res.status(500).json({ error: 'Failed to schedule food donation' });
  }
});

module.exports = router;
