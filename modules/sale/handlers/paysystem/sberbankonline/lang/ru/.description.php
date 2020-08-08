<?php
$MESS["SALE_HPS_SBERBANK_DESCRIPTION_MAIN"] = "Для смены статуса заказа настройте <a href='https://securepayments.sberbank.ru/wiki/doku.php/integration:api:callback:start' target='_blank'>Уведомления обратного вызова</a> c контрольной суммой и дополнительным параметром \"bx_paysystem_code\".";
$MESS["SALE_HPS_SBERBANK"] = "Сбербанк";
$MESS["SALE_HPS_SBERBANK_LOGIN"] = "Логин";
$MESS["SALE_HPS_SBERBANK_LOGIN_DESC"] = "Логин";
$MESS["SALE_HPS_SBERBANK_PASSWORD"] = "Пароль";
$MESS["SALE_HPS_SBERBANK_PASSWORD_DESC"] = "Пароль";
$MESS["SALE_HPS_SBERBANK_SECRET_KEY"] = "Закрытый ключ";
$MESS["SALE_HPS_SBERBANK_SECRET_KEY_DESC"] = "Указывается при использовании уведомлений обратного вызова с контрольной суммой";
$MESS["SALE_HPS_SBERBANK_TEST_MODE"] = "Тестовый режим";
$MESS["SALE_HPS_SBERBANK_TEST_MODE_DESC"] = "Если опция отмечена, оплата будет работать в тестовом режиме. При пустом значении будет стандартный режим работы.";
$MESS["SALE_HPS_SBERBANK_RETURN_SUCCESS_URL"] = "Адрес, на который требуется перенаправить пользователя в случае успешной оплаты";
$MESS["SALE_HPS_SBERBANK_RETURN_SUCCESS_URL_DESC"] = "Адрес должен быть указан полностью, включая используемый протокол. Оставьте пустым для автоматического определения адреса, клиент вернется на страницу с которой был выполнен переход на оплату";
$MESS["SALE_HPS_SBERBANK_RETURN_FAIL_URL"] = "Адрес, на который требуется перенаправить пользователя в случае неуспешной оплаты";
$MESS["SALE_HPS_SBERBANK_RETURN_FAIL_URL_DESC"] = "Адрес должен быть указан полностью, включая используемый протокол. Оставьте пустым для автоматического определения адреса, клиент вернется на страницу с которой был выполнен переход на оплату";
$MESS["SALE_HPS_SBERBANK_ORDER_DESCRIPTION"] = "Описание заказа в свободной форме";
$MESS["SALE_HPS_SBERBANK_ORDER_DESCRIPTION_DESC"] = "В процессинг «Сбербанка» для включения в финансовую отчётность продавца передаются только первые 24 символа этого поля. Чтобы получить возможность отправлять это поле в процессинг, обратитесь в техническую поддержку. Текст может содержать метки: #PAYMENT_ID# - ID оплаты, #ORDER_ID# - ID заказа, #PAYMENT_NUMBER# - номер оплаты, #ORDER_NUMBER# - номер заказа, #USER_EMAIL# - Email покупателя";
$MESS["SALE_HPS_SBERBANK_ORDER_DESCRIPTION_TEMPLATE"] = "Оплата №#PAYMENT_NUMBER# заказа №#ORDER_NUMBER# для #USER_EMAIL#";
$MESS["SALE_HPS_SBERBANK_CHANGE_STATUS_PAY"] = "Автоматически оплачивать заказ при получении успешного статуса оплаты";
