<?php
require_once 'config/database.php';

// Перевірка авторизації
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ініціалізуємо змінну $products як порожній масив
$products = [];
$error = '';

// Отримання товарів користувача з їх зображеннями
try {
    // Спочатку перевіримо, чи існує таблиця product_images
    $check_table_sql = "SHOW TABLES LIKE 'product_images'";
    $check_stmt = $pdo->query($check_table_sql);
    $table_exists = ($check_stmt->rowCount() > 0);
    
    if($table_exists) {
        // Якщо таблиця існує, використовуємо запит з підзапитами
        $sql = "SELECT p.*, 
                (SELECT image_name FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as main_image,
                (SELECT COUNT(*) FROM product_images WHERE product_id = p.id) as image_count
                FROM products p 
                WHERE p.user_id = ? 
                ORDER BY p.created_at DESC";
    } else {
        // Якщо таблиці немає, використовуємо простий запит
        $sql = "SELECT p.*, 
                '' as main_image,
                0 as image_count
                FROM products p 
                WHERE p.user_id = ? 
                ORDER BY p.created_at DESC";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(Exception $e) {
    // У разі помилки виводимо повідомлення та встановлюємо порожній масив
    $error = "Помилка при завантаженні товарів: " . $e->getMessage();
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мої товари - iShop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .my-products-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
        }
        
        .my-products-header h1 {
            font-size: 2.5rem;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .add-product-btn {
            background: white;
            color: #667eea;
            padding: 15px 30px;
            border-radius: 15px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .add-product-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .my-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .my-product-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .my-product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .my-product-image {
            height: 250px;
            overflow: hidden;
            position: relative;
        }
        
        .my-product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .my-product-card:hover .my-product-image img {
            transform: scale(1.1);
        }
        
        .image-count {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(52, 152, 219, 0.9);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .my-product-info {
            padding: 25px;
        }
        
        .my-product-info h3 {
            font-size: 1.4rem;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .my-product-price {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2ecc71;
            margin: 15px 0;
        }
        
        .my-product-category {
            background: #f8f9fa;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 14px;
            color: #7f8c8d;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn-edit {
            background: #3498db;
            color: white;
            padding: 8px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-edit:hover {
            background: #2980b9;
            transform: translateY(-3px);
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
            padding: 8px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-delete:hover {
            background: #c0392b;
            transform: translateY(-3px);
        }
        
        .product-date {
            font-size: 12px;
            color: #95a5a6;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .no-products {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 40px auto;
        }
        
        .no-products i {
            font-size: 5rem;
            color: #3498db;
            margin-bottom: 20px;
        }
        
        .no-products h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .no-products p {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        .stats-bar {
            display: flex;
            justify-content: space-around;
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
            display: block;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="my-products-container">
            <div class="my-products-header">
                <div>
                    <h1><i class="fas fa-box-open"></i> Мої товари</h1>
                    <div class="product-count">Всього товарів: <?php echo count($products); ?></div>
                </div>
                <a href="add-product.php" class="add-product-btn">
                    <i class="fas fa-plus-circle"></i> Додати товар
                </a>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-error" style="animation: fadeIn 0.5s ease;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo escape($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_GET['deleted'])): ?>
                <div class="alert alert-success" style="animation: fadeIn 0.5s ease;">
                    <i class="fas fa-check-circle"></i> Товар успішно видалено
                </div>
            <?php endif; ?>
            
            <?php if(empty($products)): ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <h3>У вас ще немає товарів</h3>
                    <p>Створіть свій перший товар і почніть продавати на iShop!</p>
                    <a href="add-product.php" class="btn btn-primary" style="padding: 15px 40px; font-size: 1.1rem;">
                        <i class="fas fa-plus"></i> Додати перший товар
                    </a>
                </div>
            <?php else: ?>
                <div class="stats-bar">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($products); ?></span>
                        <span class="stat-label">Товарів</span>
                    </div>
                    <div class="stat-item">
                        <?php 
                        $total_images = 0;
                        foreach($products as $product) {
                            $total_images += $product['image_count'];
                        }
                        ?>
                        <span class="stat-number"><?php echo $total_images; ?></span>
                        <span class="stat-label">Фотографій</span>
                    </div>
                    <div class="stat-item">
                        <?php 
                        $total_value = 0;
                        foreach($products as $product) {
                            $total_value += $product['price'];
                        }
                        ?>
                        <span class="stat-number">$<?php echo number_format($total_value, 2); ?></span>
                        <span class="stat-label">Загальна вартість</span>
                    </div>
                </div>
                
                <div class="my-products-grid">
                    <?php foreach($products as $product): ?>
                    <div class="my-product-card">
                        <div class="my-product-image">
                            <?php if(!empty($product['main_image'])): ?>
                                <img src="uploads/<?php echo escape($product['main_image']); ?>" 
                                     alt="<?php echo escape($product['name']); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x300/667eea/ffffff?text=<?php echo urlencode(substr($product['name'], 0, 20)); ?>" 
                                     alt="<?php echo escape($product['name']); ?>">
                            <?php endif; ?>
                            <?php if($product['image_count'] > 0): ?>
                                <div class="image-count">
                                    <i class="fas fa-camera"></i> <?php echo $product['image_count']; ?> фото
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="my-product-info">
                            <h3><?php echo escape($product['name']); ?></h3>
                            
                            <div class="my-product-price">$<?php echo number_format($product['price'], 2); ?></div>
                            
                            <div class="my-product-category">
                                <i class="fas fa-tag"></i> <?php echo escape($product['category']); ?>
                            </div>
                            
                            <p class="product-description" style="color: #7f8c8d; font-size: 14px; line-height: 1.5;">
                                <?php 
                                $description = escape($product['description']);
                                echo strlen($description) > 150 ? substr($description, 0, 150) . '...' : $description; 
                                ?>
                            </p>
                            
                            <div class="product-date">
                                <i class="far fa-clock"></i> Додано: <?php echo date('d.m.Y', strtotime($product['created_at'])); ?>
                            </div>
                            
                            <div class="product-actions">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" 
                                   class="btn-edit">
                                    <i class="fas fa-eye"></i> Переглянути
                                </a>
                                <a href="edit-product.php?id=<?php echo $product['id']; ?>" 
                                   class="btn-edit">
                                    <i class="fas fa-edit"></i> Редагувати
                                </a>
                                <form method="POST" action="delete-product.php" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn-delete" 
                                            onclick="return confirm('Ви впевнені, що хочете видалити цей товар?')">
                                        <i class="fas fa-trash"></i> Видалити
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="script.js"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</body>
</html>