<?php
session_start();
require_once 'includes/db.php';

$greska = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $korime = $_POST['korime'];
    $lozinka = $_POST['lozinka'];

    $stmt = mysqli_prepare($conn, "SELECT ID, Password, role FROM users WHERE Username = ?");
    mysqli_stmt_bind_param($stmt, "s", $korime);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $hash_lozinka, $uloga);
    mysqli_stmt_fetch($stmt);

    if (password_verify($lozinka, $hash_lozinka)) {
        $_SESSION['ID'] = $id;
        $_SESSION['Username'] = $korime;
        $_SESSION['role'] = $uloga;

        if ($uloga === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: home.php");
        }
        exit;
    } else {
        $greska = "Pogrešno korisničko ime ili lozinka.";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head><title>Prijava</title><link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Prijava</h2>
    <?php if ($greska) echo "<p style='color:red;'>$greska</p>"; ?>
    <form method="POST">
        <input type="text" name="korime" placeholder="Korisničko ime" required>
        <br>
        <input type="password" name="lozinka" placeholder="Lozinka" required>
        <br>
        <button type="submit">Prijavi se</button>
    </form>
    <p>Nemaš račun? <a href="register.php">Registriraj se</a></p>
</body>
</html>
