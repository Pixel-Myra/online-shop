<?php
// Створюємо папку uploads, якщо її немає
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Створюємо файл index.php для захисту папки
$index_content = '<?php header("HTTP/1.0 403 Forbidden"); echo "<h1>Доступ заборонено</h1>"; exit(); ?>';
file_put_contents('uploads/index.php', $index_content);

// Список тестових зображень
$test_images = [
    'iphone_front.webp',
    'iphone_back.webp',
    'iphone_side.webp',
    'laptop_open.webp',
    'laptop_closed.webp',
    'book_cover.webp',
    'book_content.webp',
    'tshirt_front.webp',
    'tshirt_back.webp',
    'headphones_box.webp',
    'headphones_wearing.webp',
    'coffee_pack.webp',
    'coffee_beans.webp'
];

echo "<h1>Створення тестових WebP зображень</h1>";

foreach ($test_images as $image_name) {
    $product_name = str_replace(['.webp', '_'], ['', ' '], $image_name);
    
    // Створюємо просте зображення
    $width = 800;
    $height = 600;
    
    $image = imagecreatetruecolor($width, $height);
    
    // Градієнтний фон
    $color1 = imagecolorallocate($image, rand(50, 150), rand(50, 150), rand(50, 150));
    $color2 = imagecolorallocate($image, rand(150, 255), rand(150, 255), rand(150, 255));
    
    for($y = 0; $y < $height; $y++) {
        $r = (($color2 >> 16) & 0xFF) - (($color1 >> 16) & 0xFF);
        $g = (($color2 >> 8) & 0xFF) - (($color1 >> 8) & 0xFF);
        $b = ($color2 & 0xFF) - ($color1 & 0xFF);
        
        $newcolor = imagecolorallocate(
            $image,
            (($color1 >> 16) & 0xFF) + $r * $y / $height,
            (($color1 >> 8) & 0xFF) + $g * $y / $height,
            ($color1 & 0xFF) + $b * $y / $height
        );
        
        imageline($image, 0, $y, $width, $y, $newcolor);
    }
    
    // Додаємо текст
    $text_color = imagecolorallocate($image, 255, 255, 255);
    $text = ucwords($product_name);
    $font_size = 24;
    
    // Використовуємо вбудований шрифт
    $font = 5; // Вбудований великий шрифт
    $text_x = ($width - imagefontwidth($font) * strlen($text)) / 2;
    $text_y = ($height - imagefontheight($font)) / 2;
    
    imagestring($image, $font, $text_x, $text_y, $text, $text_color);
    
    // Додаємо водяний знак
    $watermark = "iShop Demo";
    $watermark_x = 20;
    $watermark_y = $height - 30;
    imagestring($image, 3, $watermark_x, $watermark_y, $watermark, $text_color);
    
    // Зберігаємо як WebP
    if (function_exists('imagewebp')) {
        $success = imagewebp($image, "uploads/$image_name", 90);
        imagedestroy($image);
        
        if ($success) {
            echo "<p style='color: green;'>✅ Створено: $image_name</p>";
        } else {
            echo "<p style='color: red;'>❌ Помилка: $image_name</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Функція imagewebp не підтримується</p>";
        break;
    }
}

echo "<h2>Готово! Тестові зображення створені у форматі WebP.</h2>";
echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px;'>Повернутися на головну</a>";
?>