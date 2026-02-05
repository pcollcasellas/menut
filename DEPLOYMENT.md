# Production Deployment Guide for Menut

This guide covers deploying the Menut application to an Ubuntu VPS server.

## Prerequisites

- Ubuntu 22.04 or 24.04 LTS VPS
- Root or sudo access
- Domain name pointing to your server's IP (for SSL)
- **Optional but Recommended:** Cloudflare account with domain configured (see Cloudflare Setup section)

## Cloudflare Setup (Recommended)

If you're using Cloudflare for your domain, follow these steps before deployment:

### 1. Configure DNS in Cloudflare

1. Log in to [Cloudflare Dashboard](https://dash.cloudflare.com)
2. Select your domain
3. Go to **DNS** → **Records**
4. Add an **A record**:
   - **Type:** `A`
   - **Name:** `@` (for root domain) or `www` (for www subdomain)
   - **IPv4 address:** Your VPS IP address
   - **Proxy status:** Toggle **ON** (orange cloud) - Recommended
   - **TTL:** Auto
5. Click **Save**

Add both `@` and `www` records pointing to your VPS IP.

### 2. Configure SSL/TLS Settings

1. Go to **SSL/TLS** → **Overview**
2. Set encryption mode to **"Full (strict)"** (requires Let's Encrypt on server)
3. Go to **SSL/TLS** → **Edge Certificates**
4. Enable **"Always Use HTTPS"**

### 3. Wait for DNS Propagation

DNS changes can take a few minutes to propagate. Verify with:
```bash
dig your-domain.com
# Should show your VPS IP address
```

**Note:** With Cloudflare Proxy enabled (orange cloud), your server will receive traffic through Cloudflare's network, providing DDoS protection, CDN caching, and better performance. The deployment steps below work with or without Cloudflare.

## Step 1: Initial Server Setup

### 1.1 Update System Packages

```bash
sudo apt update && sudo apt upgrade -y
```

### 1.2 Create Application User

```bash
sudo adduser --disabled-password --gecos "" menut
sudo usermod -aG sudo menut
```

### 1.3 Set Up SSH Key Authentication (Recommended)

```bash
# On your local machine, copy your SSH key
ssh-copy-id menut@your-server-ip

# Or manually add your public key to ~/.ssh/authorized_keys
```

## Step 2: Install Required Software

### 2.1 Install PHP 8.2+ and Extensions

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y \
    php8.2-fpm \
    php8.2-cli \
    php8.2-common \
    php8.2-zip \
    php8.2-gd \
    php8.2-mbstring \
    php8.2-curl \
    php8.2-xml \
    php8.2-bcmath \
    php8.2-sqlite3 \
    php8.2-intl \
    php8.2-redis
```

### 2.2 Install Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### 2.3 Install Node.js and npm

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### 2.4 Install Nginx

```bash
sudo apt install -y nginx
```

### 2.5 Install Redis (Optional but Recommended)

```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```


## Step 3: Deploy Application

### 3.1 Clone Repository

```bash
sudo mkdir -p /var/www
cd /var/www
sudo git clone https://github.com/your-username/menut.git
sudo chown -R menut:menut /var/www/menut
cd /var/www/menut
```

### 3.2 Install Dependencies

```bash
composer install --optimize-autoloader --no-dev
npm ci
npm run build
```

### 3.3 Configure Environment

```bash
cp .env.example .env
nano .env
```

Update the following key values:

```env
APP_NAME=Menut
APP_ENV=production
APP_KEY=                    # Will be generated in next step
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/menut/database/database.sqlite

SESSION_DRIVER=database
QUEUE_CONNECTION=sync        # Using sync since no queue jobs are used
CACHE_STORE=database         # or redis if installed

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 3.4 Generate Application Key

```bash
php artisan key:generate
```

### 3.5 Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/menut/storage
sudo chown -R www-data:www-data /var/www/menut/bootstrap/cache
sudo chown -R www-data:www-data /var/www/menut/database
sudo chmod -R 775 /var/www/menut/storage
sudo chmod -R 775 /var/www/menut/bootstrap/cache
sudo chmod -R 775 /var/www/menut/database
```

**Note:** The SQLite database file will be created automatically when migrations run. We just need to ensure the `database` directory is writable by the web server.

### 3.6 Run Migrations

```bash
php artisan migrate --force
```

## Step 4: Configure Nginx

### 4.1 Create Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/menut
```

Add the following configuration:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/menut/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 4.2 Enable Site

```bash
sudo ln -s /etc/nginx/sites-available/menut /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## Step 5: Install SSL Certificate (Let's Encrypt)

**Important:** If you're using Cloudflare Proxy (orange cloud), you need to temporarily disable it during certificate issuance, then re-enable it afterward.

### 5.1 Temporarily Disable Cloudflare Proxy (if using Proxy mode)

1. Go to Cloudflare Dashboard → DNS → Records
2. Find your domain's A record (e.g., `menut`)
3. Click the **orange cloud** icon to turn it **gray** (DNS only, not proxied)
4. Wait 2-3 minutes for DNS to update
5. Verify DNS has updated:
   ```bash
   dig @8.8.8.8 your-domain.com
   ```
   You should see your VPS IP address instead of Cloudflare IPs

### 5.2 Install Certbot and Get Certificate

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

**Note:** Only include `www` if you have a `www` subdomain configured. For subdomains like `menut.pau-coll.com`, you typically don't need `www`.

Follow the prompts:
- Enter your email address
- Agree to Terms of Service
- Certbot will automatically configure Nginx for HTTPS

### 5.3 Re-enable Cloudflare Proxy

After the certificate is successfully issued:

1. Go back to Cloudflare Dashboard → DNS → Records
2. Click the **gray cloud** icon to turn it **orange** (proxied) again
3. Wait 1-2 minutes for DNS to update

The SSL certificate will continue to work perfectly with Cloudflare Proxy enabled.

### 5.4 Verify Auto-renewal

```bash
sudo certbot renew --dry-run
```

This verifies that certificate auto-renewal is configured correctly.

## Step 6: Turn Cloudflare Proxy Back ON (if disabled)

If you temporarily disabled Cloudflare Proxy in Step 5.1, make sure to re-enable it now:

1. Go to Cloudflare Dashboard → DNS → Records
2. Find your domain's A record
3. Click the **gray cloud** icon to turn it **orange** (proxied)
4. Wait 1-2 minutes for DNS to update

Your SSL certificate will work perfectly with Cloudflare Proxy enabled.

## Step 7: Optimize Laravel for Production

```bash
cd /var/www/menut
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## Step 8: Security Hardening

### 8.1 Configure Firewall

**Option A: Basic Firewall (if not using Cloudflare Proxy)**

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

**Option B: Cloudflare-Only Firewall (Recommended if using Cloudflare Proxy)**

This restricts HTTP/HTTPS access to Cloudflare IPs only, providing better security:

```bash
# Allow SSH first
sudo ufw allow OpenSSH

# Download Cloudflare IP ranges and create firewall rules
curl -s https://www.cloudflare.com/ips-v4 > /tmp/cloudflare-ips.txt

# Allow HTTP (port 80) from Cloudflare IPs
while read -r ip; do
    sudo ufw allow from "$ip" to any port 80 comment 'Cloudflare HTTP'
done < /tmp/cloudflare-ips.txt

# Allow HTTPS (port 443) from Cloudflare IPs
while read -r ip; do
    sudo ufw allow from "$ip" to any port 443 comment 'Cloudflare HTTPS'
done < /tmp/cloudflare-ips.txt

# Enable firewall
sudo ufw enable

# Clean up
rm /tmp/cloudflare-ips.txt
```

**Note:** If you're using Cloudflare Proxy, Option B is recommended to prevent direct access to your server IP and only allow traffic through Cloudflare.

### 8.2 Disable PHP Version Disclosure

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

Set:
```ini
expose_php = Off
```

### 8.3 Secure PHP-FPM

```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

Ensure:
```ini
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

## Step 9: Monitoring and Logs

### 9.1 View Application Logs

```bash
tail -f /var/www/menut/storage/logs/laravel.log
```

### 9.2 View Nginx Logs

```bash
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
```

## Step 10: Deployment Script (Optional)

Create a deployment script for future updates:

```bash
nano /var/www/menut/deploy.sh
```

```bash
#!/bin/bash

cd /var/www/menut

# Pull latest changes
git pull origin main

# Install/update dependencies
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache config
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "Deployment complete!"
```

Make it executable:
```bash
chmod +x /var/www/menut/deploy.sh
```

## Post-Deployment Checklist

- [ ] Cloudflare DNS configured (if using Cloudflare)
- [ ] Cloudflare Proxy temporarily disabled for SSL certificate issuance
- [ ] SSL certificate installed successfully
- [ ] Cloudflare Proxy re-enabled (orange cloud)
- [ ] Cloudflare SSL/TLS set to "Full" or "Full (strict)"
- [ ] Application accessible via HTTPS
- [ ] Database directory has proper write permissions
- [ ] Database migrations completed (SQLite file will be created automatically)
- [ ] SSL certificate installed and auto-renewal configured
- [ ] Environment variables set correctly (especially `APP_URL`)
- [ ] File permissions configured
- [ ] Firewall configured (Cloudflare-only if using Proxy mode)
- [ ] Application logs accessible
- [ ] Test user registration/login

## Troubleshooting

### 502 Bad Gateway
- Check PHP-FPM status: `sudo systemctl status php8.2-fpm`
- Check Nginx error logs: `sudo tail -f /var/log/nginx/error.log`

### Permission Denied Errors
- Verify ownership: `ls -la /var/www/menut/storage`
- Fix permissions: `sudo chown -R www-data:www-data /var/www/menut/storage`
- Check database directory permissions: `ls -la /var/www/menut/database`
- Fix database directory permissions: `sudo chown -R www-data:www-data /var/www/menut/database && sudo chmod -R 775 /var/www/menut/database`
- If database file exists but has wrong permissions: `sudo chown www-data:www-data /var/www/menut/database/database.sqlite && sudo chmod 664 /var/www/menut/database/database.sqlite`

### Assets Not Loading
- Rebuild assets: `npm run build`
- Clear cache: `php artisan cache:clear`

## Maintenance Commands

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan optimize

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```
