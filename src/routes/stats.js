const express = require('express');

const Statistics = require('../models/Statistics');
const { formatTimestamp } = require('../utils/dates');

const router = express.Router();

router.get('/', async (_req, res) => {
  try {
    const dashboardStats = await Statistics.getDashboardStats();

    return res.json({
      success: true,
      data: {
        people_fed: dashboardStats.people_served,
        meals_served: dashboardStats.meals_distributed,
        volunteers: dashboardStats.active_volunteers,
        locations: 10,
        total_donations: dashboardStats.total_donations,
        total_amount: dashboardStats.total_amount,
        food_donations: dashboardStats.food_donations
      },
      last_updated: formatTimestamp()
    });
  } catch (error) {
    console.error('Stats fetch failed:', error.message);
    return res.status(500).json({ error: 'Failed to fetch statistics' });
  }
});

module.exports = router;
