<?php
require_once 'config/database.php';

// Перевірка авторизації
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Отримуємо всіх користувачів
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Отримуємо статистику
$stats_sql = "SELECT 
    COUNT(*) as total_users,
    (SELECT COUNT(*) FROM products) as total_products,
    (SELECT COUNT(*) FROM products WHERE user_id = ?) as my_products
    FROM users";
$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute([$_SESSION['user_id']]);
$stats = $stats_stmt->fetch();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Користувачі - iShop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .users-table th, .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .users-table th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
        }
        
        .users-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .password-cell {
            font-family: monospace;
            color: #e74c3c;
            font-weight: bold;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card i {
            font-size: 3rem;
            margin-bottom: 10px;
            color: #3498db;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            margin: 10px 0;
            color: #2c3e50;
        }
        
        .user-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-left: 4px solid #f39c12;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="user-actions">
            <h1>Користувачі системи</h1>
            <div>
                <a href="index.php" class="btn btn-secondary">На головну</a>
                <a href="products.php" class="btn btn-primary">Всі товари</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="add-product.php" class="btn btn-primary">Додати товар</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="warning-box">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Увага!</strong> Ця сторінка для тестування!
        </div>
        
        <div class="stats-cards">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h3><?php echo $stats['total_users']; ?></h3>
                <p>Користувачів</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-box"></i>
                <h3><?php echo $stats['total_products']; ?></h3>
                <p>Товарів всього</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-boxes"></i>
                <h3><?php echo $stats['my_products']; ?></h3>
                <p>Моїх товарів</p>
            </div>
        </div>
        
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ім'я</th>
                    <th>Email</th>
                    <th>Пароль</th>
                    <th>Дата реєстрації</th>
                    <th>Дії</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                <tr>
                    <td><?php echo escape($user['id']); ?></td>
                    <td>
                        <strong><?php echo escape($user['username']); ?></strong>
                        <?php if($user['id'] == $_SESSION['user_id']): ?>
                            <span style="color: #27ae60;">(Ви)</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo escape($user['email']); ?></td>
                    <td class="password-cell"><?php echo escape($user['password']); ?></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                    <td>
                        <?php if($user['id'] == $_SESSION['user_id']): ?>
                            <a href="my-products.php" class="btn btn-small btn-secondary">Мої товари</a>
                        <?php else: ?>
                            <a href="products.php?user=<?php echo $user['id']; ?>" class="btn btn-small">Товари</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <h3>Інформація для тестування:</h3>
            <p>Ви увійшли як: <strong><?php echo escape($_SESSION['username']); ?></strong></p>
            <p>Ваш пароль: <strong style="color: #e74c3c;"><?php echo isset($_SESSION['password_debug']) ? escape($_SESSION['password_debug']) : 'Невідомо'; ?></strong></p>
            <p>Ваш ID: <?php echo escape($_SESSION['user_id']); ?></p>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>