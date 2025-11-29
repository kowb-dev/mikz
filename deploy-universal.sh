#!/bin/bash

# === WordPress Universal Deployment Script ===

# --- КОНФИГУРАЦИЯ ---
LOCAL_WP_PATH="/var/www/mix.dev.loc"            # Путь к локальной WordPress
REMOTE_SSH_ALIAS="mix-hst"                       # SSH алиас для подключения к хостингу
REMOTE_WP_PATH="/home/c/ca27120/MIKZ"         # Путь к WordPress на хостинге

LOCAL_URL="http://mix.dev.loc"                   # URL локального сайта
REMOTE_URL="https://mix.coiv.ru"               # URL сайта на хостинге
BACKUP_DIR="$LOCAL_WP_PATH/db-backups"

# Учетные данные БД на хостинге (для DB push)
REMOTE_DB_NAME="ca27120_mix"
REMOTE_DB_USER="ca27120_mix"
REMOTE_DB_PASS="wani_Zok5"
REMOTE_DB_HOST="localhost"

# --- ЦВЕТА ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# --- ФУНКЦИИ ---

# Функция для вывода заголовка
print_header() {
    echo -e "${YELLOW}=== WordPress Universal Deployment Script ===${NC}"
    echo ""
}

# Функция для деплоя файлов через rsync
deploy_files() {
    echo -e "${BLUE}--- Этап 1: Деплой файлов (rsync) ---${NC}"
    echo ""
    
    echo -e "${YELLOW}ВНИМАНИЕ!${NC}" "Эта операция синхронизирует вашу локальную директорию с хостингом."
    echo "Будут удалены файлы на хостинге, которых нет локально (в пределах темы)."
    echo ""
    echo "Локально: $LOCAL_WP_PATH/"
    echo "Хостинг:  $REMOTE_SSH_ALIAS:$REMOTE_WP_PATH/"
    echo ""
    read -p "Продолжить деплой файлов? (yes/no): " confirm
    if [ "$confirm" != "yes" ]; then
        echo "Операция отменена."
        return
    fi
    
    echo ""
    echo -e "${GREEN}Шаг 1/3: Синхронизация файлов темы...${NC}"
    rsync -avz --delete \
        --exclude '.git' \
        --exclude 'db-backups' \
        --exclude '.idea' \
        --exclude 'deploy-universal.sh' \
        --exclude 'db_push_script.sh' \
        "$LOCAL_WP_PATH/wp-content/themes/shoptimizer-child/" "$REMOTE_SSH_ALIAS:$REMOTE_WP_PATH/wp-content/themes/shoptimizer-child/" || {
        echo -e "${RED}Ошибка при синхронизации файлов темы.${NC}"
        exit 1
    }

    echo -e "${GREEN}Шаг 2/3: Установка прав на директории и файлы на хостинге...${NC}"
    ssh $REMOTE_SSH_ALIAS "cd $REMOTE_WP_PATH/wp-content/themes/shoptimizer-child/ && find . -type d -exec chmod 755 {} \; && find . -type f -exec chmod 644 {} \;" || {
        echo -e "${RED}Ошибка при установке прав на файлы.${NC}"
        exit 1
    }

    echo -e "${GREEN}Шаг 3/3: Сброс кеша на хостинге...${NC}"
    ssh $REMOTE_SSH_ALIAS "cd $REMOTE_WP_PATH && wp cache flush" || {
        echo -e "${YELLOW}Предупреждение: не удалось сбросить кеш. Возможно, WP-CLI не установлен.${NC}"
    }

    echo ""
    echo -e "${GREEN}✓ Деплой файлов успешно завершен!${NC}"
}

# Функция для загрузки БД
push_database() {
    echo -e "${BLUE}--- Этап 2: Загрузка базы данных ---${NC}"
    echo ""

    echo -e "${YELLOW}ВНИМАНИЕ!${NC}" "Эта операция заменит БД на хостинге вашей локальной БД."
    read -p "Продолжить загрузку БД? (yes/no): " confirm
    if [ "$confirm" != "yes" ]; then
        echo "Операция отменена."
        return
    fi

    TIMESTAMP=$(date +%Y%m%d_%H%M%S)

    echo ""
    echo -e "${GREEN}Шаг 1/8: Создание бэкапа БД на хостинге...${NC}"
    ssh $REMOTE_SSH_ALIAS "mysqldump --no-tablespaces -u $REMOTE_DB_USER -p'$REMOTE_DB_PASS' -h $REMOTE_DB_HOST $REMOTE_DB_NAME > $REMOTE_WP_PATH/backup_before_push_$TIMESTAMP.sql" || {
        echo -e "${RED}Ошибка создания бэкапа на хостинге.${NC}"
        exit 1
    }
    echo "Бэкап создан: backup_before_push_$TIMESTAMP.sql"
    
    sleep 1

    echo -e "${GREEN}Шаг 2/8: Экспорт локальной БД...${NC}"
    cd "$LOCAL_WP_PATH"
    
    WP_CLI_ARGS=""
    if [ "$EUID" -eq 0 ]; then
        WP_CLI_ARGS="--allow-root"
    fi

    wp db export "$BACKUP_DIR/local_export_$TIMESTAMP.sql" $WP_CLI_ARGS || {
        echo -e "${RED}Ошибка экспорта локальной БД.${NC}"
        exit 1
    }

    echo -e "${GREEN}Шаг 3/8: Замена URL в дампе...${NC}"
    sed -i "s|$LOCAL_URL|$REMOTE_URL|g" "$BACKUP_DIR/local_export_$TIMESTAMP.sql"

    echo -e "${GREEN}Шаг 4/8: Сжатие дампа...${NC}"
    gzip "$BACKUP_DIR/local_export_$TIMESTAMP.sql"

    echo -e "${GREEN}Шаг 5/8: Загрузка дампа на хостинг...${NC}"
    scp "$BACKUP_DIR/local_export_$TIMESTAMP.sql.gz" $REMOTE_SSH_ALIAS:$REMOTE_WP_PATH/ || {
        echo -e "${RED}Ошибка загрузки файла на хостинг.${NC}"
        exit 1
    }
    
    sleep 1

    echo -e "${GREEN}Шаг 6/8: Распаковка дампа на хостинге...${NC}"
    ssh $REMOTE_SSH_ALIAS "cd $REMOTE_WP_PATH && gunzip local_export_$TIMESTAMP.sql.gz" || {
        echo -e "${RED}Ошибка распаковки дампа.${NC}"
        exit 1
    }

    sleep 1

    echo -e "${GREEN}Шаг 7/8: Импорт БД на хостинге...${NC}"
    ssh $REMOTE_SSH_ALIAS "mysql -u $REMOTE_DB_USER -p'$REMOTE_DB_PASS' -h $REMOTE_DB_HOST $REMOTE_DB_NAME < $REMOTE_WP_PATH/local_export_$TIMESTAMP.sql" || {
        echo -e "${RED}Ошибка импорта БД на хостинге.${NC}"
        exit 1
    }

    echo -e "${GREEN}Шаг 8/8: Очистка временных файлов на хостинге...${NC}"
    ssh $REMOTE_SSH_ALIAS "rm $REMOTE_WP_PATH/local_export_$TIMESTAMP.sql"

    echo ""
    echo -e "${GREEN}✓ База данных успешно загружена на хостинг!${NC}"
}

# --- ОСНОВНАЯ ЛОГИКА ---
print_header

if [ "$1" == "files" ]; then
    deploy_files
elif [ "$1" == "db" ]; then
    push_database
else
    echo "Использование: $0 [files|db]"
    echo ""
    echo "  files:  Синхронизировать файлы темы (wp-content/themes/shoptimizer-child) с хостингом."
    echo "  db:     Загрузить локальную базу данных на хостинг."
    echo ""
    exit 1
fi

echo ""
echo -e "${YELLOW}Все операции завершены.${NC}"
echo ""
