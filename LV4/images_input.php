<?php
session_start();
if (!isset($_SESSION['ID']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Galerija Slika</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { max-width: 600px; margin-top: 50px; }
        #upload-status { margin-top: 15px; padding: 10px; border-radius: 4px; display: none; }
        #upload-status.success { background-color: #e8f5e9; color: green; border: 1px solid green; }
        #upload-status.error { background-color: #ffebee; color: red; border: 1px solid red; }
        progress { width: 100%; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container admin-container">
        <h2>Admin Panel - Učitavanje Slika</h2>
        <p>Dobrodošao, <?php echo htmlspecialchars($_SESSION['Username']); ?>! (<a href="logout.php">Odjava</a>)</p>
        <p><a href="gallery.php">Idi na Galeriju</a></p>

        <form id="upload-image-form" enctype="multipart/form-data">
            <div>
                <label for="slika_datoteka">Odaberi sliku (JPEG/PNG, max 5MB):</label><br>
                <input type="file" name="slika_datoteka" id="slika_datoteka" accept=".jpg,.jpeg,.png" required>
            </div>
            <br>
            <div>
                <label for="opis">Opis slike (opcionalno):</label><br>
                <textarea name="opis" id="opis" rows="3"></textarea>
            </div>
            <br>
            <button type="submit">Učitaj sliku</button>
        </form>
        <progress id="upload-progress" value="0" max="100" style="display:none;"></progress>
        <div id="upload-status"></div>
    </div>

    <script>
        document.getElementById('upload-image-form').addEventListener('submit', async function(event) {
            event.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'upload_image');
            
            const statusDiv = document.getElementById('upload-status');
            const progressBar = document.getElementById('upload-progress');
            statusDiv.style.display = 'none';
            progressBar.style.display = 'block';
            progressBar.value = 0;

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'gallery_handler.php', true);

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    progressBar.value = (e.loaded / e.total) * 100;
                }
            };

            xhr.onload = function() {
                progressBar.style.display = 'none';
                statusDiv.style.display = 'block';
                if (xhr.status === 200) {
                    const result = JSON.parse(xhr.responseText);
                    if (result.success) {
                        statusDiv.textContent = result.message;
                        statusDiv.className = 'success';
                        document.getElementById('upload-image-form').reset(); 
                    } else {
                        statusDiv.textContent = 'Greška: ' + result.message;
                        statusDiv.className = 'error';
                    }
                } else {
                    statusDiv.textContent = 'Server greška: ' + xhr.status;
                    statusDiv.className = 'error';
                }
            };
            
            xhr.onerror = function() {
                progressBar.style.display = 'none';
                statusDiv.style.display = 'block';
                statusDiv.textContent = 'Greška u mreži ili zahtjevu.';
                statusDiv.className = 'error';
            };

            xhr.send(formData);
        });
    </script>
</body>
</html>