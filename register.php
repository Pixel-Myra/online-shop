<?php
require_once 'config/database.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Валідація
    if(empty($username) || empty($email) || empty($password)) {
        $error = 'Будь ласка, заповніть всі поля';
    } elseif($password !== $confirm_password) {
        $error = 'Паролі не співпадають';
    } elseif(strlen($password) < 3) {
        $error = 'Пароль повинен містити принаймні 3 символи';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Невірний формат email';
    } else {
        // Перевірка чи існує користувач
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email]);
        
        if($stmt->rowCount() > 0) {
            $error = 'Користувач з таким іменем або email вже існує';
        } else {
            // Зберігаємо пароль у чистому вигляді
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if($stmt->execute([$username, $email, $password])) {
                $success = 'Реєстрація успішна! Тепер ви можете увійти.';
                header('refresh:2;url=login.php');
            } else {
                $error = 'Сталася помилка. Спробуйте ще раз.';
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
    <title>Реєстрація - iShop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content auth-page">
        <div class="auth-container">
            <h1 class="auth-title">Реєстрація</h1>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo escape($error); ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo escape($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Ім'я користувача *</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo isset($_POST['username']) ? escape($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email *</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo isset($_POST['email']) ? escape($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Пароль *</label>
                    <input type="password" id="password" name="password" required>
                    <!-- <small>Пароль зберігається у чистому вигляді. Використовуйте унікальний пароль для тестування.</small> -->
                </div>
                
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Підтвердіть пароль *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Зареєструватися</button>
            </form>
            
            <div class="auth-links">
                <p>Вже маєте акаунт? <a href="login.php">Увійти</a></p>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>