# РемонтСервис (Вайб-кодер тестовое)

Современный веб-сервис для приёма и обработки заявок в ремонтную службу. Позволяет клиентам оставлять заявки, диспетчерам — модерировать их, а мастерам — брать в работу.

## Стек технологий
- **Backend:** Laravel 11, PostgreSQL
- **Frontend:** Vue 3 (Composition API), Inertia.js, Tailwind CSS
- **Инфраструктура:** Laravel Sail (Docker)

## Развертывание проекта

Так как проект использует Laravel Sail, для локального запуска нужен только установленный Docker (и WSL, если вы на Windows).

1. Клонируйте репозиторий и перейдите в папку проекта.
2. Скопируйте `.env.example` в `.env`:
   ```bash
   cp .env.example .env
   ```
3. Поднимите контейнеры:
   ```bash
   ./vendor/bin/sail up -d
   ```
4. Сгенерируйте ключ приложения, накатите миграции и сиды:
   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate:fresh --seed
   ```
5. Установите NPM-зависимости и соберите фронтенд:
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run build
   ```
   (Для разработки в реальном времени используйте ./vendor/bin/sail npm run dev)

Проект будет доступен по адресу: http://localhost