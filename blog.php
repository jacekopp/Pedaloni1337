<?php
require_once 'config.php';

// Pobierz wpisy z bloga
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM blog WHERE status = 'opublikowany' ORDER BY data_dodania DESC LIMIT $offset, $limit";
$result = $conn->query($sql);
$posts = $result->fetch_all(MYSQLI_ASSOC);

// Policz wszystkie wpisy
$total = $conn->query("SELECT COUNT(*) as count FROM blog WHERE status = 'opublikowany'")->fetch_assoc()['count'];
$total_pages = ceil($total / $limit);

$message = getMessage();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - KonZValony</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo-container">
                <img src="images/image.png" alt="KonZValony" class="logo">
            </div>
            <h1>KonZValony</h1>
            <p class="tagline">Blog i aktualności ze stajni</p>
        </div>
        <nav>
            <ul>
                <li><a href="index.php" class="active">Stajnia</a></li>
                <li><a href="blog.php">Blog</a></li>
                <li><a href="mapa.php">Mapa</a></li>
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
        <?php if ($message): ?>
            <div class="message message-<?php echo $message['type']; ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endif; ?>

        <section class="blog-header">
            <h2><i class="fas fa-blog"></i> Blog KonZValony</h2>
            <p>Porady, ciekawostki i nowości ze świata koni</p>
        </section>

        <div class="blog-grid">
            <?php foreach ($posts as $post): ?>
                <article class="blog-card">
                    <div class="blog-image">
                        <img src="images/<?php echo htmlspecialchars($post['zdjecie']); ?>" 
                             alt="<?php echo htmlspecialchars($post['tytul']); ?>">
                        <div class="blog-date">
                            <i class="far fa-calendar-alt"></i> 
                            <?php echo date('d.m.Y', strtotime($post['data_dodania'])); ?>
                        </div>
                    </div>
                    <div class="blog-content">
                        <h3><?php echo htmlspecialchars($post['tytul']); ?></h3>
                        <p class="blog-meta">
                            <i class="far fa-user"></i> <?php echo htmlspecialchars($post['autor'] ?? 'Redakcja'); ?>
                            <i class="far fa-eye"></i> <?php echo $post['views']; ?> wyświetleń
                        </p>
                        <p class="blog-excerpt">
                            <?php echo substr(htmlspecialchars($post['tresc']), 0, 200) . '...'; ?>
                        </p>
                        <a href="blog-post.php?id=<?php echo $post['id']; ?>" class="btn-read-more">
                            Czytaj więcej <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2026 KonZValony - Wypożyczalnia koni z humorem</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>