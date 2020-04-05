<?
$MESS["SALE_HPS_YANDEX"] = "Yandex.Checkout";
$MESS["SALE_HPS_YANDEX_SHOP_ID"] = "Shop identifier in payment collector system (ShopID)";
$MESS["SALE_HPS_YANDEX_SHOP_ID_DESC"] = "Yandex Shop ID";
$MESS["SALE_HPS_YANDEX_SCID"] = "Showcase identifier in payment collector system (scid)";
$MESS["SALE_HPS_YANDEX_SCID_DESC"] = "Showcase identifier in payment collector system (scid)";
$MESS["SALE_HPS_YANDEX_PAYMENT_ID"] = "Payment #";
$MESS["SALE_HPS_YANDEX_SHOP_KEY"] = "Shop Password";
$MESS["SALE_HPS_YANDEX_SHOP_KEY_DESC"] = "Shop password as used on Yandex";
$MESS["SALE_HPS_YANDEX_SHOULD_PAY"] = "Order total";
$MESS["SALE_HPS_YANDEX_PAYMENT_DATE"] = "Payment created on";
$MESS["SALE_HPS_YANDEX_IS_TEST"] = "Test Mode";
$MESS["SALE_HPS_YANDEX_CHANGE_STATUS_PAY"] = "Auto change order status to paid when payment success status is received";
$MESS["SALE_HPS_YANDEX_PAYMENT_TYPE"] = "Payment system type";
$MESS["SALE_HPS_YANDEX_BUYER_ID"] = "Customer ID";
$MESS["SALE_HPS_YANDEX_RETURN"] = "Chargebacks are not supported";
$MESS["SALE_HPS_YANDEX_RESTRICTION"] = "Payment amount restriction is a matter of payment method selected by customer";
$MESS["SALE_HPS_YANDEX_COMMISSION"] = "No comission";
$MESS["SALE_HPS_YANDEX_REFERRER"] = "<a href=\"https://money.yandex.ru/joinups/?source=bitrix24\" target=\"_blank\">Quick registration</a>";
$MESS["SALE_HPS_YANDEX_DESCRIPTION"] = "Payment collector engine - <a href=\"https://kassa.yandex.ru\" target=\"_blank\">https://kassa.yandex.ru</a>
<br/>Using commonHTTP-3.0 protocol
<br/><br/>
<input
	id=\"https_check_button\"
	type=\"button\"
	value=\"HTTPS check\"
	title=\"Check if the site supports HTTPS. Required by payment system\"
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
?>