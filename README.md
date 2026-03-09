# 💳 Wallet — Laravel Payment App

Laravel 10 приложение для управления платежами и балансом пользователей. Полностью контейнеризировано через Docker.

---

## 🛠 Стек технологий

- **PHP 8.2** + **Laravel 10**
- **MySQL 8.0**
- **Nginx** (alpine)
- **phpMyAdmin**
- **Docker** + **Docker Compose**

---

## 📁 Структура проекта

```
mywallet/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php       # Регистрация, вход, выход
│   │   ├── DashboardController.php  # Дашборд, профиль, пароль
│   │   └── PaymentController.php    # Платежи между пользователями
│   └── Models/
│       ├── User.php
│       └── Payment.php
├── database/
│   ├── migrations/                  # users, payments таблицы
│   └── seeders/
│       └── UserSeeder.php           # Тестовый admin пользователь
├── docker/
│   ├── entrypoint.sh                # Авто-миграции при старте
│   ├── nginx/default.conf
│   ├── php/local.ini
│   └── mysql/my.cnf
├── Dockerfile
├── docker-compose.yml
└── Makefile
```

---

## 🚀 Быстрый старт

### 1. Клонировать репозиторий

```bash
git clone https://github.com/coderstack2007/adminpanel.git mywallet
cd mywallet
```

### 2. Настроить окружение

```bash
cp .env.example .env
```

Обязательно установить в `.env`:

```env
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=Wallet
DB_USERNAME=root
DB_PASSWORD=root
```

> ⚠️ `DB_HOST` должен быть `mysql` (имя сервиса Docker), а не `127.0.0.1`

### 3. Собрать и запустить

```bash
make build    # сборка Docker образов
make up       # запуск контейнеров
```

### 4. Первичная настройка БД

```bash
make fresh    # migrate:fresh + seed
```

Создаётся тестовый пользователь:
- **username:** `admin`
- **password:** `777`

---

## 🌐 Адреса

| Сервис       | URL                          |
|--------------|------------------------------|
| Приложение   | http://localhost:8080        |
| phpMyAdmin   | http://localhost:8081        |
| MySQL (хост) | `127.0.0.1:3307`             |

---

## ⚙️ Makefile команды

```bash
make build        # Собрать Docker образы
make up           # Запустить контейнеры
make down         # Остановить контейнеры
make restart      # Перезапустить

make migrate      # php artisan migrate
make seed         # php artisan db:seed
make fresh        # migrate:fresh --seed (⚠ дропает все таблицы)

make test         # Запустить тесты (PHPUnit)
make lint         # Laravel Pint (форматирование кода)

make shell        # Bash внутри контейнера app
make tinker       # Laravel Tinker

make cache-clear  # Очистить все кэши Laravel
make logs         # Логи всех контейнеров
```

---

## 💡 Функциональность

### Аутентификация
- Регистрация с автогенерацией номера карты (`8600 XXXX XXXX XXXX`)
- Вход / выход
- Начальный баланс при регистрации: **1 000 000**

### Платежи
- Перевод средств между пользователями
- Комиссия **10%** с каждого платежа
- Транзакции через `DB::transaction` (откат при ошибке)
- Проверка достаточности баланса

### Профиль
- Обновление username, email, фото
- Смена пароля с проверкой текущего

### Дашборд
- График доходов и расходов
- История всех платежей

---

## 🗄 База данных

### Таблица `users`

| Поле       | Тип          | Описание              |
|------------|--------------|-----------------------|
| id         | bigint       | Primary key           |
| username   | varchar(255) | Имя пользователя      |
| email      | varchar(255) | Email                 |
| card       | varchar(19)  | Номер карты           |
| balance    | int          | Текущий баланс        |
| profits    | int          | Сумма входящих        |
| expenses   | int          | Сумма исходящих       |
| password   | varchar(255) | Хэш пароля            |

### Таблица `payments`

| Поле     | Тип     | Описание                    |
|----------|---------|-----------------------------|
| id       | bigint  | Primary key                 |
| amount   | int     | Сумма (с комиссией)         |
| user_id  | bigint  | Получатель (FK → users)     |
| card     | string  | Карта получателя            |
| payer    | bigint  | Отправитель (FK → users)    |
| positive | boolean | Входящий платёж             |
| negative | boolean | Исходящий платёж            |

---

## 🔧 Решение проблем

**Connection refused / не подключается к MySQL**
```bash
# Убедись что DB_HOST=mysql в .env, затем:
make down && make up
# подождать 15 секунд
make fresh
```

**Ошибка при сборке (composer)**
```bash
make down
docker volume rm mywallet_wallet_mysql_data
make build
make up
```

**Таблица уже существует**
```bash
docker volume rm mywallet_wallet_mysql_data
make up && make fresh
```