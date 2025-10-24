<?php
// config.php - 数据库配置和基础函数
session_start();

// 检查是否已安装
if (!file_exists('install.lock')) {
    header('Location: install.php');
    exit;
}

// 包含数据库配置
require_once 'db_config.php';

// 创建数据库连接
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 防止XSS攻击
function clean_input($data) {
    if (is_array($data)) {
        return array_map('clean_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// 检查用户是否登录
function is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// 获取网站配置
function get_site_config($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM site_config LIMIT 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 获取图库图片
function get_gallery_images($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM gallery_images ORDER BY display_order, created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 获取服务器信息
function get_server_info($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM server_info ORDER BY display_order");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 获取公告
function get_announcements($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE is_active = 1 ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 获取所有公告（包括非活跃的）
function get_all_announcements($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM announcements ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 获取需要页面加载时显示的公告
function get_popup_announcements($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE is_active = 1 AND show_on_load = 1 ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 处理外部链接 - 确保外部链接有正确的协议
function process_external_link($link) {
    if (empty($link) || $link === '#') {
        return '#';
    }
    
    // 如果已经是完整URL，直接返回
    if (strpos($link, 'http://') === 0 || strpos($link, 'https://') === 0) {
        return $link;
    }
    
    // 如果是外部链接但没有协议，添加https://
    if (strpos($link, 'www.') === 0 || 
        strpos($link, '//') === 0 ||
        (strpos($link, '.') !== false && strpos($link, '/') === false)) {
        return 'https://' . ltrim($link, '/');
    }
    
    // 内部链接，直接返回
    return $link;
}

// 获取服务器状态 - 带缓存机制
function get_server_status($ip, $port = '25565') {
    // 创建缓存目录
    $cache_dir = __DIR__ . '/cache';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    
    $cache_file = $cache_dir . '/server_status_' . md5($ip . ':' . $port) . '.json';
    $cache_time = 20; // 缓存时间20秒
    
    // 检查缓存是否存在且未过期
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
        $cached_data = file_get_contents($cache_file);
        return json_decode($cached_data, true);
    }
    
    // 如果没有缓存或缓存过期，请求API
    $api_url = "https://motd.minebbs.com/api/status?ip=" . urlencode($ip) . "&port=" . urlencode($port);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Minecraft Server Status Checker');
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 && $response) {
        $data = json_decode($response, true);
        
        // 保存到缓存
        file_put_contents($cache_file, json_encode($data));
        
        return $data;
    }
    
    return null;
}

// 处理公告关闭
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['close_announcement'])) {
    setcookie('announcement_closed', 'true', time() + 86400, '/'); // 24小时内不再显示
}

// 获取所有数据
$site_config = get_site_config($pdo);
$gallery_images = get_gallery_images($pdo);
$server_info = get_server_info($pdo);
$announcements = get_announcements($pdo);
$all_announcements = get_all_announcements($pdo);
$popup_announcements = get_popup_announcements($pdo);

// 处理赞助链接和加入链接
$site_config['sponsor_link'] = process_external_link($site_config['sponsor_link']);
$site_config['join_link'] = process_external_link($site_config['join_link']);

// 如果是国际版服务器，获取服务器状态
$server_status = null;
if ($site_config['server_type'] === 'international' && !empty($site_config['server_ip'])) {
    $server_status = get_server_status($site_config['server_ip'], $site_config['server_port']);
}
?>