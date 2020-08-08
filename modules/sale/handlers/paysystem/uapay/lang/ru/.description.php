<?php
$MESS["SALE_HPS_UAPAY"] = "UAPAY";
$MESS["SALE_HPS_UAPAY_CLIENT_ID"] = "Идентификатор клиента";
$MESS["SALE_HPS_UAPAY_SIGN_KEY"] = "Ключ для подписи";
$MESS["SALE_HPS_UAPAY_SIGN_KEY_DESC"] = "Все запросы к API UAPAY должны подписываться ключом который можно получить в Вашем кабинете";
$MESS["SALE_HPS_UAPAY_CALLBACK_URL"] = "Адрес отправки POST-ответов";
$MESS["SALE_HPS_UAPAY_CALLBACK_URL_DESC"] = "Адрес на который будут приходить ответы от UAPAY. Адрес должен быть указан полностью, включая используемый протокол";
$MESS["SALE_HPS_UAPAY_REDIRECT_URL"] = "Адрес, на который требуется перенаправить пользователя в случае успешной оплаты";
$MESS["SALE_HPS_UAPAY_REDIRECT_URL_DESC"] = "Адрес должен быть указан полностью, включая используемый протокол. Оставьте пустым для автоматического определения адреса, клиент вернется на страницу с которой был выполнен переход на оплату";
$MESS["SALE_HPS_UAPAY_INVOICE_DESCRIPTION"] = "Описание оплаты в свободной форме";
$MESS["SALE_HPS_UAPAY_INVOICE_DESCRIPTION_DESC"] = "Текст может содержать метки: #PAYMENT_ID# - ID оплаты, #ORDER_ID# - ID заказа, #PAYMENT_NUMBER# - номер оплаты, #ORDER_NUMBER# - номер заказа, #USER_EMAIL# - Email покупателя";
$MESS["SALE_HPS_UAPAY_INVOICE_DESCRIPTION_TEMPLATE"] = "Оплата №#PAYMENT_NUMBER# заказа №#ORDER_NUMBER# для #USER_EMAIL#";
$MESS["SALE_HPS_UAPAY_TEST_MODE"] = "Тестовый режим";
$MESS["SALE_HPS_UAPAY_TEST_MODE_DESC"] = "Если опция отмечена, оплата будет работать в тестовом режиме. При пустом значении будет стандартный режим работы.";
$MESS["SALE_HPS_UAPAY_CHANGE_STATUS_PAY"] = "Автоматически оплачивать заказ при получении успешного статуса оплаты";
