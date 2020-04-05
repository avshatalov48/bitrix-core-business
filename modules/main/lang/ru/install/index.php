<?
$MESS["MAIN_ADMIN_GROUP_NAME"] = "Администраторы";
$MESS["MAIN_ADMIN_GROUP_DESC"] = "Полный доступ к управлению сайтом.";
$MESS["MAIN_EVERYONE_GROUP_NAME"] = "Все пользователи (в том числе неавторизованные)";
$MESS["MAIN_EVERYONE_GROUP_DESC"] = "Все пользователи, включая неавторизованных.";
$MESS["MAIN_VOTE_RATING_GROUP_NAME"] = "Пользователи, имеющие право голосовать за рейтинг";
$MESS["MAIN_VOTE_RATING_GROUP_DESC"] = "В эту группу пользователи добавляются автоматически.";
$MESS["MAIN_VOTE_AUTHORITY_GROUP_NAME"] = "Пользователи имеющие право голосовать за авторитет";
$MESS["MAIN_VOTE_AUTHORITY_GROUP_DESC"] = "В эту группу пользователи добавляются автоматически.";
$MESS["MAIN_RULE_ADD_GROUP_AUTHORITY_NAME"] = "Добавление в группу пользователей, имеющих право голосовать за авторитет";
$MESS["MAIN_RULE_ADD_GROUP_RATING_NAME"] = "Добавление в группу пользователей, имеющих право голосовать за рейтинг";
$MESS["MAIN_RULE_REM_GROUP_AUTHORITY_NAME"] = "Удаление из группы пользователей, не имеющих права голосовать за авторитет";
$MESS["MAIN_RULE_REM_GROUP_RATING_NAME"] = "Удаление из группы пользователей, не имеющих права голосовать за рейтинг";
$MESS["MAIN_RULE_AUTO_AUTHORITY_VOTE_NAME"] = "Автоматическое голосование за авторитет пользователя";
$MESS["MAIN_RATING_NAME"] = "Рейтинг";
$MESS["MAIN_RATING_AUTHORITY_NAME"] = "Авторитет";
$MESS["MAIN_RATING_TEXT_LIKE_Y"] = "Нравится";
$MESS["MAIN_RATING_TEXT_LIKE_N"] = "Не нравится";
$MESS["MAIN_RATING_TEXT_LIKE_D"] = "Это нравится";
$MESS["MAIN_DEFAULT_SITE_NAME"] = "Сайт по умолчанию";
$MESS["MAIN_DEFAULT_LANGUAGE_NAME"] = "Russian";
$MESS["MAIN_DEFAULT_LANGUAGE_FORMAT_DATE"] = "DD.MM.YYYY";
$MESS["MAIN_DEFAULT_LANGUAGE_FORMAT_DATETIME"] = "DD.MM.YYYY HH:MI:SS";
$MESS["MAIN_DEFAULT_LANGUAGE_FORMAT_NAME"] = "#NAME# #LAST_NAME#";
$MESS["MAIN_DEFAULT_LANGUAGE_FORMAT_CHARSET"] = "windows-1251";
$MESS["MAIN_DEFAULT_SITE_FORMAT_DATE"] = "DD.MM.YYYY";
$MESS["MAIN_DEFAULT_SITE_FORMAT_DATETIME"] = "DD.MM.YYYY HH:MI:SS";
$MESS["MAIN_DEFAULT_SITE_FORMAT_NAME"] = "#NAME# #LAST_NAME#";
$MESS["MAIN_DEFAULT_SITE_FORMAT_CHARSET"] = "windows-1251";
$MESS["MAIN_SMILE_DEF_SET_NAME"] = "Основной набор";
$MESS["MAIN_MODULE_NAME"] = "Главный модуль";
$MESS["MAIN_MODULE_DESC"] = "Ядро системы";
$MESS["MAIN_INSTALL_DB_ERROR"] = "Не могу соединиться с базой данных. Проверьте правильность введенных параметров";
$MESS["MAIN_NEW_USER_TYPE_NAME"] = "Зарегистрировался новый пользователь";
$MESS["MAIN_NEW_USER_TYPE_DESC"] = "

#USER_ID# - ID пользователя
#LOGIN# - Логин
#EMAIL# - EMail
#NAME# - Имя
#LAST_NAME# - Фамилия
#USER_IP# - IP пользователя
#USER_HOST# - Хост пользователя
";
$MESS["MAIN_USER_INFO_TYPE_NAME"] = "Информация о пользователе";
$MESS["MAIN_USER_INFO_TYPE_DESC"] = "

#USER_ID# - ID пользователя
#STATUS# - Статус логина
#MESSAGE# - Сообщение пользователю
#LOGIN# - Логин
#URL_LOGIN# - Логин, закодированный для использования в URL
#CHECKWORD# - Контрольная строка для смены пароля
#NAME# - Имя
#LAST_NAME# - Фамилия
#EMAIL# - E-Mail пользователя
";
$MESS["MAIN_NEW_USER_CONFIRM_TYPE_NAME"] = "Подтверждение регистрации нового пользователя";
$MESS["MAIN_NEW_USER_CONFIRM_TYPE_DESC"] = "


#USER_ID# - ID пользователя
#LOGIN# - Логин
#EMAIL# - EMail
#NAME# - Имя
#LAST_NAME# - Фамилия
#USER_IP# - IP пользователя
#USER_HOST# - Хост пользователя
#CONFIRM_CODE# - Код подтверждения
";
$MESS["MAIN_USER_INVITE_TYPE_NAME"] = "Приглашение на сайт нового пользователя";
$MESS["MAIN_USER_INVITE_TYPE_DESC"] = "#ID# - ID пользователя
#LOGIN# - Логин
#URL_LOGIN# - Логин, закодированный для использования в URL
#EMAIL# - EMail
#NAME# - Имя
#LAST_NAME# - Фамилия
#PASSWORD# - пароль пользователя 
#CHECKWORD# - Контрольная строка для смены пароля
#XML_ID# - ID пользователя для связи с внешними источниками
";
$MESS["MAIN_NEW_USER_EVENT_NAME"] = "#SITE_NAME#: Зарегистрировался новый пользователь";
$MESS["MAIN_NEW_USER_EVENT_DESC"] = "Информационное сообщение сайта #SITE_NAME#
------------------------------------------

На сайте #SERVER_NAME# успешно зарегистрирован новый пользователь.

Данные пользователя:
ID пользователя: #USER_ID#

Имя: #NAME#
Фамилия: #LAST_NAME#
E-Mail: #EMAIL#

Login: #LOGIN#

Письмо сгенерировано автоматически.";
$MESS["MAIN_USER_INFO_EVENT_NAME"] = "#SITE_NAME#: Регистрационная информация";
$MESS["MAIN_USER_INFO_EVENT_DESC"] = "Информационное сообщение сайта #SITE_NAME#
------------------------------------------
#NAME# #LAST_NAME#,

#MESSAGE#

Ваша регистрационная информация:

ID пользователя: #USER_ID#
Статус профиля: #STATUS#
Login: #LOGIN#

Вы можете изменить пароль, перейдя по следующей ссылке:
http://#SERVER_NAME#/auth/index.php?change_password=yes&lang=ru&USER_CHECKWORD=#CHECKWORD#&USER_LOGIN=#URL_LOGIN#

Сообщение сгенерировано автоматически.";
$MESS["MAIN_USER_PASS_REQUEST_EVENT_DESC"] = "Информационное сообщение сайта #SITE_NAME#
------------------------------------------
#NAME# #LAST_NAME#,

#MESSAGE#

Для смены пароля перейдите по следующей ссылке:
http://#SERVER_NAME#/auth/index.php?change_password=yes&lang=ru&USER_CHECKWORD=#CHECKWORD#&USER_LOGIN=#URL_LOGIN#

Ваша регистрационная информация:

ID пользователя: #USER_ID#
Статус профиля: #STATUS#
Login: #LOGIN#

Сообщение сгенерировано автоматически.";
$MESS["MAIN_USER_PASS_CHANGED_EVENT_DESC"] = "Информационное сообщение сайта #SITE_NAME#
------------------------------------------
#NAME# #LAST_NAME#,

#MESSAGE#

Ваша регистрационная информация:

ID пользователя: #USER_ID#
Статус профиля: #STATUS#
Login: #LOGIN#

Сообщение сгенерировано автоматически.";
$MESS["MAIN_NEW_USER_CONFIRM_EVENT_NAME"] = "#SITE_NAME#: Подтверждение регистрации нового пользователя";
$MESS["MAIN_NEW_USER_CONFIRM_EVENT_DESC"] = "Информационное сообщение сайта #SITE_NAME#
------------------------------------------

Здравствуйте,

Вы получили это сообщение, так как ваш адрес был использован при регистрации нового пользователя на сервере #SERVER_NAME#.

Ваш код для подтверждения регистрации: #CONFIRM_CODE#

Для подтверждения регистрации перейдите по следующей ссылке:
http://#SERVER_NAME#/auth/index.php?confirm_registration=yes&confirm_user_id=#USER_ID#&confirm_code=#CONFIRM_CODE#

Вы также можете ввести код для подтверждения регистрации на странице:
http://#SERVER_NAME#/auth/index.php?confirm_registration=yes&confirm_user_id=#USER_ID#

Внимание! Ваш профиль не будет активным, пока вы не подтвердите свою регистрацию.

---------------------------------------------------------------------

Сообщение сгенерировано автоматически.";
$MESS["MAIN_USER_INVITE_EVENT_NAME"] = "#SITE_NAME#: Приглашение на сайт";
$MESS["MAIN_USER_INVITE_EVENT_DESC"] = "Информационное сообщение сайта #SITE_NAME#
------------------------------------------
Здравствуйте, #NAME# #LAST_NAME#!

Администратором сайта вы добавлены в число зарегистрированных пользователей.

Приглашаем Вас на наш сайт.

Ваша регистрационная информация:

ID пользователя: #ID#
Login: #LOGIN#

Рекомендуем вам сменить установленный автоматически пароль.

Для смены пароля перейдите по следующей ссылке:
http://#SERVER_NAME#/auth.php?change_password=yes&USER_LOGIN=#URL_LOGIN#&USER_CHECKWORD=#CHECKWORD#
";
$MESS["MF_EVENT_NAME"] = "Отправка сообщения через форму обратной связи";
$MESS["MF_EVENT_DESCRIPTION"] = "#AUTHOR# - Автор сообщения
#AUTHOR_EMAIL# - Email автора сообщения
#TEXT# - Текст сообщения
#EMAIL_FROM# - Email отправителя письма
#EMAIL_TO# - Email получателя письма";
$MESS["MF_EVENT_SUBJECT"] = "#SITE_NAME#: Сообщение из формы обратной связи";
$MESS["MF_EVENT_MESSAGE"] = "Информационное сообщение сайта #SITE_NAME#
------------------------------------------

Вам было отправлено сообщение через форму обратной связи

Автор: #AUTHOR#
E-mail автора: #AUTHOR_EMAIL#

Текст сообщения:
#TEXT#

Сообщение сгенерировано автоматически.";
$MESS["MAIN_USER_PASS_REQUEST_TYPE_NAME"] = "Запрос на смену пароля";
$MESS["MAIN_USER_PASS_CHANGED_TYPE_NAME"] = "Подтверждение смены пароля";
$MESS["MAIN_USER_PASS_REQUEST_EVENT_NAME"] = "#SITE_NAME#: Запрос на смену пароля";
$MESS["MAIN_USER_PASS_CHANGED_EVENT_NAME"] = "#SITE_NAME#: Подтверждение смены пароля";
$MESS["MAIN_DESKTOP_CREATEDBY_KEY"] = "Создатель сайта";
$MESS["MAIN_DESKTOP_CREATEDBY_VALUE"] = "Группа компаний &laquo;1С-Битрикс&raquo;.";
$MESS["MAIN_DESKTOP_URL_KEY"] = "Адрес сайта";
$MESS["MAIN_DESKTOP_URL_VALUE"] = "<a href=\"http://www.1c-bitrix.ru\">www.1c-bitrix.ru</a>";
$MESS["MAIN_DESKTOP_PRODUCTION_KEY"] = "Сайт сдан";
$MESS["MAIN_DESKTOP_PRODUCTION_VALUE"] = "12 декабря 2010 г.";
$MESS["MAIN_DESKTOP_RESPONSIBLE_KEY"] = "Ответственное лицо";
$MESS["MAIN_DESKTOP_RESPONSIBLE_VALUE"] = "Иван Иванов";
$MESS["MAIN_DESKTOP_EMAIL_KEY"] = "E-mail";
$MESS["MAIN_DESKTOP_EMAIL_VALUE"] = "<a href=\"mailto:info@1c-bitrix.ru\">info@1c-bitrix.ru</a>";
$MESS["MAIN_DESKTOP_INFO_TITLE"] = "Информация о сайте";
$MESS["MAIN_DESKTOP_RSS_TITLE"] = "Новости 1С-Битрикс";

$MESS["MAIN_MAIL_CONFIRM_EVENT_TYPE_NAME"] = "Подтверждение email-адреса отправителя";
$MESS["MAIN_MAIL_CONFIRM_EVENT_TYPE_DESC"] = "

#EMAIL_TO# - Email-адрес для подтверждения
#MESSAGE_SUBJECT# - Тема сообщения
#CONFIRM_CODE# - Код подтверждения";
$MESS["main_install_sms_event_confirm_name"] = "Подтверждение номера телефона по СМС";
$MESS["main_install_sms_event_confirm_descr"] = "#USER_PHONE# - номер телефона
#CODE# - код подтверждения
";
$MESS["main_install_sms_event_restore_name"] = "Восстановление пароля через СМС";
$MESS["main_install_sms_event_restore_descr"] = "#USER_PHONE# - номер телефона
#CODE# - код для восстановления
";
$MESS["main_install_sms_template_confirm_mess"] = "Код подтверждения #CODE#";
$MESS["main_install_sms_template_restore_mess"] = "Код для восстановления пароля #CODE#";
?>