<?php

$MESS['ABTEST_ADD_TITLE'] = "Создание A/B-теста";
$MESS['ABTEST_EDIT_TITLE1'] = "Редактирование A/B-теста ##ID#";
$MESS['ABTEST_EDIT_TITLE2'] = "Редактирование A/B-теста: #NAME#";

$MESS['ABTEST_GOTO_LIST'] = "Список тестов";
$MESS['ABTEST_GOTO_ADD'] = "Новый тест";
$MESS['ABTEST_DELETE'] = "Удалить";

$MESS['ABTEST_DELETE_CONFIRM'] = "Вы действительно хотите удалить тест?";

$MESS['ABTEST_EMPTY_SITE'] = "Не задан сайт";
$MESS['ABTEST_UNKNOWN_SITE'] = "Неизвестный сайт: #VALUE#";

$MESS['ABTEST_PORTION_ERROR'] = "Недопустимое значение для поля \"трафик на тест\"";
$MESS['ABTEST_TEST_DATA_ERROR'] = "Ошибка списка тестов";

$MESS['ABTEST_PORTION_HINT'] = "Доля трафика не может быть меньше 1% или больше 100%.";
$MESS['ABTEST_EMPTY_TEST_DATA'] = "Не задано ни одного теста.";

$MESS['ABTEST_UNKNOWN_TEST_TYPE'] = "Неизвестный тип теста ##ID#: #VALUE#.";
$MESS['ABTEST_EMPTY_TEST_VALUES'] = "Не заданы значения для теста ##ID#.";
$MESS['ABTEST_EMPTY_TEST_VALUE'] = "Не задано значение для теста ##ID#.";

$MESS['ABTEST_UNKNOWN_TEST_TEMPLATE'] = "Неизвестный шаблон для теста ##ID#: #VALUE#";
$MESS['ABTEST_UNKNOWN_TEST_PAGE'] = "Несуществующая страница для теста ##ID#: #VALUE#";


$MESS['ABTEST_SAVE_ERROR'] = "Ошибка сохранения теста";

$MESS['ABTEST_TAB_NAME'] = "A/B-тест";
$MESS['ABTEST_TAB_TITLE'] = "Параметры теста";

$MESS['ABTEST_SITE_FIELD'] = "Сайт";
$MESS['ABTEST_NAME_FIELD'] = "Название";
$MESS['ABTEST_DESCR_FIELD'] = "Описание";
$MESS['ABTEST_DURATION_FIELD'] = "Длительность теста";
$MESS['ABTEST_PORTION_FIELD'] = "Трафик на тест";

$MESS['ABTEST_DURATION_OPTION_1'] = "1 день";
$MESS['ABTEST_DURATION_OPTION_3'] = "3 дня";
$MESS['ABTEST_DURATION_OPTION_5'] = "5 дней";
$MESS['ABTEST_DURATION_OPTION_7'] = "Неделя";
$MESS['ABTEST_DURATION_OPTION_14'] = "2 недели";
$MESS['ABTEST_DURATION_OPTION_30'] = "Месяц";
$MESS['ABTEST_DURATION_OPTION_0'] = "До ручной остановки теста";

$MESS['ABTEST_DURATION_OPTION_C'] = "Дней: #NUM#";
$MESS['ABTEST_DURATION_OPTION_A'] = "Авто (примерно дней: #NUM#)";
$MESS['ABTEST_DURATION_OPTION_NA'] = "н/д";

$MESS['ABTEST_TEST_DATA'] = "Тесты";

$MESS['ABTEST_TEST_TEMPLATE_TITLE'] = "Шаблон сайта";
$MESS['ABTEST_TEST_TEMPLATE_TITLE_A'] = "Текущий шаблон";
$MESS['ABTEST_TEST_TEMPLATE_TITLE_B'] = "Тестовый шаблон";

$MESS['ABTEST_TEST_PAGE_TITLE'] = "Страница";
$MESS['ABTEST_TEST_PAGE_TITLE_A'] = "Текущая страница";
$MESS['ABTEST_TEST_PAGE_TITLE_B'] = "Путь к новой странице";

$MESS['ABTEST_TEST_ADD'] = "Добавить тест";
$MESS['ABTEST_TEST_TITLE'] = "Тест<span class=\"test-num\">:</span> #TYPE#";

$MESS['ABTEST_TEST_SELECT_PAGE'] = "Выбрать файл";
$MESS['ABTEST_TEST_COPY_PAGE'] = "Скопировать страницу";
$MESS['ABTEST_TEST_EDIT_PAGE'] = "Редактировать страницу";
$MESS['ABTEST_AJAX_ERROR'] = "Ошибка при выполнении запроса";

$MESS['ABTEST_UNKNOWN_PAGE'] = "Страница не существует";

$MESS['ABTEST_TEST_CHECK'] = "Посмотреть";

$MESS['ABTEST_TEST_EDIT_WARNING'] = "<b>Внимание!</b> Изменение активного A/B-теста может привести к искажению результатов!";

$MESS['ABTEST_DURATION_AUTO_HINT'] = 'Автоматическая длительность теста &mdash; прогнозируется на основе текущей посещаемости и величины выборки, необходимой для достижения статистической мощности 80%. Тест будет завершен автоматически после получения необходимой выборки в обоих группах.';
$MESS['ABTEST_MATH_POWER_HINT'] = 'Статистическая мощность &mdash; вероятность того, что тест определит разницу между двумя вариантами, если эта разница действительно существует. Статистическая мощность увеличивается при увеличении размера выборки. Если статистическая мощность меньше 80%, то доверять результатам теста нельзя.';
