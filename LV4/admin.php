<?php
session_start();
if (!isset($_SESSION['ID']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
?>

<h2>Admin panel</h2>
<p>Dobrodošao, admin <?php echo htmlspecialchars($_SESSION['Username']); ?>.</p>
<a href="logout.php">Odjavi se</a>
<p><a href="weather_input.php">Unesi vrijeme</a></p>
<p><a href="images_input.php">Unesi slike</a></p>