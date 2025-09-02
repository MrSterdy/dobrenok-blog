#!/bin/bash

# Скрипт инициализации Laravel приложения для Docker

set -e

echo "🚀 Инициализация Laravel приложения..."

# Проверка существования .env файла
if [ ! -f .env ]; then
    echo "⚠️  Файл .env не найден. Копирование из docker/env.example..."
    cp docker/env.example .env
    echo "✅ Файл .env создан. Обязательно настройте параметры подключения к БД!"
fi

# Генерация ключа приложения если его нет
if grep -q "APP_KEY=base64:GENERATE_NEW_KEY_HERE" .env; then
    echo "🔑 Генерация ключа приложения..."
    php artisan key:generate --force
    echo "✅ Ключ приложения сгенерирован"
fi

# Выполнение миграций
echo "🗄️  Проверка и выполнение миграций..."
php artisan migrate --force --no-interaction

# Очистка кеша
echo "🧹 Очистка кеша..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Оптимизация для production
if [ "$APP_ENV" = "production" ]; then
    echo "⚡ Оптимизация для production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Создание символической ссылки для storage
echo "🔗 Создание символической ссылки для storage..."
php artisan storage:link || echo "Символическая ссылка уже существует"

# Установка прав доступа
echo "🔒 Настройка прав доступа..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "✅ Инициализация завершена!"

# Запуск supervisor для управления процессами
echo "🔄 Запуск supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
