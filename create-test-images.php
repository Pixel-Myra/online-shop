<?php
// Створюємо папку uploads, якщо її немає
if (!file_exists('uploads')) {
    mkdir('uploads', 0755, true);
}

// Створюємо файл index.php для захисту папки
$index_content = '<?php header("HTTP/1.0 403 Forbidden"); echo "<h1>Доступ заборонено</h1>"; exit(); ?>';
file_put_contents('uploads/index.php', $index_content);

// Список тестових зображень (основні та додаткові)
$test_images = [
    'iphone.jpg',
    'iphone_2.jpg',
    'iphone_3.jpg',
    'laptop.jpg',
    'laptop_2.jpg',
    'book.jpg',
    'book_2.jpg',
    'tshirt.jpg',
    'headphones.jpg',
    'headphones_2.jpg',
    'headphones_3.jpg',
    'coffee.jpg',
    'monitor.jpg',
    'mouse.jpg',
    'backpack.jpg',
    'shoes.jpg'
];

// Створюємо просте тестове зображення для кожного файлу
foreach ($test_images as $image_name) {
    // Створюємо просте зображення з текстом
    $width = 300;
    $height = 200;
    
    $image = imagecreatetruecolor($width, $height);
    
    // Заповнюємо фон
    $bg_color = imagecolorallocate($image, rand(100, 200), rand(100, 200), rand(100, 200));
    imagefill($image, 0, 0, $bg_color);
    
    // Додаємо текст
    $text_color = imagecolorallocate($image, 255, 255, 255);
    $text = str_replace(['.jpg', '_'], ['', ' '], $image_name);
    $font = 5; // Вбудований шрифт
    $text_x = 50;
    $text_y = 100;
    
    imagestring($image, $font, $text_x, $text_y, $text, $text_color);
    
    // Зберігаємо зображення
    imagejpeg($image, "uploads/$image_name", 90);
    imagedestroy($image);
    
    echo "Створено: $image_name<br>";
}

echo "<h2>Готово! Тестові зображення створені.</h2>";
echo "<a href='index.php'>Повернутися на головну</a>";
?>