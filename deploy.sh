#!/bin/bash

# === НАСТРОЙКИ ===
SERVER="ca27120@vh314.timeweb.ru"
REMOTE_PATH="~/MIKZ/public_html"
SSH_KEY="~/.ssh/mix-hst"  # твой ключ
LOCAL_PATH="./"

# === ИСКЛЮЧЕНИЯ ===
EXCLUDES=(
    ".git"
    ".gitignore"
    ".env"
    "wp-config.php"
    "wp-content/uploads"
    "wp-content/cache"
    "wp-content/upgrade"
    "node_modules"
    ".DS_Store"
    "*.log"
    "deploy.sh"
)

# Формируем параметры exclude
EXCLUDE_PARAMS=""
for item in "${EXCLUDES[@]}"; do
    EXCLUDE_PARAMS="$EXCLUDE_PARAMS --exclude='$item'"
done

# === ДЕПЛОЙ ===
echo "🚀 Начинаем деплой..."
echo "📁 Сервер: $SERVER:$REMOTE_PATH"

# Сухой прогон (показать что изменится)
echo ""
echo "📋 Предпросмотр изменений:"
eval rsync -avzn --delete $EXCLUDE_PARAMS \
    -e "ssh -i $SSH_KEY" \
    "$LOCAL_PATH" "$SERVER:$REMOTE_PATH"

# Подтверждение
echo ""
read -p "Продолжить деплой? (y/n): " confirm
if [ "$confirm" != "y" ]; then
    echo "❌ Отменено"
    exit 1
fi

# Реальный деплой
eval rsync -avz --delete $EXCLUDE_PARAMS \
    -e "ssh -i $SSH_KEY" \
    "$LOCAL_PATH" "$SERVER:$REMOTE_PATH"

echo ""
echo "✅ Деплой завершён!"