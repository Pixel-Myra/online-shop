<?php
require_once 'config/database.php';

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if(empty($username) || empty($password)) {
        $error = 'Будь ласка, заповніть всі поля';
    } else {
        // Шукаємо користувача
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Порівнюємо паролі в чистому вигляді
        if($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            
            // Додаємо інформацію про пароль у сесію (для перевірки)
            $_SESSION['password_debug'] = $user['password'];
            
            header('Location: index.php');
            exit();
        } else {
            $error = 'Невірне ім\'я користувача або пароль';
            // Додаємо інформацію для дебагу
            if($user) {
                $error .= ' (Очікуваний пароль: ' . $user['password'] . ')';
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
    <title>Увійти - iShop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content auth-page">
        <div class="auth-container">
            <h1 class="auth-title">Увійти</h1>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo escape($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Ім'я користувача або Email *</label>
                    <input type="text" id="username" name="username" required
                           value="<?php echo isset($_POST['username']) ? escape($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Пароль *</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Увійти</button>
            </form>
            
            <div class="auth-links">
                <p>Не маєте акаунту? <a href="register.php">Зареєструватися</a></p>
            </div>
            
            <!-- <div class="test-accounts" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                <h3 style="margin-bottom: 10px;">Тестові акаунти:</h3> -->
                <!-- <p><strong>admin / admin123</strong> - Адміністратор</p>
                <p><strong>john / john123</strong> - Звичайний користувач</p>
                <p><strong>mary / mary123</strong> - Продавець</p> -->
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>