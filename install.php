<?php
// install.php - 安装向导
if (file_exists('install.lock')) {
    header('Location: index.php');
    exit;
}

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$error = '';
$success = '';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        // 检查环境
        $php_version = phpversion();
        $pdo_available = extension_loaded('pdo_mysql');
        $write_permission = is_writable('.');
        
        if (version_compare($php_version, '7.0.0', '<')) {
            $error = 'PHP版本需要7.0.0或更高，当前版本：' . $php_version;
        } elseif (!$pdo_available) {
            $error = '需要启用PDO MySQL扩展';
        } elseif (!$write_permission) {
            $error = '当前目录没有写入权限，无法创建安装锁定文件';
        } else {
            header('Location: install.php?step=2');
            exit;
        }
    } elseif ($step === 2) {
        // 数据库配置
        $db_host = clean_input($_POST['db_host']);
        $db_name = clean_input($_POST['db_name']);
        $db_user = clean_input($_POST['db_user']);
        $db_pass = $_POST['db_pass'];
        $admin_user = clean_input($_POST['admin_user']);
        $admin_pass = $_POST['admin_pass'];
        $admin_pass_confirm = $_POST['admin_pass_confirm'];
        $server_type = clean_input($_POST['server_type']);
        
        if (empty($db_host) || empty($db_name) || empty($db_user) || empty($admin_user) || empty($admin_pass)) {
            $error = '请填写所有必填字段';
        } elseif ($admin_pass !== $admin_pass_confirm) {
            $error = '管理员密码不匹配';
        } else {
            // 测试数据库连接
            try {
                $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // 创建数据库配置
                $db_config_content = "<?php\n// db_config.php - 数据库配置（由安装程序生成）\ndefine('DB_HOST', '{$db_host}');\ndefine('DB_USER', '{$db_user}');\ndefine('DB_PASS', '{$db_pass}');\ndefine('DB_NAME', '{$db_name}');\n?>";
                
                if (file_put_contents('db_config.php', $db_config_content) === false) {
                    $error = '无法创建数据库配置文件，请检查目录权限';
                } else {
                    // 创建数据库表
                    $queries = [
                        "CREATE TABLE IF NOT EXISTS site_config (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            site_name VARCHAR(255) DEFAULT '我的世界网易版联机大厅',
                            join_link VARCHAR(500) DEFAULT '#',
                            join_text VARCHAR(100) DEFAULT '加入我们',
                            sponsor_link VARCHAR(500) DEFAULT '#',
                            sponsor_text VARCHAR(100) DEFAULT '赞助我们',
                            server_type ENUM('netease', 'international') DEFAULT 'netease',
                            server_ip VARCHAR(255) DEFAULT '',
                            server_port VARCHAR(10) DEFAULT '25565',
                            footer_icp VARCHAR(100) DEFAULT '',
                            footer_public_security VARCHAR(100) DEFAULT '',
                            logo_image VARCHAR(500) DEFAULT '',
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                        )",
                        
                        "CREATE TABLE IF NOT EXISTS announcements (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            title VARCHAR(255) NOT NULL,
                            content TEXT NOT NULL,
                            is_active TINYINT(1) DEFAULT 1,
                            show_on_load TINYINT(1) DEFAULT 1,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                        )",
                        
                        "CREATE TABLE IF NOT EXISTS gallery_images (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            image_url VARCHAR(500) NOT NULL,
                            caption VARCHAR(255),
                            display_order INT DEFAULT 0,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )",
                        
                        "CREATE TABLE IF NOT EXISTS server_info (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            title VARCHAR(255) NOT NULL,
                            description TEXT,
                            icon VARCHAR(100) DEFAULT 'fa-globe',
                            display_order INT DEFAULT 0,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )",
                        
                        "CREATE TABLE IF NOT EXISTS admin_users (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            username VARCHAR(100) NOT NULL UNIQUE,
                            password VARCHAR(255) NOT NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )"
                    ];
                    
                    foreach ($queries as $query) {
                        $pdo->exec($query);
                    }
                    
                    // 初始化默认配置
                    $pdo->exec("INSERT INTO site_config (site_name, join_link, join_text, sponsor_link, sponsor_text, server_type) VALUES ('我的世界网易版联机大厅', '#', '加入我们', '#', '赞助我们', '$server_type')");
                    
                    // 初始化默认公告
                    $pdo->exec("INSERT INTO announcements (title, content, is_active, show_on_load) VALUES ('欢迎来到我们的服务器！', '欢迎各位玩家加入我们的我的世界服务器！请遵守服务器规则，享受游戏乐趣。', 1, 1)");
                    
                    // 初始化服务器信息
                    $server_info_data = [
                        ['稳定流畅', '高性能服务器保障，提供稳定流畅的游戏体验，拒绝卡顿和延迟。', 'fa-globe'],
                        ['友好社区', '活跃友好的玩家社区，定期举办活动，营造良好的游戏氛围。', 'fa-users'],
                        ['安全保障', '完善的防作弊系统和数据备份，保护玩家的游戏成果和数据安全。', 'fa-shield'],
                        ['持续更新', '定期更新游戏内容和功能，保持服务器的新鲜感和可玩性。', 'fa-heart']
                    ];
                    
                    $stmt = $pdo->prepare("INSERT INTO server_info (title, description, icon, display_order) VALUES (?, ?, ?, ?)");
                    $order = 0;
                    foreach ($server_info_data as $info) {
                        $stmt->execute([$info[0], $info[1], $info[2], $order++]);
                    }
                    
                    // 创建管理员账户
                    $hashed_password = password_hash($admin_pass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
                    $stmt->execute([$admin_user, $hashed_password]);
                    
                    // 创建安装锁定文件
                    if (file_put_contents('install.lock', '安装完成于: ' . date('Y-m-d H:i:s')) === false) {
                        $error = '无法创建安装锁定文件，请检查目录权限';
                    } else {
                        header('Location: install.php?step=3');
                        exit;
                    }
                }
            } catch(PDOException $e) {
                $error = '数据库连接失败: ' . $e->getMessage();
            }
        }
    }
}

function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安装向导 - 我的世界服务器</title>
    <style>
        :root {
            --primary: #4F46E5;
            --secondary: #10B981;
            --accent: #F59E0B;
            --dark: #0f172a;
            --light: #1e293b;
            --text-light: #f1f5f9;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background-color: var(--dark);
            color: var(--text-light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 1rem;
        }
        
        .install-container {
            background: var(--light);
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
        }
        
        .install-title {
            text-align: center;
            margin-bottom: 1.5rem;
            color: var(--secondary);
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .step-indicator:before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.1);
            z-index: 1;
        }
        
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
        }
        
        .step.active {
            background: var(--secondary);
        }
        
        .step.completed {
            background: var(--primary);
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
        }
        
        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(15, 23, 42, 0.5);
            border-radius: 0.25rem;
            color: var(--text-light);
        }
        
        .btn {
            display: block;
            width: 100%;
            padding: 0.75rem;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s;
            margin-top: 1rem;
        }
        
        .btn:hover {
            background: rgba(16, 185, 129, 0.9);
        }
        
        .error {
            color: #EF4444;
            text-align: center;
            margin-top: 1rem;
            padding: 0.75rem;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 0.25rem;
        }
        
        .success {
            color: var(--secondary);
            text-align: center;
            margin-top: 1rem;
            padding: 0.75rem;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 0.25rem;
        }
        
        .env-check {
            margin-bottom: 1rem;
            padding: 0.75rem;
            border-radius: 0.25rem;
            background: rgba(15, 23, 42, 0.5);
        }
        
        .env-check.pass {
            border-left: 4px solid var(--secondary);
        }
        
        .env-check.fail {
            border-left: 4px solid #EF4444;
        }
        
        .server-type-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .server-type-option {
            padding: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        
        .server-type-option:hover {
            border-color: var(--secondary);
        }
        
        .server-type-option.selected {
            border-color: var(--secondary);
            background: rgba(16, 185, 129, 0.1);
        }
        
        .server-type-option input {
            display: none;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <h1 class="install-title">我的世界服务器 - 安装向导</h1>
        
        <div class="step-indicator">
            <div class="step <?php echo $step >= 1 ? 'active' : ''; ?>">1</div>
            <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">2</div>
            <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">3</div>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($step === 1): ?>
            <h2>环境检查</h2>
            <p>在继续安装之前，系统需要检查您的服务器环境是否符合要求。</p>
            
            <div class="env-check <?php echo version_compare(phpversion(), '7.0.0', '>=') ? 'pass' : 'fail'; ?>">
                <strong>PHP版本:</strong> <?php echo phpversion(); ?> 
                <?php echo version_compare(phpversion(), '7.0.0', '>=') ? '✓ 符合要求' : '✗ 需要7.0.0或更高版本'; ?>
            </div>
            
            <div class="env-check <?php echo extension_loaded('pdo_mysql') ? 'pass' : 'fail'; ?>">
                <strong>PDO MySQL扩展:</strong> 
                <?php echo extension_loaded('pdo_mysql') ? '✓ 已启用' : '✗ 未启用'; ?>
            </div>
            
            <div class="env-check <?php echo is_writable('.') ? 'pass' : 'fail'; ?>">
                <strong>目录写入权限:</strong> 
                <?php echo is_writable('.') ? '✓ 有写入权限' : '✗ 无写入权限'; ?>
            </div>
            
            <form method="POST">
                <button type="submit" class="btn">继续</button>
            </form>
            
        <?php elseif ($step === 2): ?>
            <h2>数据库配置</h2>
            <p>请输入您的MySQL数据库信息。</p>
            
            <form method="POST">
                <div class="form-group">
                    <label for="db_host">数据库主机</label>
                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label for="db_name">数据库名称</label>
                    <input type="text" id="db_name" name="db_name" required>
                </div>
                
                <div class="form-group">
                    <label for="db_user">数据库用户名</label>
                    <input type="text" id="db_user" name="db_user" required>
                </div>
                
                <div class="form-group">
                    <label for="db_pass">数据库密码</label>
                    <input type="password" id="db_pass" name="db_pass">
                </div>
                
                <h3>服务器类型</h3>
                <p>请选择您的服务器类型：</p>
                
                <div class="server-type-options">
                    <label class="server-type-option selected">
                        <input type="radio" name="server_type" value="netease" checked>
                        <strong>网易版</strong>
                        <p>适用于网易我的世界联机大厅</p>
                    </label>
                    
                    <label class="server-type-option">
                        <input type="radio" name="server_type" value="international">
                        <strong>国际版</strong>
                        <p>适用于国际版我的世界服务器</p>
                    </label>
                </div>
                
                <h3>管理员账户</h3>
                
                <div class="form-group">
                    <label for="admin_user">管理员用户名</label>
                    <input type="text" id="admin_user" name="admin_user" value="admin" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_pass">管理员密码</label>
                    <input type="password" id="admin_pass" name="admin_pass" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_pass_confirm">确认密码</label>
                    <input type="password" id="admin_pass_confirm" name="admin_pass_confirm" required>
                </div>
                
                <button type="submit" class="btn">安装</button>
            </form>
            
        <?php elseif ($step === 3): ?>
            <h2>安装完成</h2>
            <p>恭喜！我的世界服务器网站已成功安装。</p>
            
            <div class="success">
                您现在可以访问网站前台和管理后台。
            </div>
            
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <a href="index.php" class="btn" style="text-decoration: none; text-align: center;">访问网站</a>
                <a href="admin_login.php" class="btn" style="text-decoration: none; text-align: center; background: var(--primary);">管理后台</a>
            </div>
            
            <div style="margin-top: 2rem; padding: 1rem; background: rgba(15, 23, 42, 0.5); border-radius: 0.25rem;">
                <strong>安全提示:</strong> 为了安全起见，建议您删除 install.php 文件。
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // 服务器类型选择
        document.addEventListener('DOMContentLoaded', function() {
            const serverTypeOptions = document.querySelectorAll('.server-type-option');
            
            serverTypeOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // 移除所有选中状态
                    serverTypeOptions.forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    
                    // 添加选中状态到当前选项
                    this.classList.add('selected');
                    
                    // 选中对应的radio按钮
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                });
            });
        });
    </script>
</body>
</html>