<?php
// admin_login.php - 管理员登录页面
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);
    
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $user['username'];
        header('Location: admin.php');
        exit;
    } else {
        $error = '用户名或密码错误！';
    }
}

if (is_logged_in()) {
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - <?php echo $site_config['site_name']; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Minecraft&display=swap" rel="stylesheet">
    <style>
        /* 内联样式确保加载页面立即显示 */
        body:not(.loaded) #loading-screen {
            display: flex !important;
        }
        body:not(.loaded) #main-container {
            display: none !important;
        }
    </style>
</head>
<body>
    <!-- 加载动画 -->
    <div id="loading-screen">
        <div class="loading-content">
            <div class="minecraft-logo">管理员登录</div>
            <div class="loading-bar">
                <div class="loading-progress"></div>
            </div>
            <p class="loading-text">正在加载登录页面...</p>
        </div>
    </div>

    <!-- 主容器 -->
    <div id="main-container">
        <div class="login-container">
            <div class="login-header">
                <h1 class="minecraft-font"><?php echo $site_config['site_name']; ?></h1>
                <p class="login-subtitle">管理员登录</p>
            </div>
            
            <?php if ($error): ?>
                <div class="toast error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login">登录</button>
            </form>
            
            <div class="login-footer">
                <a href="index.php" class="btn btn-secondary">返回首页</a>
            </div>
        </div>
    </div>

    <script>
        // 页面加载完成后显示内容
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.body.classList.add('loaded');
            }, 1000);
        });
    </script>
</body>
</html>