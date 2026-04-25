const FoodDonation = require('../src/models/FoodDonation');
const { isTodayOrFuture } = require('../src/utils/dates');
const { handleOptions, methodNotAllowed, sendJson } = require('../src/vercel/http');

module.exports = async (req, res) => {
  if (handleOptions(req, res, 'POST, OPTIONS')) {
    return;
  }

  if (req.method !== 'POST') {
    return methodNotAllowed(res);
  }

  const data = req.body || {};

  if (
    !data.contact_name ||
    !data.contact_phone ||
    !data.food_type ||
    !data.pickup_date ||
    !data.pickup_address
  ) {
    return sendJson(res, 400, { error: 'Missing required fields' });
  }

  if (!isTodayOrFuture(data.pickup_date)) {
    return sendJson(res, 400, { error: 'Invalid pickup date' });
  }

  try {
    await FoodDonation.ensureLocationColumns();
    const donationId = await FoodDonation.create(data);

    console.error(
      `FOOD PICKUP NOTIFICATION: New food donation scheduled:\nContact: ${data.contact_name}\nPhone: ${data.contact_phone}\nFood Type: ${data.food_type}\nPickup Date: ${data.pickup_date}\nAddress: ${data.pickup_address}`
    );

    return sendJson(res, 201, {
      success: true,
      donation_id: donationId,
      message: "Food donation scheduled successfully! We'll contact you soon.",
      pickup_date: data.pickup_date,
      notification_sent: true
    });
  } catch (error) {
    console.error('Food donation insert failed:', error.message);
    return sendJson(res, 500, { error: 'Failed to schedule food donation' });
  }
};
