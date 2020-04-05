<?php
$MESS['SALE_HPS_YANDEX'] = 'Яндекс.Касса (до версии 3.х)';
$MESS["SALE_HPS_YANDEX_SHOP_ID"] = "Идентификатор магазина в ЦПП (ShopID)";
$MESS["SALE_HPS_YANDEX_SHOP_ID_DESC"] = "Код магазина, который получен от Яндекс";
$MESS["SALE_HPS_YANDEX_SCID"] = "Номер витрины магазина в ЦПП (scid)";
$MESS["SALE_HPS_YANDEX_SCID_DESC"] = "Номер витрины магазина в ЦПП (scid)";
$MESS["SALE_HPS_YANDEX_PAYMENT_ID"] = "Номер оплаты";
$MESS["SALE_HPS_YANDEX_SHOP_KEY"] = "Пароль магазина";
$MESS["SALE_HPS_YANDEX_SHOP_KEY_DESC"] = "Пароль магазина на Яндекс";
$MESS["SALE_HPS_YANDEX_SHOULD_PAY"] = "Сумма к оплате";
$MESS["SALE_HPS_YANDEX_PAYMENT_DATE"] = "Дата создания оплаты";
$MESS["SALE_HPS_YANDEX_IS_TEST"] = "Тестовый режим";
$MESS["SALE_HPS_YANDEX_CHANGE_STATUS_PAY"] = "Автоматически оплачивать заказ при получении успешного статуса оплаты";
$MESS["SALE_HPS_YANDEX_PAYMENT_TYPE"] = "Тип платёжной системы";
$MESS["SALE_HPS_YANDEX_BUYER_ID"] = "Код покупателя";

$MESS["SALE_HPS_YANDEX_RETURN"] = "Возвраты платежей не поддерживаются";
$MESS["SALE_HPS_YANDEX_RESTRICTION"] = "Ограничение по сумме платежей зависит от способа оплаты, который выберет покупатель";
$MESS["SALE_HPS_YANDEX_COMMISSION"] = "Без комисси для покупателя";
$MESS["SALE_HPS_YANDEX_CHECKOUT_REFERRER"] = "<a href=\"https://money.yandex.ru/joinups/?source=bitrix24\" target=\"_blank\">Быстрая регистрация</a>";

$MESS["SALE_HPS_YANDEX_DESCRIPTION"] = "Работа через Центр Приема Платежей <a href=\"https://kassa.yandex.ru\" target=\"_blank\">https://kassa.yandex.ru</a>
<br/>Используется протокол commonHTTP-3.0
<br/><br/>
<input
	id=\"https_check_button\"
	type=\"button\"
	value=\"Проверка HTTPS\"
	title=\"Проверка доступности сайта по протоколу HTTPS. Необходимо для корректной работы платежной системы\"
	onclick=\"
		var checkHTTPS = function(){
			BX.showWait()
			var postData = {
				action: 'checkHttps',
				https_check: 'Y',
				lang: BX.message('LANGUAGE_ID'),
				sessid: BX.bitrix_sessid()
			};

			BX.ajax({
				timeout: 30,
				method: 'POST',
				dataType: 'json',
				url: '/bitrix/admin/sale_pay_system_ajax.php',
				data: postData,

				onsuccess: function (result)
				{
					BX.closeWait();
					BX.removeClass(BX('https_check_result'), 'https_check_success');
					BX.removeClass(BX('https_check_result'), 'https_check_fail');

					BX('https_check_result').innerHTML = '&nbsp;' + result.CHECK_MESSAGE;
					if (result.CHECK_STATUS == 'OK')
						BX.addClass(BX('https_check_result'), 'https_check_success');
					else
						BX.addClass(BX('https_check_result'), 'https_check_fail');
				},
				onfailure : function()
				{
					BX.closeWait();
					BX.removeClass(BX('https_check_result'), 'https_check_success');

					BX('https_check_result').innerHTML = '&nbsp;' + BX.message('SALE_PS_YANDEX_ERROR');
					BX.addClass(BX('https_check_result'), 'https_check_fail');
				}
			});
		};
		checkHTTPS();\"
	/>
<span id=\"https_check_result\"></span>
<br/>
<br/>";