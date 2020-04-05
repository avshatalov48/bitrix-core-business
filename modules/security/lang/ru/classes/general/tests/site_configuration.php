<?
$MESS["SECURITY_SITE_CHECKER_SiteConfigurationTest_NAME"] = "Проверка настроек сайта";
$MESS["SECURITY_SITE_CHECKER_WAF_OFF"] = "Проактивный фильтр выключен";
$MESS["SECURITY_SITE_CHECKER_WAF_OFF_DETAIL"] = "Выключенный проактивный фильтр не сможет отразить попытки нападения на ресурс";
$MESS["SECURITY_SITE_CHECKER_WAF_OFF_RECOMMENDATION"] = "Включить проактивный фильтр: <a href=\"/bitrix/admin/security_filter.php\" target=\"_blank\">Проактивный фильтр</a>";
$MESS["SECURITY_SITE_CHECKER_REDIRECT_OFF"] = "Защита редиректов выключена";
$MESS["SECURITY_SITE_CHECKER_REDIRECT_OFF_DETAIL"] = "Редирект на произвольный сторонний ресурс может служить подспорьем для множества атак, защита редиректов исключает эту возможность (при использовании стандартного API)";
$MESS["SECURITY_SITE_CHECKER_REDIRECT_OFF_RECOMMENDATION"] = "Включить защиту редиректов: <a href=\"/bitrix/admin/security_redirect.php\" target=\"_blank\">Защита редиректов</a>";
$MESS["SECURITY_SITE_CHECKER_ADMIN_SECURITY_LEVEL"] = "Уровень безопасности административной группы не является повышенным";
$MESS["SECURITY_SITE_CHECKER_ADMIN_SECURITY_LEVEL_DETAIL"] = "Пониженный уровень безопасности административной группы может значительно помочь злоумышленнику";
$MESS["SECURITY_SITE_CHECKER_ADMIN_SECURITY_LEVEL_RECOMMENDATION"] = "Ужесточить <a href=\"/bitrix/admin/group_edit.php?ID=1&tabControl_active_tab=edit2\"  target=\"_blank\">политики безопасности административной</a> группы или выбрать предопределенную настройку уровня безопасности \"Повышенный\".";
$MESS["SECURITY_SITE_CHECKER_ERROR_REPORTING"] = "Уровень вывода ошибок должен быть \"только ошибки\" или \"не выводить\"";
$MESS["SECURITY_SITE_CHECKER_ERROR_REPORTING_DETAIL"] = "Отображение предупреждений php может позволить узнать полный физический путь к вашему проекту";
$MESS["SECURITY_SITE_CHECKER_ERROR_REPORTING_RECOMMENDATION"] = "Изменить уровень вывода ошибок на \"не выводить\": <a href=\"/bitrix/admin/settings.php?mid=main\" target=\"_blank\">Настройки главного модуля</a>";
$MESS["SECURITY_SITE_CHECKER_DB_DEBUG"] = "Включена отладка SQL запросов (\$DBDebug в значении true)";
$MESS["SECURITY_SITE_CHECKER_DB_DEBUG_DETAIL"] = "Отладка SQL запросов может раскрыть важную информацию о ресурсе";
$MESS["SECURITY_SITE_CHECKER_DB_DEBUG_RECOMMENDATION"] = "Выключить, установив значение переменной \$DBDebug в false";
$MESS["SECURITY_SITE_CHECKER_DB_EMPTY_PASS"] = "Пароль к базе данных пустой";
$MESS["SECURITY_SITE_CHECKER_DB_EMPTY_PASS_DETAIL"] = "Пустой пароль к БД повышает риск взлома учетной записи в базе данных";
$MESS["SECURITY_SITE_CHECKER_DB_EMPTY_PASS_RECOMMENDATION"] = "Установить пароль";
$MESS["SECURITY_SITE_CHECKER_DB_SAME_REGISTER_PASS"] = "Символы пароля к БД в одном регистре";
$MESS["SECURITY_SITE_CHECKER_DB_SAME_REGISTER_PASS_DETAIL"] = "Пароль слишком простой, что повышает риск взлома учетной записи в базе данных";
$MESS["SECURITY_SITE_CHECKER_DB_SAME_REGISTER_PASS_RECOMMENDATION"] = "Использовать различный регистр символов в пароле";
$MESS["SECURITY_SITE_CHECKER_DB_NO_DIT_PASS"] = "Пароль к БД не содержит чисел";
$MESS["SECURITY_SITE_CHECKER_DB_NO_DIT_PASS_DETAIL"] = "Пароль слишком прост, что повышает риск взлома учетной записи в базе данных";
$MESS["SECURITY_SITE_CHECKER_DB_NO_DIT_PASS_RECOMMENDATION"] = "Добавить чисел в пароль";
$MESS["SECURITY_SITE_CHECKER_DB_NO_SIGN_PASS"] = "Пароль к БД не содержит спецсимволов(знаков препинания)";
$MESS["SECURITY_SITE_CHECKER_DB_NO_SIGN_PASS_DETAIL"] = "Пароль слишком прост, что повышает риск взлома учетной записи в базе данных";
$MESS["SECURITY_SITE_CHECKER_DB_NO_SIGN_PASS_RECOMMENDATION"] = "Добавить спецсимволов в пароль";
$MESS["SECURITY_SITE_CHECKER_DB_MIN_LEN_PASS"] = "Длина пароля к БД меньше 8 символов";
$MESS["SECURITY_SITE_CHECKER_DB_MIN_LEN_PASS_DETAIL"] = "Пароль слишком прост, что повышает риск взлома учетной записи в базе данных";
$MESS["SECURITY_SITE_CHECKER_DB_MIN_LEN_PASS_RECOMMENDATION"] = "Увеличить длину пароля";
$MESS["SECURITY_SITE_CHECKER_DANGER_EXTENSIONS"] = "Ограничен список потенциально опасных расширений файлов";
$MESS["SECURITY_SITE_CHECKER_DANGER_EXTENSIONS_DETAIL"] = "Текущий список расширений файлов, которые считаются потенциально опасными, не содержит всех рекомендованных значений. Список расширений исполняемых файлов всегда должен находится в актуальном состоянии";
$MESS["SECURITY_SITE_CHECKER_DANGER_EXTENSIONS_RECOMMENDATION"] = "Вы всегда можете изменить список расширений исполняемых файлов в настройках сайта: <a href=\"/bitrix/admin/settings.php?mid=fileman\" target=\"_blank\">Управление структурой</a>";
$MESS["SECURITY_SITE_CHECKER_DANGER_EXTENSIONS_ADDITIONAL"] = "Текущие: #ACTUAL#<br>
Рекомендованные (без учета настроек вашего сервера): #EXPECTED#<br>
Отсутствующие: #MISSING#";
$MESS["SECURITY_SITE_CHECKER_EXCEPTION_DEBUG"] = "Включен расширенный вывод ошибок";
$MESS["SECURITY_SITE_CHECKER_EXCEPTION_DEBUG_DETAIL"] = "Расширенный вывод ошибок может раскрыть важную информацию о ресурсе";
$MESS["SECURITY_SITE_CHECKER_EXCEPTION_DEBUG_RECOMMENDATION"] = "Выключить в файле настроек .settings.php";
$MESS["SECURITY_SITE_CHECKER_MODULES_VERSION"] = "Используются устаревшие модули платформы";
$MESS["SECURITY_SITE_CHECKER_MODULES_VERSION_DETAIL"] = "Доступны новые версии модулей";
$MESS["SECURITY_SITE_CHECKER_MODULES_VERSION_RECOMMENDATION"] = "Рекомендуется своевременно обновлять модули платформы, установить рекомендуемые обновления: <a href=\"/bitrix/admin/update_system.php\" target=\"_blank\">Обновление платформы</a>";
$MESS["SECURITY_SITE_CHECKER_MODULES_VERSION_ERROR"] = "Не удалось проверить доступность обновлений платформы";
$MESS["SECURITY_SITE_CHECKER_MODULES_VERSION_ERROR_DETAIL"] = "Возможно доступно обновление системы SiteUpdate или у вашей копии продукта истек период получения обновлений";
$MESS["SECURITY_SITE_CHECKER_MODULES_VERSION_ERROR_RECOMMENDATION"] = "Подробнее на странице: <a href=\"/bitrix/admin/update_system.php\" target=\"_blank\">Обновление платформы</a>";
$MESS["SECURITY_SITE_CHECKER_MODULES_VERSION_ARRITIONAL"] = "Модули для которых доступны обновления:<br>#MODULES#";
?>