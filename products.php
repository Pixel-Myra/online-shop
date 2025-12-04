<?php
require_once 'config/database.php';

// Обробка параметрів фільтрації
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

// Побудова SQL запиту
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if(!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if(!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Отримання загальної кількості товарів для пагінації
$count_sql = "SELECT COUNT(*) as total FROM products WHERE 1=1";
if(!empty($category)) {
    $count_sql .= " AND category = ?";
}
if(!empty($search)) {
    $count_sql .= " AND (name LIKE ? OR description LIKE ?)";
}

$stmt = $pdo->prepare($count_sql);
$count_params = [];
if(!empty($category)) $count_params[] = $category;
if(!empty($search)) {
    $count_params[] = "%$search%";
    $count_params[] = "%$search%";
}

if(!empty($count_params)) {
    $stmt->execute($count_params);
} else {
    $stmt->execute();
}
$total_products = $stmt->fetch()['total'];
$total_pages = ceil($total_products / $limit);

// Отримання товарів з пагінацією
$sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset"; // Виправлено тут

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Отримання унікальних категорій для фільтра
$categories_sql = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category";
$categories_stmt = $pdo->query($categories_sql);
$categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Товари - iShop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="products-header">
            <h1>Наші товари</h1>
            
            <div class="filters">
                <form method="GET" action="" class="filter-form">
                    <input type="text" name="search" placeholder="Пошук товарів..." 
                           value="<?php echo escape($search); ?>" class="search-input">
                    
                    <select name="category" class="category-select">
                        <option value="">Всі категорії</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo escape($cat); ?>" 
                                    <?php echo $category == $cat ? 'selected' : ''; ?>>
                                <?php echo escape($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Фільтрувати
                    </button>
                    
                    <?php if(!empty($category) || !empty($search)): ?>
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Скинути
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <?php if($total_products > 0): ?>
            <div class="products-grid">
                <?php foreach($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if(!empty($product['image'])): ?>
                            <img src="uploads/<?php echo escape($product['image']); ?>" 
                                 alt="<?php echo escape($product['name']); ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x200?text=<?php echo urlencode(substr($product['name'], 0, 20)); ?>" alt="<?php echo escape($product['name']); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?php echo escape($product['name']); ?></h3>
                        <p class="product-description">
                            <?php 
                            $description = escape($product['description']);
                            echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description; 
                            ?>
                        </p>
                        <div class="product-meta">
                            <span class="product-price">$<?php echo escape($product['price']); ?></span>
                            <span class="product-category"><?php echo escape($product['category']); ?></span>
                        </div>
                        <?php 
                        // Отримуємо ім'я власника товару
                        $seller_sql = "SELECT username FROM users WHERE id = ?";
                        $seller_stmt = $pdo->prepare($seller_sql);
                        $seller_stmt->execute([$product['user_id']]);
                        $seller = $seller_stmt->fetch();
                        ?>
                        <p class="product-seller">Продавець: <?php echo escape($seller['username']); ?></p>
                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" 
                           class="btn btn-secondary">Детальніше</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Пагінація -->
            <?php if($total_pages > 1): ?>
            <div class="pagination">
                <?php if($page > 1): ?>
                    <a href="?page=<?php echo $page-1; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="page-link">← Попередня</a>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if($i == $page): ?>
                        <span class="page-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="page-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="page-link">Наступна →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-products">
                <i class="fas fa-box-open"></i>
                <h3>Товарів не знайдено</h3>
                <p>Спробуйте змінити параметри пошуку</p>
                <a href="products.php" class="btn btn-primary">Показати всі товари</a>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>