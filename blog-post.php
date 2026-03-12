<?php
require_once 'config.php';

$id = intval($_GET['id'] ?? 0);

// Zwiększ licznik wyświetleń
$conn->query("UPDATE blog SET views = views + 1 WHERE id = $id");

$stmt = $conn->prepare("SELECT * FROM blog WHERE id = ? AND status = 'opublikowany'");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    showMessage('Nie znaleziono wpisu!', 'error');
    redirect('blog.php');
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['tytul']); ?> - KonZValony</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <!-- ten sam header co w blog.php -->
    </header>

    <main>
        <article class="blog-post-full">
            <div class="post-header">
                <h1><?php echo htmlspecialchars($post['tytul']); ?></h1>
                <div class="post-meta">
                    <span><i class="far fa-user"></i> <?php echo htmlspecialchars($post['autor'] ?? 'Redakcja'); ?></span>
                    <span><i class="far fa-calendar-alt"></i> <?php echo date('d.m.Y', strtotime($post['data_dodania'])); ?></span>
                    <span><i class="far fa-eye"></i> <?php echo $post['views']; ?> wyświetleń</span>
                </div>
            </div>
            
            <div class="post-image">
                <img src="images/<?php echo htmlspecialchars($post['zdjecie']); ?>" 
                     alt="<?php echo htmlspecialchars($post['tytul']); ?>">
            </div>
            
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['tresc'])); ?>
            </div>
            
            <div class="post-footer">
                <a href="blog.php" class="btn-back"><i class="fas fa-arrow-left"></i> Powrót do bloga</a>
                
                <div class="share-buttons">
                    <span>Udostępnij:</span>
                    <a href="#" class="share-fb"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="share-twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="share-linkedin"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </article>
    </main>

    <footer>
        <!-- ten sam footer -->
    </footer>
</body>
</html>