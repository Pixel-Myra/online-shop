<?php
require_once 'config/database.php';

echo "<h2>Перевірка товарів</h2>";

try {
    // Перевіряємо, чи є товари у поточного користувача
    $sql = "SELECT * FROM products WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([1]); // ID адміна
    $products = $stmt->fetchAll();
    
    echo "<p>Знайдено товарів: " . count($products) . "</p>";
    
    if(count($products) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Назва</th><th>Ціна</th><th>Категорія</th></tr>";
        foreach($products as $product) {
            echo "<tr>";
            echo "<td>" . $product['id'] . "</td>";
            echo "<td>" . $product['name'] . "</td>";
            echo "<td>$" . $product['price'] . "</td>";
            echo "<td>" . $product['category'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch(Exception $e) {
    echo "Помилка: " . $e->getMessage();
}

echo "<h2>Перевірка таблиці product_images</h2>";

try {
    $check_sql = "SHOW TABLES LIKE 'product_images'";
    $check_stmt = $pdo->query($check_sql);
    
    if($check_stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Таблиця product_images існує</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Таблиця product_images не існує</p>";
    }
} catch(Exception $e) {
    echo "Помилка: " . $e->getMessage();
}
?>