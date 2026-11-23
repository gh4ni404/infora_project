#!/usr/bin/env bash
set -e

echo "🚀 [1/6] Mengaktifkan Mode Pemeliharaan..."
./dev artisan down || true

echo "📥 [2/6] Menarik update dari GitHub..."
git pull origin main

echo "📦 [3/6] Menginstal dependensi Composer..."
./dev composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "🎨 [4/6] Membangun Frontend Assets (Vite)..."
./dev npm install --no-audit --prefer-offline
./dev build

echo "🗄️ [5/6] Menjalankan Migrasi Database..."
./dev artisan migrate --force

echo "⚡ [6/6] Optimasi Cache & Hak Akses..."
./dev clear
./dev artisan optimize
chmod -R 775 storage bootstrap/cache

echo "✨ Menonaktifkan Mode Pemeliharaan..."
./dev artisan up

echo "🎉 Deployment selesai dan aplikasi siap digunakan!"
