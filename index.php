<?php
// index.php - 前台网站页面
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $site_config['site_name']; ?> - 我的世界服务器">
    <meta name="keywords" content="我的世界,网易版,国际版,服务器">
    <title><?php echo $site_config['site_name']; ?></title>
    
    <style>
        :root {
            --primary: #4F46E5;
            --secondary: #10B981;
            --accent: #F59E0B;
            --dark: #0f172a;
            --light: #1e293b;
            --text-light: #f1f5f9;
            --text-gray: #94a3b8;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--dark);
            color: var(--text-light);
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .logo {
            width: 2.5rem;
            height: 2.5rem;
            background-color: var(--secondary);
            clip-path: polygon(
                0% 4px, 4px 4px, 4px 0%, calc(100% - 4px) 0%, calc(100% - 4px) 4px, 
                100% 4px, 100% calc(100% - 4px), calc(100% - 4px) calc(100% - 4px), 
                calc(100% - 4px) 100%, 4px 100%, 4px calc(100% - 4px), 0% calc(100% - 4px)
            );
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.25rem;
        }
        
        .logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .logo-text {
            font-family: "Minecraftia", system-ui, sans-serif;
            font-size: 1.25rem;
        }
        
        @media (min-width: 768px) {
            .logo-text {
                font-size: 1.5rem;
            }
        }
        
        .desktop-nav {
            display: none;
            gap: 2rem;
            align-items: center;
        }
        
        @media (min-width: 768px) {
            .desktop-nav {
                display: flex;
            }
        }
        
        .nav-link {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s ease;
        }
        
        .nav-link:hover {
            color: var(--secondary);
        }
        
        .nav-button {
            background-color: var(--secondary);
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
        }
        
        .nav-button:hover {
            background-color: rgba(16, 185, 129, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .sponsor-button {
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
        }
        
        .sponsor-button:hover {
            background-color: rgba(245, 158, 11, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .mobile-nav-btn {
            display: block;
            background: none;
            border: none;
            color: var(--text-light);
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        @media (min-width: 768px) {
            .mobile-nav-btn {
                display: none;
            }
        }
        
        .mobile-menu {
            display: none;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .mobile-menu.active {
            display: block;
        }
        
        .mobile-nav {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding: 0.75rem 1rem 1rem;
        }
        
        .mobile-nav-link {
            color: var(--text-light);
            text-decoration: none;
            padding: 0.5rem 0;
            transition: color 0.15s ease;
        }
        
        .mobile-nav-link:hover {
            color: var(--secondary);
        }
        
        .hero {
            position: relative;
            padding: 5rem 1rem;
            background: linear-gradient(to bottom right, rgba(79, 70, 229, 0.2), rgba(16, 185, 129, 0.2));
            overflow: hidden;
        }
        
        @media (min-width: 768px) {
            .hero {
                padding: 8rem 1rem;
            }
        }
        
        .hero-content {
            max-width: 48rem;
            margin: 0 auto;
            text-align: center;
            position: relative;
            z-index: 10;
        }
        
        .hero-title {
            font-family: "Minecraftia", system-ui, sans-serif;
            font-size: 2.25rem;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        @media (min-width: 768px) {
            .hero-title {
                font-size: 3.75rem;
            }
        }
        
        .hero-subtitle {
            font-size: 1.125rem;
            margin-bottom: 2.5rem;
        }
        
        @media (min-width: 768px) {
            .hero-subtitle {
                font-size: 1.5rem;
            }
        }
        
        .hero-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            justify-content: center;
        }
        
        @media (min-width: 640px) {
            .hero-buttons {
                flex-direction: row;
            }
        }
        
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            padding: 0.75rem 2rem;
            border-radius: 0.25rem;
            text-decoration: none;
            font-size: 1.125rem;
            clip-path: polygon(
                0% 4px, 4px 4px, 4px 0%, calc(100% - 4px) 0%, calc(100% - 4px) 4px, 
                100% 4px, 100% calc(100% - 4px), calc(100% - 4px) calc(100% - 4px), 
                calc(100% - 4px) 100%, 4px 100%, 4px calc(100% - 4px), 0% calc(100% - 4px)
            );
            transition: all 0.2s ease;
        }
        
        .button-primary {
            background-color: var(--secondary);
            color: white;
        }
        
        .button-primary:hover {
            background-color: rgba(16, 185, 129, 0.9);
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .button-secondary {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            backdrop-filter: blur(8px);
        }
        
        .button-secondary:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .button-accent {
            background-color: var(--accent);
            color: white;
        }
        
        .button-accent:hover {
            background-color: rgba(245, 158, 11, 0.9);
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .button-icon {
            margin-right: 0.5rem;
        }
        
        .block-decoration {
            position: absolute;
            background-color: rgba(16, 185, 129, 0.2);
            clip-path: polygon(
                0% 4px, 4px 4px, 4px 0%, calc(100% - 4px) 0%, calc(100% - 4px) 4px, 
                100% 4px, 100% calc(100% - 4px), calc(100% - 4px) calc(100% - 4px), 
                calc(100% - 4px) 100%, 4px 100%, 4px calc(100% - 4px), 0% calc(100% - 4px)
            );
        }
        
        .block-1 {
            bottom: -2.5rem;
            left: -2.5rem;
            width: 10rem;
            height: 10rem;
            transform: rotate(12deg);
        }
        
        .block-2 {
            top: 5rem;
            right: -4rem;
            width: 8rem;
            height: 8rem;
            background-color: rgba(245, 158, 11, 0.2);
            transform: rotate(-6deg);
        }
        
        .bg-grid {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.1;
            z-index: 0;
        }
        
        .section {
            padding: 5rem 1rem;
        }
        
        .section-light {
            background-color: var(--light);
        }
        
        .section-dark {
            background-color: var(--dark);
        }
        
        .section-gradient {
            background: linear-gradient(to bottom right, rgba(79, 70, 229, 0.05), rgba(16, 185, 129, 0.05));
        }
        
        .section-title {
            font-family: "Minecraftia", system-ui, sans-serif;
            font-size: 2.25rem;
            text-align: center;
            margin-bottom: 1rem;
            color: var(--primary);
        }
        
        @media (min-width: 768px) {
            .section-title {
                font-size: 2.5rem;
            }
        }
        
        .section-subtitle {
            text-align: center;
            color: var(--text-gray);
            margin-bottom: 3rem;
            max-width: 42rem;
            margin-left: auto;
            margin-right: auto;
        }
        
        .card {
            background: rgba(30, 41, 59, 0.7);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .card-center {
            text-align: center;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .gallery-item {
            border-radius: 0.5rem;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
        }
        
        .gallery-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .gallery-caption {
            padding: 1rem;
            background: rgba(15, 23, 42, 0.7);
        }
        
        .server-info {
            display: grid;
            gap: 2rem;
            max-width: 80rem;
            margin: 0 auto;
        }
        
        @media (min-width: 768px) {
            .server-info {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .info-card {
            text-align: center;
        }
        
        .info-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--secondary);
        }
        
        .info-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .info-description {
            color: var(--text-gray);
        }
        
        footer {
            background: rgba(15, 23, 42, 0.95);
            padding: 2.5rem 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .footer-container {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }
        
        @media (min-width: 768px) {
            .footer-container {
                flex-direction: row;
            }
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .footer-logo {
                margin-bottom: 0;
            }
        }
        
        .footer-logo-icon {
            width: 2rem;
            height: 2rem;
            background-color: var(--secondary);
            clip-path: polygon(
                0% 4px, 4px 4px, 4px 0%, calc(100% - 4px) 0%, calc(100% - 4px) 4px, 
                100% 4px, 100% calc(100% - 4px), calc(100% - 4px) calc(100% - 4px), 
                calc(100% - 4px) 100%, 4px 100%, 4px calc(100% - 4px), 0% calc(100% - 4px)
            );
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Minecraftia", system-ui, sans-serif;
            font-size: 0.875rem;
            color: white;
        }
        
        .footer-logo-text {
            font-family: "Minecraftia", system-ui, sans-serif;
            font-size: 1.125rem;
        }
        
        .footer-description {
            color: var(--text-gray);
            margin-top: 0.5rem;
        }
        
        .footer-copyright {
            text-align: center;
        }
        
        @media (min-width: 768px) {
            .footer-copyright {
                text-align: right;
            }
        }
        
        .footer-text {
            color: var(--text-gray);
        }
        
        .footer-note {
            color: #6B7280;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .fa {
            display: inline-block;
            font-style: normal;
            font-variant: normal;
            text-rendering: auto;
            line-height: 1;
        }
        
        .fa-download:before {
            content: "↓";
        }
        
        .fa-play-circle:before {
            content: "▶";
        }
        
        .fa-external-link:before {
            content: "↗";
        }
        
        .fa-users:before {
            content: "👥";
        }
        
        .fa-shield:before {
            content: "🛡️";
        }
        
        .fa-globe:before {
            content: "🌐";
        }
        
        .fa-heart:before {
            content: "❤️";
        }
        
        .fa-bars:before {
            content: "☰";
        }
        
        .fa-coffee:before {
            content: "☕";
        }
        
        .fa-star:before {
            content: "⭐";
        }
        
        .fa-bell:before {
            content: "🔔";
        }
        
        .fa-server:before {
            content: "🖥️";
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
            background: rgba(30, 41, 59, 0.7);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: center;
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
            align-items: center;
        }
        
        .status-label {
            font-size: 0.875rem;
            color: var(--text-gray);
            margin-bottom: 0.25rem;
        }
        
        .status-value {
            font-weight: bold;
        }
        
        .announcements-section {
            margin-top: 2rem;
        }
        
        .announcement-item {
            background: rgba(30, 41, 59, 0.7);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .announcement-title {
            font-weight: bold;
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--accent);
        }
        
        .announcement-content {
            line-height: 1.6;
        }
        
        /* 修复公告弹窗样式 */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 1rem;
            opacity: 1;
            visibility: visible;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .modal-overlay.hidden {
            opacity: 0;
            visibility: hidden;
        }
        
        .modal-content {
            background: var(--light);
            border-radius: 0.75rem;
            padding: 2rem;
            max-width: 600px;
            width: 100%;
            max-height: 80vh;
            overflow-y: auto;
            transform: scale(1);
            transition: transform 0.3s ease;
        }
        
        .modal-overlay.hidden .modal-content {
            transform: scale(0.9);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--accent);
        }
        
        .modal-close {
            background: none;
            border: none;
            color: var(--text-light);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.25rem;
            transition: background 0.2s;
        }
        
        .modal-close:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .room-number {
            display: inline-block;
            background: var(--accent);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            font-weight: bold;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <!-- 导航栏 -->
    <header>
        <div class="header-container">
            <!-- Logo 位置 -->
            <div class="logo-container">
                <?php if ($site_config['logo_image']): ?>
                    <div class="logo">
                        <img src="<?php echo $site_config['logo_image']; ?>" alt="<?php echo $site_config['site_name']; ?> Logo" class="logo-img">
                    </div>
                <?php else: ?>
                    <div class="logo">
                        <div class="logo-img" style="background-color: var(--secondary);"></div>
                    </div>
                <?php endif; ?>
                <h1 class="logo-text"><?php echo $site_config['site_name']; ?></h1>
            </div>
            
            <!-- 导航链接 - 桌面版 -->
            <nav class="desktop-nav">
                <a href="#intro" class="nav-link">服务器介绍</a>
                <a href="#gallery" class="nav-link">服务器截图</a>
                <a href="#features" class="nav-link">特色功能</a>
                <?php if ($site_config['server_type'] === 'international' && !empty($site_config['server_ip'])): ?>
                    <a href="#status" class="nav-link">服务器状态</a>
                <?php endif; ?>
                <a href="<?php echo $site_config['join_link']; ?>" target="_blank" class="nav-button"><?php echo $site_config['join_text']; ?></a>
                <a href="<?php echo $site_config['sponsor_link']; ?>" target="_blank" class="sponsor-button"><?php echo $site_config['sponsor_text']; ?></a>
                <a href="admin_login.php" class="nav-link">管理后台</a>
            </nav>
            
            <!-- 移动端菜单按钮 -->
            <button id="menuBtn" class="mobile-nav-btn">
                <i class="fa fa-bars"></i>
            </button>
        </div>
        
        <!-- 移动端导航菜单 -->
        <div id="mobileMenu" class="mobile-menu">
            <div class="mobile-nav">
                <a href="#intro" class="mobile-nav-link">服务器介绍</a>
                <a href="#gallery" class="mobile-nav-link">服务器截图</a>
                <a href="#features" class="mobile-nav-link">特色玩法</a>
                <?php if ($site_config['server_type'] === 'international' && !empty($site_config['server_ip'])): ?>
                    <a href="#status" class="mobile-nav-link">服务器状态</a>
                <?php endif; ?>
                <a href="<?php echo $site_config['join_link']; ?>" target="_blank" class="nav-button"><?php echo $site_config['join_text']; ?></a>
                <a href="<?php echo $site_config['sponsor_link']; ?>" target="_blank" class="sponsor-button"><?php echo $site_config['sponsor_text']; ?></a>
                <a href="admin_login.php" class="mobile-nav-link">管理后台</a>
            </div>
        </div>
    </header>

    <main>
        <!-- 英雄区域 -->
        <section class="hero">
            <div class="bg-grid"></div>
            <div class="hero-content">
                <h1 class="hero-title"><?php echo $site_config['site_name']; ?></h1>
                <p class="hero-subtitle">
                    <?php if ($site_config['server_type'] === 'netease'): ?>
                        网易版我的世界联机大厅服务器 - 与好友一起创造无限可能
                    <?php else: ?>
                        国际版我的世界服务器 - 体验原版我的世界的乐趣
                    <?php endif; ?>
                </p>
                
                <?php if ($site_config['server_type'] === 'netease'): ?>
                    <div class="room-number">
                        当前房间号: <?php echo $site_config['server_ip'] ?: '待设置'; ?>
                    </div>
                <?php endif; ?>
                
                <div class="hero-buttons">
                    <a href="<?php echo $site_config['join_link']; ?>" target="_blank" class="button button-primary">
                        <i class="fa fa-play-circle button-icon"></i>立即加入
                    </a>
                    <a href="<?php echo $site_config['sponsor_link']; ?>" target="_blank" class="button button-accent">
                        <i class="fa fa-star button-icon"></i><?php echo $site_config['sponsor_text']; ?>
                    </a>
                    <a href="#features" class="button button-secondary">
                        <i class="fa fa-external-link button-icon"></i>了解更多
                    </a>
                </div>
            </div>
            
            <!-- 装饰性方块元素 -->
            <div class="block-decoration block-1"></div>
            <div class="block-decoration block-2"></div>
        </section>

        <!-- 公告区域 -->
        <?php if (count($announcements) > 0): ?>
            <section class="section section-light">
                <div class="container">
                    <h2 class="section-title">最新公告</h2>
                    <p class="section-subtitle">了解服务器最新动态和重要通知</p>
                    
                    <div class="announcements-section">
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="announcement-item">
                                <div class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                                <div class="announcement-content"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- 服务器状态（仅国际版显示） -->
        <?php if ($site_config['server_type'] === 'international' && !empty($site_config['server_ip'])): ?>
            <section id="status" class="section section-gradient">
                <div class="container">
                    <h2 class="section-title">服务器状态</h2>
                    <p class="section-subtitle">实时监控服务器运行状态</p>
                    
                    <div class="server-status-card">
                        <div class="status-item">
                            <span class="status-label">服务器地址:</span>
                            <span class="status-value"><?php echo htmlspecialchars($site_config['server_ip']); ?><?php echo $site_config['server_port'] !== '25565' ? ':' . htmlspecialchars($site_config['server_port']) : ''; ?></span>
                        </div>
                        
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
                                    <span class="status-label">延迟:</span>
                                    <span class="status-value"><?php echo htmlspecialchars($server_status['delay'] ?? '0'); ?>ms</span>
                                </div>
                            </div>
                            
                            <?php if (!empty($server_status['pureMotd'])): ?>
                                <div class="status-item" style="margin-top: 1rem;">
                                    <span class="status-label">服务器描述:</span>
                                    <span class="status-value"><?php echo htmlspecialchars($server_status['pureMotd']); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="status-item">
                                <span class="status-label">状态:</span>
                                <span class="status-value">
                                    <span class="status-indicator status-offline"></span>
                                    离线或无法连接
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- 服务器介绍区域 -->
        <section id="intro" class="section section-light">
            <div class="container">
                <div class="card card-center">
                    <h2 class="section-title">服务器介绍</h2>
                    <p class="section-subtitle">欢迎来到我们的我的世界服务器</p>
                    
                    <div class="card">
                        <p class="text-lg mb-4 leading-relaxed">
                            <?php if ($site_config['server_type'] === 'netease'): ?>
                                我们的网易版服务器专注于为玩家提供稳定、流畅的联机体验，支持多种游戏模式和自定义玩法。
                            <?php else: ?>
                                我们的国际版服务器提供原版我的世界体验，支持最新版本，拥有稳定的服务器性能和友好的玩家社区。
                            <?php endif; ?>
                        </p>
                        <p class="text-lg leading-relaxed">
                            无论你是建筑爱好者、冒险家还是红石大师，这里都有适合你的游戏内容。加入我们，与来自各地的玩家一起创造属于你们的我的世界故事！
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 服务器截图 -->
        <section id="gallery" class="section section-dark">
            <div class="container">
                <h2 class="section-title">服务器截图</h2>
                <p class="section-subtitle">看看我们的服务器环境和玩家作品</p>
                
                <?php if (count($gallery_images) > 0): ?>
                    <div class="gallery-grid">
                        <?php foreach ($gallery_images as $image): ?>
                            <div class="card gallery-item">
                                <img src="<?php echo $image['image_url']; ?>" alt="<?php echo htmlspecialchars($image['caption']); ?>" class="gallery-image">
                                <div class="gallery-caption">
                                    <p><?php echo htmlspecialchars($image['caption']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card card-center">
                        <p>暂无图片，请管理员在后台添加服务器截图。</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- 特色功能 -->
        <section id="features" class="section section-light">
            <div class="container">
                <h2 class="section-title">服务器特色</h2>
                <p class="section-subtitle">体验我们服务器的独特功能和优质服务</p>
                
                <div class="server-info">
                    <?php foreach ($server_info as $info): ?>
                        <div class="card info-card">
                            <i class="fa <?php echo $info['icon']; ?> info-icon"></i>
                            <h3 class="info-title"><?php echo htmlspecialchars($info['title']); ?></h3>
                            <p class="info-description"><?php echo htmlspecialchars($info['description']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        
        <!-- 赞助区域 -->
        <section id="sponsor" class="section section-gradient">
            <div class="container">
                <h2 class="section-title">支持我们</h2>
                <p class="section-subtitle">您的支持是我们持续改进服务器的动力</p>
                
                <div class="card card-center">
                    <i class="fa fa-heart" style="font-size: 3rem; color: var(--accent); margin-bottom: 1rem;"></i>
                    <h3>感谢您考虑支持我们！</h3>
                    <p class="section-subtitle">您的赞助将用于服务器维护、功能开发和提供更好的游戏体验。</p>
                    
                    <div style="margin-top: 2rem;">
                        <a href="<?php echo $site_config['sponsor_link']; ?>" class="button button-accent" target="_blank">
                            <i class="fa fa-star button-icon"></i><?php echo $site_config['sponsor_text']; ?>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- 页脚 -->
    <footer>
        <div class="container">
            <div class="footer-container">
                <div>
                    <div class="footer-logo">
                        <div class="footer-logo-icon">M</div>
                        <span class="footer-logo-text"><?php echo $site_config['site_name']; ?></span>
                    </div>
                    <div class="footer-description">
                        <?php if ($site_config['server_type'] === 'netease'): ?>
                            网易版我的世界联机大厅服务器
                        <?php else: ?>
                            国际版我的世界服务器
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="footer-copyright">
                    <p class="footer-text">© <?php echo date('Y'); ?> <?php echo $site_config['site_name']; ?>. 保留所有权利.</p>
                    <?php if ($site_config['footer_icp']): ?>
                        <p class="footer-text">
                            <a href="https://beian.miit.gov.cn/" target="_blank" style="color: inherit; text-decoration: none;">
                                <?php echo $site_config['footer_icp']; ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    <?php if ($site_config['footer_public_security']): ?>
                        <p class="footer-text">
                            <?php
                                // 提取公安备案号中的数字部分
                                $public_security_number = preg_replace('/[^\d]/', '', $site_config['footer_public_security']);
                            ?>
                            <a href="https://www.beian.gov.cn/portal/registerSystemInfo?recordcode=<?php echo $public_security_number; ?>" target="_blank" style="color: inherit; text-decoration: none;">
                                <?php echo $site_config['footer_public_security']; ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    <p class="footer-note">我的世界相关商标归 Mojang AB 所有</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- 公告弹窗 -->
    <?php if (count($popup_announcements) > 0): ?>
        <div id="announcementModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">最新公告</h2>
                    <button id="closeModal" class="modal-close">×</button>
                </div>
                <?php foreach ($popup_announcements as $announcement): ?>
                    <div class="announcement-item" style="margin-bottom: 1.5rem;">
                        <div class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                        <div class="announcement-content"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></div>
                    </div>
                <?php endforeach; ?>
                <div style="text-align: center; margin-top: 1.5rem;">
                    <button id="closeModalBtn" class="button button-primary">我知道了</button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- JavaScript -->
    <script>
        // 页面加载完成后执行
        document.addEventListener('DOMContentLoaded', function() {
            // 移动端菜单切换
            const menuBtn = document.getElementById('menuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            
            if (menuBtn && mobileMenu) {
                menuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('active');
                });
            }
            
            // 平滑滚动
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 80,
                            behavior: 'smooth'
                        });
                        
                        // 移动端点击后关闭菜单
                        if (mobileMenu && mobileMenu.classList.contains('active')) {
                            mobileMenu.classList.remove('active');
                        }
                    }
                });
            });
            
            // 修复公告弹窗控制 - 解决关闭延迟问题
            const announcementModal = document.getElementById('announcementModal');
            const closeModal = document.getElementById('closeModal');
            const closeModalBtn = document.getElementById('closeModalBtn');
            
            // 检查是否已经关闭过公告
            function shouldShowAnnouncement() {
                const cookies = document.cookie.split(';');
                for (let cookie of cookies) {
                    const [name, value] = cookie.trim().split('=');
                    if (name === 'announcement_closed' && value === 'true') {
                        return false;
                    }
                }
                return true;
            }
            
            if (announcementModal && closeModal && closeModalBtn) {
                // 页面加载时检查是否显示公告
                if (!shouldShowAnnouncement()) {
                    announcementModal.classList.add('hidden');
                    setTimeout(() => {
                        announcementModal.style.display = 'none';
                    }, 300);
                }
                
                // 立即关闭弹窗的函数
                function closeAnnouncementModal() {
                    announcementModal.classList.add('hidden');
                    
                    // 动画结束后完全隐藏
                    setTimeout(() => {
                        announcementModal.style.display = 'none';
                        // 设置cookie，避免重复显示
                        document.cookie = "announcement_closed=true; max-age=86400; path=/"; // 24小时内不再显示
                    }, 300); // 匹配CSS过渡时间
                }
                
                closeModal.addEventListener('click', closeAnnouncementModal);
                closeModalBtn.addEventListener('click', closeAnnouncementModal);
                
                announcementModal.addEventListener('click', function(e) {
                    if (e.target === announcementModal) {
                        closeAnnouncementModal();
                    }
                });
                
                // 添加键盘支持：按ESC键关闭
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && announcementModal.style.display !== 'none') {
                        closeAnnouncementModal();
                    }
                });
            }
        });
    </script>
</body>
</html>