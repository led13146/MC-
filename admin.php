<?php
// admin.php - 后台管理页面
require_once 'config.php';

// 先处理退出登录逻辑
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
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'site-config';

// 更新网站配置
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_config'])) {
    $site_name = clean_input($_POST['site_name']);
    $join_link = clean_input($_POST['join_link']);
    $join_text = clean_input($_POST['join_text']);
    $sponsor_link = clean_input($_POST['sponsor_link']);
    $sponsor_text = clean_input($_POST['sponsor_text']);
    $server_type = clean_input($_POST['server_type']);
    
    // 根据服务器类型处理不同的字段
    if ($server_type === 'netease') {
        $server_ip = clean_input($_POST['netease_server_ip']);
        $server_port = ''; // 网易版不需要端口
    } else {
        $server_ip = clean_input($_POST['international_server_ip']);
        $server_port = clean_input($_POST['server_port']);
    }
    
    $footer_icp = clean_input($_POST['footer_icp']);
    $footer_public_security = clean_input($_POST['footer_public_security']);
    $logo_image = clean_input($_POST['logo_image']);
    
    $stmt = $pdo->prepare("UPDATE site_config SET site_name = ?, join_link = ?, join_text = ?, sponsor_link = ?, sponsor_text = ?, server_type = ?, server_ip = ?, server_port = ?, footer_icp = ?, footer_public_security = ?, logo_image = ? WHERE id = 1");
    $stmt->execute([$site_name, $join_link, $join_text, $sponsor_link, $sponsor_text, $server_type, $server_ip, $server_port, $footer_icp, $footer_public_security, $logo_image]);
    
    $message = '配置已更新！';
    $site_config = get_site_config($pdo);
    $current_tab = 'site-config';
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
        $current_tab = 'announcements';
        $all_announcements = get_all_announcements($pdo);
    } else {
        $message = '请填写标题和内容！';
        $current_tab = 'announcements';
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
        $current_tab = 'announcements';
        $all_announcements = get_all_announcements($pdo);
    }
}

// 删除公告
if (isset($_GET['delete_announcement'])) {
    $announcement_id = intval($_GET['delete_announcement']);
    
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->execute([$announcement_id]);
    $message = '公告已删除！';
    $current_tab = 'announcements';
    $all_announcements = get_all_announcements($pdo);
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
        $current_tab = 'gallery';
        $gallery_images = get_gallery_images($pdo);
    } else {
        $message = '请填写图片URL！';
        $current_tab = 'gallery';
    }
}

// 删除图库图片
if (isset($_GET['delete_image'])) {
    $image_id = intval($_GET['delete_image']);
    
    $stmt = $pdo->prepare("DELETE FROM gallery_images WHERE id = ?");
    $stmt->execute([$image_id]);
    $message = '图片已删除！';
    $current_tab = 'gallery';
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
        $current_tab = 'server-info';
        $server_info = get_server_info($pdo);
    } else {
        $message = '请填写标题和描述！';
        $current_tab = 'server-info';
    }
}

// 删除服务器信息
if (isset($_GET['delete_server_info'])) {
    $info_id = intval($_GET['delete_server_info']);
    
    $stmt = $pdo->prepare("DELETE FROM server_info WHERE id = ?");
    $stmt->execute([$info_id]);
    $message = '服务器信息已删除！';
    $current_tab = 'server-info';
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
            $current_tab = 'change-password';
        } else {
            $message = '新密码和确认密码不匹配！';
            $current_tab = 'change-password';
        }
    } else {
        $message = '当前密码不正确！';
        $current_tab = 'change-password';
    }
}

// 测试服务器状态
if (isset($_GET['test_server_status'])) {
    if (!empty($site_config['server_ip']) && $site_config['server_type'] === 'international') {
        $test_status = get_server_status($site_config['server_ip'], $site_config['server_port']);
        if ($test_status) {
            $message = '服务器状态测试成功！';
        } else {
            $message = '无法获取服务器状态，请检查服务器IP和端口设置。';
        }
    } else {
        $message = '请先设置服务器IP地址并确保服务器类型为国际版。';
    }
    $current_tab = 'site-config';
}

// 重新获取所有数据以确保显示最新内容
$announcements = get_announcements($pdo);
$all_announcements = get_all_announcements($pdo);
$gallery_images = get_gallery_images($pdo);
$server_info = get_server_info($pdo);

// 如果是国际版服务器，获取服务器状态用于显示
$server_status = null;
if ($site_config['server_type'] === 'international' && !empty($site_config['server_ip'])) {
    $server_status = get_server_status($site_config['server_ip'], $site_config['server_port']);
}
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
    --border-radius: 0.5rem;
    --border-radius-sm: 0.25rem;
    --spacing-xs: 0.25rem;
    --spacing-sm: 0.5rem;
    --spacing-md: 0.75rem;
    --spacing-lg: 1rem;
    --spacing-xl: 1.5rem;
    --spacing-2xl: 2rem;
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
    min-height: 100vh;
    line-height: 1.5;
}

.admin-header {
    background: var(--light);
    padding: var(--spacing-lg) var(--spacing-2xl);
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
    width: 280px; /* 稍微增加一点宽度，但不是固定不可变 */
    min-width: 250px; /* 最小宽度 */
    max-width: 320px; /* 最大宽度 */
    background: var(--light);
    padding: var(--spacing-lg);
    overflow-y: auto;
}

.admin-content {
    flex: 1;
    padding: var(--spacing-2xl);
    overflow-y: auto;
}

/* 导航链接 */
.nav-link {
    display: block;
    padding: var(--spacing-sm) var(--spacing-lg);
    color: var(--text-light);
    text-decoration: none;
    border-radius: var(--border-radius-sm);
    margin-bottom: var(--spacing-xs);
    transition: background 0.2s;
}

.nav-link:hover, 
.nav-link.active {
    background: rgba(255, 255, 255, 0.1);
}

/* 区块样式 */
.section {
    background: var(--light);
    padding: var(--spacing-xl);
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing-2xl);
}

.section-title {
    margin-bottom: var(--spacing-xl);
    color: var(--secondary);
}

/* 表单样式 */
.form-group {
    margin-bottom: var(--spacing-lg);
}

label {
    display: block;
    margin-bottom: var(--spacing-xs);
}

input[type="text"],
input[type="password"],
input[type="number"],
textarea,
select {
    width: 100%;
    padding: var(--spacing-lg);
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(15, 23, 42, 0.5);
    border-radius: var(--border-radius-sm);
    color: var(--text-light);
    font-size: 1rem;
}

textarea {
    min-height: 120px;
    resize: vertical;
    min-height: clamp(100px, 15vh, 200px); /* 更灵活的高度 */
}

/* 复选框组 */
.checkbox-group {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
}

.checkbox-group input {
    width: auto;
}

/* 按钮样式 */
.btn {
    display: inline-block;
    padding: var(--spacing-lg) var(--spacing-2xl);
    background: var(--secondary);
    color: white;
    border: none;
    border-radius: var(--border-radius-sm);
    cursor: pointer;
    font-weight: bold;
    transition: background 0.2s;
    text-decoration: none;
    font-size: 1rem;
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

/* 消息提示 */
.message {
    padding: var(--spacing-lg);
    background: rgba(16, 185, 129, 0.2);
    border-radius: var(--border-radius-sm);
    margin-bottom: var(--spacing-lg);
}

/* 图片画廊 */
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: var(--spacing-lg);
    margin-top: var(--spacing-lg);
}

.gallery-item {
    position: relative;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.gallery-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.gallery-caption {
    padding: var(--spacing-xs);
    background: rgba(0, 0, 0, 0.7);
}

.delete-btn {
    position: absolute;
    top: var(--spacing-xs);
    right: var(--spacing-xs);
    background: rgba(239, 68, 68, 0.8);
    color: white;
    border: none;
    border-radius: var(--border-radius-sm);
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-decoration: none;
}

/* 服务器信息 */
.server-info-list {
    margin-top: var(--spacing-lg);
}

.server-info-item {
    background: rgba(15, 23, 42, 0.5);
    padding: var(--spacing-lg);
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing-lg);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.server-info-content h4 {
    margin-bottom: var(--spacing-xs);
}

.server-info-actions {
    display: flex;
    gap: var(--spacing-xs);
}

/* 标签页内容 */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* 公告列表 */
.announcements-list {
    margin-top: var(--spacing-lg);
}

.announcement-item {
    background: rgba(15, 23, 42, 0.5);
    padding: var(--spacing-lg);
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing-lg);
}

.announcement-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-xs);
}

.announcement-title {
    font-weight: bold;
    font-size: 1.125rem;
}

.announcement-meta {
    color: var(--text-light);
    font-size: 0.875rem;
    opacity: 0.7;
}

.announcement-actions {
    display: flex;
    gap: var(--spacing-xs);
    margin-top: var(--spacing-xs);
}

/* 状态指示器 */
.status-indicator {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: var(--spacing-xs);
}

.status-online {
    background-color: var(--secondary);
}

.status-offline {
    background-color: #EF4444;
}

/* 服务器状态卡片 */
.server-status-card {
    background: rgba(15, 23, 42, 0.5);
    padding: var(--spacing-xl);
    border-radius: var(--border-radius);
    margin-top: var(--spacing-lg);
}

.server-status-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-lg);
    margin-top: var(--spacing-lg);
}

.status-item {
    display: flex;
    flex-direction: column;
}

.status-label {
    font-size: 0.875rem;
    color: var(--text-light);
    opacity: 0.7;
    margin-bottom: var(--spacing-xs);
}

.status-value {
    font-weight: bold;
}

/* 网易云设置 */
.netease-settings {
    background: rgba(15, 23, 42, 0.3);
    padding: var(--spacing-lg);
    border-radius: var(--border-radius);
    margin-top: var(--spacing-lg);
    border-left: 4px solid var(--accent);
}

/* 图片预览 */
.image-preview {
    margin-top: var(--spacing-xs);
}

.image-preview img {
    max-width: 100%; /* 不固定宽度，响应式 */
    max-height: 200px; /* 更灵活的高度 */
    border-radius: var(--border-radius-sm);
    object-fit: contain;
}

/* 服务器状态测试 */
.server-status-test {
    background: rgba(15, 23, 42, 0.5);
    padding: var(--spacing-lg);
    border-radius: var(--border-radius);
    margin-top: var(--spacing-lg);
}

.status-test {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    margin-bottom: var(--spacing-xs);
    padding: var(--spacing-xs);
    border-radius: var(--border-radius-sm);
}

.status-test.success {
    background-color: rgba(16, 185, 129, 0.2);
    color: var(--secondary);
}

.status-test.error {
    background-color: rgba(239, 68, 68, 0.2);
    color: #EF4444;
}

/* 弹出徽章 */
.popup-badge {
    background: var(--accent);
    color: white;
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--border-radius-sm);
    font-size: 0.75rem;
    font-weight: bold;
}

.status-badge {
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--border-radius-sm);
    font-size: 0.75rem;
    font-weight: bold;
}

.status-badge.active {
    background: var(--secondary);
    color: white;
}

.status-badge.inactive {
    background: #EF4444;
    color: white;
}

/* 管理员公告 */
.admin-announcement {
    border-left: 4px solid var(--secondary);
}

/* 管理员表单 */
.admin-form {
    max-width: 100%; /* 不固定最大宽度，响应式 */
    width: 100%;
    margin: 0 auto;
}

/* 管理员画廊 */
.admin-gallery {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); /* 更灵活的列宽 */
}

/* 通用小字体 */
small {
    color: var(--text-light);
    opacity: 0.7;
    font-size: 0.875rem;
}

/* 响应式设计 */
@media (max-width: 768px) {
    .admin-header {
        padding: var(--spacing-lg) var(--spacing-lg);
        flex-direction: column;
        gap: var(--spacing-md);
    }
    
    .admin-sidebar {
        width: 100%;
        min-width: unset;
        max-width: unset;
    }
    
    .admin-container {
        flex-direction: column;
    }
    
    .admin-content {
        padding: var(--spacing-lg);
    }
    
    .gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: var(--spacing-md);
    }
    
    .server-status-info {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .admin-header {
        padding: var(--spacing-md);
    }
    
    .admin-content {
        padding: var(--spacing-md);
    }
    
    .section {
        padding: var(--spacing-lg);
    }
    
    .gallery-grid {
        grid-template-columns: 1fr;
    }
}
    </style>
</head>
<body>
    <div class="admin-header">
        <h1><?php echo $site_config['site_name']; ?> - 后台管理</h1>
        <div>
            <span>欢迎, <?php echo $_SESSION['admin_username']; ?></span>
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
        </div>
        
        <div class="admin-content">
            <?php if ($message): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div id="site-config" class="tab-content active">
                <div class="section">
                    <h2 class="section-title">网站配置</h2>
                    <form method="POST" class="admin-form">
                        <input type="hidden" name="update_config" value="1">
                        <div class="form-group">
                            <label for="site_name">网站名称</label>
                            <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($site_config['site_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="server_type">服务器类型</label>
                            <select id="server_type" name="server_type" required onchange="updateServerSettings()">
                                <option value="netease" <?php echo $site_config['server_type'] === 'netease' ? 'selected' : ''; ?>>网易版</option>
                                <option value="international" <?php echo $site_config['server_type'] === 'international' ? 'selected' : ''; ?>>国际版</option>
                            </select>
                        </div>
                        
                        <!-- 网易版设置 -->
                        <div id="netease-settings" class="netease-settings" style="<?php echo $site_config['server_type'] === 'netease' ? '' : 'display: none;'; ?>">
                            <div class="form-group">
                                <label for="netease_server_ip">房间号</label>
                                <input type="text" id="netease_server_ip" name="netease_server_ip" value="<?php echo htmlspecialchars($site_config['server_ip']); ?>" placeholder="例如: 12345678">
                                <small>请输入网易版我的世界联机大厅的房间号</small>
                            </div>
                        </div>
                        
                        <!-- 国际版设置 -->
                        <div id="international-settings" style="<?php echo $site_config['server_type'] === 'international' ? '' : 'display: none;'; ?>">
                            <div class="form-group">
                                <label for="international_server_ip">服务器IP地址</label>
                                <input type="text" id="international_server_ip" name="international_server_ip" value="<?php echo htmlspecialchars($site_config['server_ip']); ?>" placeholder="例如: play.example.com">
                            </div>
                            
                            <div class="form-group">
                                <label for="server_port">服务器端口</label>
                                <input type="text" id="server_port" name="server_port" value="<?php echo htmlspecialchars($site_config['server_port']); ?>" placeholder="默认: 25565">
                            </div>
                            
                            <?php if (!empty($site_config['server_ip']) && $site_config['server_type'] === 'international'): ?>
                                <div class="server-status-test">
                                    <h3>服务器状态测试</h3>
                                    <?php if ($server_status): ?>
                                        <div class="status-test success">
                                            <span class="status-indicator status-online"></span>
                                            服务器在线 - 版本: <?php echo htmlspecialchars($server_status['version'] ?? '未知'); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="status-test error">
                                            <span class="status-indicator status-offline"></span>
                                            服务器离线或无法连接
                                        </div>
                                    <?php endif; ?>
                                    <a href="?test_server_status=1&tab=site-config" class="btn">测试服务器状态</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="join_link">加入链接</label>
                            <input type="text" id="join_link" name="join_link" value="<?php echo htmlspecialchars($site_config['join_link']); ?>" required>
                            <small>请输入完整的外部链接地址，例如: https://discord.gg/your-server</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="join_text">加入按钮文本</label>
                            <input type="text" id="join_text" name="join_text" value="<?php echo htmlspecialchars($site_config['join_text']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="sponsor_link">赞助链接</label>
                            <input type="text" id="sponsor_link" name="sponsor_link" value="<?php echo htmlspecialchars($site_config['sponsor_link']); ?>" required>
                            <small>请输入完整的外部链接地址，例如: https://www.paypal.com/your-link</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="sponsor_text">赞助按钮文本</label>
                            <input type="text" id="sponsor_text" name="sponsor_text" value="<?php echo htmlspecialchars($site_config['sponsor_text']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="logo_image">Logo图片URL</label>
                            <input type="text" id="logo_image" name="logo_image" value="<?php echo htmlspecialchars($site_config['logo_image']); ?>" placeholder="输入图片URL">
                            <?php if ($site_config['logo_image']): ?>
                                <div class="image-preview">
                                    <img src="<?php echo $site_config['logo_image']; ?>" alt="Logo预览">
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
                        
                        <button type="submit" class="btn">保存配置</button>
                    </form>
                </div>
            </div>
            
            <div id="announcements" class="tab-content">
                <div class="section">
                    <h2 class="section-title">公告管理</h2>
                    
                    <form method="POST" class="admin-form">
                        <input type="hidden" name="add_announcement" value="1">
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
                        
                        <button type="submit" class="btn">添加公告</button>
                    </form>
                    
                    <div class="announcements-list">
                        <?php foreach ($all_announcements as $announcement): ?>
                            <div class="announcement-item admin-announcement">
                                <div class="announcement-header">
                                    <div class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                                    <div class="announcement-meta">
                                        <?php echo date('Y-m-d H:i', strtotime($announcement['created_at'])); ?>
                                        <?php if ($announcement['show_on_load']): ?>
                                            <span class="popup-badge">弹出显示</span>
                                        <?php endif; ?>
                                        <?php if ($announcement['is_active']): ?>
                                            <span class="status-badge active">已启用</span>
                                        <?php else: ?>
                                            <span class="status-badge inactive">已禁用</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="announcement-content">
                                    <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                                </div>
                                <div class="announcement-actions">
                                    <a href="?toggle_announcement=<?php echo $announcement['id']; ?>&tab=announcements" class="btn btn-warning">
                                        <?php echo $announcement['is_active'] ? '禁用' : '启用'; ?>
                                    </a>
                                    <a href="?delete_announcement=<?php echo $announcement['id']; ?>&tab=announcements" class="btn btn-danger" onclick="return confirm('确定删除这个公告吗？')">删除</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div id="gallery" class="tab-content">
                <div class="section">
                    <h2 class="section-title">图片展示管理</h2>
                    
                    <form method="POST" class="admin-form">
                        <input type="hidden" name="add_gallery_image" value="1">
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
                        
                        <button type="submit" class="btn">添加图片</button>
                    </form>
                    
                    <div class="gallery-grid admin-gallery">
                        <?php foreach ($gallery_images as $image): ?>
                            <div class="gallery-item">
                                <img src="<?php echo $image['image_url']; ?>" alt="<?php echo htmlspecialchars($image['caption']); ?>">
                                <div class="gallery-caption"><?php echo htmlspecialchars($image['caption']); ?></div>
                                <a href="?delete_image=<?php echo $image['id']; ?>&tab=gallery" class="delete-btn" onclick="return confirm('确定删除这张图片吗？')">×</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div id="server-info" class="tab-content">
                <div class="section">
                    <h2 class="section-title">服务器信息管理</h2>
                    
                    <form method="POST" class="admin-form">
                        <input type="hidden" name="add_server_info" value="1">
                        <div class="form-group">
                            <label for="title">标题</label>
                            <input type="text" id="title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">描述</label>
                            <textarea id="description" name="description" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="icon">图标</label>
                            <select id="icon" name="icon">
                                <option value="fa-globe">🌐 地球</option>
                                <option value="fa-users">👥 用户</option>
                                <option value="fa-shield">🛡️ 盾牌</option>
                                <option value="fa-heart">❤️ 爱心</option>
                                <option value="fa-star">⭐ 星星</option>
                                <option value="fa-gamepad">🎮 游戏手柄</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="display_order">显示顺序</label>
                            <input type="number" id="display_order" name="display_order" value="0">
                        </div>
                        
                        <button type="submit" class="btn">添加信息</button>
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
                                    <a href="?delete_server_info=<?php echo $info['id']; ?>&tab=server-info" class="btn btn-danger" onclick="return confirm('确定删除这条信息吗？')">删除</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div id="change-password" class="tab-content">
                <div class="section">
                    <h2 class="section-title">修改密码</h2>
                    <form method="POST" class="admin-form">
                        <input type="hidden" name="change_password" value="1">
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
                        
                        <button type="submit" class="btn">修改密码</button>
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
        
        // 服务器类型切换显示对应的设置
        function updateServerSettings() {
            const serverType = document.getElementById('server_type').value;
            const neteaseSettings = document.getElementById('netease-settings');
            const internationalSettings = document.getElementById('international-settings');
            
            if (serverType === 'netease') {
                neteaseSettings.style.display = 'block';
                internationalSettings.style.display = 'none';
            } else {
                neteaseSettings.style.display = 'none';
                internationalSettings.style.display = 'block';
            }
        }
        
        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', function() {
            // 初始化服务器类型显示
            updateServerSettings();
        });
    </script>
</body>
</html>