const path = require('path');
const express = require('express');
const cors = require('cors');
const mysql = require('mysql2');

const app = express();
const port = process.env.PORT || 3000;

/* ===============================
   Middleware
================================= */
app.use(
  cors({
    origin: '*',
    methods: ['GET', 'POST', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
  })
);

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

/* ===============================
   Static Files
================================= */
app.use(express.static(path.join(__dirname)));

/* ===============================
   Railway MySQL Connection
================================= */
const db = mysql.createPool({
  host: process.env.DB_HOST,
  port: process.env.DB_PORT || 3306,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
});

/* ===============================
   Test Route
================================= */
app.get('/', (_req, res) => {
  res.sendFile(path.join(__dirname, 'index.html'));
});

/* ===============================
   API Routes
================================= */

/* Stats API */
app.get('/api/stats', async (_req, res) => {
  try {
    db.query(
      'SELECT COUNT(*) AS total FROM donations',
      (err, results) => {
        if (err) {
          console.log(err);
          return res.status(500).json({ error: 'Database error' });
        }

        res.json({
          success: true,
          donations: results[0].total
        });
      }
    );
  } catch (error) {
    res.status(500).json({ error: 'Server error' });
  }
});

/* Money Donation API */
app.post('/api/donate_money', (req, res) => {
  try {
    const { name, email, phone, amount } = req.body;

    const sql = `
      INSERT INTO donations (name, email, phone, amount)
      VALUES (?, ?, ?, ?)
    `;

    db.query(
      sql,
      [name, email, phone, amount],
      (err, result) => {
        if (err) {
          console.log(err);
          return res.status(500).json({ error: 'Insert failed' });
        }

        res.json({
          success: true,
          message: 'Donation submitted successfully',
          id: result.insertId
        });
      }
    );
  } catch (error) {
    res.status(500).json({ error: 'Server error' });
  }
});

/* ===============================
   Start Server
================================= */
app.listen(port, () => {
  console.log(`FOODSHARE running on port ${port}`);
});