# Goal Planner API

Backend API для проекта Goal Planner, разработанный на Laravel.

---

## Технологии

- PHP 8.4
- Laravel 13
- Laravel Sanctum (Bearer-токены)
- SQLite / MySQL
- REST API

---

## Быстрый запуск через Docker (рекомендуется)

Требуется только установленный **Docker Desktop**.

```bash
git clone https://github.com/Windy-Fruit/Goal-Planner-PHP.git
cd Goal-Planner-PHP
docker compose up --build
```

После сборки (первый раз ~3-5 минут) проект будет доступен:

- API: `http://localhost:8000/api/...`
- Демо-страница для проверки: `http://localhost:8000/demo.html`

Чтобы остановить — Ctrl+C в терминале, либо `docker compose down`.

---

## Установка вручную (без Docker)

```bash
git clone https://github.com/Windy-Fruit/Goal-Planner-PHP.git
cd Goal-Planner-PHP
composer install
cp .env.example .env
php artisan key:generate
```

### База данных (SQLite, по умолчанию)

```bash
touch database/database.sqlite
php artisan migrate
```

### База данных (MySQL)

Создать БД:

```sql
CREATE DATABASE goal_planner;
```

В `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=goal_planner
DB_USERNAME=root
DB_PASSWORD=
```

И затем:

```bash
php artisan migrate
```

### Запуск сервера

```bash
php artisan serve
```

API будет доступен по адресу `http://127.0.0.1:8000/api`.

---

## Аутентификация

API использует Laravel Sanctum. После регистрации или входа возвращается `token`, который нужно передавать в заголовке:

```
Authorization: Bearer <token>
```

---

## Эндпоинты

### Публичные

| Метод | URL              | Описание                       |
|-------|------------------|--------------------------------|
| POST  | `/api/register`  | Регистрация пользователя       |
| POST  | `/api/login`     | Вход и получение токена        |

#### POST `/api/register`

```json
{
  "name": "Иван",
  "email": "ivan@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

#### POST `/api/login`

```json
{
  "email": "ivan@example.com",
  "password": "secret123"
}
```

Ответ:

```json
{
  "user": { "id": 1, "name": "Иван", "email": "ivan@example.com" },
  "token": "1|abcdef..."
}
```

---

### Защищённые (требуют `Authorization: Bearer <token>`)

| Метод   | URL                              | Описание                                |
|---------|----------------------------------|-----------------------------------------|
| POST    | `/api/logout`                    | Выход (удаление текущего токена)        |
| GET     | `/api/me`                        | Текущий пользователь                    |
| GET     | `/api/goals`                     | Список целей с пагинацией/фильтрами     |
| POST    | `/api/goals`                     | Создать цель                            |
| GET     | `/api/goals/{id}`                | Получить одну цель                      |
| PUT     | `/api/goals/{id}`                | Обновить цель                           |
| DELETE  | `/api/goals/{id}`                | Удалить цель (soft delete)              |
| GET     | `/api/goals/{id}/progress`       | Процент выполнения цели                 |
| GET     | `/api/tasks`                     | Список задач пользователя               |
| POST    | `/api/tasks`                     | Создать задачу                          |
| GET     | `/api/tasks/{id}`                | Получить одну задачу                    |
| PUT     | `/api/tasks/{id}`                | Обновить задачу                         |
| DELETE  | `/api/tasks/{id}`                | Удалить задачу                          |
| GET     | `/api/categories`                | Список категорий                        |
| POST    | `/api/categories`                | Создать категорию                       |
| GET     | `/api/categories/{id}`           | Получить категорию                      |
| PUT     | `/api/categories/{id}`           | Обновить категорию                      |
| DELETE  | `/api/categories/{id}`           | Удалить категорию                       |

---

## Параметры списков

### Цели `/api/goals`

- `status=active|completed` — фильтр по статусу
- `title=<строка>` — поиск по названию (LIKE)
- `category_id=<id>` — только цели с этой категорией
- `sort_by=id|title|deadline|created_at|status` (по умолчанию `id`)
- `sort_order=asc|desc` (по умолчанию `desc`)
- `per_page=N` (по умолчанию 5)
- `page=N`

Пример: `/api/goals?status=active&sort_by=deadline&sort_order=asc&per_page=10`

### Задачи `/api/tasks`

- `goal_id=<id>` — задачи конкретной цели
- `is_completed=true|false`
- `title=<строка>`
- `sort_by=id|title|due_date|created_at|is_completed`
- `sort_order=asc|desc`
- `per_page=N` (по умолчанию 10)

### Категории `/api/categories`

- `name=<строка>` — поиск по названию
- `sort_by=id|name|created_at`
- `sort_order=asc|desc`
- `per_page=N` (по умолчанию 20)

---

## Создание цели с категориями

```http
POST /api/goals
Authorization: Bearer <token>
Content-Type: application/json

{
  "title": "Пробежать марафон",
  "description": "42 km",
  "deadline": "2026-12-31",
  "status": "active",
  "category_ids": [1, 2]
}
```

Связь many-to-many через таблицу `goal_category`.

---

## Прогресс цели

```http
GET /api/goals/1/progress
```

```json
{
  "goal_id": 1,
  "total_tasks": 3,
  "completed_tasks": 2,
  "progress_percent": 67
}
```

Формула: `(кол-во выполненных задач / общее количество задач) * 100`.

---

## Структура БД

- **users** — id, name, email, password, timestamps
- **goals** — id, user_id (FK), title, description, deadline, status, timestamps, deleted_at
- **tasks** — id, goal_id (FK), title, description, due_date, is_completed, timestamps
- **categories** — id, name, timestamps
- **goal_category** (pivot) — goal_id, category_id (составной первичный ключ)
- **personal_access_tokens** — таблица Sanctum для Bearer-токенов

### Связи

- `User hasMany Goals`
- `Goal belongsTo User`, `hasMany Tasks`, `belongsToMany Categories`
- `Task belongsTo Goal`
- `Category belongsToMany Goals`

---

## Реализованные возможности

- Регистрация и авторизация через Laravel Sanctum
- CRUD для целей, задач и категорий
- Many-to-many между целями и категориями
- Soft Deletes для целей
- Пагинация, фильтрация и сортировка для всех списков
- Расчёт прогресса по цели
- Привязка целей и задач к авторизованному пользователю
- Проверка прав: пользователь работает только со своими целями и задачами
- JSON-ответы со связанными моделями (`with([...])`)

---

## Примеры запросов через curl

Регистрация:

```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Ivan","email":"ivan@test.com","password":"secret123","password_confirmation":"secret123"}'
```

Создание цели:

```bash
curl -X POST http://127.0.0.1:8000/api/goals \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test","status":"active"}'
```

Прогресс:

```bash
curl http://127.0.0.1:8000/api/goals/1/progress \
  -H "Authorization: Bearer <token>"
```
