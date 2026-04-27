# Сервис рекомендаций товаров

Система рекомендаций товаров на базе Symfony 7, PostgreSQL, Redis и RabbitMQ.

## Стек

- **PHP 8.3** + Symfony 7.2
- **PostgreSQL 16** — каталог товаров и категорий
- **Redis 7.4** — хранение просмотров, трендов, кэша рекомендаций
- **RabbitMQ 3.13** — асинхронная обработка событий

## Запуск

```bash
docker compose up -d
docker exec php php bin/console doctrine:migrations:migrate --no-interaction
docker exec php php bin/console app:setup-queues
```

## Консольные команды

### Настройка

```bash
# создать очереди и exchange в RabbitMQ
docker exec php php bin/console app:setup-queues
```

### Консумеры (запускать в отдельных терминалах или через -d)

```bash
# обработка товаров
docker exec -d php php bin/console app:create-product
docker exec -d php php bin/console app:update-product
docker exec -d php php bin/console app:delete-product

# обработка просмотров
docker exec -d php php bin/console app:view-write-top
docker exec -d php php bin/console app:view-write-trend
docker exec -d php php bin/console app:view-user
```

Запустить все сразу:
```bash
for cmd in app:create-product app:update-product app:delete-product app:view-write-top app:view-write-trend app:view-user; do
    docker exec -d php php bin/console $cmd
done
```

### Агрегация трендов (крон, раз в час)

```bash
docker exec php php bin/console app:calculation-trends
```

### Тестовые данные

```bash
# загрузить товары и категории в PostgreSQL
docker exec php php bin/console doctrine:fixtures:load --no-interaction

# заполнить тренды в Redis (--top-product — ID товара который должен быть в топе)
docker exec php php bin/console app:fill-trends --views=3000 --top-product=1

# заполнить топ категорий (--top-products — через запятую ID топовых товаров)
docker exec php php bin/console app:fill-top-category --top-products=1,2,3
```

## API

### Товары

| Метод | URL | Описание |
|-------|-----|----------|
| POST | `/api/v1/product/` | Создать товар |
| PUT | `/api/v1/product/{id}` | Обновить товар |
| DELETE | `/api/v1/product/{id}` | Удалить товар |

Пример создания:
```json
POST /api/v1/product/
{
    "name": "iPhone 15",
    "description": "Смартфон Apple",
    "price": "999.99",
    "category_id": 1,
    "attributes": {
        "brand": "Apple",
        "color": "black",
        "size": "6.1"
    }
}
```

### Просмотры

| Метод | URL | Описание |
|-------|-----|----------|
| POST | `/api/v1/product/view/` | Записать просмотр товара |

```json
POST /api/v1/product/view/
{
    "product_id": 1,
    "user_id": 42,
    "category_id": 5
}
```

### Рекомендации

| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/api/v1/recommendation/trends` | Трендовые товары |
| POST | `/api/v1/recommendation/top` | Топ товаров в категории |
| POST | `/api/v1/recommendation/user/view` | Последние просмотры пользователя |
| POST | `/api/v1/recommendation/attr` | Похожие по атрибутам |

```json
POST /api/v1/recommendation/top
{
    "category_id": 5
}

POST /api/v1/recommendation/user/view
{
    "user_id": 42
}

POST /api/v1/recommendation/attr
{
    "product_id": 1,
    "match": "all",
    "limit": 10
}
```

Параметр `match`:
- `all` — совпадение по всем атрибутам
- `priority` — важные атрибуты обязательны, второстепенные влияют на сортировку

## Архитектура

```
HTTP Request
    ↓
Controller → Producer → RabbitMQ → Consumer → PostgreSQL
                                       ↓
                                    Redis (просмотры)

GET /recommendation/*
    ↓
Service → Redis (кэш/агрегация) → PostgreSQL (детали товаров)
```

### Redis ключи

| Ключ | Тип | Описание |
|------|-----|----------|
| `views:{Y-m-d-H}` | ZSet | Просмотры товаров по часам |
| `category:{id}:top:{d.m.Y}` | ZSet | Топ товаров в категории по дням |
| `view:user:{id}` | List | Последние 10 просмотров пользователя |
| `cache:trends` | String | Закэшированные тренды (JSON) |
