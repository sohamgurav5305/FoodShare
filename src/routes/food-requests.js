const express = require('express');

const FoodSupportRequest = require('../models/FoodSupportRequest');
const { isTodayOrFuture } = require('../utils/dates');

const router = express.Router();

router.post('/', async (req, res) => {
  const data = req.body || {};

  if (
    !data.applicant_type ||
    !data.applicant_name ||
    !data.phone ||
    !data.address ||
    !data.people_count ||
    !data.food_needed
  ) {
    return res.status(400).json({ error: 'Missing required fields' });
  }

  if (data.applicant_type === 'organization' && !data.organization_name) {
    return res.status(400).json({ error: 'Organization name is required' });
  }

  const peopleCount = Number.parseInt(data.people_count, 10);
  if (!Number.isInteger(peopleCount) || peopleCount < 1) {
    return res.status(400).json({ error: 'People count must be at least 1' });
  }

  if (data.preferred_date && !isTodayOrFuture(data.preferred_date)) {
    return res.status(400).json({ error: 'Preferred support date must be today or later' });
  }

  try {
    await FoodSupportRequest.ensureTable();
    const requestId = await FoodSupportRequest.create({
      ...data,
      people_count: peopleCount
    });

    console.error(
      `FOOD SUPPORT REQUEST: #${requestId} ${data.applicant_name} (${data.applicant_type}) - ${data.phone}`
    );

    return res.status(201).json({
      success: true,
      request_id: requestId,
      message: 'Food support request submitted successfully. Our team will review it and contact you soon.'
    });
  } catch (error) {
    console.error('Food support request insert failed:', error.message);
    return res.status(500).json({ error: 'Failed to submit food support request' });
  }
});

module.exports = router;
