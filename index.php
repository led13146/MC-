<?php
// index.php - 前台网站页面
require_once 'config.php';

// 获取当前活动部分
$current_section = isset($_GET['section']) ? $_GET['section'] : 'home';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_config['site_name']; ?> - 专业的Minecraft服务器</title>
    <meta name="description" content="<?php echo $site_config['site_name']; ?> - 专业的Minecraft服务器，提供稳定的游戏环境和丰富的游戏内容。">
    <meta name="keywords" content="Minecraft,我的世界,服务器,<?php echo $site_config['site_name']; ?>">
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
            <div class="minecraft-logo"><?php echo $site_config['site_name']; ?></div>
            <div class="loading-bar">
                <div class="loading-progress"></div>
            </div>
            <p class="loading-text">正在加载方块世界...</p>
        </div>
    </div>

    <!-- 主容器 -->
    <div id="main-container">
        <!-- 导航栏 -->
        <nav class="navbar">
            <div class="nav-brand">
                <h1 class="minecraft-font"><?php echo $site_config['site_name']; ?></h1>
            </div>
            <ul class="nav-links">
                <li><a href="?section=home" class="nav-link <?php echo $current_section === 'home' ? 'active' : ''; ?>">首页</a></li>
                <li><a href="?section=status" class="nav-link <?php echo $current_section === 'status' ? 'active' : ''; ?>">服务器状态</a></li>
                <li><a href="?section=gallery" class="nav-link <?php echo $current_section === 'gallery' ? 'active' : ''; ?>">服务器截图</a></li>
                <li><a href="?section=features" class="nav-link <?php echo $current_section === 'features' ? 'active' : ''; ?>">特色功能</a></li>
                <li><a href="?section=announcements" class="nav-link <?php echo $current_section === 'announcements' ? 'active' : ''; ?>">最新公告</a></li>
                <li><a href="<?php echo $site_config['join_link']; ?>" target="_blank" class="nav-link"><?php echo $site_config['join_text']; ?></a></li>
                <li><a href="<?php echo $site_config['sponsor_link']; ?>" target="_blank" class="nav-link"><?php echo $site_config['sponsor_text']; ?></a></li>
                <li><a href="admin_login.php" class="nav-link">管理后台</a></li>
            </ul>
        </nav>

        <!-- 主要内容区域 -->
        <main>
            <!-- 首页部分 -->
            <section id="home" class="section <?php echo $current_section === 'home' ? 'active' : ''; ?>">
                <div class="hero-section">
                    <div class="hero-content">
                        <h2 class="minecraft-font">欢迎来到 <?php echo $site_config['site_name']; ?></h2>
                        <p class="hero-subtitle">专业的Minecraft服务器</p>
                        <p class="hero-description">
                            <?php if ($site_config['server_type'] === 'netease'): ?>
                                网易版我的世界联机大厅服务器 - 与好友一起创造无限可能
                            <?php else: ?>
                                国际版我的世界服务器 - 体验原版我的世界的乐趣
                            <?php endif; ?>
                        </p>
                        <?php if ($site_config['server_type'] === 'netease' && !empty($site_config['server_ip'])): ?>
                            <div class="room-number">
                                当前房间号: <?php echo $site_config['server_ip']; ?>
                            </div>
                        <?php endif; ?>
                        <div class="hero-buttons">
                            <a href="<?php echo $site_config['join_link']; ?>" target="_blank" class="btn btn-primary">立即加入</a>
                            <a href="<?php echo $site_config['sponsor_link']; ?>" target="_blank" class="btn btn-secondary"><?php echo $site_config['sponsor_text']; ?></a>
                            <a href="?section=features" class="btn btn-secondary">了解更多</a>
                        </div>
                    </div>
                </div>
                
                <div class="features-section">
                    <h2 class="section-title">我们的服务</h2>
                    <div class="features-grid">
                        <?php foreach ($server_info as $info): ?>
                        <div class="feature-card">
                            <div class="feature-icon"><?php 
                                $icon_map = [
                                    'fa-globe' => '🌐',
                                    'fa-users' => '👥',
                                    'fa-shield' => '🛡️',
                                    'fa-heart' => '❤️',
                                    'fa-star' => '⭐',
                                    'fa-gamepad' => '🎮'
                                ];
                                echo $icon_map[$info['icon']] ?? '🔧';
                            ?></div>
                            <h3><?php echo htmlspecialchars($info['title']); ?></h3>
                            <p><?php echo htmlspecialchars($info['description']); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- 服务器状态部分 -->
            <section id="status" class="section <?php echo $current_section === 'status' ? 'active' : ''; ?>">
                <h2 class="section-title">服务器状态</h2>
                <p class="section-subtitle">实时监控服务器运行状态</p>
                
                <div class="server-status-card">
                    <?php if ($site_config['server_type'] === 'international' && !empty($site_config['server_ip'])): ?>
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
                    <?php else: ?>
                        <div class="status-item">
                            <span class="status-label">服务器类型:</span>
                            <span class="status-value"><?php echo $site_config['server_type'] === 'netease' ? '网易版联机大厅' : '国际版'; ?></span>
                        </div>
                        <?php if ($site_config['server_type'] === 'netease' && !empty($site_config['server_ip'])): ?>
                            <div class="status-item">
                                <span class="status-label">房间号:</span>
                                <span class="status-value"><?php echo htmlspecialchars($site_config['server_ip']); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- 服务器截图 -->
            <section id="gallery" class="section <?php echo $current_section === 'gallery' ? 'active' : ''; ?>">
                <h2 class="section-title">服务器截图</h2>
                <p class="section-subtitle">看看我们的服务器环境和玩家作品</p>
                
                <?php if (count($gallery_images) > 0): ?>
                    <div class="files-grid">
                        <?php foreach ($gallery_images as $image): ?>
                            <div class="file-card">
                                <?php if ($image['image_url']): ?>
                                <div class="file-image">
                                    <img src="<?php echo $image['image_url']; ?>" alt="<?php echo htmlspecialchars($image['caption']); ?>">
                                </div>
                                <?php endif; ?>
                                <div class="file-icon">🖼️</div>
                                <div class="file-card-content">
                                    <h3><?php echo htmlspecialchars($image['caption'] ?: '服务器截图'); ?></h3>
                                    <p class="file-description"><?php echo htmlspecialchars($image['caption']); ?></p>
                                    <div class="file-meta">
                                        <span class="file-size">图片展示</span>
                                        <span class="file-version">#<?php echo $image['id']; ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card card-center">
                        <p>暂无图片，请管理员在后台添加服务器截图。</p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- 特色功能 -->
            <section id="features" class="section <?php echo $current_section === 'features' ? 'active' : ''; ?>">
                <div class="container">
                    <h2 class="section-title">服务器特色</h2>
                    <p class="section-subtitle">体验我们服务器的独特功能和优质服务</p>
                    
                    <div class="server-info">
                        <?php foreach ($server_info as $info): ?>
                            <div class="feature-card">
                                <div class="feature-icon"><?php 
                                    $icon_map = [
                                        'fa-globe' => '🌐',
                                        'fa-users' => '👥',
                                        'fa-shield' => '🛡️',
                                        'fa-heart' => '❤️',
                                        'fa-star' => '⭐',
                                        'fa-gamepad' => '🎮'
                                    ];
                                    echo $icon_map[$info['icon']] ?? '🔧';
                                ?></div>
                                <h3><?php echo htmlspecialchars($info['title']); ?></h3>
                                <p><?php echo htmlspecialchars($info['description']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            
            <!-- 公告区域 -->
            <section id="announcements" class="section <?php echo $current_section === 'announcements' ? 'active' : ''; ?>">
                <h2 class="section-title">最新公告</h2>
                <p class="section-subtitle">了解服务器最新动态和重要通知</p>
                
                <div class="announcements-section">
                    <?php if (count($announcements) > 0): ?>
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="announcement-item">
                                <div class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                                <div class="announcement-content"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></div>
                                <div class="announcement-meta">
                                    <?php echo date('Y-m-d H:i', strtotime($announcement['created_at'])); ?>
                                    <?php if ($announcement['show_on_load']): ?>
                                        <span class="popup-badge">弹出显示</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="announcement-item">
                            <div class="announcement-title">暂无公告</div>
                            <div class="announcement-content">管理员还没有发布任何公告。</div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- 赞助区域 -->
            <section id="sponsor" class="section <?php echo $current_section === 'sponsor' ? 'active' : ''; ?>">
                <h2 class="section-title">支持我们</h2>
                <p class="section-subtitle">您的支持是我们持续改进服务器的动力</p>
                
                <div class="card card-center">
                    <div class="feature-icon">❤️</div>
                    <h3>感谢您考虑支持我们！</h3>
                    <p class="section-subtitle">您的赞助将用于服务器维护、功能开发和提供更好的游戏体验。</p>
                    
                    <div style="margin-top: 2rem;">
                        <a href="<?php echo $site_config['sponsor_link']; ?>" class="btn btn-primary" target="_blank">
                            <?php echo $site_config['sponsor_text']; ?>
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <!-- 页脚 -->
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-links">
                    <a href="<?php echo $site_config['join_link']; ?>" target="_blank" class="footer-link">
                        <span class="footer-icon">🎮</span>
                        <span><?php echo $site_config['join_text']; ?></span>
                    </a>
                    <a href="<?php echo $site_config['sponsor_link']; ?>" target="_blank" class="footer-link">
                        <span class="footer-icon">❤️</span>
                        <span><?php echo $site_config['sponsor_text']; ?></span>
                    </a>
                    <a href="admin_login.php" class="footer-link">
                        <span class="footer-icon">⚙️</span>
                        <span>管理后台</span>
                    </a>
                </div>
                <div class="footer-copyright">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo $site_config['site_name']; ?>. 保留所有权利.</p>
                    <?php if ($site_config['footer_icp']): ?>
                        <p>
                            <a href="https://beian.miit.gov.cn/" target="_blank" style="color: inherit; text-decoration: none;">
                                <?php echo $site_config['footer_icp']; ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    <?php if ($site_config['footer_public_security']): ?>
                        <p>
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
        </footer>
    </div>

    <!-- 背景图片容器 -->
    <div id="background-container">
        <div class="background-image active"></div>
        <div class="background-image"></div>
        <div class="background-image"></div>
    </div>

    <!-- 公告弹窗 -->
    <?php if (count($popup_announcements) > 0 && !isset($_COOKIE['announcement_closed'])): ?>
        <div id="announcementModal" class="modal">
            <div class="modal-content">
                <form method="POST" action="?section=<?php echo $current_section; ?>" class="close-form">
                    <button type="submit" name="close_announcement" class="close-modal">×</button>
                </form>
                <h3>最新公告</h3>
                <?php foreach ($popup_announcements as $announcement): ?>
                    <div class="announcement-item" style="margin-bottom: 1.5rem;">
                        <div class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                        <div class="announcement-content"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></div>
                    </div>
                <?php endforeach; ?>
                <div style="text-align: center; margin-top: 1.5rem;">
                    <form method="POST" action="?section=<?php echo $current_section; ?>">
                        <button type="submit" name="close_announcement" class="btn btn-primary">我知道了</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="script.js"></script>
    <script>
        // 页面加载完成后显示内容
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.body.classList.add('loaded');
            }, 2000);
        });
    </script>
</body>
</html>