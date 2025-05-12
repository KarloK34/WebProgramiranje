<?php
session_start();
if (!isset($_SESSION['ID'])) { 
    header("Location: index.php");
    exit;
}
require_once 'includes/db.php';
$user_id = $_SESSION['ID'];
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Galerija slika za ocjenjivanje">
    <title>Galerija Slika</title>
    <link rel="stylesheet" href="style_slike.css"> 
    <style>
        .rating-stars {
            font-size: 24px; 
            cursor: pointer;
        }
        .rating-stars .star {
            color: #ccc;
            margin-right: 2px;
        }
        .rating-stars .star.rated,
        .rating-stars .star:hover,
        .rating-stars .star:hover ~ .star { 
            color: #f8d64e; 
        }
        .average-rating {
            font-size: 14px;
            color: #555;
            margin-top: 5px;
        }
        .gallery-container figure {
            position: relative;
            margin: 10px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #fff;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.1);
        }
        .gallery-container figcaption {
            margin-top: 8px;
            font-size: 1em;
        }
    </style>
</head>
<body>
    <header>
        <h1>Galerija Slika</h1>
        <nav>
            <a href="home.php">Naslovnica</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin.php">Admin Panel</a>
            <?php endif; ?>
            <a href="logout.php">Odjava (<?php echo htmlspecialchars($_SESSION['Username']); ?>)</a>
        </nav>
    </header>

    <div style="text-align: center; margin: 20px;">
        <button id="load-local-images-btn">Prikaži Moje Slike</button>
        <button id="load-api-images-btn">Učitaj Slike s API-ja (Unsplash)</button>
    </div>

    <section class="gallery-container" id="gallery-container-main">
    </section>

    <div id="image-lightbox" class="lightbox" style="display:none;">
        <a href="#" class="close" onclick="closeLightbox(); return false;">×</a>
        <img src="" alt="Uvećana slika" id="lightbox-image-src">
        <div id="lightbox-caption"></div>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Galerija Slika</p>
    </footer>

    <script src="gallery_script.js"></script>
</body>
</html>