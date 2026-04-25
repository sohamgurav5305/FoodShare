const ContactMessage = require('../src/models/ContactMessage');
const { handleOptions, methodNotAllowed, sendJson } = require('../src/vercel/http');

module.exports = async (req, res) => {
  if (handleOptions(req, res, 'POST, OPTIONS')) {
    return;
  }

  if (req.method !== 'POST') {
    return methodNotAllowed(res);
  }

  const data = req.body || {};

  if (!data.name || !data.email || !data.subject || !data.message) {
    return sendJson(res, 400, {
      success: false,
      error: 'Missing required fields'
    });
  }

  try {
    const messageId = await ContactMessage.create(data);
    return sendJson(res, 200, {
      success: true,
      message_id: messageId,
      message: 'Message stored successfully'
    });
  } catch (error) {
    console.error('Contact message insert failed:', error.message);
    return sendJson(res, 500, {
      success: false,
      error: 'Unable to save message'
    });
  }
};
