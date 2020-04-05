<?
global $MESS;

$MESS["SPCP_DTITLE"] = "MoneyMail";
$MESS["SPCP_DDESCR"] = "<a href=\"http://www.MoneyMail.ru\">MoneyMail</a>.<br>Для получения результата возможно два варианта:<br>1. стандартный скрипт (но для его работы необходима поддежка на хостинге обращений к https:// через функцию fopen());<br>2. файл result_receive.php - этот файл необходимо скопировать в публичную часть и сообщить его адрес платежной системе, чтобы Платежная система обращалась к нему в случае успешной оплаты.";

$MESS["SHOULD_PAY"] = "Сумма заказа";
$MESS["SHOULD_PAY_DESCR"] = "Сумма к оплате";
$MESS["CURRENCY"] = "Валюта";
$MESS["CURRENCY_DESCR"] = "Валюта в которой производится оплата";
$MESS["DATE_INSERT"] = "Дата создания заказа";
$MESS["DATE_INSERT_DESCR"] = "";

$MESS["ShopEmail"] = "Email магазина";
$MESS["ShopEmail_DESCR"] = "";
$MESS["PASS"] = "Пароль магазина";
$MESS["PASS_DESCR"] = "Пароль магазина в moneymail.ru";
$MESS["PAYER_EMAIL"] = "Email покупателя";
$MESS["PAYER_EMAIL_DESCR"] = "";
$MESS["ERROR_URL"] = "Адрес страницы с ошибкой";
$MESS["ERROR_URL_DESCR"] = "Адрес, куда будет возвращать платежная система в случае ошибки";
?>