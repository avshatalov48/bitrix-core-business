<?
global $MESS;
$MESS['SALE_QH_TITLE'] = "Qiwi Wallet";
$MESS['SALE_QH_DESCRIPTION'] = "<div class='adm-info-message'>
	Платежный сервис <a href='https://ishop.qiwi.com' target='_blank'>Visa QIWI Wallet</a><br/>
	<ol>
		<li>Укажите обязательные параметры.</li>
		<li>Создайте страницу для получения уведомлений от платежной системы и расположите на ней компонент <strong>bitrix:sale.order.payment.receive</strong>.</li>
		<li>Настройте <strong>bitrix:sale.order.payment.receive</strong> на эту платежную систему.</li>
		<li>Укажите в <a href = 'https://ishop.qiwi.com/options/merchants.action'>личном кабинете</a> Qiwi Wallet url-адрес созданной страницы.</li>
	</ol>
</div>";
$MESS['SALE_QH_SHOP_ID'] = "Идентификатор магазина.";
$MESS['SALE_QH_SHOP_ID_DESCR'] = "Узнать этот id можно на странице настроек в разделе <a target='_blank' href='https://ishop.qiwi.com/options/merchants.action'>Настройки HTTP-протокола</a>.";

$MESS['SALE_QH_API_LOGIN'] = "Идентификатор API";
$MESS['SALE_QH_API_LOGIN_DESCR'] = "Идентификатор для доступа к API. Задается в <a href='https://ishop.qiwi.com/options/merchants.action' target='_blank'>настройках магазинa</a> в разделе 'Аутентификационные данные для всех протоколов'.";

$MESS['SALE_QH_API_PASS'] = "Пароль API";
$MESS['SALE_QH_API_PASS_DESCR'] = "Пароль для доступа к API. Задается в <a href='https://ishop.qiwi.com/options/merchants.action' target='_blank'>настройках магазинa</a> в разделе 'Аутентификационные данные для всех протоколов'.";

$MESS['SALE_QH_CLIENT_PHONE'] = "Телефон клиента, на который выставлять счет.";
$MESS['SALE_QH_CLIENT_PHONE_DESCR'] = "";
$MESS["SALE_QH_ORDER_ID"] = "Номер оплаты";
$MESS["SALE_QH_ORDER_ID_DESCR"] = "(например, номер заказа в интернет-магазине)";
$MESS["SALE_QH_SHOULD_PAY"] = "К оплате";
$MESS["SALE_QH_SHOULD_PAY_DESCR"] = "Сумма счета.";
$MESS["SALE_QH_CURRENCY"] = "Валюта счета";
$MESS["SALE_QH_CURRENCY_DESCR"] = "(должна быть в формате ISO 4217 в буквенном или цифровом формате)";
$MESS["SALE_QH_BILL_LIFETIME"] = "Время действия счета";
$MESS["SALE_QH_BILL_LIFETIME_DESCR"] = "(в минутах)";
$MESS["SALE_QH_FAIL_URL"] = "Url, на который перенаправляется пользователь при <strong>неуспешной</strong> оплате счета";
$MESS["SALE_QH_FAIL_URL_DESCR"] = "";
$MESS["SALE_QH_SUCCESS_URL"] = "Url, на который перенаправляется пользователь при <strong>успешной</strong> оплате счета";
$MESS["SALE_QH_SUCCESS_URL_DESCR"] = "";
$MESS["SALE_QH_CHANGE_STATUS_PAY"] = "Автоматически оплачивать заказ при получении успешного статуса оплаты";
$MESS["SALE_QH_CHANGE_STATUS_PAY_DESC"] = "(<strong>Y</strong> - да, <strong>N</strong> - нет)";
$MESS["SALE_QH_YES"] = "Да";
$MESS["SALE_QH_NO"] = "Нет";
$MESS["SALE_QH_AUTHORIZATION"] = "Способ авторизации";
$MESS["SALE_QH_AUTHORIZATION_DESCR"] = "Используется для авторизации при уведомлениях. Настраивается в личном кабинете в разделе <a href='https://ishop.qiwi.com/options/merchants.action' target='_blank'>Настройки Pull (REST) протокола</a> (галочка 'Подпись'). <br/> (<strong>OPEN</strong> - Передача пароля в открытом виде, <strong>SIMPLE</strong> - Использование простой подписи)";
$MESS["SALE_QH_AUTH_OPEN"] = "Передача пароля в открытом виде";
$MESS["SALE_QH_AUTH_SIMPLE"] = "Использование простой подписи";
$MESS["SALE_QH_NOTICE_PASSWORD"] = "Пароль оповещения.";
$MESS["SALE_QH_NOTICE_PASSWORD_DESCR"] = "Пароль можно сменить в пункте <a target='_blank' href='https://ishop.qiwi.com/options/merchants.action'>Сменить пароль оповещения</a> в разделе Настройки Pull (REST). <strong>Обязательно укажите URL для оповещения!</strong>";
?>