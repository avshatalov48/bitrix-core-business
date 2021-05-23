<?php
$MESS["SALE_QH_API_LOGIN"] = "API ID";
$MESS["SALE_QH_API_LOGIN_DESCR"] = "API access ID. Provide the ID in the <a href='https://ishop.qiwi.com/options/merchants.action' target='_blank'>store settings</a>.";
$MESS["SALE_QH_API_PASS"] = "API password";
$MESS["SALE_QH_API_PASS_DESCR"] = "API access password. Provide your password in the <a href='https://ishop.qiwi.com/options/merchants.action' target='_blank'>store settings</a>.";
$MESS["SALE_QH_AUTHORIZATION"] = "Authentication method";
$MESS["SALE_QH_AUTHORIZATION_DESCR"] = "This one is used to authenticate when notifying. Can be set in the user account on the <a href='https://ishop.qiwi.com/options/merchants.action' target='_blank'>Pull (REST) parameters</a> page (the Signature check box). <br/> (<strong>OPEN</strong> - supplies password in open form, <strong>SIMPLE</strong> - uses simple signature)";
$MESS["SALE_QH_AUTH_OPEN"] = "Supply password in open form";
$MESS["SALE_QH_AUTH_SIMPLE"] = "Use simple signature";
$MESS["SALE_QH_BILL_LIFETIME"] = "Invoice lifetime";
$MESS["SALE_QH_BILL_LIFETIME_DESCR"] = "(minutes)";
$MESS["SALE_QH_CHANGE_STATUS_PAY"] = "Auto change order status to paid when payment success status is received";
$MESS["SALE_QH_CHANGE_STATUS_PAY_DESC"] = "(<strong>Y</strong> - yes, <strong>N</strong> - no)";
$MESS["SALE_QH_CLIENT_PHONE"] = "Payer phone number.";
$MESS["SALE_QH_CURRENCY"] = "Invoice currency";
$MESS["SALE_QH_CURRENCY_DESCR"] = "(currency in ISO 4217 format, symbolic or numeric)";
$MESS["SALE_QH_DESCRIPTION"] = "<div class='adm-info-message'>
	<a href='https://ishop.qiwi.com' target='_blank'>Visa QIWI Wallet</a><br/>
	<ol>
		<li>Specify the required parameters.</li>
		<li>Create a page to receive notifications from the payment system and add the <strong>bitrix:sale.order.payment.receive</strong> component to it.</li>
		<li>Configure the <strong>bitrix:sale.order.payment.receive</strong> for use with this payment system.</li>
		<li>Specify this page's URL in your <a href = 'https://ishop.qiwi.com/options/merchants.action'>Qiwi Wallet account</a>.</li>
	</ol>
</div>";
$MESS["SALE_QH_FAIL_URL"] = "The URL to redirect a customer to upon <strong>unsuccessful</strong> payment";
$MESS["SALE_QH_NO"] = "No";
$MESS["SALE_QH_NOTICE_PASSWORD"] = "Notification password.";
$MESS["SALE_QH_NOTICE_PASSWORD_DESCR"] = "Change your password on the <a target='_blank' href='https://ishop.qiwi.com/options/merchants.action'>Notification password</a> page,  the Pull (REST) section. <strong>Remember to specify notification URL!</strong>";
$MESS["SALE_QH_ORDER_ID"] = "Transaction ID in your system";
$MESS["SALE_QH_ORDER_ID_DESCR"] = "(e.g. order ID)";
$MESS["SALE_QH_SHOP_ID"] = "e-Store ID.";
$MESS["SALE_QH_SHOP_ID_DESCR"] = "Pick this ID from the settings page in the <a target='_blank' href='https://ishop.qiwi.com/options/merchants.action'>HTTP preferences</a> section.";
$MESS["SALE_QH_SHOULD_PAY"] = "Amount payable";
$MESS["SALE_QH_SHOULD_PAY_DESCR"] = "Order total.";
$MESS["SALE_QH_SUCCESS_URL"] = "The URL to redirect a customer to upon <strong>successful</strong> payment";
$MESS["SALE_QH_TITLE"] = "Qiwi Wallet";
$MESS["SALE_QH_YES"] = "Yes";
