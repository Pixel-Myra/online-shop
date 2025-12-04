<?php
// Включаємо відображення помилок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Перевіряємо, чи існує папка uploads
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    echo "Папка uploads не існує. Спробуємо створити...<br>";
    if (mkdir($upload_dir, 0777, true)) {
        echo "Папка створена.<br>";
    } else {
        echo "Не вдалося створити папку.<br>";
    }
} else {
    echo "Папка uploads існує.<br>";
    // Перевіряємо права
    echo "Права на папку: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "<br>";
}

// Перевіряємо, чи доступна папка для запису
if (is_writable($upload_dir)) {
    echo "Папка доступна для запису.<br>";
} else {
    echo "Папка НЕ доступна для запису.<br>";
}

// Перевіряємо, чи є файли в папці
$files = scandir($upload_dir);
echo "Файли в папці uploads: " . count($files) . "<br>";
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "$file<br>";
    }
}

// Форма для завантаження тестового файлу
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['test_file'])) {
    $tmp_name = $_FILES['test_file']['tmp_name'];
    $name = basename($_FILES['test_file']['name']);
    $target = $upload_dir . $name;
    if (move_uploaded_file($tmp_name, $target)) {
        echo "Файл успішно завантажено.<br>";
        echo "<img src='$target' style='max-width: 300px;'><br>";
    } else {
        echo "Помилка завантаження файлу.<br>";
    }
}
?>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="test_file">
    <input type="submit" value="Завантажити">
</form>