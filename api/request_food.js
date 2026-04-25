const FoodSupportRequest = require('../src/models/FoodSupportRequest');
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
    !data.applicant_type ||
    !data.applicant_name ||
    !data.phone ||
    !data.address ||
    !data.people_count ||
    !data.food_needed
  ) {
    return sendJson(res, 400, { error: 'Missing required fields' });
  }

  if (data.applicant_type === 'organization' && !data.organization_name) {
    return sendJson(res, 400, { error: 'Organization name is required' });
  }

  const peopleCount = Number.parseInt(data.people_count, 10);
  if (!Number.isInteger(peopleCount) || peopleCount < 1) {
    return sendJson(res, 400, { error: 'People count must be at least 1' });
  }

  if (data.preferred_date && !isTodayOrFuture(data.preferred_date)) {
    return sendJson(res, 400, { error: 'Preferred support date must be today or later' });
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

    return sendJson(res, 201, {
      success: true,
      request_id: requestId,
      message: 'Food support request submitted successfully. Our team will review it and contact you soon.'
    });
  } catch (error) {
    console.error('Food support request insert failed:', error.message);
    return sendJson(res, 500, { error: 'Failed to submit food support request' });
  }
};
