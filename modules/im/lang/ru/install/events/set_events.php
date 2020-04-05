<?
$MESS["IM_NEW_NOTIFY_NAME"] = "Новое уведомление";
$MESS["IM_NEW_NOTIFY_DESC"] = "#MESSAGE_ID# - ID сообщения
#USER_ID# - ID пользователя
#USER_LOGIN# - Логин пользователя
#USER_NAME# - Имя пользователя
#USER_LAST_NAME# - Фамилия пользователя
#FROM_USER_ID# - ID отправителя сообщения
#FROM_USER# - Имя отправителя сообщения
#DATE_CREATE# - Дата создания сообщения
#TITLE# - Заголовок сообщения
#MESSAGE# - Сообщение
#MESSAGE_50# - Сообщение, первые 50 символов
#EMAIL_TO# - Email получателя письма";
$MESS["IM_NEW_NOTIFY_SUBJECT"] = "#SITE_NAME#: Уведомление \"#MESSAGE_50#\"";
$MESS["IM_NEW_NOTIFY_MESSAGE"] = "Здравствуйте, #USER_NAME#!

У вас есть новое уведомление от пользователя #FROM_USER#

------------------------------------------

#MESSAGE#

------------------------------------------

Перейти к уведомлениям: http://#SERVER_NAME#/?IM_NOTIFY=Y
Вы можете изменить настройки уведомлений: http://#SERVER_NAME#/?IM_SETTINGS=NOTIFY

Это письмо сформировано автоматически.";
$MESS["IM_NEW_NOTIFY_GROUP_NAME"] = "Новое уведомление (групповое)";
$MESS["IM_NEW_NOTIFY_GROUP_DESC"] = "#MESSAGE_ID# - ID сообщения
#USER_ID# - ID пользователя
#USER_LOGIN# - Логин пользователя
#USER_NAME# - Имя пользователя
#USER_LAST_NAME# - Фамилия пользователя
#FROM_USERS# - Имена отправителелей сообщения
#DATE_CREATE# - Дата создания сообщения
#TITLE# - Заголовок сообщения
#MESSAGE# - Текст уведомления
#MESSAGE_50# - Текст уведомления, первые 50 символов
#EMAIL_TO# - Email получателя письма";
$MESS["IM_NEW_NOTIFY_GROUP_SUBJECT"] = "#SITE_NAME#: Уведомление \"#MESSAGE_50#\"";
$MESS["IM_NEW_NOTIFY_GROUP_MESSAGE"] = "Здравствуйте, #USER_NAME#!

У вас есть новое уведомление от пользователей: #FROM_USERS#

------------------------------------------

#MESSAGE#

------------------------------------------

Перейти к уведомлениям: http://#SERVER_NAME#/?IM_NOTIFY=Y
Вы можете изменить настройки уведомлений: http://#SERVER_NAME#/?IM_SETTINGS=NOTIFY

Это письмо сформировано автоматически.";
$MESS["IM_NEW_MESSAGE_NAME"] = "Новое сообщение";
$MESS["IM_NEW_MESSAGE_DESC"] = "#USER_ID# - ID пользователя
#USER_LOGIN# - Логин пользователя
#USER_NAME# - Имя пользователя
#USER_LAST_NAME# - Фамилия пользователя
#FROM_USER# - Имя отправителя сообщения
#MESSAGES# - Блок сообщений
#EMAIL_TO# - Email получателя письма";
$MESS["IM_NEW_MESSAGE_SUBJECT"] = "#SITE_NAME#: Мгновенные сообщения от #FROM_USER#";
$MESS["IM_NEW_MESSAGE_MESSAGE"] = "Здравствуйте, #USER_NAME#!

У вас есть новые мгновенные сообщения от пользователя #FROM_USER#.

------------------------------------------
#MESSAGES#
------------------------------------------

Перейти к диалогу с пользователем: http://#SERVER_NAME#/?IM_DIALOG=#USER_ID#
Вы можете изменить настройки уведомлений: http://#SERVER_NAME#/?IM_SETTINGS=NOTIFY

Это письмо сформировано автоматически.";
$MESS["IM_NEW_MESSAGE_GROUP_NAME"] = "Новое сообщение (групповое)";
$MESS["IM_NEW_MESSAGE_GROUP_DESC"] = "#USER_ID# - ID пользователя
#USER_LOGIN# - Логин пользователя
#USER_NAME# - Имя пользователя
#USER_LAST_NAME# - Фамилия пользователя
#FROM_USERS# - Имена отправителелей сообщения
#MESSAGES# - Блок сообщений
#EMAIL_TO# - Email получателя письма";
$MESS["IM_NEW_MESSAGE_GROUP_SUBJECT"] = "#SITE_NAME#: Мгновенные сообщения от #FROM_USERS#";
$MESS["IM_NEW_MESSAGE_GROUP_MESSAGE"] = "Здравствуйте, #USER_NAME#!

У вас есть новые мгновенные сообщения от пользователей #FROM_USERS#.

#MESSAGES#

Перейти к диалогам: http://#SERVER_NAME#/?IM_DIALOG=Y
Вы можете изменить настройки уведомлений: http://#SERVER_NAME#/?IM_SETTINGS=NOTIFY

Это письмо сформировано автоматически.";
?>