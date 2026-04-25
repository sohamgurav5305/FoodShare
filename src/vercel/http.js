function setCommonHeaders(res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
}

function handleOptions(req, res, allowedMethods = 'GET, POST, OPTIONS') {
  setCommonHeaders(res);
  res.setHeader('Access-Control-Allow-Methods', allowedMethods);

  if (req.method === 'OPTIONS') {
    res.status(200).end();
    return true;
  }

  return false;
}

function methodNotAllowed(res) {
  setCommonHeaders(res);
  return res.status(405).json({ error: 'Method not allowed' });
}

function sendJson(res, statusCode, payload) {
  setCommonHeaders(res);
  return res.status(statusCode).json(payload);
}

module.exports = {
  handleOptions,
  methodNotAllowed,
  sendJson
};
