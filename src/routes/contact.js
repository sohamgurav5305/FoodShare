const express = require('express');

const ContactMessage = require('../models/ContactMessage');

const router = express.Router();

router.post('/', async (req, res) => {
  const data = req.body || {};

  if (!data.name || !data.email || !data.subject || !data.message) {
    return res.status(400).json({
      success: false,
      error: 'Missing required fields'
    });
  }

  try {
    const messageId = await ContactMessage.create(data);
    return res.json({
      success: true,
      message_id: messageId,
      message: 'Message stored successfully'
    });
  } catch (error) {
    console.error('Contact message insert failed:', error.message);
    return res.status(500).json({
      success: false,
      error: 'Unable to save message'
    });
  }
});

module.exports = router;
