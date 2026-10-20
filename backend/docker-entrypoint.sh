#!/bin/sh
set -e

# Tunggu database siap, lalu migrasi + seed akun admin (idempotent)
until php artisan migrate --force; do
  echo "Menunggu database siap..."
  sleep 3
done
php artisan db:seed --force

exec "$@"
