<?php
require_once 'includes/db.php';

$greska = '';
$uspjeh = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $korime = trim($_POST['korime']);
    $lozinka = $_POST['lozinka'];

    if (strlen($korime) < 3 || strlen($lozinka) < 5) {
        $greska = "Korisničko ime mora imati barem 3 znaka, a lozinka 5.";
    } else {
        $hash = password_hash($lozinka, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conn, "INSERT INTO users (Username, Password) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $korime, $hash);

        if (mysqli_stmt_execute($stmt)) {
            $uspjeh = "Registracija uspješna! Možete se prijaviti.";
        } else {
            $greska = "Greška: Korisničko ime je možda već zauzeto.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Registracija</title></head>
<body>
    <h2>Registracija korisnika</h2>
    <?php if ($greska) echo "<p style='color:red;'>$greska</p>"; ?>
    <?php if ($uspjeh) echo "<p style='color:green;'>$uspjeh</p>"; ?>
    <form method="POST">
        <input type="text" name="korime" placeholder="Korisničko ime" required>
        <br>
        <input type="password" name="lozinka" placeholder="Lozinka" required>
        <br>
        <button type="submit">Registriraj se</button>
    </form>
    <p>Već imate račun? <a href="index.php">Prijavi se</a></p>
</body>
</html>
