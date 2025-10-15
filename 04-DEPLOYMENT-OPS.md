# 04-DEPLOYMENT-OPS.md
# Bukupasar — Deployment & Operations Guide

**Deployment ke Laragon (Development) dan aaPanel (Production)**

---

## 📋 Table of Contents

1. [Prerequisites Checklist](#prerequisites-checklist)
2. [Local Development (Laragon)](#local-development-laragon)
3. [Production Deployment (aaPanel)](#production-deployment-aapanel)
4. [Backup & Restore](#backup--restore)
5. [Monitoring](#monitoring)
6. [Troubleshooting](#troubleshooting)

---

## 1. Prerequisites Checklist

### ✅ Already Ready (Per User Confirmation)
- [x] Laragon installed (PHP 8.2+, MySQL 8, Nginx)
- [x] Node.js 18+ installed
- [x] Git installed
- [x] Composer installed

### Additional Needs for Production
- [ ] Domain/subdomain untuk aplikasi
- [ ] VPS dengan aaPanel installed
- [ ] SSH access ke VPS
- [ ] SSL certificate (Let's Encrypt via aaPanel)

---

## 2. Local Development (Laragon)

### Step 1: Setup Laravel Backend

```bash
# Navigate to Laragon www directory
cd C:\laragon\www

# Clone or create Laravel project (already done in 02-BACKEND-GUIDE.md)
cd bukupasar-backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure database in .env
# DB_DATABASE=bukupasar_dev
# DB_USERNAME=root
# DB_PASSWORD=

# Create database
# Via Laragon: Click Database → Create new database: bukupasar_dev

# Run migrations
php artisan migrate

# Seed initial data (if seeder exists)
php artisan db:seed

# Create storage link
php artisan storage:link

# Start Laravel dev server (optional, Laragon auto-serves)
php artisan serve
```

**Access:** `http://bukupasar-backend.test`

### Step 2: Setup Next.js Frontend

```bash
# Navigate to frontend project
cd C:\laragon\www\bukupasar-frontend

# Install dependencies
npm install

# Configure environment
# Create .env.local:
NEXT_PUBLIC_API_URL=http://bukupasar-backend.test/api

# Start dev server
npm run dev
```

**Access:** `http://localhost:3000`

### Step 3: Local Testing

**Test Backend:**
1. Access Filament: `http://bukupasar-backend.test/admin`
2. Login with admin user
3. Test CRUD operations

**Test Frontend:**
1. Access: `http://localhost:3000`
2. Login via SPA
3. Test transaction input flow
4. Verify API connection

---

## 3. Production Deployment (aaPanel)

### Prerequisites Setup on VPS

**Login to VPS:**
```bash
ssh root@your-vps-ip
```

**Install aaPanel (if not installed):**
```bash
# Ubuntu/Debian
wget -O install.sh http://www.aapanel.com/script/install-ubuntu_6.0_en.sh && bash install.sh

# After installation, note the:
# - aaPanel URL
# - Username
# - Password
```

**Install Required Software via aaPanel:**
1. Login to aaPanel web interface
2. Install:
   - PHP 8.2+
   - MySQL 8.0
   - Nginx
   - Node.js 18+
   - PM2
   - Composer (via SSH or aaPanel terminal)

---

### Deploy Laravel Backend

**Step 1: Prepare VPS Directory**

```bash
# SSH to VPS
ssh root@your-vps-ip

# Create directory for project
mkdir -p /www/wwwroot/bukupasar-backend
cd /www/wwwroot/bukupasar-backend
```

**Step 2: Upload Code**

**Option A: Git Clone (Recommended)**
```bash
# Clone repository
git clone https://github.com/your-repo/bukupasar-backend.git .

# Or if using SSH key
git clone git@github.com:your-repo/bukupasar-backend.git .
```

**Option B: Upload via FTP/SFTP**
- Use FileZilla or WinSCP
- Upload all files from local project to `/www/wwwroot/bukupasar-backend`

**Step 3: Install Dependencies**

```bash
cd /www/wwwroot/bukupasar-backend

# Install Composer dependencies (production mode)
composer install --optimize-autoloader --no-dev
```

**Step 4: Configure Environment**

```bash
# Copy environment file
cp .env.example .env

# Edit .env for production
nano .env

# Production settings:
APP_NAME=Bukupasar
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bukupasar.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bukupasar_prod
DB_USERNAME=bukupasar_user
DB_PASSWORD=strong_password_here

# Generate app key
php artisan key:generate

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Step 5: Database Setup**

**Via aaPanel:**
1. Go to Database → Add Database
2. Database name: `bukupasar_prod`
3. User: `bukupasar_user`
4. Password: (strong password)

**Run Migrations:**
```bash
php artisan migrate --force
```

**Step 6: Set Permissions**

```bash
# Set ownership (www user is default for aaPanel)
chown -R www:www /www/wwwroot/bukupasar-backend

# Set permissions
chmod -R 755 /www/wwwroot/bukupasar-backend
chmod -R 775 /www/wwwroot/bukupasar-backend/storage
chmod -R 775 /www/wwwroot/bukupasar-backend/bootstrap/cache

# Storage link
php artisan storage:link
```

**Step 7: Configure Nginx (via aaPanel)**

1. In aaPanel → Website → Add Site
2. Domain: `api.bukupasar.yourdomain.com` (or main domain)
3. Root directory: `/www/wwwroot/bukupasar-backend/public`
4. PHP Version: 8.2

**Manual Nginx Config (if needed):**
```nginx
server {
    listen 80;
    server_name api.bukupasar.yourdomain.com;
    root /www/wwwroot/bukupasar-backend/public;
    
    index index.php index.html;
    charset utf-8;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi-82.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Step 8: SSL Certificate**

1. In aaPanel → Website → Select site
2. Click SSL
3. Choose "Let's Encrypt"
4. Apply
5. Force HTTPS redirect

---

### Deploy Next.js Frontend

**Step 1: Prepare Directory**

```bash
mkdir -p /www/wwwroot/bukupasar-frontend
cd /www/wwwroot/bukupasar-frontend
```

**Step 2: Upload Code**

```bash
# Clone repository
git clone https://github.com/your-repo/bukupasar-frontend.git .
```

**Step 3: Configure Environment**

```bash
# Create .env.local
nano .env.local

# Production API URL
NEXT_PUBLIC_API_URL=https://api.bukupasar.yourdomain.com/api
NEXT_PUBLIC_APP_NAME=Bukupasar
```

**Step 4: Build Production**

```bash
# Install dependencies
npm ci

# Build for production
npm run build

# Test build locally
npm start
```

**Step 5: Run with PM2**

```bash
# Install PM2 globally (if not installed)
npm install -g pm2

# Start Next.js with PM2
pm2 start npm --name "bukupasar-frontend" -- start

# Save PM2 process list
pm2 save

# Setup PM2 to start on boot
pm2 startup

# Check status
pm2 status
pm2 logs bukupasar-frontend
```

**Step 6: Configure Nginx Reverse Proxy**

1. In aaPanel → Website → Add Site
2. Domain: `bukupasar.yourdomain.com`
3. Root directory: (any, will be overridden)
4. Click "Config" to edit Nginx

**Nginx Config:**
```nginx
server {
    listen 80;
    server_name bukupasar.yourdomain.com;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name bukupasar.yourdomain.com;
    
    # SSL certificates (managed by aaPanel)
    ssl_certificate /www/server/panel/vhost/cert/bukupasar/fullchain.pem;
    ssl_certificate_key /www/server/panel/vhost/cert/bukupasar/privkey.pem;
    
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }
}
```

**Step 7: Apply SSL**

Same as Laravel: Let's Encrypt via aaPanel SSL tab.

---

### Post-Deployment Checks

**Backend:**
- [ ] `https://api.bukupasar.yourdomain.com` accessible
- [ ] `https://api.bukupasar.yourdomain.com/admin` Filament login works
- [ ] API endpoints respond correctly
- [ ] Database connections working

**Frontend:**
- [ ] `https://bukupasar.yourdomain.com` loads
- [ ] Login functionality works
- [ ] API integration successful
- [ ] Mobile responsive

---

## 4. Backup & Restore

### Automated Daily Backup (via aaPanel)

**Database Backup:**
1. aaPanel → Database
2. Click database name → Backup
3. Schedule: Daily at 2 AM
4. Retention: 7 days

**Manual Backup Script:**
```bash
#!/bin/bash
# Save as /root/backup-bukupasar.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/www/backup/bukupasar"
DB_NAME="bukupasar_prod"
DB_USER="bukupasar_user"
DB_PASS="your_password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup uploads
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz /www/wwwroot/bukupasar-backend/storage/app/public

# Delete backups older than 7 days
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: $DATE"
```

**Setup Cron:**
```bash
# Edit crontab
crontab -e

# Add line:
0 2 * * * /root/backup-bukupasar.sh >> /root/backup.log 2>&1
```

### Restore from Backup

**Restore Database:**
```bash
# Decompress
gunzip /www/backup/bukupasar/db_20250115_020000.sql.gz

# Import
mysql -u bukupasar_user -p bukupasar_prod < /www/backup/bukupasar/db_20250115_020000.sql
```

**Restore Uploads:**
```bash
tar -xzf /www/backup/bukupasar/uploads_20250115_020000.tar.gz -C /
```

---

## 5. Monitoring

### Health Check Endpoints

**Backend Health Check:**
Create route in `routes/web.php`:
```php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'timestamp' => now()->toIso8601String(),
    ]);
});
```

**Monitor with Uptime Robot (Free):**
1. Sign up: https://uptimerobot.com
2. Add monitor: `https://api.bukupasar.yourdomain.com/health`
3. Interval: 5 minutes
4. Alert: Email when down

### Log Monitoring

**Laravel Logs:**
```bash
# View latest logs
tail -f /www/wwwroot/bukupasar-backend/storage/logs/laravel.log

# Clear old logs
truncate -s 0 /www/wwwroot/bukupasar-backend/storage/logs/laravel.log
```

**PM2 Logs:**
```bash
# View Next.js logs
pm2 logs bukupasar-frontend

# Clear logs
pm2 flush
```

**Nginx Logs:**
```bash
# Access log
tail -f /www/wwwlogs/bukupasar.yourdomain.com.log

# Error log
tail -f /www/wwwlogs/bukupasar.yourdomain.com.error.log
```

---

## 6. Troubleshooting

### Common Issues & Solutions

**1. "500 Internal Server Error" on Laravel**

**Cause:** Permissions or config cache

**Solution:**
```bash
cd /www/wwwroot/bukupasar-backend

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Check permissions
chmod -R 775 storage bootstrap/cache
chown -R www:www storage bootstrap/cache

# Check error log
tail -f storage/logs/laravel.log
```

---

**2. "Database Connection Failed"**

**Cause:** Wrong credentials or MySQL not running

**Solution:**
```bash
# Check MySQL status
systemctl status mysql

# Test connection
mysql -u bukupasar_user -p

# Verify .env credentials match database user
cat .env | grep DB_
```

---

**3. Next.js Not Loading (502 Bad Gateway)**

**Cause:** PM2 process not running

**Solution:**
```bash
# Check PM2 status
pm2 status

# If stopped, restart
pm2 restart bukupasar-frontend

# If not in list
cd /www/wwwroot/bukupasar-frontend
pm2 start npm --name "bukupasar-frontend" -- start
pm2 save

# Check logs
pm2 logs bukupasar-frontend --lines 50
```

---

**4. SSL Certificate Issues**

**Cause:** Let's Encrypt renewal failed

**Solution:**
- aaPanel → Website → SSL → Re-apply Let's Encrypt
- Ensure port 80 is open for ACME challenge
- Check domain DNS points to VPS IP

---

**5. "Permission Denied" on File Upload**

**Cause:** Wrong folder permissions

**Solution:**
```bash
chmod -R 775 /www/wwwroot/bukupasar-backend/storage
chown -R www:www /www/wwwroot/bukupasar-backend/storage
```

---

**6. API CORS Error from Frontend**

**Cause:** CORS not configured

**Solution:**
Edit `config/cors.php` in Laravel:
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['https://bukupasar.yourdomain.com'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'supports_credentials' => true,
```

Then:
```bash
php artisan config:cache
```

---

## 📝 Deployment Checklist

**Pre-Deployment:**
- [ ] Code tested locally
- [ ] Environment variables prepared
- [ ] Database credentials ready
- [ ] Domain/subdomain configured

**Backend Deployment:**
- [ ] Code uploaded
- [ ] Dependencies installed
- [ ] .env configured
- [ ] Migrations run
- [ ] Permissions set
- [ ] Nginx configured
- [ ] SSL applied
- [ ] Health check accessible

**Frontend Deployment:**
- [ ] Code uploaded
- [ ] Environment configured
- [ ] Production build successful
- [ ] PM2 running
- [ ] Nginx proxy configured
- [ ] SSL applied
- [ ] Site accessible

**Post-Deployment:**
- [ ] Backup script configured
- [ ] Monitoring setup
- [ ] Admin user created
- [ ] Test complete user flow
- [ ] Document credentials safely

---

**Document Status:** ✅ Complete | **Last Updated:** 2025-01-15
