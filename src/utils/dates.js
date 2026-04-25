function isValidDateString(value) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(String(value ?? ''))) {
    return false;
  }

  const parsed = new Date(`${value}T00:00:00`);
  return !Number.isNaN(parsed.getTime());
}

function isTodayOrFuture(value) {
  if (!isValidDateString(value)) {
    return false;
  }

  const input = new Date(`${value}T00:00:00`);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return input >= today;
}

function formatTimestamp(date = new Date()) {
  const pad = (value) => String(value).padStart(2, '0');

  return [
    date.getFullYear(),
    pad(date.getMonth() + 1),
    pad(date.getDate())
  ].join('-') + ` ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
}

module.exports = {
  isValidDateString,
  isTodayOrFuture,
  formatTimestamp
};
