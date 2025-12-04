<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="header">
    <nav class="navbar">
        <div class="nav-brand">
            <a href="index.php" class="logo">iShop</a>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                <a href="index.php" class="nav-link">Головна</a>
            </li>
            <li class="nav-item <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                <a href="products.php" class="nav-link">Товари</a>
            </li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li class="nav-item <?php echo $current_page == 'add-product.php' ? 'active' : ''; ?>">
                    <a href="add-product.php" class="nav-link">Додати товар</a>
                </li>
                <li class="nav-item <?php echo $current_page == 'my-products.php' ? 'active' : ''; ?>">
                    <a href="my-products.php" class="nav-link">Мої товари</a>
                </li>
                <li class="nav-item <?php echo $current_page == 'view-users.php' ? 'active' : ''; ?>">
                    <a href="view-users.php" class="nav-link">Користувачі</a>
                </li>
                <li class="nav-item">
                    <span class="nav-link user-info">
                        <i class="fas fa-user"></i> <?php echo escape($_SESSION['username']); ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link logout">Вийти</a>
                </li>
            <?php else: ?>
                <li class="nav-item <?php echo $current_page == 'login.php' ? 'active' : ''; ?>">
                    <a href="login.php" class="nav-link">Увійти</a>
                </li>
                <li class="nav-item <?php echo $current_page == 'register.php' ? 'active' : ''; ?>">
                    <a href="register.php" class="nav-link">Реєстрація</a>
                </li>
            <?php endif; ?>
        </ul>
        
        <div class="hamburger">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
    </nav>
</header>