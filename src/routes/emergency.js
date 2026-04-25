const express = require('express');

const EmergencyRequest = require('../models/EmergencyRequest');

const router = express.Router();

router.post('/', async (req, res) => {
  const data = req.body || {};

  if (
    !data.requester_name ||
    !data.phone ||
    !data.address ||
    !data.family_size ||
    !data.urgent_reason
  ) {
    return res.status(400).json({ error: 'Missing required fields' });
  }

  try {
    await EmergencyRequest.ensureLocationColumns();
    const requestId = await EmergencyRequest.create(data);

    const alert = [
      `EMERGENCY FOOD REQUEST #${requestId}`,
      `Name: ${data.requester_name}`,
      `Phone: ${data.phone}`,
      `Address: ${data.address}`,
      `Family Size: ${data.family_size}`,
      `Reason: ${data.urgent_reason}`
    ].join('\n');
    console.error(`EMERGENCY ALERT: ${alert}`);

    return res.status(201).json({
      success: true,
      request_id: requestId,
      message: 'Emergency request submitted. Our team will contact you within 30 minutes.',
      hotline: '+91 9356822437'
    });
  } catch (error) {
    console.error('Emergency request insert failed:', error.message);
    return res.status(500).json({ error: 'Failed to submit emergency request' });
  }
});

module.exports = router;
