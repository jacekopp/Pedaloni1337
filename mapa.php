<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa dojazdu - KonZValony</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo-container">
                <img src="images/image.png" alt="KonZValony - wypożyczalnia koni" class="logo">
            </div>
            <h1>KonZValony</h1>
            <p class="tagline">Wypożyczalnia koni, które mają więcej charakteru niż Twój były!</p>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Stajnia</a></li>
                <li><a href="blog.php">Blog</a></li>
                <li><a href="mapa.php" class="active">Mapa</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="panel.php">Panel</a></li>
                    <li><a href="ulubione.php">Ulubione</a></li>
                    <li><a href="polec.php">Poleć znajomym</a></li>
                    <li><a href="logout.php">Wyloguj (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Logowanie</a></li>
                    <li><a href="register.php">Rejestracja</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="map-header">
            <h2><i class="fas fa-map-marked-alt"></i> Gdzie nas znaleźć?</h2>
            <p>Stajnia KonZValony znajduje się w malowniczej okolicy, z dala od zgiełku miasta</p>
        </section>

        <div class="map-container">
            <div class="map-info">
                <div class="info-card">
                    <h3><i class="fas fa-location-dot"></i> Adres</h3>
                    <p>Stajnia KonZValony<br>
                    ul. Leśna 12<br>
                    05-500 Konstancin-Jeziorna</p>
                    
                    <h3><i class="fas fa-phone"></i> Kontakt</h3>
                    <p>Tel: +48 123 456 789<br>
                    Email: stajnia@konzvalony.pl</p>
                    
                    <h3><i class="fas fa-clock"></i> Godziny otwarcia</h3>
                    <p>Pon-Pt: 8:00 - 18:00<br>
                    Sob: 9:00 - 16:00<br>
                    Nd: Nieczynne</p>
                    
                    <h3><i class="fas fa-car"></i> Dojazd</h3>
                    <p>Z Warszawy: jedź drogą nr 724 w kierunku Konstancina. Za mostem w prawo, po 2 km skręć w Leśną. Stajnia znajduje się 500 m za skrętem.</p>
                </div>
                
                <div class="info-card">
                    <h3><i class="fas fa-tree"></i> Okolica</h3>
                    <ul>
                        <li><i class="fas fa-tree"></i> Rezerwat przyrody "Las Kabacki" - 3 km</li>
                        <li><i class="fas fa-water"></i> Wisła - 2 km</li>
                        <li><i class="fas fa-utensils"></i> Restauracja "Pod Kasztanem" - 500 m</li>
                        <li><i class="fas fa-parking"></i> Bezpłatny parking dla klientów</li>
                        <li><i class="fas fa-bus"></i> Przystanek autobusowy - 200 m</li>
                    </ul>
                </div>
            </div>

            <!-- Mapa Google -->
            <div class="map-iframe">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2443.482847615573!2d21.09794431579674!3d52.22967597975995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x471ecc5f3b7b7b7b%3A0x3b7b7b7b7b7b7b!2sWarsaw!5e0!3m2!1sen!2spl!4v1620000000000!5m2!1sen!2spl" 
                    width="100%" 
                    height="450" 
                    style="border:0; border-radius: 20px;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 KonZValony - Wypożyczalnia koni z humorem</p>
        <p class="small">* Nie ponosimy odpowiedzialności za rozerwanie oka saurona</p>
    </footer>

    <!-- Panel dostępności -->
    <div class="accessibility-panel">
        <button class="accessibility-toggle" aria-label="Otwórz panel dostępności">
            <span class="accessibility-icon">♿</span>
        </button>
        
        <div class="accessibility-menu">
            <h4>Ułatwienia dostępu</h4>
            
            <div class="accessibility-section">
                <p>Rozmiar czcionki:</p>
                <div class="font-size-controls">
                    <button class="font-size-btn small" data-size="small">A-</button>
                    <button class="font-size-btn normal active" data-size="normal">A</button>
                    <button class="font-size-btn large" data-size="large">A+</button>
                    <button class="font-size-btn xlarge" data-size="xlarge">A++</button>
                </div>
            </div>
            
            <div class="accessibility-section">
                <p>Kontrast:</p>
                <div class="contrast-controls">
                    <button class="contrast-btn normal active" data-contrast="normal">Normalny</button>
                    <button class="contrast-btn high" data-contrast="high">Wysoki</button>
                    <button class="contrast-btn dark" data-contrast="dark">Ciemny</button>
                </div>
            </div>
            
            <div class="accessibility-section">
                <p>Odstępy:</p>
                <div class="spacing-controls">
                    <button class="spacing-btn normal active" data-spacing="normal">Standard</button>
                    <button class="spacing-btn large" data-spacing="large">Szerokie</button>
                </div>
            </div>
            
            <div class="accessibility-section">
                <p>Animacje:</p>
                <div class="motion-controls">
                    <button class="motion-btn normal active" data-motion="normal">Włączone</button>
                    <button class="motion-btn reduced" data-motion="reduced">Wyłączone</button>
                </div>
            </div>
            
            <div class="accessibility-section">
                <p>Czytelność:</p>
                <div class="readability-controls">
                    <button class="readability-btn serif" data-readability="serif">Szeryfowa</button>
                    <button class="readability-btn sans active" data-readability="sans">Bezszeryfowa</button>
                    <button class="readability-btn mono" data-readability="mono">Monospace</button>
                </div>
            </div>
            
            <div class="accessibility-section">
                <p>Linia pomocnicza:</p>
                <div class="ruler-controls">
                    <button class="ruler-btn off active" data-ruler="off">Wyłącz</button>
                    <button class="ruler-btn line" data-ruler="line">Linia</button>
                    <button class="ruler-btn ruler" data-ruler="ruler">Linijka</button>
                </div>
            </div>
            
            <div class="accessibility-section">
                <button class="reset-all-btn">⟲ Resetuj wszystko</button>
            </div>
            
            <div class="accessibility-footer">
                <small>Ustawienia zapisują się automatycznie</small>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>