# ============================================================================
# [RENDER-FIX 3] Dockerfile — file BARU (wajib agar bisa deploy di Render)
# ----------------------------------------------------------------------------
# SEBELUM  : tidak ada Dockerfile → Render tidak tahu PHP versi berapa,
#           extension apa, dan port berapa (Apache default :80, Render minta
#           $PORT dinamis).
# SESUDAH : php:8.3-apache + mysqli + mod_rewrite + entrypoint yang menulis
#           ulang Listen/VirtualHost ke $PORT + php.ini production.
# ALASAN  : reproducible di lokal & Render; tanpa ini deploy gagal start.
# CARA PAKAI LOKAL:
#   docker build -t portopro . && docker run -p 8000:10000 --env-file .env portopro
# ============================================================================
FROM php:8.3-apache

# Extension yang dipakai app: mysqli (koneksi DB) + curl (upload Cloudinary).
# ca-certificates WAJIB: connection.php memakai CA bundle OS
# (/etc/ssl/certs/ca-certificates.crt) untuk TLS verifikasi-penuh ke TiDB.
RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates libcurl4-openssl-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql curl \
    && docker-php-ext-enable mysqli \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# php.ini production: sembunyikan error ke pengunjung, batasi upload 8MB
# (mengimbangi validasi JS 8MB di add_project.php).
COPY docker/php.ini /usr/local/etc/php/conf.d/portopro.ini

# Kode aplikasi
COPY . /var/www/html/
# Folder upload lokal tetap perlu writable untuk mode fallback dev
RUN mkdir -p /var/www/html/media/image/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod 775 /var/www/html/media/image/uploads

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Render memberi $PORT dinamis (default 10000). EXPOSE hanya dokumentasi.
EXPOSE 10000
CMD ["/entrypoint.sh"]
