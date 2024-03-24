<?php
$MESS['REST_INTEGRATION_PATTERNS_1_TITLE'] = 'Добавить лиды';
$MESS['REST_INTEGRATION_PATTERNS_1_DESCRIPTION'] = 'У вас есть форма обратной связи на сайте или своя база данных с клиентами? Добавляйте лиды оттуда простым обращением к Битрикс24';
$MESS['REST_INTEGRATION_PATTERNS_1_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/rest_help/crm/leads/crm_lead_add.php';
$MESS['REST_INTEGRATION_PATTERNS_1_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_1_INCOMING_QUERY_ITEMS_VALUE_0'] = 'Новый лид';
$MESS['REST_INTEGRATION_PATTERNS_1_INCOMING_QUERY_ITEMS_VALUE_1'] = 'Иван';
$MESS['REST_INTEGRATION_PATTERNS_1_INCOMING_QUERY_ITEMS_VALUE_2'] = 'Петров';
$MESS['REST_INTEGRATION_PATTERNS_1_INCOMING_QUERY_ITEMS_VALUE_3'] = 'mail@example.com';
$MESS['REST_INTEGRATION_PATTERNS_1_INCOMING_QUERY_ITEMS_VALUE_4'] = '555888';
$MESS['REST_INTEGRATION_PATTERNS_1_DESCRIPTION_SCOPE_DESCRIPTION'] = '<p>Для создания лидов необходим доступ к разделу CRM</p>';

$MESS['REST_INTEGRATION_PATTERNS_2_TITLE'] = 'Импортировать контрагентов';
$MESS['REST_INTEGRATION_PATTERNS_2_DESCRIPTION'] = 'У вас есть своя база постоянных клиентов? Перенесите их в контакты CRM Битрикс24!';
$MESS['REST_INTEGRATION_PATTERNS_2_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/rest_help/crm/contacts/crm_contact_add.php';
$MESS['REST_INTEGRATION_PATTERNS_2_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_2_INCOMING_QUERY_ITEMS_VALUE_0'] = 'Иван';
$MESS['REST_INTEGRATION_PATTERNS_2_INCOMING_QUERY_ITEMS_VALUE_1'] = 'Петров';
$MESS['REST_INTEGRATION_PATTERNS_2_INCOMING_QUERY_ITEMS_VALUE_2'] = 'mail@example.com';
$MESS['REST_INTEGRATION_PATTERNS_2_INCOMING_QUERY_ITEMS_VALUE_3'] = '555888';
$MESS['REST_INTEGRATION_PATTERNS_2_DESCRIPTION_METHOD_DESCRIPTION'] = '<p>Добавляет новый контакт</p>';
$MESS['REST_INTEGRATION_PATTERNS_2_DESCRIPTION_SCOPE_DESCRIPTION'] = '<p>Для создания контактов необходим доступ к разделу CRM</p>';

$MESS['REST_INTEGRATION_PATTERNS_3_TITLE'] = 'Экспортировать контрагентов';
$MESS['REST_INTEGRATION_PATTERNS_3_DESCRIPTION'] = 'Вам нужно выгрузить список клиентов недавно добавленных в Битрикс24 для использования в сервисе рассылок или сохранения в своей базе данных? Отфильтруйте и получите нужные адреса простым запросом!';
$MESS['REST_INTEGRATION_PATTERNS_3_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/rest_help/crm/contacts/crm_contact_list.php';
$MESS['REST_INTEGRATION_PATTERNS_3_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_3_DESCRIPTION_METHOD_DESCRIPTION'] = '<p>Выгружает список контактов по фильтру. Обратите внимание, что за один вызов выгружается максимум 50 элементов. Подробности читайте в документации</p>';
$MESS['REST_INTEGRATION_PATTERNS_3_DESCRIPTION_SCOPE_DESCRIPTION'] = '<p>Для получения контактов необходим доступ к разделу CRM</p>';

$MESS['REST_INTEGRATION_PATTERNS_4_TITLE'] = 'Добавить сотрудников';
$MESS['REST_INTEGRATION_PATTERNS_4_DESCRIPTION'] = 'Хотите автоматически приглашать новых сотрудников при приеме на работу?';
$MESS['REST_INTEGRATION_PATTERNS_4_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/rest_help/users/user_add.php';
$MESS['REST_INTEGRATION_PATTERNS_4_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_4_INCOMING_QUERY_ITEMS_VALUE_0'] = 'Добро пожаловать на портал нашей компании!';
$MESS['REST_INTEGRATION_PATTERNS_4_INCOMING_QUERY_ITEMS_VALUE_1'] = 'mail@example.com';
$MESS['REST_INTEGRATION_PATTERNS_4_DESCRIPTION_METHOD_DESCRIPTION'] = '<p>Приглашает пользователя. Пользователю будет выслано стандартное приглашение на портал</p>';
$MESS['REST_INTEGRATION_PATTERNS_4_DESCRIPTION_SCOPE_DESCRIPTION'] = '<p>Для приглашения необходим доступ к разделу Пользователей</p>';

$MESS['REST_INTEGRATION_PATTERNS_5_TITLE'] = 'Продвинуть лид по воронке';
$MESS['REST_INTEGRATION_PATTERNS_5_DESCRIPTION'] = 'Автоматически продвигайте лид по воронке продаж, меняя его стадию обращением из внешней системы!';
$MESS['REST_INTEGRATION_PATTERNS_5_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/rest_help/crm/leads/crm_lead_update.php';
$MESS['REST_INTEGRATION_PATTERNS_5_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_5_DESCRIPTION_METHOD_DESCRIPTION'] = '<p>Изменяет данные существующего лида и, в частности, статус STATUS_ID</p>';
$MESS['REST_INTEGRATION_PATTERNS_5_DESCRIPTION_SCOPE_DESCRIPTION'] = '<p>Для изменения лидов необходим доступ к разделу CRM</p>';

$MESS['REST_INTEGRATION_PATTERNS_6_TITLE'] = 'Поставить задачу';
$MESS['REST_INTEGRATION_PATTERNS_6_DESCRIPTION'] = 'Можно автоматически поставить задачу на основании письма. Просто подставьте данные из e-mail в нужные параметры';
$MESS['REST_INTEGRATION_PATTERNS_6_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/rest_help/tasks/task/tasks/tasks_task_add.php';
$MESS['REST_INTEGRATION_PATTERNS_6_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_6_INCOMING_QUERY_ITEMS_VALUE_0'] = 'Ответить на письмо';
$MESS['REST_INTEGRATION_PATTERNS_6_INCOMING_QUERY_ITEMS_VALUE_1'] = 'Текст письма';
$MESS['REST_INTEGRATION_PATTERNS_6_DESCRIPTION_METHOD_DESCRIPTION'] = '<p>Создает новую задачу</p>';
$MESS['REST_INTEGRATION_PATTERNS_6_DESCRIPTION_SCOPE_DESCRIPTION'] = '<p>Для добавления задачи необходим доступ к разделу Задач</p>';

$MESS['REST_INTEGRATION_PATTERNS_7_TITLE'] = 'Послать нотификацию';
$MESS['REST_INTEGRATION_PATTERNS_7_DESCRIPTION'] = 'Пошлите нотификацию о важной информации или событии нужному сотруднику';
$MESS['REST_INTEGRATION_PATTERNS_7_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=93&CHAPTER_ID=07693';
$MESS['REST_INTEGRATION_PATTERNS_7_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_7_INCOMING_QUERY_ITEMS_VALUE_0'] = 'Текст нотификации';
$MESS['REST_INTEGRATION_PATTERNS_7_DESCRIPTION_METHOD_DESCRIPTION'] = 'Отправляет нотификацию указанному сотруднику';
$MESS['REST_INTEGRATION_PATTERNS_7_DESCRIPTION_SCOPE_DESCRIPTION'] = '<p>Для отправки нотификаций необходим доступ к разделу Чат и уведомления</p>';

$MESS['REST_INTEGRATION_PATTERNS_8_TITLE'] = 'Опубликовать отчет в живой ленте';
$MESS['REST_INTEGRATION_PATTERNS_8_DESCRIPTION'] = 'Настройте автопубликацию отчетов из внешней системы прямо в живую ленту своего Битрикс24!';
$MESS['REST_INTEGRATION_PATTERNS_8_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/rest_help/log/log_blogpost_add.php';
$MESS['REST_INTEGRATION_PATTERNS_8_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_8_INCOMING_QUERY_ITEMS_VALUE_0'] = 'Автоотчет';
$MESS['REST_INTEGRATION_PATTERNS_8_INCOMING_QUERY_ITEMS_VALUE_1'] = 'Новый отчет о продажах за месяц';
$MESS['REST_INTEGRATION_PATTERNS_8_DESCRIPTION_METHOD_DESCRIPTION'] = 'Добавляет пост живой ленты';
$MESS['REST_INTEGRATION_PATTERNS_8_DESCRIPTION_SCOPE_DESCRIPTION'] = '<p>Для добавления поста необходим доступ к разделу Живая лента</p>';

$MESS['REST_INTEGRATION_PATTERNS_9_TITLE'] = 'Продвинуть сделку по воронке';
$MESS['REST_INTEGRATION_PATTERNS_9_DESCRIPTION'] = 'Автоматически продвигайте сделку по воронке продаж, меняя ее стадию обращением из внешней системы!';
$MESS['REST_INTEGRATION_PATTERNS_9_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/rest_help/crm/cdeals/crm_deal_update.php';
$MESS['REST_INTEGRATION_PATTERNS_9_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_9_DESCRIPTION_METHOD_DESCRIPTION'] = '<p>Изменяет данные существующей сделки и, в частности, стадию STAGE_ID</p>';
$MESS['REST_INTEGRATION_PATTERNS_9_DESCRIPTION_SCOPE_DESCRIPTION'] = '<p>Для изменения сделок необходим доступ к разделу CRM</p>';

$MESS['REST_INTEGRATION_PATTERNS_10_TITLE'] = 'Синхронизировать контрагентов';
$MESS['REST_INTEGRATION_PATTERNS_10_DESCRIPTION'] = 'Хотите автоматически узнавать об изменениях контактов в Битрикс24 и выгружать изменения в свою базу данных? Добавьте собственный обработчик событий REST!';
$MESS['REST_INTEGRATION_PATTERNS_10_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/rest_help/crm/contacts/crm_contact_get.php';
$MESS['REST_INTEGRATION_PATTERNS_10_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_10_DESCRIPTION_METHOD_DESCRIPTION'] = '<p>Получает данные указанной сделки</p>';
$MESS['REST_INTEGRATION_PATTERNS_10_DESCRIPTION_OUTGOING_DESCRIPTION'] = '<p>Вебхук срабатывает при любом изменении контактов в Битрикс24, а вы получаете в обработчик идентификатор измененного контакта и можете получить подробную информацию, выполнив запрос при помощи приведенного выше входящего вебхука</p>';
$MESS['REST_INTEGRATION_PATTERNS_10_DESCRIPTION_SCOPE_DESCRIPTION'] = '<p>Для данного примера нужны права на CRM, поскольку в нем используется метод crm.contact.get и событие на изменение данных CRM</p>';

$MESS['REST_INTEGRATION_PATTERNS_11_TITLE'] = 'Следить за задачами';
$MESS['REST_INTEGRATION_PATTERNS_11_DESCRIPTION'] = 'Хотите автоматически узнавать о закрытии задач исполнителями? Добавьте собственный обработчик событий REST!';
$MESS['REST_INTEGRATION_PATTERNS_11_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/rest_help/tasks/task/tasks/tasks_task_get.php';
$MESS['REST_INTEGRATION_PATTERNS_11_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_11_DESCRIPTION_METHOD_DESCRIPTION'] = '<p>Получает параметры указанной задачи</p>';
$MESS['REST_INTEGRATION_PATTERNS_11_DESCRIPTION_OUTGOING_DESCRIPTION'] = '<p>Вебхук срабатывает при любом изменении задач в Битрикс24, а вы получаете в обработчик идентификатор измененной задачи и можете получить подробную информацию о ней, выполнив запрос при помощи приведенного выше входящего вебхука</p>';
$MESS['REST_INTEGRATION_PATTERNS_11_DESCRIPTION_SCOPE_DESCRIPTION'] = '<p>Для данного примера нужны права на Задачи, поскольку в нем используется метод tasks.task.get и событие на изменение задач</p>';

$MESS['REST_INTEGRATION_PATTERNS_12_TITLE'] = 'Вывести свои данные в карточку CRM';
$MESS['REST_INTEGRATION_PATTERNS_12_DESCRIPTION'] = 'Добавьте свою закладку в карточке CRM и выводите туда нужные вам данные о клиенте';
$MESS['REST_INTEGRATION_PATTERNS_12_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/rest_help/crm/contacts/crm_contact_get.php';
$MESS['REST_INTEGRATION_PATTERNS_12_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_12_DESCRIPTION_METHOD_DESCRIPTION'] = '<p>Получает данные указанного контакта</p>';
$MESS['REST_INTEGRATION_PATTERNS_12_DESCRIPTION_WIDGET_DESCRIPTION'] = '<p>Выводит избранные данные текущего контакта на дополнительной закладке карточки CRM</p>';
$MESS['REST_INTEGRATION_PATTERNS_12_DESCRIPTION_SCOPE_DESCRIPTION'] = '<p>Для данного примера нужны права на CRM и на Встраивание приложений, поскольку в нем используется метод crm.contact.get и регистраци виджета в карточке CRM</p>';

$MESS['REST_INTEGRATION_PATTERNS_13_TITLE'] = 'Добавить свое действие в карточку CRM';
$MESS['REST_INTEGRATION_PATTERNS_13_DESCRIPTION'] = 'Добавьте свою кнопку с открывающимся слайдером в тайм-лайн карточки CRM и выводите туда нужные вам данные или производите нужные вам операции по обработке';
$MESS['REST_INTEGRATION_PATTERNS_13_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/rest_help/crm/contacts/crm_contact_get.php';
$MESS['REST_INTEGRATION_PATTERNS_13_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_13_DESCRIPTION_METHOD_DESCRIPTION'] = '<p>Получает данные указанного контакта</p>';
$MESS['REST_INTEGRATION_PATTERNS_13_DESCRIPTION_WIDGET_DESCRIPTION'] = '<p>Выводит в слайдере избранные данные текущего контакта и кнопку "Позвонить" для вызова через текущую телефонию</p>';
$MESS['REST_INTEGRATION_PATTERNS_13_DESCRIPTION_SCOPE_DESCRIPTION'] = '<p>Для данного примера нужны права на CRM и на Встраивание приложений, поскольку в нем используется метод crm.contact.get и регистраци виджета в карточке CRM</p>';

$MESS['REST_INTEGRATION_PATTERNS_14_TITLE'] = 'Добавить скрипт продаж в карточку звонка';
$MESS['REST_INTEGRATION_PATTERNS_14_DESCRIPTION'] = 'Добавьте свой скрипт продаж для оператора прямо в карточку звонка Битрикс24!';
$MESS['REST_INTEGRATION_PATTERNS_14_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/rest_help/crm/leads/crm_lead_get.php';
$MESS['REST_INTEGRATION_PATTERNS_14_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_14_DESCRIPTION_METHOD_DESCRIPTION'] = '<p>Получает данные указанного лида</p>';
$MESS['REST_INTEGRATION_PATTERNS_14_DESCRIPTION_WIDGET_DESCRIPTION'] = '<p>Выводит в карточке звонка подсказки оператору для общения с клиентом</p>';
$MESS['REST_INTEGRATION_PATTERNS_14_DESCRIPTION_SCOPE_DESCRIPTION'] = '<p>Для данного примера нужны права на CRM, Телефонию и Встраивание приложений, поскольку в нем используется метод crm.lead.get и регистрация виджета в карточке звонка</p>';

$MESS['REST_INTEGRATION_PATTERNS_15_TITLE'] = 'Формировать счет по трудозатратам задачи';
$MESS['REST_INTEGRATION_PATTERNS_15_DESCRIPTION'] = 'Добавьте свое действие с открывающимся слайдером в кнопку Ещё в карточке задачи для формирования счета на основе затрат времени у задачи';
$MESS['REST_INTEGRATION_PATTERNS_15_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/rest_help/tasks/task/elapseditem/getlist.php';
$MESS['REST_INTEGRATION_PATTERNS_15_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_15_DESCRIPTION_METHOD_DESCRIPTION'] = '<p>Возвращает список записей о затраченном времени по задаче. Обратите внимание, что за один вызов выгружается максимум 50 элементов. Подробности читайте в документации</p>';
$MESS['REST_INTEGRATION_PATTERNS_15_DESCRIPTION_WIDGET_DESCRIPTION'] = '<p>Добавляет пункт в меню кнопки Ещё в карточке задачи и выводит в слайдере прототип счета на основании данных о трудозатратах по текущей задаче</p>';
$MESS['REST_INTEGRATION_PATTERNS_15_DESCRIPTION_SCOPE_DESCRIPTION'] = '<p>Для данного примера нужны права на Задачи и Встраивание приложений, поскольку в нем используется метод task.elapseditem.getlist и регистрация виджета в карточке задачи</p>';

$MESS['REST_INTEGRATION_PATTERNS_16_TITLE'] = 'Другое';
$MESS['REST_INTEGRATION_PATTERNS_16_DESCRIPTION'] = 'Реализуйте свои сценарии получения и изменения данных в Битрикс24';
$MESS['REST_INTEGRATION_PATTERNS_16_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/rest_help/';
$MESS['REST_INTEGRATION_PATTERNS_16_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_16_INCOMING_QUERY_ITEMS_VALUE_0'] = 'Значение';
$MESS['REST_INTEGRATION_PATTERNS_16_DESCRIPTION_SCOPE_DESCRIPTION'] = 'Укажите нужные права в зависимости от использованных методов';

$MESS['REST_INTEGRATION_PATTERNS_17_TITLE'] = 'Другое';
$MESS['REST_INTEGRATION_PATTERNS_17_DESCRIPTION'] = 'Реализуйте свои сценарии добавления виджетов в Битрикс24';
$MESS['REST_INTEGRATION_PATTERNS_17_INCOMING_QUERY_INFORMATION_URL'] = 'https://dev.1c-bitrix.ru/rest_help/crm/leads/crm_lead_get.php';
$MESS['REST_INTEGRATION_PATTERNS_17_INCOMING_QUERY_TITLE'] = 'Параметры';

$MESS['REST_INTEGRATION_PATTERNS_18_TITLE'] = 'Информировать сотрудников в чате';
$MESS['REST_INTEGRATION_PATTERNS_18_DESCRIPTION'] = 'Создайте чатбота, который будет информировать сотрудников о важных событиях в индивидуальных или групповых чатах мессенджера Битрикс24';
$MESS['REST_INTEGRATION_PATTERNS_18_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_18_INCOMING_QUERY_ITEMS_VALUE_0'] = 'Привет! Я чат-бот!';

$MESS['REST_INTEGRATION_PATTERNS_19_TITLE_MSGVER_1'] = 'Передавать боту сообщения из чата';
$MESS['REST_INTEGRATION_PATTERNS_19_DESCRIPTION'] = 'Создайте чатбота, который будет следить за перепиской в чате и сможет реагировать на ключевые слова';
$MESS['REST_INTEGRATION_PATTERNS_19_INCOMING_QUERY_TITLE'] = 'Параметры';
$MESS['REST_INTEGRATION_PATTERNS_19_INCOMING_QUERY_ITEMS_VALUE_0'] = 'Привет! Я чат-бот!';

$MESS['REST_INTEGRATION_PATTERNS_1001_TITLE'] = 'Входящий вебхук';
$MESS['REST_INTEGRATION_PATTERNS_1001_DESCRIPTION'] = 'Создайте входящий вебхук, для работы с данными вашего Битрикс24 через API';
$MESS['REST_INTEGRATION_PATTERNS_1001_INCOMING_QUERY_TITLE_ITEMS'] = 'Параметры';

$MESS['REST_INTEGRATION_PATTERNS_1002_TITLE'] = 'Исходящий вебхук';
$MESS['REST_INTEGRATION_PATTERNS_1002_DESCRIPTION'] = 'Создайте исходящий вебхук, чтобы получать информацию о событиях, происходящих в вашем Битрикс24';

$MESS['REST_INTEGRATION_PATTERNS_1003_TITLE'] = 'Локальное приложение';
$MESS['REST_INTEGRATION_PATTERNS_1003_DESCRIPTION'] = 'Создайте приложение самостоятельно или закажите разработку у наших партнеров';
$MESS['REST_INTEGRATION_PATTERNS_1003_DESCRIPTION_FULL'] = 'Создайте приложение самостоятельно или <a target="_blank" href="https://www.bitrix24.ru/partners/" >закажите разработку у наших партнеров</a>';
$MESS['REST_INTEGRATION_PATTERNS_1003_DESCRIPTION_SCOPE_DESCRIPTION'] = 'Выберите доступный функционал Битрикс24 данному приложению';
