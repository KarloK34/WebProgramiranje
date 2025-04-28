const express = require('express');
const app = express();
app.use(express.static('public')); 
app.get('/', (req, res) => {
res.send("Ili obican tekst ako nema HTML datoteke.");
});
const port = process.env.PORT || 3000;
app.listen(port, '0.0.0.0', () => {
  console.log(`Server running on port ${port}`);
});