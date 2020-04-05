<?
global $MESS;

$MESS["SWMPP_DTITLE"] = "Оплата через WebMoney (PCI)";
$MESS["SWMPP_DDESCR"] = "Оплата через WebMoney с помощью сервиса Click&Buy Merchant Interface <a href=\"https://merchant.webmoney.ru/conf/guide_pci.asp\" target=\"_blank\">https://merchant.webmoney.ru/conf/guide_pci.asp</a>. Перед использованием необходимо скопировать файл /bitrix/modules/sale/payment/webmoney_pci/result.php куда-либо в публичную часть сайта и задать путь к нему в соответствующем свойстве.";

$MESS["SWMPP_ORDER_ID"] = "Номер заказа";
$MESS["SWMPP_SUMMA"] = "Сумма к оплате";
$MESS["SWMPP_NUMBER"] = "Номер кошелька";
$MESS["SWMPP_NUMBER_DESC"] = "Введите сюда номер вашего кошелька";
$MESS["SWMPP_TEST"] = "Тестовый режим";
$MESS["SWMPP_TEST_DESC"] = "test - для тестового режима, иначе пустое значение";
$MESS["SWMPP_DIR"] = "Путь к скрипту обработки ответа платежной системы";
$MESS["SWMPP_DIR_DESC"] = "Путь задается относительно корня сайта";
$MESS["SWMPP_PASSW"] = "Пароль в системе WebMoney Transfer";
$MESS["SWMPP_PASSW_DESC"] = "Пароль продавца в системе WebMoney Transfer";
?>