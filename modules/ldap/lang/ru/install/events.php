<?
$MESS ['LDAP_USER_CONFIRM_TYPE_NAME'] = "Подтверждение регистрации";
$MESS ['LDAP_USER_CONFIRM_TYPE_DESC'] = "
#USER_ID# - ID пользователя
#EMAIL# - E-mail
#LOGIN# - Логин
#XML_ID# - Внешний идентификатор
#BACK_URL# - Обратная ссылка
";
$MESS ['LDAP_USER_CONFIRM_EVENT_NAME'] = "#SITE_NAME#: Подтвержение регистрации";
$MESS ['LDAP_USER_CONFIRM_EVENT_DESC'] = "Информационное сообщение сайта #SITE_NAME#
------------------------------------------
Здравствуйте,

Вы получили это сообщение, так как ваш адрес был использован при регистрации нового пользователя на сервере #SERVER_NAME#.

Для подтверждения регистрации необходимо авторизоваться (ввести логин и пароль, используемые в локальной сети) на следующей странице:
http://#SERVER_NAME#/bitrix/admin/ldap_user_auth.php?ldap_user_id=#XML_ID#&back_url=#BACK_URL#

Сообщение сгенерировано автоматически.";
?>