# Goal Planner API

Backend API для проекта Goal Planner, разработанный на Laravel.

---

## Технологии

- PHP 8
- Laravel 13
- MySQL
- REST API

---

## Установка проекта

Клонировать репозиторий:

```bash
git clone <repository-url>
```

Установить зависимости:

```bash
composer install
```

Создать `.env` файл:

```bash
cp .env.example .env
```

Сгенерировать APP_KEY:

```bash
php artisan key:generate
```

---

## Настройка базы данных

Создать базу данных MySQL:

```sql
CREATE DATABASE goal_planner;
```

Настроить `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=goal_planner
DB_USERNAME=root
DB_PASSWORD=
```

Запустить миграции:

```bash
php artisan migrate
```

---

## Запуск сервера

```bash
php artisan serve
```

Сервер будет доступен по адресу:

```text
http://127.0.0.1:8000
```

---

# API endpoints

## Получить все цели

```http
GET /api/goals
```

---

## Получить одну цель

```http
GET /api/goals/{id}
```

---

## Создать цель

```http
POST /api/goals
```

Пример JSON:

```json
{
  "title": "Learn Laravel",
  "description": "Practice backend",
  "deadline": "2026-05-20",
  "status": "active",
  "category_id": 1
}
```

---

## Обновить цель

```http
PUT /api/goals/{id}
```

---

## Удалить цель

```http
DELETE /api/goals/{id}
```

---

# Фильтрация

```http
/api/goals?status=active
```

---

# Сортировка

```http
/api/goals?sort_by=deadline&sort_order=asc
```

---

# Пагинация

```http
/api/goals?page=1
```

---

# Связи между моделями

- Goal принадлежит Category
- Goal содержит много Tasks

---

# Реализованные возможности

- CRUD API
- JSON responses
- Пагинация
- Фильтрация
- Сортировка
- Soft Deletes
- Middleware
- Eloquent relationships
