# Dockerfile для Laravel приложения
FROM php:8.4-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    openssl \
    nodejs \
    npm \
    supervisor \
    cron \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip intl opcache pcntl sodium \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Создание рабочей директории
WORKDIR /var/www/html

# Копирование файлов composer и установка PHP зависимостей
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Копирование исходного кода
COPY . .

# Завершение установки Composer (выполнение скриптов)
RUN composer run-script post-autoload-dump

# Настройка прав доступа
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Создание символической ссылки для storage
RUN php artisan storage:link || true

# Создание директории для supervisor логов
RUN mkdir -p /var/log/supervisor

# Копирование конфигурации supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Копирование скрипта инициализации
COPY docker/init.sh /usr/local/bin/init.sh
RUN chmod +x /usr/local/bin/init.sh

# Открытие порта
EXPOSE 9000

# Запуск через supervisor
CMD ["/usr/local/bin/init.sh"]
