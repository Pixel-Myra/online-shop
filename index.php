<?php
require_once 'config/database.php';

// Отримуємо останні товари (виправлений запит)
try {
    // Простий запит без JOIN, щоб уникнути помилок
    $sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 6";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    // Якщо виникла помилка, показуємо тестові товари
    $products = [];
    error_log("Помилка БД: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Головна - Інтернет Магазин</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <section class="hero">
            <div class="hero-content">
                <h1>Ласкаво просимо до нашого магазину!</h1>
                <p>Знайдіть найкращі товари за вигідними цінами</p>
                <a href="products.php" class="btn btn-primary">Переглянути всі товари</a>
            </div>
        </section>

        <section class="featured-products">
            <h2 class="section-title">Нові надходження</h2>
            <div class="products-grid">
                <?php if(!empty($products)): ?>
                    <?php foreach($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if(!empty($product['image'])): ?>
                                <img src="uploads/<?php echo escape($product['image']); ?>" alt="<?php echo escape($product['name']); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/300x200?text=<?php echo urlencode(substr($product['name'], 0, 20)); ?>" alt="<?php echo escape($product['name']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo escape($product['name']); ?></h3>
                            <p class="product-price">$<?php echo escape($product['price']); ?></p>
                            <p class="product-category"><?php echo escape($product['category']); ?></p>
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">Детальніше</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>Наразі немає товарів. Будьте першим, хто додасть товар!</p>
                        <a href="register.php" class="btn btn-primary">Зареєструватися та додати товар</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="features">
            <div class="feature">
                <i class="fas fa-shipping-fast"></i>
                <h3>Безкоштовна доставка</h3>
                <p>Для замовлень від $100</p>
            </div>
            <div class="feature">
                <i class="fas fa-shield-alt"></i>
                <h3>Гарантія якості</h3>
                <p>30 днів на повернення</p>
            </div>
            <div class="feature">
                <i class="fas fa-headset"></i>
                <h3>Підтримка 24/7</h3>
                <p>Наші оператори завжди на зв'язку</p>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>