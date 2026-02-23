# Nickname Avatar — Laravel + Redis + Docker

[English](#english) | [Русский](#русский)

---

## English

A Laravel 11 application for registering users with a nickname and avatar. All data is stored in Redis — no SQL database required.

### Stack

- **PHP 8.3** + **Laravel 11**
- **Redis 7** — all data storage
- **Nginx 1.25**
- **Docker / Docker Compose**
- **Laravel Pint** — code style

### Features

- `POST /api/register` — register a user with a unique nickname and avatar image
  - Validates nickname uniqueness in Redis
  - Validates avatar MIME type (`jpeg`, `jpg`, `png`, `gif`, `webp`) and max size (2 MB)
  - Rate limited: **10 requests per minute** per IP (configurable)
- `GET /` — HTML page listing all registered users (nickname + avatar)
- **Scheduled job** cleans up users older than 60 minutes, runs every 5 minutes (configurable)

### Getting started

**Requirements:** Docker & Docker Compose

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan storage:link
```

App will be available at `http://localhost:8080`

### Environment variables

| Variable | Default | Description |
| --- | --- | --- |
| `RATE_LIMIT_PER_MINUTE` | `10` | Max API requests per minute per IP |
| `USER_TTL_MINUTES` | `60` | How long user records live in Redis (minutes) |
| `CLEANUP_INTERVAL_MINUTES` | `5` | How often the cleanup job runs (minutes) |

### API

#### Register user

```text
POST /api/register
Content-Type: multipart/form-data

Fields:
  nickname  string  required  2–50 chars, alphanumeric / _ / -
  avatar    file    required  jpeg/jpg/png/gif/webp, max 2 MB
```

**Success (201):**

```json
{
  "message": "User registered successfully.",
  "data": {
    "id": "uuid",
    "nickname": "john_doe",
    "avatar": "avatars/filename.jpg",
    "created_at": "2024-01-01T12:00:00+00:00"
  }
}
```

**Duplicate nickname (422):**

```json
{
  "message": "Nickname already taken.",
  "errors": { "nickname": ["This nickname is already registered."] }
}
```

**Rate limit exceeded (429):** standard Laravel throttle response.

### Running tests

```bash
# Inside Docker
docker compose exec app php artisan test

# Locally (requires PHP 8.3 + Redis on 127.0.0.1:6379)
php artisan test
```

### Code style

```bash
# Check
./vendor/bin/pint --test

# Fix
./vendor/bin/pint
```

### Project structure

```text
├── app/
│   ├── Http/
│   │   ├── Controllers/UserController.php
│   │   └── Requests/RegisterUserRequest.php
│   ├── Jobs/CleanupExpiredUsersJob.php
│   └── Services/UserService.php
├── docker/
│   ├── nginx/default.conf
│   └── php/Dockerfile, php.ini
├── routes/
│   ├── api.php      — POST /api/register
│   ├── web.php      — GET /
│   └── console.php  — scheduler
├── resources/views/users/index.blade.php
├── tests/Feature/UserRegistrationTest.php
└── docker-compose.yml
```

---

## Русский

Laravel 11 приложение для регистрации пользователей с никнеймом и аватаром. Все данные хранятся в Redis — SQL-база данных не нужна.

### Стек

- **PHP 8.3** + **Laravel 11**
- **Redis 7** — хранилище всех данных
- **Nginx 1.25**
- **Docker / Docker Compose**
- **Laravel Pint** — code style

### Функциональность

- `POST /api/register` — регистрация пользователя с уникальным никнеймом и аватаром
  - Проверка уникальности никнейма через Redis
  - Валидация аватара по MIME-типу (`jpeg`, `jpg`, `png`, `gif`, `webp`) и максимальному размеру (2 МБ)
  - Rate limiting: **10 запросов в минуту** с одного IP (настраивается)
- `GET /` — HTML-страница со списком всех зарегистрированных пользователей (никнейм + аватар)
- **Job по расписанию** удаляет пользователей старше 60 минут, запускается каждые 5 минут (настраивается)

### Быстрый старт

**Требования:** Docker и Docker Compose

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan storage:link
```

Приложение будет доступно по адресу `http://localhost:8080`

### Переменные окружения

| Переменная | По умолчанию | Описание |
| --- | --- | --- |
| `RATE_LIMIT_PER_MINUTE` | `10` | Максимум запросов в минуту с одного IP |
| `USER_TTL_MINUTES` | `60` | Время жизни записи пользователя в Redis (минуты) |
| `CLEANUP_INTERVAL_MINUTES` | `5` | Как часто запускается job очистки (минуты) |

### API приложения

#### Регистрация пользователя

```text
POST /api/register
Content-Type: multipart/form-data

Поля:
  nickname  string  обязательно  2–50 символов, буквы/цифры / _ / -
  avatar    file    обязательно  jpeg/jpg/png/gif/webp, максимум 2 МБ
```

**Успех (201):**

```json
{
  "message": "User registered successfully.",
  "data": {
    "id": "uuid",
    "nickname": "john_doe",
    "avatar": "avatars/filename.jpg",
    "created_at": "2024-01-01T12:00:00+00:00"
  }
}
```

**Никнейм уже занят (422):**

```json
{
  "message": "Nickname already taken.",
  "errors": { "nickname": ["This nickname is already registered."] }
}
```

**Превышен лимит запросов (429):** стандартный ответ Laravel throttle.

### Запуск тестов

```bash
# Внутри Docker
docker compose exec app php artisan test

# Локально (требуется PHP 8.3 + Redis на 127.0.0.1:6379)
php artisan test
```

### Стиль кода

```bash
# Проверка
./vendor/bin/pint --test

# Исправление
./vendor/bin/pint
```

### Структура проекта

```text
├── app/
│   ├── Http/
│   │   ├── Controllers/UserController.php   — контроллер
│   │   └── Requests/RegisterUserRequest.php — валидация запроса
│   ├── Jobs/CleanupExpiredUsersJob.php       — job очистки
│   └── Services/UserService.php             — работа с Redis
├── docker/
│   ├── nginx/default.conf                   — конфиг Nginx
│   └── php/Dockerfile, php.ini              — конфиг PHP-контейнера
├── routes/
│   ├── api.php      — POST /api/register
│   ├── web.php      — GET /
│   └── console.php  — планировщик
├── resources/views/users/index.blade.php    — HTML-список пользователей
├── tests/Feature/UserRegistrationTest.php   — тесты API
└── docker-compose.yml
```
