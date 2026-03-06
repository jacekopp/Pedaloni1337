<?php
// Plik konfiguracyjny - config.php
session_start();

// Stałe do połączenia z bazą danych
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'konzvalony');

// Połączenie z bazą danych
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Sprawdzenie połączenia
if ($conn->connect_errno) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

// Ustawienie kodowania
$conn->set_charset("utf8mb4");

// Funkcja sprawdzająca czy użytkownik jest zalogowany
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Funkcja sprawdzająca czy użytkownik jest adminem
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Funkcja przekierowująca
function redirect($url) {
    header("Location: $url");
    exit();
}

// Funkcja wyświetlająca komunikaty
function showMessage($message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

// Funkcja pobierająca i usuwająca komunikat
function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = [
            'text' => $_SESSION['message'],
            'type' => $_SESSION['message_type'] ?? 'info'
        ];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return $message;
    }
    return null;
}
?>