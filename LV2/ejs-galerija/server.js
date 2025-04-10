const express = require('express');
const path = require('path');
const app = express();
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));
app.use(express.static('public'));
const fs = require('fs');

app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

app.get('/slike', (req, res) => {
    const folderPath = path.join(__dirname, 'public', 'images');
    const files = fs.readdirSync(folderPath);
    const images = files
        .filter(file => file.endsWith('.jpg') || file.endsWith('.svg'))
        .map((file, index) => ({
            url: `/images/${file}`,
            id: `slika${index + 1}`,
            title: `Slika ${index + 1}`
        }));
    res.render('slike', { images });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
