#!/bin/bash

# === WordPress Universal Deployment Script ===
# Оптимизирован для слабого shared хостинга

# --- КОНФИГУРАЦИЯ ---
LOCAL_WP_PATH="/var/www/mix.dev.loc"            # Путь к локальной WordPress
REMOTE_SSH_ALIAS="mix-hst"                       # SSH алиас для подключения к хостингу
REMOTE_WP_PATH="/home/c/ca27120/MIKZ/public_html"  # Путь к WordPress на хостинге

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
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# --- ФУНКЦИИ ---

# Функция для вывода заголовка
print_header() {
    echo ""
    echo -e "${CYAN}╔════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║${NC}   ${YELLOW}WordPress Universal Deployment Script${NC}   ${CYAN}║${NC}"
    echo -e "${CYAN}╚════════════════════════════════════════════════╝${NC}"
    echo ""
}

# Функция для показа меню
show_menu() {
    echo -e "${YELLOW}Что задеплоим?${NC}"
    echo ""
    echo -e "  ${GREEN}1${NC}. Все (файлы + база данных)"
    echo -e "  ${GREEN}2${NC}. Только файлы"
    echo -e "  ${GREEN}3${NC}. Только базу данных"
    echo -e "  ${RED}0${NC}. Выход"
    echo ""
}

# Функция для деплоя файлов
deploy_files() {
    local skip_confirm=$1
    
    echo ""
    echo -e "${BLUE}╔════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║${NC}     ${YELLOW}Этап: Деплой файлов (rsync)${NC}           ${BLUE}║${NC}"
    echo -e "${BLUE}╚════════════════════════════════════════════════╝${NC}"
    echo ""
    
    if [ "$skip_confirm" != "yes" ]; then
        echo -e "${YELLOW}ВНИМАНИЕ!${NC} Эта операция синхронизирует вашу локальную директорию с хостингом."
        echo "Будут удалены файлы на хостинге, которых нет локально."
        echo ""
        echo "Локально: $LOCAL_WP_PATH/"
        echo "Хостинг:  $REMOTE_SSH_ALIAS:$REMOTE_WP_PATH/"
        echo ""
        read -p "Продолжить деплой файлов? (yes/no): " confirm
        if [ "$confirm" != "yes" ]; then
            echo "Операция отменена."
            return 1
        fi
    fi
    
    echo ""
    echo -e "${GREEN}Шаг 1/3: Синхронизация файлов...${NC}"
    rsync -avz --delete \
        --exclude '.git' \
        --exclude '.gitignore' \
        --exclude '.env' \
        --exclude 'wp-config.php' \
        --exclude 'wp-content/uploads/' \
        --exclude 'wp-content/backups-dup-pro/' \
        --exclude 'wp-content/cache/' \
        --exclude 'wp-content/upgrade/' \
        --exclude 'node_modules/' \
        --exclude '.DS_Store' \
        --exclude '*.log' \
        --exclude 'deploy.sh' \
        --exclude 'deploy-universal.sh' \
        --exclude 'db_push_script.sh' \
        --exclude 'db-backups/' \
        "$LOCAL_WP_PATH/" "$REMOTE_SSH_ALIAS:$REMOTE_WP_PATH/" || {
        echo -e "${RED}✗ Ошибка при синхронизации файлов.${NC}"
        exit 1
    }

    echo -e "${GREEN}Шаг 2/3: Установка прав на директории и файлы на хостинге...${NC}"
    echo "  → Обработка директорий..."
    ssh $REMOTE_SSH_ALIAS "cd $REMOTE_WP_PATH && \
        find . -type d \
            -not -path './wp-content/uploads*' \
            -not -path './wp-content/plugins*' \
            -not -path './wp-content/cache*' \
            -not -path './wp-content/upgrade*' \
            -print0 | xargs -0 -n 50 chmod 755" || {
        echo -e "${RED}✗ Ошибка при установке прав на директории.${NC}"
        exit 1
    }
    
    echo "  → Обработка файлов..."
    ssh $REMOTE_SSH_ALIAS "cd $REMOTE_WP_PATH && \
        find . -type f \
            -not -path './wp-content/uploads*' \
            -not -path './wp-content/plugins*' \
            -not -path './wp-content/cache*' \
            -not -path './wp-content/upgrade*' \
            -print0 | xargs -0 -n 50 chmod 644" || {
        echo -e "${RED}✗ Ошибка при установке прав на файлы.${NC}"
        exit 1
    }
    
    echo "  → Установка специальных прав..."
    ssh $REMOTE_SSH_ALIAS "cd $REMOTE_WP_PATH && \
        chmod 600 wp-config.php 2>/dev/null || true && \
        chmod 755 wp-content/themes 2>/dev/null || true"

    echo -e "${GREEN}Шаг 3/3: Сброс кеша на хостинге...${NC}"
    ssh $REMOTE_SSH_ALIAS "cd $REMOTE_WP_PATH && wp cache flush --allow-root 2>/dev/null || wp cache flush 2>/dev/null || true"

    echo ""
    echo -e "${GREEN}✓ Деплой файлов успешно завершен!${NC}"
}

# Функция для загрузки БД
push_database() {
    local skip_confirm=$1
    
    echo ""
    echo -e "${BLUE}╔════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║${NC}     ${YELLOW}Этап: Загрузка базы данных${NC}            ${BLUE}║${NC}"
    echo -e "${BLUE}╚════════════════════════════════════════════════╝${NC}"
    echo ""

    if [ "$skip_confirm" != "yes" ]; then
        echo -e "${YELLOW}ВНИМАНИЕ!${NC} Эта операция заменит БД на хостинге вашей локальной БД."
        read -p "Продолжить загрузку БД? (yes/no): " confirm
        if [ "$confirm" != "yes" ]; then
            echo "Операция отменена."
            return 1
        fi
    fi

    TIMESTAMP=$(date +%Y%m%d_%H%M%S)

    # Создаем директорию для бэкапов, если её нет
    mkdir -p "$BACKUP_DIR"

    echo ""
    echo -e "${GREEN}Шаг 1/8: Создание бэкапа БД на хостинге...${NC}"
    ssh $REMOTE_SSH_ALIAS "mysqldump --no-tablespaces --single-transaction --quick \
        -u $REMOTE_DB_USER -p'$REMOTE_DB_PASS' -h $REMOTE_DB_HOST $REMOTE_DB_NAME \
        > $REMOTE_WP_PATH/backup_before_push_$TIMESTAMP.sql" || {
        echo -e "${RED}✗ Ошибка создания бэкапа на хостинге.${NC}"
        exit 1
    }
    echo "  → Бэкап создан: backup_before_push_$TIMESTAMP.sql"
    
    sleep 2

    echo -e "${GREEN}Шаг 2/8: Экспорт локальной БД...${NC}"
    cd "$LOCAL_WP_PATH"
    
    WP_CLI_ARGS=""
    if [ "$EUID" -eq 0 ]; then
        WP_CLI_ARGS="--allow-root"
    fi

    wp db export "$BACKUP_DIR/local_export_$TIMESTAMP.sql" $WP_CLI_ARGS || {
        echo -e "${RED}✗ Ошибка экспорта локальной БД.${NC}"
        exit 1
    }

    echo -e "${GREEN}Шаг 3/8: Замена URL в дампе...${NC}"
    if [[ "$OSTYPE" == "darwin"* ]]; then
        sed -i '' "s|$LOCAL_URL|$REMOTE_URL|g" "$BACKUP_DIR/local_export_$TIMESTAMP.sql"
    else
        sed -i "s|$LOCAL_URL|$REMOTE_URL|g" "$BACKUP_DIR/local_export_$TIMESTAMP.sql"
    fi

    echo -e "${GREEN}Шаг 4/8: Сжатие дампа...${NC}"
    gzip -f "$BACKUP_DIR/local_export_$TIMESTAMP.sql"

    echo -e "${GREEN}Шаг 5/8: Загрузка дампа на хостинг...${NC}"
    scp "$BACKUP_DIR/local_export_$TIMESTAMP.sql.gz" $REMOTE_SSH_ALIAS:$REMOTE_WP_PATH/ || {
        echo -e "${RED}✗ Ошибка загрузки файла на хостинг.${NC}"
        exit 1
    }
    
    sleep 2

    echo -e "${GREEN}Шаг 6/8: Распаковка дампа на хостинге...${NC}"
    ssh $REMOTE_SSH_ALIAS "cd $REMOTE_WP_PATH && gunzip -f local_export_$TIMESTAMP.sql.gz" || {
        echo -e "${RED}✗ Ошибка распаковки дампа.${NC}"
        exit 1
    }

    sleep 2

    echo -e "${GREEN}Шаг 7/8: Импорт БД на хостинге (может занять время)...${NC}"
    ssh $REMOTE_SSH_ALIAS "mysql --max_allowed_packet=256M \
        -u $REMOTE_DB_USER -p'$REMOTE_DB_PASS' -h $REMOTE_DB_HOST $REMOTE_DB_NAME \
        < $REMOTE_WP_PATH/local_export_$TIMESTAMP.sql" || {
        echo -e "${RED}✗ Ошибка импорта БД на хостинге.${NC}"
        echo -e "${YELLOW}Совет: Если база большая, попробуйте импортировать вручную через phpMyAdmin${NC}"
        exit 1
    }

    sleep 1

    echo -e "${GREEN}Шаг 8/8: Очистка временных файлов на хостинге...${NC}"
    ssh $REMOTE_SSH_ALIAS "rm -f $REMOTE_WP_PATH/local_export_$TIMESTAMP.sql"

    echo ""
    echo -e "${GREEN}✓ База данных успешно загружена на хостинг!${NC}"
}

# Функция для полного деплоя
deploy_all() {
    echo ""
    echo -e "${YELLOW}╔════════════════════════════════════════════════╗${NC}"
    echo -e "${YELLOW}║${NC}     ${YELLOW}Полный деплой (файлы + БД)${NC}            ${YELLOW}║${NC}"
    echo -e "${YELLOW}╚════════════════════════════════════════════════╝${NC}"
    echo ""
    echo "Будет выполнено:"
    echo "  1. Синхронизация файлов с хостингом"
    echo "  2. Загрузка базы данных на хостинг"
    echo ""
    read -p "Продолжить полный деплой? (yes/no): " confirm_all
    if [ "$confirm_all" != "yes" ]; then
        echo "Операция отменена."
        return 1
    fi
    
    # Деплоим файлы без дополнительного подтверждения
    deploy_files "yes"
    
    echo ""
    echo -e "${BLUE}═══════════════════════════════════════════════${NC}"
    echo ""
    sleep 2
    
    # Деплоим БД без дополнительного подтверждения
    push_database "yes"
}

# --- ОСНОВНАЯ ЛОГИКА ---
print_header

# Если передан параметр командной строки (для обратной совместимости)
if [ "$1" == "files" ]; then
    deploy_files
    echo ""
    echo -e "${GREEN}✓✓✓ Все операции успешно завершены! ✓✓✓${NC}"
    echo ""
    exit 0
elif [ "$1" == "db" ]; then
    push_database
    echo ""
    echo -e "${GREEN}✓✓✓ Все операции успешно завершены! ✓✓✓${NC}"
    echo ""
    exit 0
elif [ "$1" == "all" ]; then
    deploy_all
    echo ""
    echo -e "${GREEN}✓✓✓ Все операции успешно завершены! ✓✓✓${NC}"
    echo ""
    exit 0
fi

# Интерактивное меню (если параметры не переданы)
while true; do
    show_menu
    read -p "Выберите опцию [0-3]: " choice
    
    case $choice in
        1)
            deploy_all
            echo ""
            echo -e "${GREEN}✓✓✓ Все операции успешно завершены! ✓✓✓${NC}"
            echo ""
            break
            ;;
        2)
            deploy_files
            echo ""
            echo -e "${GREEN}✓✓✓ Все операции успешно завершены! ✓✓✓${NC}"
            echo ""
            break
            ;;
        3)
            push_database
            echo ""
            echo -e "${GREEN}✓✓✓ Все операции успешно завершены! ✓✓✓${NC}"
            echo ""
            break
            ;;
        0)
            echo ""
            echo -e "${YELLOW}Выход из программы.${NC}"
            echo ""
            exit 0
            ;;
        *)
            echo ""
            echo -e "${RED}✗ Неверный выбор. Пожалуйста, выберите опцию от 0 до 3.${NC}"
            echo ""
            sleep 1
            ;;
    esac
done