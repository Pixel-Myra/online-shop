<?php
require_once 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Обробка форми
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $category = trim($_POST['category']);
    
    if(empty($name) || empty($price) || empty($category)) {
        $error = 'Будь ласка, заповніть всі обов\'язкові поля';
    } elseif(!is_numeric($price) || $price <= 0) {
        $error = 'Ціна повинна бути додатнім числом';
    } else {
        $image_name = '';
        
        // Обробка завантаження одного зображення (спрощено)
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if(in_array($_FILES['image']['type'], $allowed_types)) {
                if($_FILES['image']['size'] <= $max_size) {
                    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $image_name = uniqid() . '.' . $extension;
                    $upload_path = 'uploads/' . $image_name;
                    
                    if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        // Успіх
                    } else {
                        $error = 'Помилка при завантаженні зображення';
                    }
                } else {
                    $error = 'Розмір файлу не повинен перевищувати 5MB';
                }
            } else {
                $error = 'Дозволені тільки файли зображень (JPG, PNG, GIF, WebP)';
            }
        }
        
        if(empty($error)) {
            // Додавання товару в базу даних
            $sql = "INSERT INTO products (user_id, name, description, price, category, image) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if($stmt->execute([$_SESSION['user_id'], $name, $description, $price, $category, $image_name])) {
                $success = 'Товар успішно додано!';
                header('refresh:2;url=my-products.php');
            } else {
                $error = 'Сталася помилка при додаванні товару';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Додати товар - iShop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .add-product-container {
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
        
        .form-control:focus {
            border-color: #3498db;
            outline: none;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .image-upload {
            border: 2px dashed #3498db;
            padding: 40px;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            background: #f8f9fa;
        }
        
        .image-upload:hover {
            background: #e8f4fc;
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
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="add-product-container">
            <h1 style="text-align: center; margin-bottom: 30px;">Додати новий товар</h1>
            
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
            
            <form method="POST" action="" enctype="multipart/form-data" class="product-form">
                <div class="form-group">
                    <label for="name"><i class="fas fa-tag"></i> Назва товару *</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="price"><i class="fas fa-dollar-sign"></i> Ціна ($) *</label>
                    <input type="number" id="price" name="price" class="form-control" step="0.01" min="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="category"><i class="fas fa-list"></i> Категорія *</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">Виберіть категорію</option>
                        <option value="Електроніка">Електроніка</option>
                        <option value="Одяг">Одяг</option>
                        <option value="Книги">Книги</option>
                        <option value="Спорт">Спорт</option>
                        <option value="Побутова техніка">Побутова техніка</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Опис товару</label>
                    <textarea id="description" name="description" class="form-control" rows="5"></textarea>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-image"></i> Зображення товару</label>
                    <div class="image-upload" onclick="document.getElementById('image').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #3498db; margin-bottom: 15px;"></i>
                        <h3>Натисніть для вибору зображення</h3>
                        <p>Дозволені формати: JPG, PNG, GIF, WebP. Максимальний розмір: 5MB</p>
                        <input type="file" id="image" name="image" accept="image/*" style="display: none;">
                    </div>
                    <p id="file-name" style="margin-top: 10px; color: #7f8c8d;"></p>
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Додати товар
                    </button>
                    <a href="my-products.php" class="btn btn-secondary">Скасувати</a>
                </div>
            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Показуємо ім'я вибраного файлу
        document.getElementById('image').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Файл не вибрано';
            document.getElementById('file-name').textContent = 'Вибрано: ' + fileName;
        });
    </script>
</body>
</html>