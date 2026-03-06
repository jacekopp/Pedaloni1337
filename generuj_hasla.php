<?php
// Zapisz jako: C:\xampp\htdocs\konzvalony\generuj_hasla.php
echo "<h2>Generowanie hash'y haseł dla KonZValony</h2>";

$admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
$user_hash = password_hash('user123', PASSWORD_DEFAULT);

echo "<b>Hash dla admin123:</b><br>";
echo $admin_hash . "<br><br>";

echo "<b>Hash dla user123:</b><br>";
echo $user_hash . "<br><br>";

echo "<hr>";
echo "<h3>Skopiuj te zapytania SQL do phpMyAdmin:</h3>";
echo "<textarea rows='10' cols='80' style='padding:10px;'>";
echo "USE konzvalony;\n\n";
echo "-- Update hasła dla admina\n";
echo "UPDATE uzytkownicy SET haslo = '" . $admin_hash . "' WHERE id = 1;\n\n";
echo "-- Update hasła dla użytkowników\n";
echo "UPDATE uzytkownicy SET haslo = '" . $user_hash . "' WHERE id IN (2,3);\n";
echo "</textarea>";
?>