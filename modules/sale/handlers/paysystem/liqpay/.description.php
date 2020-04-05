<?
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = array(
	'NAME' => 'LiqPay',
	'SORT' => 400,
	'CODES' => array(
		"LIQPAY_MERCHANT_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_MERCHANT_ID"),
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY',
			'SORT' => 100,
		),
		"LIQPAY_SIGN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_SIGN"),
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY',
			'SORT' => 200,
		),
		"LIQPAY_PATH_TO_RESULT_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_PATH_TO_RESULT_URL"),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY',
			"DEFAULT" => array(
				"PROVIDER_VALUE" => "http://".$_SERVER["HTTP_HOST"]."/personal/orders/",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"LIQPAY_PATH_TO_SERVER_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_PATH_TO_SERVER_URL"),
			'SORT' => 400,
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY',
			"DEFAULT" => array(
				"PROVIDER_VALUE" => "http://".$_SERVER["HTTP_HOST"]."/personal/ps_result.php",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"PAYMENT_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_ORDER_ID"),
			'SORT' => 500,
			'GROUP' => 'PAYMENT',
			"DEFAULT" => array(
				"PROVIDER_VALUE" => "ID",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"PAYMENT_CURRENCY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_CURRENCY"),
			'SORT' => 600,
			'GROUP' => 'PAYMENT',
			"DEFAULT" => array(
				"PROVIDER_VALUE" => "CURRENCY",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"PAYMENT_SHOULD_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_SHOULD_PAY"),
			'SORT' => 700,
			'GROUP' => 'PAYMENT',
			"DEFAULT" => array(
				"PROVIDER_VALUE" => "SUM",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"BUYER_PERSON_PHONE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_PHONE"),
			'SORT' => 800,
			'GROUP' => 'BUYER_PERSON',
			"DEFAULT" => array(
				"PROVIDER_VALUE" => "PHONE",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"LIQPAY_PAY_METHOD" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_PAYMENT_PM"),
			'SORT' => 900,
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY'
		)
	)
);

