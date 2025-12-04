<?php
require_once 'config/database.php';

if(!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = (int)$_GET['id'];

// Отримуємо інформацію про товар
$sql = "SELECT p.*, u.username, u.email 
        FROM products p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$product) {
    header('Location: products.php');
    exit();
}

// Перевіряємо, чи існує таблиця product_images
$images = [];
$table_exists = false;

try {
    $check_sql = "SHOW TABLES LIKE 'product_images'";
    $check_stmt = $pdo->query($check_sql);
    $table_exists = ($check_stmt->rowCount() > 0);
    
    if($table_exists) {
        // Отримуємо всі зображення товару
        $images_sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC, created_at";
        $images_stmt = $pdo->prepare($images_sql);
        $images_stmt->execute([$product_id]);
        $images = $images_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch(Exception $e) {
    // Таблиця не існує, продовжуємо без зображень
    $table_exists = false;
    error_log("Помилка таблиці product_images: " . $e->getMessage());
}

// Якщо таблиці немає, створюємо фейкові зображення для демонстрації
if(!$table_exists || empty($images)) {
    // Створюємо одне фейкове зображення для товару
    $images = [[
        'image_name' => $product['id'] . '_product.jpg',
        'is_main' => 1
    ]];
}

// Отримуємо інші товари того ж продавця
try {
    if($table_exists) {
        $related_sql = "SELECT p.*, pi.image_name 
                        FROM products p 
                        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1 
                        WHERE p.user_id = ? AND p.id != ? 
                        ORDER BY RAND() 
                        LIMIT 4";
    } else {
        $related_sql = "SELECT p.* 
                        FROM products p 
                        WHERE p.user_id = ? AND p.id != ? 
                        ORDER BY RAND() 
                        LIMIT 4";
    }
    
    $related_stmt = $pdo->prepare($related_sql);
    $related_stmt->execute([$product['user_id'], $product_id]);
    $related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $related_products = [];
    error_log("Помилка запиту пов'язаних товарів: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($product['name']); ?> - iShop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .product-detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        
        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
            }
        }
        
        .product-image-large {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .product-image-large img {
            width: 100%;
            height: auto;
            border-radius: 15px;
            transition: transform 0.5s ease;
        }
        
        .product-image-large img:hover {
            transform: scale(1.03);
        }
        
        .product-info-detail h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #3498db;
        }
        
        .product-meta-detail {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin: 20px 0;
        }
        
        .price {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2ecc71;
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 10px;
        }
        
        .category {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
        }
        
        .date {
            background: #f39c12;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
        }
        
        .seller-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin: 25px 0;
        }
        
        .seller-info h3 {
            margin-bottom: 15px;
            font-size: 1.5rem;
            color: white;
        }
        
        .seller-info p {
            margin: 8px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .seller-info i {
            width: 20px;
        }
        
        .product-description-full {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-top: 25px;
            border-left: 5px solid #3498db;
        }
        
        .product-description-full h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.8rem;
        }
        
        .product-description-full p {
            line-height: 1.8;
            color: #34495e;
        }
        
        .product-actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
        }
        
        .related-products {
            margin-top: 50px;
        }
        
        .related-products h2 {
            text-align: center;
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }
        
        .related-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .related-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .related-image {
            height: 180px;
            overflow: hidden;
        }
        
        .related-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .related-card:hover .related-image img {
            transform: scale(1.1);
        }
        
        .related-info {
            padding: 20px;
        }
        
        .related-info h4 {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .related-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2ecc71;
            margin: 10px 0;
        }
        
        .btn-small {
            padding: 8px 20px;
            font-size: 14px;
        }
        
        .image-gallery {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.3s ease;
        }
        
        .thumbnail.active {
            border-color: #3498db;
        }
        
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .gallery-controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="product-detail-container">
            <div class="product-detail">
                <!-- Галерея зображень -->
                <div class="product-gallery">
                    <div class="product-image-large">
                        <?php if($table_exists && !empty($images)): ?>
                            <img src="uploads/<?php echo escape($images[0]['image_name']); ?>" 
                                 alt="<?php echo escape($product['name']); ?>" 
                                 id="mainImage">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/800x600/667eea/ffffff?text=<?php echo urlencode($product['name']); ?>" 
                                 alt="<?php echo escape($product['name']); ?>"
                                 id="mainImage">
                        <?php endif; ?>
                    </div>
                    
                    <?php if(count($images) > 1): ?>
                        <div class="gallery-controls">
                            <button class="btn btn-secondary btn-small" onclick="prevImage()">
                                <i class="fas fa-chevron-left"></i> Попередня
                            </button>
                            <button class="btn btn-secondary btn-small" onclick="nextImage()">
                                Наступна <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        
                        <div class="image-gallery" id="thumbnailGallery">
                            <?php foreach($images as $index => $image): ?>
                                <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                     onclick="changeImage(<?php echo $index; ?>)">
                                    <?php if($table_exists): ?>
                                        <img src="uploads/<?php echo escape($image['image_name']); ?>" 
                                             alt="<?php echo escape($product['name']); ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/100x100/667eea/ffffff?text=<?php echo urlencode(substr($product['name'], 0, 5)); ?>" 
                                             alt="<?php echo escape($product['name']); ?>">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Інформація про товар -->
                <div class="product-info-detail">
                    <h1><?php echo escape($product['name']); ?></h1>
                    
                    <div class="product-meta-detail">
                        <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
                        <span class="category"><?php echo escape($product['category']); ?></span>
                        <span class="date"><?php echo date('d.m.Y', strtotime($product['created_at'])); ?></span>
                    </div>
                    
                    <div class="seller-info">
                        <h3><i class="fas fa-store"></i> Продавець</h3>
                        <p><i class="fas fa-user"></i> <?php echo escape($product['username']); ?></p>
                        <p><i class="fas fa-envelope"></i> <?php echo escape($product['email']); ?></p>
                        <p><i class="fas fa-id-card"></i> ID: <?php echo escape($product['user_id']); ?></p>
                    </div>
                    
                    <div class="product-description-full">
                        <h3><i class="fas fa-align-left"></i> Опис товару</h3>
                        <p><?php echo nl2br(escape($product['description'])); ?></p>
                    </div>
                    
                    <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $product['user_id']): ?>
                        <div class="product-actions">
                            <a href="my-products.php" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Керувати моїми товарами
                            </a>
                            <a href="add-product.php" class="btn btn-secondary">
                                <i class="fas fa-plus"></i> Додати новий товар
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!$table_exists): ?>
                        <div class="alert alert-warning" style="margin-top: 20px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Інформація:</strong> Таблиця для кількох зображень товару не створена. 
                            <a href="#" onclick="alert('Створіть таблицю product_images в базі даних')">Дізнатися більше</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Пов'язані товари -->
            <?php if(!empty($related_products)): ?>
                <section class="related-products">
                    <h2>Інші товари цього продавця</h2>
                    <div class="related-grid">
                        <?php foreach($related_products as $related): ?>
                            <div class="related-card">
                                <div class="related-image">
                                    <?php if(!empty($related['image_name'])): ?>
                                        <img src="uploads/<?php echo escape($related['image_name']); ?>" 
                                             alt="<?php echo escape($related['name']); ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/300x200/667eea/ffffff?text=<?php echo urlencode(substr($related['name'], 0, 15)); ?>" 
                                             alt="<?php echo escape($related['name']); ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="related-info">
                                    <h4><?php echo escape($related['name']); ?></h4>
                                    <p class="related-price">$<?php echo number_format($related['price'], 2); ?></p>
                                    <a href="product-detail.php?id=<?php echo $related['id']; ?>" 
                                       class="btn btn-small btn-primary">Детальніше</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="script.js"></script>
    <script>
        // Проста галерея зображень
        <?php if(count($images) > 1): ?>
        let currentImageIndex = 0;
        const totalImages = <?php echo count($images); ?>;
        
        function changeImage(index) {
            currentImageIndex = index;
            updateGallery();
        }
        
        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % totalImages;
            updateGallery();
        }
        
        function prevImage() {
            currentImageIndex = (currentImageIndex - 1 + totalImages) % totalImages;
            updateGallery();
        }
        
        function updateGallery() {
            // Оновлюємо головне зображення
            const mainImage = document.getElementById('mainImage');
            const imageNames = <?php echo json_encode(array_column($images, 'image_name')); ?>;
            
            <?php if($table_exists): ?>
                mainImage.src = 'uploads/' + imageNames[currentImageIndex];
            <?php else: ?>
                mainImage.src = 'https://via.placeholder.com/800x600/667eea/ffffff?text=<?php echo urlencode($product['name']); ?>';
            <?php endif; ?>
            
            // Оновлюємо мініатюри
            const thumbnails = document.querySelectorAll('.thumbnail');
            thumbnails.forEach((thumb, index) => {
                thumb.classList.toggle('active', index === currentImageIndex);
            });
        }
        
        // Автоматична зміна зображень кожні 5 секунд
        setInterval(nextImage, 5000);
        <?php endif; ?>
    </script>
</body>
</html>