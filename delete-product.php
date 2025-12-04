<?php
require_once 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $user_id = $_SESSION['user_id'];
    
    // Перевіряємо, чи товар належить користувачу
    $sql = "SELECT id FROM products WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id, $user_id]);
    
    if($stmt->rowCount() > 0) {
        // Перевіряємо, чи існує таблиця product_images
        $check_table_sql = "SHOW TABLES LIKE 'product_images'";
        $check_stmt = $pdo->query($check_table_sql);
        $table_exists = ($check_stmt->rowCount() > 0);
        
        if($table_exists) {
            // Отримуємо всі зображення товару
            $images_sql = "SELECT image_name FROM product_images WHERE product_id = ?";
            $images_stmt = $pdo->prepare($images_sql);
            $images_stmt->execute([$product_id]);
            $images = $images_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Видаляємо файли зображень
            foreach($images as $image) {
                $file_path = 'uploads/' . $image['image_name'];
                if(file_exists($file_path)) {
                    unlink($file_path);
                }
            }
        }
        
        // Видаляємо товар
        $delete_sql = "DELETE FROM products WHERE id = ?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$product_id]);
        
        header('Location: my-products.php?deleted=1');
        exit();
    }
}

header('Location: my-products.php');
?>