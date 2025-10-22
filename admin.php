<?php
// admin.php - 后台管理页面
require_once 'config.php';

// 先处理退出登录逻辑 - 移到文件顶部解决headers already sent问题
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

if (!is_logged_in()) {
    header('Location: admin_login.php');
    exit;
}

$message = '';

// 更新网站配置
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_config'])) {
    $site_name = clean_input($_POST['site_name']);
    $join_link = clean_input($_POST['join_link']);
    $join_text = clean_input($_POST['join_text']);
    $sponsor_link = clean_input($_POST['sponsor_link']);
    $sponsor_text = clean_input($_POST['sponsor_text']);
    $server_type = clean_input($_POST['server_type']);
    $server_ip = clean_input($_POST['server_ip']);
    $server_port = clean_input($_POST['server_port']);
    $footer_icp = clean_input($_POST['footer_icp']);
    $footer_public_security = clean_input($_POST['footer_public_security']);
    $logo_image = clean_input($_POST['logo_image']);
    
    $stmt = $pdo->prepare("UPDATE site_config SET site_name = ?, join_link = ?, join_text = ?, sponsor_link = ?, sponsor_text = ?, server_type = ?, server_ip = ?, server_port = ?, footer_icp = ?, footer_public_security = ?, logo_image = ? WHERE id = 1");
    $stmt->execute([$site_name, $join_link, $join_text, $sponsor_link, $sponsor_text, $server_type, $server_ip, $server_port, $footer_icp, $footer_public_security, $logo_image]);
    
    $message = '配置已更新！';
    $site_config = get_site_config($pdo);
    $site_config['sponsor_link'] = process_external_link($site_config['sponsor_link']);
    $site_config['join_link'] = process_external_link($site_config['join_link']);
}

// 添加公告
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_announcement'])) {
    $title = clean_input($_POST['title']);
    $content = clean_input($_POST['content']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $show_on_load = isset($_POST['show_on_load']) ? 1 : 0;
    
    if (!empty($title) && !empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, is_active, show_on_load) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $content, $is_active, $show_on_load]);
        $message = '公告已添加！';
        $announcements = get_announcements($pdo);
        $popup_announcements = get_popup_announcements($pdo);
    } else {
        $message = '请填写标题和内容！';
    }
}

// 更新公告状态
if (isset($_GET['toggle_announcement'])) {
    $announcement_id = intval($_GET['toggle_announcement']);
    
    // 获取当前状态
    $stmt = $pdo->prepare("SELECT is_active FROM announcements WHERE id = ?");
    $stmt->execute([$announcement_id]);
    $announcement = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($announcement) {
        $new_status = $announcement['is_active'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE announcements SET is_active = ? WHERE id = ?");
        $stmt->execute([$new_status, $announcement_id]);
        
        $message = '公告状态已更新！';
        $announcements = get_announcements($pdo);
        $popup_announcements = get_popup_announcements($pdo);
    }
}

// 删除公告
if (isset($_GET['delete_announcement'])) {
    $announcement_id = intval($_GET['delete_announcement']);
    
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->execute([$announcement_id]);
    
    $message = '公告已删除！';
    $announcements = get_announcements($pdo);
    $popup_announcements = get_popup_announcements($pdo);
}

// 添加图库图片
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_gallery_image'])) {
    $image_url = clean_input($_POST['image_url']);
    $caption = clean_input($_POST['caption']);
    $display_order = intval($_POST['display_order']);
    
    if (!empty($image_url)) {
        $stmt = $pdo->prepare("INSERT INTO gallery_images (image_url, caption, display_order) VALUES (?, ?, ?)");
        $stmt->execute([$image_url, $caption, $display_order]);
        $message = '图片已添加！';
        $gallery_images = get_gallery_images($pdo);
    } else {
        $message = '请填写图片URL！';
    }
}

// 删除图库图片
if (isset($_GET['delete_image'])) {
    $image_id = intval($_GET['delete_image']);
    
    $stmt = $pdo->prepare("DELETE FROM gallery_images WHERE id = ?");
    $stmt->execute([$image_id]);
    
    $message = '图片已删除！';
    $gallery_images = get_gallery_images($pdo);
}

// 添加服务器信息
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_server_info'])) {
    $title = clean_input($_POST['title']);
    $description = clean_input($_POST['description']);
    $icon = clean_input($_POST['icon']);
    $display_order = intval($_POST['display_order']);
    
    if (!empty($title) && !empty($description)) {
        $stmt = $pdo->prepare("INSERT INTO server_info (title, description, icon, display_order) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $icon, $display_order]);
        $message = '服务器信息已添加！';
        $server_info = get_server_info($pdo);
    } else {
        $message = '请填写标题和描述！';
    }
}

// 删除服务器信息
if (isset($_GET['delete_server_info'])) {
    $info_id = intval($_GET['delete_server_info']);
    
    $stmt = $pdo->prepare("DELETE FROM server_info WHERE id = ?");
    $stmt->execute([$info_id]);
    
    $message = '服务器信息已删除！';
    $server_info = get_server_info($pdo);
}

// 更改管理员密码
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = clean_input($_POST['current_password']);
    $new_password = clean_input($_POST['new_password']);
    $confirm_password = clean_input($_POST['confirm_password']);
    
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$_SESSION['admin_username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user['id']]);
            $message = '密码已更改！';
        } else {
            $message = '新密码和确认密码不匹配！';
        }
    } else {
        $message = '当前密码不正确！';
    }
}

// 测试服务器状态
if (isset($_GET['test_server_status'])) {
    if (!empty($site_config['server_ip'])) {
        $test_status = get_server_status($site_config['server_ip'], $site_config['server_port']);
        if ($test_status) {
            $message = '服务器状态测试成功！';
        } else {
            $message = '无法获取服务器状态，请检查服务器IP和端口设置。';
        }
    } else {
        $message = '请先设置服务器IP地址。';
    }
}

// 创作者赞助链接（只在后台显示）
$creator_sponsor_link = "https://pay.xiangyuwl.cn/paypage/?merchant=9e712WglztxPf78HUWcDqfMbh9p97jg5GDitw6FmweOO";
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理 - <?php echo $site_config['site_name']; ?></title>
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
        }
        
        .admin-header {
            background: var(--light);
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-container {
            display: flex;
            min-height: calc(100vh - 60px);
        }
        
        .admin-sidebar {
            width: 250px;
            background: var(--light);
            padding: 1rem;
        }
        
        .admin-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }
        
        .nav-link {
            display: block;
            padding: 0.75rem 1rem;
            color: var(--text-light);
            text-decoration: none;
            border-radius: 0.25rem;
            margin-bottom: 0.5rem;
            transition: background 0.2s;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .section {
            background: var(--light);
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
        
        .section-title {
            margin-bottom: 1.5rem;
            color: var(--secondary);
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
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(15, 23, 42, 0.5);
            border-radius: 0.25rem;
            color: var(--text-light);
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .checkbox-group input {
            width: auto;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s;
        }
        
        .btn:hover {
            background: rgba(16, 185, 129, 0.9);
        }
        
        .btn-danger {
            background: #EF4444;
        }
        
        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.9);
        }
        
        .btn-warning {
            background: var(--accent);
        }
        
        .btn-warning:hover {
            background: rgba(245, 158, 11, 0.9);
        }
        
        .message {
            padding: 0.75rem;
            background: rgba(16, 185, 129, 0.2);
            border-radius: 0.25rem;
            margin-bottom: 1rem;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .gallery-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .gallery-caption {
            padding: 0.5rem;
            background: rgba(0, 0, 0, 0.7);
        }
        
        .delete-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(239, 68, 68, 0.8);
            color: white;
            border: none;
            border-radius: 0.25rem;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
        }
        
        .server-info-list {
            margin-top: 1rem;
        }
        
        .server-info-item {
            background: rgba(15, 23, 42, 0.5);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .server-info-content h4 {
            margin-bottom: 0.5rem;
        }
        
        .server-info-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .sponsor-button-preview {
            display: inline-block;
            background-color: var(--accent);
            color: white;
            font-weight: 500;
            padding: 0.25rem 1rem;
            border-radius: 0.25rem;
            text-decoration: none;
            clip-path: polygon(
                0% 4px, 4px 4px, 4px 0%, calc(100% - 4px) 0%, calc(100% - 4px) 4px, 
                100% 4px, 100% calc(100% - 4px), calc(100% - 4px) calc(100% - 4px), 
                calc(100% - 4px) 100%, 4px 100%, 4px calc(100% - 4px), 0% calc(100% - 4px)
            );
            transition: all 0.2s ease;
            margin-top: 0.5rem;
        }
        
        .sponsor-button-preview:hover {
            background-color: rgba(245, 158, 11, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .link-help {
            color: var(--text-gray);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .link-example {
            display: block;
            color: var(--secondary);
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }
        
        .announcements-list {
            margin-top: 1rem;
        }
        
        .announcement-item {
            background: rgba(15, 23, 42, 0.5);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .announcement-title {
            font-weight: bold;
            font-size: 1.125rem;
        }
        
        .announcement-meta {
            color: var(--text-gray);
            font-size: 0.875rem;
        }
        
        .announcement-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }
        
        .status-online {
            background-color: var(--secondary);
        }
        
        .status-offline {
            background-color: #EF4444;
        }
        
        .server-status-card {
            background: rgba(15, 23, 42, 0.5);
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }
        
        .server-status-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .status-item {
            display: flex;
            flex-direction: column;
        }
        
        .status-label {
            font-size: 0.875rem;
            color: var(--text-gray);
            margin-bottom: 0.25rem;
        }
        
        .status-value {
            font-weight: bold;
        }
        
        .netease-settings {
            background: rgba(15, 23, 42, 0.3);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
            border-left: 4px solid var(--accent);
        }
        
        /* 创作者赞助按钮样式 */
        .creator-sponsor-btn {
            display: block;
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s;
            text-align: center;
            text-decoration: none;
            margin-top: 1rem;
        }
        
        .creator-sponsor-btn:hover {
            background: rgba(245, 158, 11, 0.9);
        }
        
        .creator-sponsor-header {
            display: inline-block;
            background: var(--accent);
            color: white;
            font-weight: 500;
            padding: 0.25rem 1rem;
            border-radius: 0.25rem;
            text-decoration: none;
            margin-left: 1rem;
        }
        
        .creator-sponsor-header:hover {
            background: rgba(245, 158, 11, 0.9);
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1><?php echo $site_config['site_name']; ?> - 后台管理</h1>
        <div>
            <span>欢迎, <?php echo $_SESSION['admin_username']; ?></span>
            <a href="<?php echo $creator_sponsor_link; ?>" target="_blank" class="creator-sponsor-header">
                赞助创作者
            </a>
            <a href="?logout=1" style="color: var(--text-light); margin-left: 1rem;">退出</a>
        </div>
    </div>
    
    <div class="admin-container">
        <div class="admin-sidebar">
            <a href="#site-config" class="nav-link active" onclick="switchTab('site-config')">网站配置</a>
            <a href="#announcements" class="nav-link" onclick="switchTab('announcements')">公告管理</a>
            <a href="#gallery" class="nav-link" onclick="switchTab('gallery')">图片展示</a>
            <a href="#server-info" class="nav-link" onclick="switchTab('server-info')">服务器信息</a>
            <a href="#change-password" class="nav-link" onclick="switchTab('change-password')">修改密码</a>
            
            <!-- 创作者赞助按钮 -->
            <a href="<?php echo $creator_sponsor_link; ?>" target="_blank" class="creator-sponsor-btn">
                赞助创作者
            </a>
        </div>
        
        <div class="admin-content">
            <?php if ($message): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div id="site-config" class="tab-content active">
                <div class="section">
                    <h2 class="section-title">网站配置</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label for="site_name">网站名称</label>
                            <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($site_config['site_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="server_type">服务器类型</label>
                            <select id="server_type" name="server_type" required>
                                <option value="netease" <?php echo $site_config['server_type'] === 'netease' ? 'selected' : ''; ?>>网易版</option>
                                <option value="international" <?php echo $site_config['server_type'] === 'international' ? 'selected' : ''; ?>>国际版</option>
                            </select>
                        </div>
                        
                        <!-- 网易版设置 -->
                        <div id="netease-settings" style="<?php echo $site_config['server_type'] === 'netease' ? '' : 'display: none;'; ?>" class="netease-settings">
                            <div class="form-group">
                                <label for="server_ip">房间号</label>
                                <input type="text" id="server_ip" name="server_ip" value="<?php echo htmlspecialchars($site_config['server_ip']); ?>" placeholder="例如: 12345678">
                                <div class="link-help">请输入网易版我的世界联机大厅的房间号</div>
                            </div>
                        </div>
                        
                        <!-- 国际版设置 -->
                        <div id="international-settings" style="<?php echo $site_config['server_type'] === 'international' ? '' : 'display: none;'; ?>">
                            <div class="form-group">
                                <label for="server_ip">服务器IP地址</label>
                                <input type="text" id="server_ip" name="server_ip" value="<?php echo htmlspecialchars($site_config['server_ip']); ?>" placeholder="例如: play.example.com">
                            </div>
                            
                            <div class="form-group">
                                <label for="server_port">服务器端口</label>
                                <input type="text" id="server_port" name="server_port" value="<?php echo htmlspecialchars($site_config['server_port']); ?>" placeholder="默认: 25565">
                            </div>
                            
                            <?php if (!empty($site_config['server_ip'])): ?>
                                <div class="server-status-card">
                                    <h3>服务器状态</h3>
                                    <?php if ($server_status): ?>
                                        <div class="status-item">
                                            <span class="status-label">状态:</span>
                                            <span class="status-value">
                                                <span class="status-indicator status-online"></span>
                                                在线
                                            </span>
                                        </div>
                                        
                                        <div class="server-status-info">
                                            <div class="status-item">
                                                <span class="status-label">版本:</span>
                                                <span class="status-value"><?php echo htmlspecialchars($server_status['version'] ?? '未知'); ?></span>
                                            </div>
                                            
                                            <div class="status-item">
                                                <span class="status-label">在线玩家:</span>
                                                <span class="status-value"><?php echo htmlspecialchars($server_status['players']['online'] ?? '0'); ?> / <?php echo htmlspecialchars($server_status['players']['max'] ?? '0'); ?></span>
                                            </div>
                                            
                                            <div class="status-item">
                                                <span class="status-label">MOTD:</span>
                                                <span class="status-value"><?php echo htmlspecialchars($server_status['pureMotd'] ?? '无'); ?></span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="status-item">
                                            <span class="status-label">状态:</span>
                                            <span class="status-value">
                                                <span class="status-indicator status-offline"></span>
                                                离线或无法连接
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div style="margin-top: 1rem;">
                                        <a href="?test_server_status=1" class="btn btn-warning">测试服务器状态</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="join_link">加入链接</label>
                            <input type="text" id="join_link" name="join_link" value="<?php echo htmlspecialchars($site_config['join_link']); ?>" required>
                            <div class="link-help">请输入完整的外部链接地址</div>
                            <span class="link-example">例如: https://discord.gg/your-server 或 www.patreon.com/your-page</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="join_text">加入按钮文本</label>
                            <input type="text" id="join_text" name="join_text" value="<?php echo htmlspecialchars($site_config['join_text']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="sponsor_link">赞助链接</label>
                            <input type="text" id="sponsor_link" name="sponsor_link" value="<?php echo htmlspecialchars($site_config['sponsor_link']); ?>" required>
                            <div class="link-help">请输入完整的外部链接地址</div>
                            <span class="link-example">例如: https://www.paypal.com/your-link 或 ko-fi.com/your-page</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="sponsor_text">赞助按钮文本</label>
                            <input type="text" id="sponsor_text" name="sponsor_text" value="<?php echo htmlspecialchars($site_config['sponsor_text']); ?>" required>
                            <div>
                                <a href="<?php echo htmlspecialchars($site_config['sponsor_link']); ?>" class="sponsor-button-preview" target="_blank">
                                    <?php echo htmlspecialchars($site_config['sponsor_text']); ?>
                                </a>
                                <small>按钮预览 - 点击测试链接</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="logo_image">Logo图片URL</label>
                            <input type="text" id="logo_image" name="logo_image" value="<?php echo htmlspecialchars($site_config['logo_image']); ?>" placeholder="输入图片URL或上传图片">
                            <?php if ($site_config['logo_image']): ?>
                                <div style="margin-top: 0.5rem;">
                                    <img src="<?php echo $site_config['logo_image']; ?>" alt="Logo" style="max-width: 200px; max-height: 100px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="footer_icp">ICP备案号</label>
                            <input type="text" id="footer_icp" name="footer_icp" value="<?php echo htmlspecialchars($site_config['footer_icp']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="footer_public_security">公网安备</label>
                            <input type="text" id="footer_public_security" name="footer_public_security" value="<?php echo htmlspecialchars($site_config['footer_public_security']); ?>">
                        </div>
                        
                        <button type="submit" name="update_config" class="btn">保存配置</button>
                    </form>
                </div>
            </div>
            
            <div id="announcements" class="tab-content">
                <div class="section">
                    <h2 class="section-title">公告管理</h2>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="title">公告标题</label>
                            <input type="text" id="title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">公告内容</label>
                            <textarea id="content" name="content" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label for="is_active">启用公告</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="show_on_load" name="show_on_load" value="1" checked>
                                <label for="show_on_load">页面加载时弹出显示</label>
                            </div>
                        </div>
                        
                        <button type="submit" name="add_announcement" class="btn">添加公告</button>
                    </form>
                    
                    <div class="announcements-list">
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="announcement-item">
                                <div class="announcement-header">
                                    <div class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                                    <div class="announcement-meta">
                                        <?php echo date('Y-m-d H:i', strtotime($announcement['created_at'])); ?>
                                        <?php if ($announcement['show_on_load']): ?>
                                            <span style="color: var(--accent);">[弹出]</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="announcement-content">
                                    <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                                </div>
                                <div class="announcement-actions">
                                    <a href="?toggle_announcement=<?php echo $announcement['id']; ?>" class="btn btn-warning">
                                        <?php echo $announcement['is_active'] ? '禁用' : '启用'; ?>
                                    </a>
                                    <a href="?delete_announcement=<?php echo $announcement['id']; ?>" class="btn btn-danger" onclick="return confirm('确定删除这个公告吗？')">删除</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div id="gallery" class="tab-content">
                <div class="section">
                    <h2 class="section-title">图片展示管理</h2>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="image_url">图片URL</label>
                            <input type="text" id="image_url" name="image_url" placeholder="输入图片URL" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="caption">图片描述</label>
                            <input type="text" id="caption" name="caption">
                        </div>
                        
                        <div class="form-group">
                            <label for="display_order">显示顺序</label>
                            <input type="number" id="display_order" name="display_order" value="0">
                        </div>
                        
                        <button type="submit" name="add_gallery_image" class="btn">添加图片</button>
                    </form>
                    
                    <div class="gallery-grid">
                        <?php foreach ($gallery_images as $image): ?>
                            <div class="gallery-item">
                                <img src="<?php echo $image['image_url']; ?>" alt="<?php echo htmlspecialchars($image['caption']); ?>">
                                <div class="gallery-caption"><?php echo htmlspecialchars($image['caption']); ?></div>
                                <a href="?delete_image=<?php echo $image['id']; ?>" class="delete-btn" onclick="return confirm('确定删除这张图片吗？')">×</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div id="server-info" class="tab-content">
                <div class="section">
                    <h2 class="section-title">服务器信息管理</h2>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="title">标题</label>
                            <input type="text" id="title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">描述</label>
                            <textarea id="description" name="description" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="icon">图标类名</label>
                            <input type="text" id="icon" name="icon" value="fa-globe" placeholder="例如: fa-globe, fa-users, fa-shield">
                            <small>可用图标: fa-globe, fa-users, fa-shield, fa-heart, fa-star, fa-gamepad</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="display_order">显示顺序</label>
                            <input type="number" id="display_order" name="display_order" value="0">
                        </div>
                        
                        <button type="submit" name="add_server_info" class="btn">添加信息</button>
                    </form>
                    
                    <div class="server-info-list">
                        <?php foreach ($server_info as $info): ?>
                            <div class="server-info-item">
                                <div class="server-info-content">
                                    <h4><?php echo htmlspecialchars($info['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($info['description']); ?></p>
                                    <small>图标: <?php echo $info['icon']; ?> | 顺序: <?php echo $info['display_order']; ?></small>
                                </div>
                                <div class="server-info-actions">
                                    <a href="?delete_server_info=<?php echo $info['id']; ?>" class="btn btn-danger" onclick="return confirm('确定删除这条信息吗？')">删除</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div id="change-password" class="tab-content">
                <div class="section">
                    <h2 class="section-title">修改密码</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label for="current_password">当前密码</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">新密码</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">确认新密码</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn">修改密码</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // 选项卡切换函数
        function switchTab(tabName) {
            // 隐藏所有选项卡内容
            var tabContents = document.getElementsByClassName('tab-content');
            for (var i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }
            
            // 移除所有导航链接的active类
            var navLinks = document.getElementsByClassName('nav-link');
            for (var i = 0; i < navLinks.length; i++) {
                navLinks[i].classList.remove('active');
            }
            
            // 显示选中的选项卡内容
            document.getElementById(tabName).classList.add('active');
            
            // 为当前选中的导航链接添加active类
            event.currentTarget.classList.add('active');
        }
        
        // 页面加载完成后执行
        document.addEventListener('DOMContentLoaded', function() {
            // 实时更新赞助按钮预览
            const sponsorLinkInput = document.getElementById('sponsor_link');
            const sponsorTextInput = document.getElementById('sponsor_text');
            const sponsorButtonPreview = document.querySelector('.sponsor-button-preview');
            
            function processLinkForPreview(link) {
                if (!link || link === '#') {
                    return '#';
                }
                
                // 如果已经是完整URL，直接返回
                if (link.startsWith('http://') || link.startsWith('https://')) {
                    return link;
                }
                
                // 如果是外部链接但没有协议，添加https://
                if (link.startsWith('www.') || link.startsWith('//') || 
                    (link.includes('.') && !link.includes('/') && !link.includes(' '))) {
                    return 'https://' + link.replace(/^\/+/, '');
                }
                
                // 其他情况，直接返回
                return link;
            }
            
            function updateSponsorButtonPreview() {
                const processedLink = processLinkForPreview(sponsorLinkInput.value);
                sponsorButtonPreview.href = processedLink;
                sponsorButtonPreview.textContent = sponsorTextInput.value;
            }
            
            if (sponsorLinkInput && sponsorTextInput && sponsorButtonPreview) {
                sponsorLinkInput.addEventListener('input', updateSponsorButtonPreview);
                sponsorTextInput.addEventListener('input', updateSponsorButtonPreview);
            }
            
            // 服务器类型切换显示对应的设置
            const serverTypeSelect = document.getElementById('server_type');
            const neteaseSettings = document.getElementById('netease-settings');
            const internationalSettings = document.getElementById('international-settings');
            
            function updateServerSettings() {
                if (serverTypeSelect && neteaseSettings && internationalSettings) {
                    if (serverTypeSelect.value === 'netease') {
                        neteaseSettings.style.display = 'block';
                        internationalSettings.style.display = 'none';
                    } else {
                        neteaseSettings.style.display = 'none';
                        internationalSettings.style.display = 'block';
                    }
                }
            }
            
            if (serverTypeSelect) {
                serverTypeSelect.addEventListener('change', updateServerSettings);
                // 初始化时更新一次
                updateServerSettings();
            }
        });
    </script>
</body>
</html>