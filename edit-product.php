<?php
require_once 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if(!isset($_GET['id'])) {
    header('Location: my-products.php');
    exit();
}

$product_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Перевіряємо, чи товар належить користувачу
$sql = "SELECT * FROM products WHERE id = ? AND user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$product_id, $user_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$product) {
    header('Location: my-products.php');
    exit();
}

// Перевіряємо, чи існує таблиця product_images
$table_exists = false;
try {
    $check_sql = "SHOW TABLES LIKE 'product_images'";
    $check_stmt = $pdo->query($check_sql);
    $table_exists = ($check_stmt->rowCount() > 0);
} catch(Exception $e) {
    $table_exists = false;
}

// Отримуємо зображення товару (якщо таблиця існує)
$existing_images = [];
if($table_exists) {
    try {
        $images_sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC";
        $images_stmt = $pdo->prepare($images_sql);
        $images_stmt->execute([$product_id]);
        $existing_images = $images_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
        // Таблиця не існує або є помилка
        $existing_images = [];
    }
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $category = trim($_POST['category']);
    
    // Валідація
    if(empty($name) || empty($price) || empty($category)) {
        $error = 'Будь ласка, заповніть всі обов\'язкові поля';
    } elseif(!is_numeric($price) || $price <= 0) {
        $error = 'Ціна повинна бути додатнім числом';
    } else {
        // Оновлюємо товар
        $update_sql = "UPDATE products SET name = ?, description = ?, price = ?, category = ? WHERE id = ?";
        $update_stmt = $pdo->prepare($update_sql);
        
        if($update_stmt->execute([$name, $description, $price, $category, $product_id])) {
            $success = 'Товар успішно оновлено!';
            // Оновлюємо змінну $product
            $product = array_merge($product, [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'category' => $category
            ]);
        } else {
            $error = 'Сталася помилка при оновленні товару';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редагувати товар - iShop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .edit-product-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }
        
        .product-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .current-images {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }
        
        .current-image {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            border: 2px solid #ddd;
        }
        
        .current-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .main-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: #2ecc71;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="edit-product-container">
            <h1 style="text-align: center; margin-bottom: 30px;">Редагувати товар</h1>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo escape($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo escape($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="product-form">
                <div class="form-group">
                    <label for="name"><i class="fas fa-tag"></i> Назва товару *</label>
                    <input type="text" id="name" name="name" class="form-control" required
                           value="<?php echo escape($product['name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="price"><i class="fas fa-dollar-sign"></i> Ціна ($) *</label>
                    <input type="number" id="price" name="price" class="form-control" step="0.01" min="0.01" required
                           value="<?php echo escape($product['price']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="category"><i class="fas fa-list"></i> Категорія *</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">Виберіть категорію</option>
                        <option value="Електроніка" <?php echo $product['category'] == 'Електроніка' ? 'selected' : ''; ?>>Електроніка</option>
                        <option value="Одяг" <?php echo $product['category'] == 'Одяг' ? 'selected' : ''; ?>>Одяг</option>
                        <option value="Книги" <?php echo $product['category'] == 'Книги' ? 'selected' : ''; ?>>Книги</option>
                        <option value="Спорт" <?php echo $product['category'] == 'Спорт' ? 'selected' : ''; ?>>Спорт</option>
                        <option value="Побутова техніка" <?php echo $product['category'] == 'Побутова техніка' ? 'selected' : ''; ?>>Побутова техніка</option>
                        <option value="Взуття" <?php echo $product['category'] == 'Взуття' ? 'selected' : ''; ?>>Взуття</option>
                        <option value="Продукти" <?php echo $product['category'] == 'Продукти' ? 'selected' : ''; ?>>Продукти</option>
                        <option value="Інше" <?php echo $product['category'] == 'Інше' ? 'selected' : ''; ?>>Інше</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Опис товару</label>
                    <textarea id="description" name="description" class="form-control" rows="5"><?php echo escape($product['description']); ?></textarea>
                </div>
                
                <?php if($table_exists && !empty($existing_images)): ?>
                    <div class="form-group">
                        <label><i class="fas fa-images"></i> Поточні зображення</label>
                        <div class="current-images">
                            <?php foreach($existing_images as $image): ?>
                                <div class="current-image">
                                    <img src="uploads/<?php echo escape($image['image_name']); ?>" 
                                         alt="Зображення товару">
                                    <?php if($image['is_main']): ?>
                                        <div class="main-badge">Головна</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Зберегти зміни
                    </button>
                    <a href="my-products.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Скасувати
                    </a>
                </div>
            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>