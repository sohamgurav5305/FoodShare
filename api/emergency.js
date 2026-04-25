const EmergencyRequest = require('../src/models/EmergencyRequest');
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
    !data.requester_name ||
    !data.phone ||
    !data.address ||
    !data.family_size ||
    !data.urgent_reason
  ) {
    return sendJson(res, 400, { error: 'Missing required fields' });
  }

  try {
    await EmergencyRequest.ensureLocationColumns();
    const requestId = await EmergencyRequest.create(data);

    console.error(
      `EMERGENCY ALERT: EMERGENCY FOOD REQUEST #${requestId}\nName: ${data.requester_name}\nPhone: ${data.phone}\nAddress: ${data.address}\nFamily Size: ${data.family_size}\nReason: ${data.urgent_reason}`
    );

    return sendJson(res, 201, {
      success: true,
      request_id: requestId,
      message: 'Emergency request submitted. Our team will contact you within 30 minutes.',
      hotline: '+91 9356822437'
    });
  } catch (error) {
    console.error('Emergency request insert failed:', error.message);
    return sendJson(res, 500, { error: 'Failed to submit emergency request' });
  }
};
