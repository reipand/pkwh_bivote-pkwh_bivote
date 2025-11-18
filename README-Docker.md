# Docker Setup untuk BiVote

## Prerequisites
- Docker
- Docker Compose

## Setup dan Menjalankan

### 1. Build dan Start Container
```bash
# Build dan start semua services
docker-compose up -d --build

# Atau dengan environment file
docker-compose --env-file docker.env up -d --build
```

### 2. Setup Host File (untuk multi-site)
Tambahkan ke `/etc/hosts` (Linux/Mac) atau `C:\Windows\System32\drivers\etc\hosts` (Windows):
```
127.0.0.1 bivote.local
127.0.0.1 www.bivote.local
```

### 3. Akses Aplikasi
- **Web Application**: http://localhost atau http://bivote.local
- **phpMyAdmin**: http://localhost:8080
- **MySQL**: localhost:3306

## Services

### MySQL Database
- **Host**: mysql (dalam container) atau localhost:3306 (dari host)
- **Database**: db_pemilos
- **User**: reip
- **Password**: bcst2526
- **Root Password**: rootpassword

### phpMyAdmin
- **URL**: http://localhost:8080
- **Username**: root
- **Password**: rootpassword

### Web Server (Apache + PHP 8.2)
- **URL**: http://localhost
- **Document Root**: /var/www/html
- **PHP Extensions**: mysqli, pdo_mysql, gd, zip, mbstring, xml

## Menambah Web Application Lain

### 1. Tambahkan Virtual Host
Edit `docker/apache/sites-available/multi-site.conf`:
```apache
<VirtualHost *:80>
    ServerName app2.local
    DocumentRoot /var/www/html/app2
    
    <Directory /var/www/html/app2>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/app2_error.log
    CustomLog ${APACHE_LOG_DIR}/app2_access.log combined
</VirtualHost>
```

### 2. Tambahkan ke Host File
```
127.0.0.1 app2.local
```

### 3. Restart Container
```bash
docker-compose restart webserver
```

## Database Connection

Update `config/koneksi.php` untuk Docker:
```php
$host = "mysql"; // Gunakan service name dari docker-compose
$user = "reip";
$password = "bcst2526";
$database = "db_pemilos";
```

## Commands Berguna

```bash
# Lihat logs
docker-compose logs -f

# Masuk ke container
docker-compose exec webserver bash
docker-compose exec mysql bash

# Stop semua services
docker-compose down

# Stop dan hapus volumes (HATI-HATI: data akan hilang)
docker-compose down -v

# Rebuild container
docker-compose up -d --build --force-recreate
```

## Troubleshooting

### Database Connection Error
1. Pastikan MySQL container sudah running: `docker-compose ps`
2. Cek logs: `docker-compose logs mysql`
3. Test koneksi dari web container: `docker-compose exec webserver php -r "new mysqli('mysql', 'reip', 'bcst2526', 'db_pemilos');"`

### Permission Issues
```bash
# Fix permissions
docker-compose exec webserver chown -R www-data:www-data /var/www/html
docker-compose exec webserver chmod -R 755 /var/www/html
```

### Port Conflicts
Jika port 80, 3306, atau 8080 sudah digunakan:
1. Edit `docker-compose.yml`
2. Ubah port mapping, contoh: `"8081:80"` untuk web server
