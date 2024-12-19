const express = require('express');
const mysql = require('mysql');
const app = express();
const port = 3000;

// Create a connection to the database
const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'links_db'
});

// Connect to the database
db.connect((err) => {
  if (err) {
    throw err;
  }
  console.log('Connected to database');
});

// Allow cross-origin requests
app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
  next();
});

// Route to get all movies filtered by year and search term
app.get('/movies', (req, res) => {
  const year = req.query.year;
  console.log(`Year: ${year}`);
  if(year != ""){
    const search = req.query.search ? `%${req.query.search}%` : '%';
    console.log(`Search term: ${search}`);
    const sql = 'SELECT * FROM links WHERE release_date = ?';
    db.query(sql, [year], (err, results) => {
    if (err) {
      return res.status(500).send(err);
    }
    res.json(results);
  });
  }
  else{
    const search = req.query.search ? `%${req.query.search}%` : '%';
    console.log(`Search term: ${search}`);
    const sql = 'SELECT * FROM links WHERE movie_title LIKE ?';
    db.query(sql, [search], (err, results) => {
      if (err) {
        return res.status(500).send(err);
      }
      res.json(results);
    });
  }
});

// Start the server
app.listen(port, '0.0.0.0', () => {
  console.log(`Server running at http://localhost:${port}`);
});