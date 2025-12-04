<?php
// Включити відображення помилок
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Налаштування підключення до БД
$host = 'localhost';
$dbname = 'online_shop';
$username = 'root';
$password = '';  // Порожній пароль для XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8'");
    $pdo->exec("SET CHARACTER SET utf8");
} catch(PDOException $e) {
    die("Помилка підключення до бази даних: " . $e->getMessage());
}

// Функція для захисту від XSS
function escape($data) {
    if(is_array($data)) {
        return array_map('escape', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function get_product_image($product_name, $image_name = '') {
    $upload_path = 'uploads/' . $image_name;
    
    if(!empty($image_name) && file_exists($upload_path)) {
        return $upload_path;
    } else {
        // Створюємо URL для placeholder з назвою товару
        $text = urlencode(substr($product_name, 0, 20));
        return "https://via.placeholder.com/300x200/3498db/ffffff?text=$text";
    }
}

// Функція для перевірки авторизації
function require_login() {
    if(!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Функція для отримання поточного користувача
function current_user() {
    if(isset($_SESSION['user_id'])) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email']
        ];
    }
    return null;
}
?>