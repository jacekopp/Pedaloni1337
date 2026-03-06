<?php
require_once 'config.php';

// Zniszcz wszystkie dane sesji
$_SESSION = array();

// Zniszcz sesję
session_destroy();

// Przekieruj na stronę główną
redirect('index.php');
?>