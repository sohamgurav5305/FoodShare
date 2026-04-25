const path = require("path");
const express = require("express");
const cors = require("cors");
const mysql = require("mysql2");

const app = express();
const port = process.env.PORT || 3000;

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(__dirname));

const db = mysql.createPool({
  host: process.env.DB_HOST,
  port: Number(process.env.DB_PORT),
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME
});

app.get("/", (req, res) => {
  res.sendFile(path.join(__dirname, "index.html"));
});

/* Dashboard Stats */
app.get("/api/stats", (req, res) => {
  const sql = `
    SELECT 
      (SELECT COUNT(*) FROM monetary_donations) AS money_count,
      (SELECT COUNT(*) FROM food_donations) AS food_count,
      (SELECT COUNT(*) FROM food_support_requests) AS request_count
  `;

  db.query(sql, (err, result) => {
    if (err) return res.status(500).json({ error: err.message });

    res.json({
      success: true,
      stats: result[0]
    });
  });
});

/* Money Donation */
app.post("/api/donate_money", (req, res) => {
  const { name, email, phone, amount, payment_method } = req.body;

  const sql = `
    INSERT INTO monetary_donations
    (donor_name, email, phone, amount, payment_method)
    VALUES (?, ?, ?, ?, ?)
  `;

  db.query(
    sql,
    [name, email, phone, amount, payment_method || "cash"],
    (err) => {
      if (err) return res.status(500).json({ error: err.message });

      res.json({
        success: true,
        message: "Donation submitted successfully"
      });
    }
  );
});

/* Food Donation */
app.post("/api/donate_food", (req, res) => {
  const {
    name,
    phone,
    email,
    food_type,
    description,
    quantity,
    pickup_date,
    address
  } = req.body;

  const sql = `
    INSERT INTO food_donations
    (contact_name, contact_phone, contact_email, food_type, description, estimated_quantity, pickup_date, pickup_address)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
  `;

  db.query(
    sql,
    [name, phone, email, food_type, description, quantity, pickup_date, address],
    (err) => {
      if (err) return res.status(500).json({ error: err.message });

      res.json({ success: true });
    }
  );
});

/* Food Request */
app.post("/api/request_food", (req, res) => {
  const {
    applicant_type,
    name,
    organization,
    phone,
    email,
    address,
    city,
    people_count,
    food_needed,
    preferred_date,
    notes
  } = req.body;

  const sql = `
    INSERT INTO food_support_requests
    (applicant_type, applicant_name, organization_name, phone, email, address, city, people_count, food_needed, preferred_date, additional_notes)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `;

  db.query(
    sql,
    [
      applicant_type,
      name,
      organization,
      phone,
      email,
      address,
      city,
      people_count,
      food_needed,
      preferred_date,
      notes
    ],
    (err) => {
      if (err) return res.status(500).json({ error: err.message });

      res.json({ success: true });
    }
  );
});

app.listen(port, () => console.log("FOODSHARE running"));