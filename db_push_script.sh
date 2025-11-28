#!/bin/bash

# Конфигурация
LOCAL_WP_PATH="/var/www/mix.dev.loc"            # Путь к локальной WordPress
LOCAL_URL="http://mix.dev.loc"                   # URL локального сайта
REMOTE_SSH_ALIAS="mix-hst"                       # SSH алиас из ~/.ssh/config
REMOTE_WP_PATH="/home/c/ca27120/MIKZ"
REMOTE_URL="https://mix.coiv.ru"               # URL сайта на хостинге
BACKUP_DIR="$LOCAL_WP_PATH/db-backups"

# Учетные данные БД на хостинге
REMOTE_DB_NAME="ca27120_mix"
REMOTE_DB_USER="ca27120_mix"
REMOTE_DB_PASS="wani_Zok5"
REMOTE_DB_HOST="localhost"

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}=== WordPress Database Push Script ===${NC}"
echo ""

# Проверка наличия WP-CLI локально
if ! command -v wp &> /dev/null; then
    echo -e "${RED}Error: WP-CLI не установлен локально${NC}"
    echo "Установите: https://wp-cli.org/"
    exit 1
fi

# Создание директории для бэкапов
mkdir -p "$BACKUP_DIR"

# Подтверждение
echo -e "${YELLOW}ВНИМАНИЕ!${NC} Эта операция:"
echo "1. Создаст бэкап текущей БД на хостинге"
echo "2. Заменит БД на хостинге вашей локальной БД"
echo ""
echo "Локальный путь: $LOCAL_WP_PATH"
echo "Хостинг: $REMOTE_SSH_ALIAS:$REMOTE_WP_PATH"
echo ""
read -p "Продолжить? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "Операция отменена"
    exit 0
fi

TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo ""
echo -e "${GREEN}Шаг 1/8: Создание бэкапа БД на хостинге через mysqldump...${NC}"
ssh $REMOTE_SSH_ALIAS "mysqldump --no-tablespaces -u $REMOTE_DB_USER -p'$REMOTE_DB_PASS' -h $REMOTE_DB_HOST $REMOTE_DB_NAME > $REMOTE_WP_PATH/backup_before_push_$TIMESTAMP.sql" || {
    echo -e "${RED}Ошибка создания бэкапа на хостинге${NC}"
    exit 1
}
echo "Бэкап создан: backup_before_push_$TIMESTAMP.sql"

echo -e "${GREEN}Шаг 2/8: Экспорт локальной БД...${NC}"
cd "$LOCAL_WP_PATH"

# Проверка, запущен ли скрипт от root
WP_CLI_ARGS=""
if [ "$EUID" -eq 0 ]; then
    WP_CLI_ARGS="--allow-root"
    echo -e "${YELLOW}Внимание: запуск от root с флагом --allow-root${NC}"
fi

wp db export "$BACKUP_DIR/local_export_$TIMESTAMP.sql" $WP_CLI_ARGS || {
    echo -e "${RED}Ошибка экспорта локальной БД${NC}"
    exit 1
}

echo -e "${GREEN}Шаг 3/8: Замена URL в дампе (локальный → хостинг)...${NC}"
sed -i "s|$LOCAL_URL|$REMOTE_URL|g" "$BACKUP_DIR/local_export_$TIMESTAMP.sql"
echo "URL заменен: $LOCAL_URL → $REMOTE_URL"

echo -e "${GREEN}Шаг 4/8: Сжатие дампа...${NC}"
gzip "$BACKUP_DIR/local_export_$TIMESTAMP.sql"

echo -e "${GREEN}Шаг 5/8: Загрузка дампа на хостинг...${NC}"
scp "$BACKUP_DIR/local_export_$TIMESTAMP.sql.gz" $REMOTE_SSH_ALIAS:$REMOTE_WP_PATH/ || {
    echo -e "${RED}Ошибка загрузки файла на хостинг${NC}"
    exit 1
}

# ИСПРАВЛЕНО: Разделение на два шага для избежания fork ошибок на shared хостинге
echo -e "${GREEN}Шаг 6/8: Распаковка дампа на хостинге...${NC}"
ssh $REMOTE_SSH_ALIAS "cd $REMOTE_WP_PATH && gunzip local_export_$TIMESTAMP.sql.gz" || {
    echo -e "${RED}Ошибка распаковки дампа${NC}"
    echo -e "${YELLOW}Файл остался на хостинге: $REMOTE_WP_PATH/local_export_$TIMESTAMP.sql.gz${NC}"
    exit 1
}
echo "Дамп распакован"

# Небольшая пауза для освобождения ресурсов
sleep 2

echo -e "${GREEN}Шаг 7/8: Импорт БД на хостинге...${NC}"
ssh $REMOTE_SSH_ALIAS "mysql -u $REMOTE_DB_USER -p'$REMOTE_DB_PASS' -h $REMOTE_DB_HOST $REMOTE_DB_NAME < $REMOTE_WP_PATH/local_export_$TIMESTAMP.sql" || {
    echo -e "${RED}Ошибка импорта БД на хостинге${NC}"
    echo ""
    echo -e "${YELLOW}Можете восстановить из бэкапа:${NC}"
    echo "ssh $REMOTE_SSH_ALIAS \"mysql -u $REMOTE_DB_USER -p'$REMOTE_DB_PASS' $REMOTE_DB_NAME < $REMOTE_WP_PATH/backup_before_push_$TIMESTAMP.sql\""
    echo ""
    echo -e "${YELLOW}SQL файл остался на хостинге:${NC}"
    echo "$REMOTE_WP_PATH/local_export_$TIMESTAMP.sql"
    exit 1
}

echo -e "${GREEN}Шаг 8/8: Очистка временных файлов на хостинге...${NC}"
ssh $REMOTE_SSH_ALIAS "rm $REMOTE_WP_PATH/local_export_$TIMESTAMP.sql"

echo ""
echo -e "${GREEN}✓ База данных успешно загружена на хостинг!${NC}"
echo ""
echo "Бэкапы сохранены:"
echo "  Локально: $BACKUP_DIR/local_export_$TIMESTAMP.sql.gz"
echo "  На хостинге: $REMOTE_WP_PATH/backup_before_push_$TIMESTAMP.sql"
echo ""
echo -e "${YELLOW}Проверьте работу сайта: $REMOTE_URL${NC}"
echo ""
echo "Если нужно откатить изменения, выполните на хостинге:"
echo "mysql -u $REMOTE_DB_USER -p'$REMOTE_DB_PASS' $REMOTE_DB_NAME < $REMOTE_WP_PATH/backup_before_push_$TIMESTAMP.sql"
