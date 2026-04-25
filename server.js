const path = require('path');
const express = require('express');
const cors = require('cors');

const apiRoutes = require('./src/routes');

const app = express();
const port = process.env.PORT || 3000;

app.use(
  cors({
    origin: '*',
    methods: ['GET', 'POST', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
  })
);
app.use(express.json());
app.use(express.static(path.join(__dirname)));
app.use('/api', apiRoutes);

app.get('/', (_req, res) => {
  res.sendFile(path.join(__dirname, 'index.html'));
});

if (require.main === module) {
  app.listen(port, () => {
    console.log(`FOODSHARE server running on http://localhost:${port}`);
  });
}

module.exports = app;
