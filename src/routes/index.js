const express = require('express');

const donationRoutes = require('./monetary-donations');
const foodDonationRoutes = require('./food-donations');
const emergencyRoutes = require('./emergency');
const contactRoutes = require('./contact');
const foodRequestRoutes = require('./food-requests');
const statsRoutes = require('./stats');

const router = express.Router();

router.use('/donate_money', donationRoutes);
router.use('/donate_food', foodDonationRoutes);
router.use('/emergency', emergencyRoutes);
router.use('/contact', contactRoutes);
router.use('/request_food', foodRequestRoutes);
router.use('/stats', statsRoutes);

module.exports = router;
