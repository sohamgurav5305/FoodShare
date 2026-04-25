const Statistics = require('../src/models/Statistics');
const { formatTimestamp } = require('../src/utils/dates');
const { handleOptions, methodNotAllowed, sendJson } = require('../src/vercel/http');

module.exports = async (req, res) => {
  if (handleOptions(req, res, 'GET, OPTIONS')) {
    return;
  }

  if (req.method !== 'GET') {
    return methodNotAllowed(res);
  }

  try {
    const dashboardStats = await Statistics.getDashboardStats();

    return sendJson(res, 200, {
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
    return sendJson(res, 500, { error: 'Failed to fetch statistics' });
  }
};
