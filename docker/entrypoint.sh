#!/bin/sh
# ============================================================================
# [RENDER-FIX 3 lanjutan] Entrypoint — file BARU
# SEBELUM  : Apache listen :80 statis → di Render kontainer langsung FAIL
#           ("No open ports detected") karena Render memeriksa $PORT.
# SESUDAH : Tulis ulang ports.conf + 000-default.conf ke ${PORT:-10000}
#           setiap container start, lalu jalankan apache2-foreground.
# ============================================================================
set -e
PORT="${PORT:-10000}"
echo "[PortoPro] starting Apache on port ${PORT}..."

# Apache ports.conf berisi "Listen 80" → ganti ke $PORT
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf || true
# VirtualHost *:80 → *:PORT
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf || true
# Pastikan ServerName ada agar tidak warning FQDN
grep -q "ServerName" /etc/apache2/apache2.conf || echo "ServerName localhost" >> /etc/apache2/apache2.conf

exec apache2-foreground
