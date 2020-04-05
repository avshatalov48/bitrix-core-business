<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$psTitle = "WorldPay";
$psDescription  = "Select Junior Integration (HTTP integration method) <a href=\"http://www.worldpay.com\">http://www.worldpay.com</a><br><br>";
$psDescription .= "If you want to get Payment Response automatically, please read the following:<br>";
$psDescription .= "Once a shopping cart or web site has sent the purchase details through to WorldPay, callback is the facility offered to inform the site about what ";
$psDescription .= "happened on the WorldPay Payment Gateway. The callback will occur in the following circumstances:<br>";
$psDescription .= "- Either the shopper selects Make Payment (and the card is accepted)<br>";
$psDescription .= "- Or the shopper selects Cancel Purchase<br>";
$psDescription .= "It is important to note that callback is not a redirection from WorldPay to your web site, although a callback can generate a custom \"thank you\" or \"no thank you\" page for display to the shopper.<br><br>";

$psDescription .= "To use this feature you should copy file <nobr>/bitrix/modules/sale/payment/worldpay/result.php</nobr> to file <nobr>/bitrix/php_interface/include/sale_payment/worldpay_res.php</nobr><br>";
$psDescription .= "Than you will initially need to log in to the WorldPay Customer Management System (via http://www.worldpay.com/admin). In the new window that ";
$psDescription .= "opens you will need to scroll down to the section headed Installations and select the Configuration options button corresponding to the instId you are using.<br>";
$psDescription .= "You need to complete the following settings:<br>";
$psDescription .= "- <b>Callback URL</b><br>This should be set to the complete address to your callback URL (this file), hosted on your server.<br>";
$psDescription .= "You should use the string like <nobr>http://your_site/bitrix/php_interface/include/sale_payment/worldpay_res.php?server_responce=Y</nobr><br>";
$psDescription .= "- <b>Callback enabled</b><br>Tick this checkbox";

$arPSCorrespondence = array(
		"TEST_TRANSACTION" => array(
				"NAME" => "Test transaction",
				"DESCR" => "Indicates whether the transaction should be processed as a test transaction: 0 - real transaction; 100, 101 - test transactions",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_ID" => array(
				"NAME" => "WorldPay ID",
				"DESCR" => "WorldPay ID",
				"VALUE" => "",
				"TYPE" => ""
			),
		"PAYER_NAME" => array(
				"NAME" => "Buyer",
				"DESCR" => "Buyer name",
				"VALUE" => "PAYER_NAME",
				"TYPE" => "PROPERTY"
			),
		"PHONE" => array(
				"NAME" => "Buyer phone",
				"DESCR" => "Buyer phone",
				"VALUE" => "PHONE",
				"TYPE" => "PROPERTY"
			),
		"EMAIL" => array(
				"NAME" => "Buyer EMail",
				"DESCR" => "",
				"VALUE" => "EMAIL",
				"TYPE" => "PROPERTY"
			),
		"FAX" => array(
				"NAME" => "Buyer FAX",
				"DESCR" => "Buyer FAX",
				"VALUE" => "FAX",
				"TYPE" => "PROPERTY"
			),
		"ADDRESS" => array(
				"NAME" => "Buyer address",
				"DESCR" => "",
				"VALUE" => "ADDRESS",
				"TYPE" => "PROPERTY"
			),
		"ZIP" => array(
				"NAME" => "Buyer ZIP",
				"DESCR" => "",
				"VALUE" => "ZIP",
				"TYPE" => "PROPERTY"
			),
		"COUNTRY" => array(
				"NAME" => "Buyer country",
				"DESCR" => "",
				"VALUE" => "COUNTRY",
				"TYPE" => "PROPERTY"
			),
		"CALLBACK_PASSWORD" => array(
				"NAME" => "Password",
				"DESCR" => "If you have set a Callback password for your installation on the WorldPay Customer Management System, than you should fill this variable with that password. As the value of this is only known to this and WorldPay it can be used as a basic security check.",
				"VALUE" => "",
				"TYPE" => ""
			)
	);
?>