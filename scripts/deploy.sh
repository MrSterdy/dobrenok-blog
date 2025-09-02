#!/bin/bash

# Скрипт деплоя для Dobrenok Blog
# Используется для автоматизации развертывания через GitHub Actions

set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Функции для вывода
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Переменные
PROJECT_PATH=${PROJECT_PATH:-"/var/www/dobrenok-blog"}
DOCKER_COMPOSE_FILE="docker-compose.prod.yml"
IMAGE_NAME=${IMAGE_NAME:-"ghcr.io/mrsterdy/dobrenok-blog:latest"}
BACKUP_DIR="${PROJECT_PATH}/backups"

# Создание директории для бэкапов
mkdir -p "$BACKUP_DIR"

# Функция для создания бэкапа
create_backup() {
    log_info "Создание бэкапа текущего состояния..."

    BACKUP_NAME="backup_$(date +%Y%m%d_%H%M%S)"

    # Бэкап volumes
    if docker-compose -f "$DOCKER_COMPOSE_FILE" ps -q app > /dev/null 2>&1; then
        docker run --rm \
            -v dobrenok_blog_app_storage:/source:ro \
            -v "$BACKUP_DIR":/backup \
            alpine tar czf "/backup/${BACKUP_NAME}_storage.tar.gz" -C /source .

        docker run --rm \
            -v dobrenok_blog_app_cache:/source:ro \
            -v "$BACKUP_DIR":/backup \
            alpine tar czf "/backup/${BACKUP_NAME}_cache.tar.gz" -C /source .

        log_info "Бэкап создан: $BACKUP_NAME"
    else
        log_warn "Контейнеры не запущены, пропускаем создание бэкапа"
    fi
}

# Функция для очистки старых бэкапов (оставляем последние 5)
cleanup_backups() {
    log_info "Очистка старых бэкапов..."
    find "$BACKUP_DIR" -name "backup_*" -type f | sort -r | tail -n +6 | xargs -r rm -f
}

# Функция для проверки здоровья приложения
health_check() {
    log_info "Проверка здоровья приложения..."

    local max_attempts=30
    local attempt=1

    while [ $attempt -le $max_attempts ]; do
        if curl -f -s http://localhost/health > /dev/null 2>&1; then
            log_info "Приложение работает корректно"
            return 0
        fi

        log_warn "Попытка $attempt/$max_attempts: приложение еще не готово"
        sleep 10
        ((attempt++))
    done

    log_error "Приложение не отвечает после $max_attempts попыток"
    return 1
}

# Функция для отката к предыдущей версии
rollback() {
    log_error "Выполняется откат к предыдущей версии..."

    # Остановка текущих контейнеров
    docker-compose -f "$DOCKER_COMPOSE_FILE" down

    # Восстановление из последнего бэкапа
    LATEST_BACKUP=$(find "$BACKUP_DIR" -name "backup_*_storage.tar.gz" -type f | sort -r | head -n 1)

    if [ -n "$LATEST_BACKUP" ]; then
        BACKUP_BASE=$(basename "$LATEST_BACKUP" "_storage.tar.gz")

        log_info "Восстановление из бэкапа: $BACKUP_BASE"

        # Восстановление storage
        docker run --rm \
            -v dobrenok_blog_app_storage:/target \
            -v "$BACKUP_DIR":/backup \
            alpine sh -c "cd /target && tar xzf /backup/${BACKUP_BASE}_storage.tar.gz"

        # Восстановление cache
        if [ -f "$BACKUP_DIR/${BACKUP_BASE}_cache.tar.gz" ]; then
            docker run --rm \
                -v dobrenok_blog_app_cache:/target \
                -v "$BACKUP_DIR":/backup \
                alpine sh -c "cd /target && tar xzf /backup/${BACKUP_BASE}_cache.tar.gz"
        fi

        log_info "Бэкап восстановлен"
    else
        log_warn "Бэкапы не найдены"
    fi

    # Запуск предыдущей версии
    docker-compose -f "$DOCKER_COMPOSE_FILE" up -d

    log_info "Откат завершен"
    exit 1
}

# Основная функция деплоя
deploy() {
    log_info "Начало развертывания Dobrenok Blog"

    # Переход в директорию проекта
    cd "$PROJECT_PATH"

    # Создание бэкапа
    create_backup

    # Получение обновлений из Git
    log_info "Получение обновлений из Git..."
    git fetch origin main
    git reset --hard origin/main

    # Загрузка нового образа
    log_info "Загрузка нового Docker образа..."
    docker pull "$IMAGE_NAME"

    # Остановка текущих контейнеров
    log_info "Остановка текущих контейнеров..."
    docker-compose -f "$DOCKER_COMPOSE_FILE" down --remove-orphans

    # Запуск новых контейнеров
    log_info "Запуск новых контейнеров..."
    docker-compose -f "$DOCKER_COMPOSE_FILE" up -d

    # Ожидание готовности приложения
    sleep 30

    # Выполнение миграций и оптимизация
    log_info "Выполнение миграций и оптимизация..."

    # Миграции
    if ! docker-compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan migrate --force; then
        log_error "Ошибка при выполнении миграций"
        rollback
    fi

    # Очистка кеша
    docker-compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan config:clear
    docker-compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan cache:clear
    docker-compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan route:clear
    docker-compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan view:clear

    # Кеширование для production
    docker-compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan config:cache
    docker-compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan route:cache
    docker-compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan view:cache

    # Создание символической ссылки для storage
    docker-compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan storage:link

    # Проверка здоровья приложения
    if ! health_check; then
        rollback
    fi

    # Очистка старых образов
    log_info "Очистка старых Docker образов..."
    docker image prune -f

    # Очистка старых бэкапов
    cleanup_backups

    # Показать статус контейнеров
    log_info "Статус контейнеров:"
    docker-compose -f "$DOCKER_COMPOSE_FILE" ps

    log_info "Развертывание успешно завершено!"
}

# Обработка сигналов для корректного завершения
trap 'log_error "Развертывание прервано"; exit 1' INT TERM

# Запуск деплоя
deploy
