# AmoCRM API Integration for Lead and Contact Management

Этот проект обеспечивает интеграцию с AmoCRM API, позволяя создавать и связывать лиды (сделки) и контакты через POST-запросы. Проект автоматизирует отправку заявок с сайта, добавляя данные пользователей в AmoCRM и устанавливая настраиваемые поля, такие как checkbox и multitext.

## Стек технологий

- PHP 7.4+
- Composer
- [AmoCRM PHP SDK](https://github.com/amocrm/amocrm-api-php) для работы с API
- [Dotenv](https://github.com/vlucas/phpdotenv) для управления переменными среды

## Установка

### Шаг 1: Клонирование проекта
```bash
git clone https://github.com/kdovlet777/amocrm.git
cd amocrm
```

### Шаг 2: Установка зависимостей
```bash
composer install
```

### Шаг 3: Настройка переменных окружения
Создайте файл .env по примеру .env.example

```dotenv
ACCESS_TOKEN=ваш_токен
```

## Где посмотреть
Проект развернут и успешно функционирует на сайте [dovlet.moscow](https://dovlet.moscow)