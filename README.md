# Ядро "1С-Битрикс: Управление сайтом" Бизнес

* Редакция "Бизнес" включает в себя модули редакций "Малый бизнес", "Стандарт" и "Старт".
* Кодировка: UTF-8
* Только стабильные

## Для чего нужен данный репозиторий

* Отследить изменения в релизах "1С-Битрикс: Управление сайтом" Бизнес:
    * Т.е., в каком релизе добавился тот или иной модуль/компонент, какие изменения в нём произошли.

* Для быстрой корректировки проекта, например, на сервере, без необходимости скачивания или установки всего проекта:
    * Каталог /modules/ - 20.0.650 (25.02.2020) - содержит 52554 файлов
    * Просто добавляем в индексацию IDE и всё.

* Быстрое индексирование проекта PHPStorm при повторном подключении:
    * Т.е. достаточно подключать только данный каталог, без необходимости повторной индексации всей папки /bitrix/ (содержит более 90 тыс.файлов) на каждом проекте. Особенно актуально для медленных ПК без SSD;
    * Просто, храните данный репозиторий в одном постоянном месте, а проекты, где будет удобно.

* Вы всегда можете оперативно переключиться на нужную доступную версию ядра.

## Подключение в PHPStorm (рекомендуемый)

* 'Меню' > 'File' > 'Settings' > 'Languages & Frameworks' > 'PHP' > 'Include Path' > '+' > 'Путь к каталогу modules'

![PHPStorm](./images/phpstorm.png "Подключение в PHPStorm")

## Composer

* `composer require avshatalov48/bitrix-core-business:dev-master --dev`

## Разное

* С 01.03.2024 будет ограничена поддержка наших продуктов на PHP версии ниже 8.1. Рекомендуемая версия PHP - 8.2 и выше.
* С 01.02.2023 будет ограничена поддержка наших продуктов на PHP версии ниже 8.0. Рекомендуемая версия PHP - 8.1 и выше.
* С 01.02.2022 будет ограничена поддержка наших продуктов на PHP версии ниже 7.4.0. Рекомендуемая версия PHP - 7.4.0 и выше.
* С 01.04.2021 будет ограничена поддержка наших продуктов на PHP версии ниже 7.3.0. Рекомендуемая версия PHP - 7.4.0 и выше.
* 20.100.0 и выше - Совместимость с PHP 8
* 20.5.393 и выше - php mbstring.func_overload 0

## Полезные ссылки

* [История версий. Последние изменения в "1С-Битрикс: Управление сайтом"](https://dev.1c-bitrix.ru/docs/versions.php)
* [Ядро "1С-Битрикс24" Корпоративный портал. Каталог /modules/](https://github.com/avshatalov48/bitrix24-core-corp/)
* [Скачать "1С-Битрикс: Управление сайтом"](https://www.1c-bitrix.ru/download/cms.php)
* [Регистрация пробных версий продуктов и решений «1С-Битрикс»](https://www.1c-bitrix.ru/bsm_register.php)
