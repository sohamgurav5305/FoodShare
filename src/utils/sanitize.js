function stripTags(value) {
  return String(value ?? '').replace(/<[^>]*>/g, '');
}

function sanitizeText(value) {
  return stripTags(value).trim();
}

function sanitizeEmail(value) {
  return sanitizeText(value).toLowerCase();
}

function sanitizeNumber(value) {
  if (value === null || value === undefined || value === '') {
    return null;
  }

  const numericValue = Number(value);
  return Number.isFinite(numericValue) ? numericValue : null;
}

function sanitizeInteger(value) {
  if (value === null || value === undefined || value === '') {
    return null;
  }

  const numericValue = Number.parseInt(value, 10);
  return Number.isInteger(numericValue) ? numericValue : null;
}

module.exports = {
  sanitizeText,
  sanitizeEmail,
  sanitizeNumber,
  sanitizeInteger
};
