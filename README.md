# 我的世界服务器网站安装文档
本项目才用GPL协议开源

## 📋 环境要求

| 组件 | 版本要求 | 备注 |
|------|----------|------|
| PHP | 7.0 或更高版本 | 推荐 PHP 7.4+ |
| MySQL | 5.6 或更高版本 | 推荐 MySQL 5.7+ |
| Web服务器 | Apache 或 Nginx | 两者均可 |
| PHP扩展 | PDO MySQL | 必须安装 |

---

## 🛠️ 安装方法

### 方法一：使用宝塔面板安装
3
#### 1. 安装宝塔面板

```bash
# CentOS
yum install -y wget && wget -O install.sh http://download.bt.cn/install/install_6.0.sh && sh install.sh

# Ubuntu/Debian
wget -O install.sh http://download.bt.cn/install/install-ubuntu_6.0.sh && sudo bash install.sh
```

#### 2. 配置环境

1. 登录宝塔面板
2. 在"软件商店"中安装：
   - Nginx 或 Apache
   - MySQL 5.7+
   - PHP 7.4+（推荐）
   - phpMyAdmin（可选）

#### 3. 创建网站

1. 点击"网站" → "添加站点"
2. 填写域名（如果没有域名，可使用IP地址）
3. 选择创建MySQL数据库，记录数据库信息
4. 选择PHP版本（7.4+）

#### 4. 上传文件

1. 进入网站根目录
2. 删除默认文件
3. 上传所有项目文件

#### 5. 设置权限

```bash
# 通过宝塔文件管理器或SSH执行
chmod -R 755 ./
chown -R www:www ./
```

#### 6. 访问安装向导

在浏览器中访问您的域名，按照安装向导完成配置。

---

### 方法二：使用1Panel面板安装

#### 1. 安装1Panel

```bash
curl -sSL https://resource.fit2cloud.com/1panel/package/quick_start.sh -o quick_start.sh && sudo bash quick_start.sh
```

#### 2. 创建网站环境

1. 登录1Panel面板
2. 进入"网站"页面
3. 创建运行环境：
   - 选择PHP 7.4+
   - 选择MySQL 5.7+
   - 选择Nginx

#### 3. 创建网站

1. 点击"创建网站"
2. 填写主域名
3. 选择刚才创建的环境
4. 开启"创建数据库"选项

#### 4. 部署代码

1. 进入网站目录
2. 上传所有项目文件
3. 设置正确的文件权限

#### 5. 完成安装

访问您的域名，按照安装向导完成配置。

---

### 方法三：纯命令行安装

#### 1. 安装环境（Ubuntu/Debian为例）

```bash
# 更新系统
sudo apt update && sudo apt upgrade -y

# 安装Nginx
sudo apt install nginx -y

# 安装MySQL
sudo apt install mysql-server -y

# 安装PHP和扩展
sudo apt install php-fpm php-mysql php-curl php-json php-mbstring php-xml -y

# 启动服务
sudo systemctl start nginx
sudo systemctl start mysql
sudo systemctl start php7.4-fpm  # 根据实际PHP版本调整

# 设置开机自启
sudo systemctl enable nginx
sudo systemctl enable mysql
sudo systemctl enable php7.4-fpm
```

#### 2. 配置MySQL

```bash
# 安全配置MySQL
sudo mysql_secure_installation

# 登录MySQL
sudo mysql -u root -p

# 创建数据库和用户
CREATE DATABASE minecraft_site;
CREATE USER 'minecraft_user'@'localhost' IDENTIFIED BY '你的密码';
GRANT ALL PRIVILEGES ON minecraft_site.* TO 'minecraft_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 3. 配置Nginx

创建配置文件 `/etc/nginx/sites-available/minecraft-site`：

```nginx
server {
    listen 80;
    server_name 你的域名或IP;
    root /var/www/minecraft-site;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

启用站点：

```bash
sudo ln -s /etc/nginx/sites-available/minecraft-site /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 4. 部署代码

```bash
# 创建网站目录
sudo mkdir -p /var/www/minecraft-site
sudo chown -R $USER:$USER /var/www/minecraft-site

# 上传代码到目录
cd /var/www/minecraft-site
# 通过FTP或SCP上传所有文件

# 设置权限
sudo chown -R www-data:www-data /var/www/minecraft-site
sudo chmod -R 755 /var/www/minecraft-site
sudo find /var/www/minecraft-site -type f -exec chmod 644 {} \;
```

#### 5. 完成安装

访问您的服务器IP或域名，按照安装向导完成配置。

---

### 方法四：纯命令行安装SSL证书（使用Let's Encrypt）

#### 1. 安装Certbot

```bash
# Ubuntu/Debian
sudo apt install certbot python3-certbot-nginx -y

# CentOS/RHEL
sudo yum install certbot python3-certbot-nginx -y
```

#### 2. 获取SSL证书

```bash
# 为域名获取证书
sudo certbot --nginx -d 你的域名.com -d www.你的域名.com

# 或者使用独立模式（如果80端口被占用）
sudo certbot certonly --standalone -d 你的域名.com -d www.你的域名.com
```

#### 3. 自动续期设置

```bash
# 测试续期
sudo certbot renew --dry-run

# 设置自动续期（每天检查两次）
echo "0 0,12 * * * root /usr/bin/certbot renew -q" | sudo tee -a /etc/crontab > /dev/null
```

#### 4. 手动配置SSL（如果需要）

如果自动配置失败，可以手动编辑Nginx配置：

```nginx
server {
    listen 443 ssl http2;
    server_name 你的域名.com;
    
    ssl_certificate /etc/letsencrypt/live/你的域名.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/你的域名.com/privkey.pem;
    
    # SSL配置
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    root /var/www/minecraft-site;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# HTTP重定向到HTTPS
server {
    listen 80;
    server_name 你的域名.com;
    return 301 https://$server_name$request_uri;
}
```

重新加载Nginx：

```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🔒 安装后配置

### 安全建议

1. **删除安装文件**：
   ```bash
   rm -f install.php
   ```

2. **保护配置文件**：
   ```bash
   chmod 644 db_config.php
   chmod 644 config.php
   ```

3. **定期备份**：
   - 数据库：使用mysqldump或面板工具
   - 网站文件：定期打包下载

---

## 🐛 故障排除

### 常见问题

1. **安装向导无法访问**：
   - 检查文件权限
   - 确认PHP已正确安装和配置
   - 查看Nginx/Apache错误日志

2. **数据库连接错误**：
   - 确认数据库信息正确
   - 检查MySQL服务是否运行
   - 确认数据库用户有足够权限

3. **页面显示异常**：
   - 检查PHP错误日志
   - 确认所有必需文件已上传
   - 验证文件完整性

---

## 💼 付费技术支持

如果您在安装过程中遇到困难，或者需要定制开发、功能扩展等服务，我们提供付费技术支持：

**QQ: 2088264797**

### 服务内容包括：

- 🚀 远程安装协助
- ⚙️ 服务器环境配置
- 🔧 功能定制开发
- 🔍 问题排查和修复
- 🚀 性能优化建议

请添加QQ时备注"**我的世界网站技术支持**"，我们会尽快为您提供专业服务。

---

*文档最后更新：2025年10月*