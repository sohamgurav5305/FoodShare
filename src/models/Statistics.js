const { query } = require('../config/database');

class Statistics {
  static async getDashboardStats() {
    const [donationData] = await query(
      "SELECT COUNT(*) AS count, SUM(amount) AS total FROM monetary_donations WHERE payment_status = 'completed'"
    );
    const [foodData] = await query('SELECT COUNT(*) AS count FROM food_donations');
    const [peopleData] = await query('SELECT SUM(family_size) AS total FROM distributions');
    const [volunteerData] = await query(
      "SELECT COUNT(*) AS count FROM volunteers WHERE status = 'active'"
    );
    const [mealsData] = await query('SELECT SUM(meals_distributed) AS total FROM daily_stats');

    return {
      total_donations: Number(donationData?.count || 0),
      total_amount: Number(donationData?.total || 0),
      food_donations: Number(foodData?.count || 0),
      people_served: Number(peopleData?.total || 0),
      active_volunteers: Number(volunteerData?.count || 0),
      meals_distributed: Number(mealsData?.total || 0)
    };
  }

  static async updateDailyStats(date = new Date().toISOString().slice(0, 10)) {
    const metrics = {
      meals_distributed: await this.getMealsDistributed(date),
      people_served: await this.getPeopleServed(date),
      monetary_donations_received: await this.getMonetaryDonations(date),
      food_donations_received: await this.getFoodDonations(date),
      volunteers_active: await this.getActiveVolunteers(date)
    };

    await query(
      `INSERT INTO daily_stats
      (date, meals_distributed, people_served, monetary_donations_received, food_donations_received, volunteers_active)
      VALUES (?, ?, ?, ?, ?, ?)
      ON DUPLICATE KEY UPDATE
      meals_distributed = VALUES(meals_distributed),
      people_served = VALUES(people_served),
      monetary_donations_received = VALUES(monetary_donations_received),
      food_donations_received = VALUES(food_donations_received),
      volunteers_active = VALUES(volunteers_active)`,
      [
        date,
        metrics.meals_distributed,
        metrics.people_served,
        metrics.monetary_donations_received,
        metrics.food_donations_received,
        metrics.volunteers_active
      ]
    );

    return true;
  }

  static async getMealsDistributed(date) {
    const [result] = await query(
      'SELECT COUNT(*) AS count FROM distributions WHERE distribution_date = ?',
      [date]
    );
    return Number(result?.count || 0);
  }

  static async getPeopleServed(date) {
    const [result] = await query(
      'SELECT SUM(family_size) AS total FROM distributions WHERE distribution_date = ?',
      [date]
    );
    return Number(result?.total || 0);
  }

  static async getMonetaryDonations(date) {
    const [result] = await query(
      "SELECT SUM(amount) AS total FROM monetary_donations WHERE DATE(donation_date) = ? AND payment_status = 'completed'",
      [date]
    );
    return Number(result?.total || 0);
  }

  static async getFoodDonations(date) {
    const [result] = await query(
      'SELECT COUNT(*) AS count FROM food_donations WHERE DATE(created_at) = ?',
      [date]
    );
    return Number(result?.count || 0);
  }

  static async getActiveVolunteers(date) {
    const [result] = await query(
      "SELECT COUNT(*) AS count FROM volunteers WHERE status = 'active' AND DATE(last_activity) = ?",
      [date]
    );
    return Number(result?.count || 0);
  }
}

module.exports = Statistics;
