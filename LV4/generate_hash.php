<?php
$lozinka = 'tajna123'; 
$hash = password_hash($lozinka, PASSWORD_DEFAULT);
echo "Hashirana lozinka: " . $hash;
?>
