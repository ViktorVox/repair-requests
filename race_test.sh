#!/bin/bash

# URL нашего локального Sail-приложения
APP_URL="http://localhost"

echo "=== Подготовка к тестированию Race Condition ==="

# Функция для получения CSRF-токена, логина и сохранения сессии
login_and_get_session() {
    local email=$1
    local cookie_jar=$2

    # 1. Запрашиваем CSRF-куку
    curl -s -c $cookie_jar "$APP_URL/sanctum/csrf-cookie" > /dev/null

    # 2. Вытаскиваем XSRF-TOKEN из файла куки и декодируем базовый URL-encode (знак =)
    local token=$(awk '/XSRF-TOKEN/ {print $7}' $cookie_jar | sed 's/%3D/=/g')

    # 3. Выполняем POST логин, притворяясь SPA (Inertia/Axios)
    curl -s -o /dev/null -c $cookie_jar -b $cookie_jar -X POST "$APP_URL/login" \
         -H "X-XSRF-TOKEN: $token" \
         -H "Accept: application/json" \
         -H "Content-Type: application/json" \
         -d "{\"email\":\"$email\", \"password\":\"password\"}"
}

echo "Авторизация Master 1 (master1@test.com)..."
login_and_get_session "master1@test.com" "cookie1.txt"

echo "Авторизация Master 2 (master2@test.com)..."
login_and_get_session "master2@test.com" "cookie2.txt"

echo "Извлекаем свежие CSRF-токены для POST запросов..."
TOKEN1=$(awk '/XSRF-TOKEN/ {print $7}' cookie1.txt | sed 's/%3D/=/g')
TOKEN2=$(awk '/XSRF-TOKEN/ {print $7}' cookie2.txt | sed 's/%3D/=/g')

echo "=== ЗАПУСК ГОНКИ ==="
echo "Оба мастера одновременно пытаются взять Заявку #1..."

# Отправляем запросы одновременно (символ & в конце)
curl -s -o /dev/null -w "[Master 1] HTTP Status: %{http_code}\n" -X POST "$APP_URL/requests/1/take" \
     -b cookie1.txt \
     -H "X-XSRF-TOKEN: $TOKEN1" \
     -H "Accept: application/json" &

curl -s -o /dev/null -w "[Master 2] HTTP Status: %{http_code}\n" -X POST "$APP_URL/requests/1/take" \
     -b cookie2.txt \
     -H "X-XSRF-TOKEN: $TOKEN2" \
     -H "Accept: application/json" &

# Ждем завершения фоновых процессов
wait

echo "=== ТЕСТ ЗАВЕРШЕН ==="
echo "Один из запросов должен вернуть 302 (успешный редирект), а второй 302 (редирект с ошибкой conflict)."
echo "Удаляем временные файлы куки..."
rm cookie1.txt cookie2.txt