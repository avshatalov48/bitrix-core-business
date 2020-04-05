<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/.description.php"));
$psTitle = "authorize.net";
$psDescription = GetMessage("AN_NAME")." <a href=\"http://www.authorize.net\" target=\"_blank\">http://www.authorize.net</a>";

$arPSCorrespondence = array(
		"PS_LOGIN" => array(
				"NAME" => GetMessage("AN_PS_LOGIN_NAME"),
				"DESCR" => GetMessage("AN_PS_LOGIN_DESCR"),
				"VALUE" => "login",
				"TYPE" => ""
			),
		"PS_TRANSACTION_KEY" => array(
				"NAME" => GetMessage("AN_PS_TRANSACTION_KEY_NAME"),
				"DESCR" => GetMessage("AN_PS_TRANSACTION_KEY_DESCR"),
				"VALUE" => "key",
				"TYPE" => ""
			),
		"HASH_VALUE" => array(
				"NAME" => GetMessage("AN_HASH_VALUE_NAME"),
				"DESCR" => GetMessage("AN_HASH_VALUE_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"AUTO_PAY" => array(
				"NAME" => GetMessage("AN_AUTO_PAY_NAME"),
				"DESCR" => GetMessage("AN_AUTO_PAY_DESCR"),
				"VALUE" => "N",
				"TYPE" => ""
			),
		"TEST_TRANSACTION" => array(
				"NAME" => GetMessage("AN_TEST_TRANSACTION_NAME"),
				"DESCR" => GetMessage("AN_TEST_TRANSACTION_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"FIRST_NAME" => array(
				"NAME" => GetMessage("AN_FIRST_NAME_NAME"),
				"DESCR" => GetMessage("AN_FIRST_NAME_DESCR"),
				"VALUE" => "FIRST_NAME",
				"TYPE" => "PROPERTY"
			),
		"LAST_NAME" => array(
				"NAME" => GetMessage("AN_LAST_NAME_NAME"),
				"DESCR" => GetMessage("AN_LAST_NAME_DESCR"),
				"VALUE" => "LAST_NAME",
				"TYPE" => "PROPERTY"
			),
		"COMPANY" => array(
				"NAME" => GetMessage("AN_COMPANY_NAME"),
				"DESCR" => GetMessage("AN_COMPANY_DESCR"),
				"VALUE" => "COMPANY",
				"TYPE" => "PROPERTY"
			),
		"ADDRESS" => array(
				"NAME" => GetMessage("AN_ADDRESS_NAME"),
				"DESCR" => GetMessage("AN_ADDRESS_DESCR"),
				"VALUE" => "ADDRESS",
				"TYPE" => "PROPERTY"
			),
		"CITY" => array(
				"NAME" => GetMessage("AN_CITY_NAME"),
				"DESCR" => GetMessage("AN_CITY_DESCR"),
				"VALUE" => "CITY",
				"TYPE" => "PROPERTY"
			),
		"STATE" => array(
				"NAME" => GetMessage("AN_STATE_NAME"),
				"DESCR" => GetMessage("AN_STATE_DESCR"),
				"VALUE" => "STATE",
				"TYPE" => "PROPERTY"
			),
		"ZIP" => array(
				"NAME" => GetMessage("AN_ZIP_NAME"),
				"DESCR" => GetMessage("AN_ZIP_DESCR"),
				"VALUE" => "ZIP",
				"TYPE" => "PROPERTY"
			),
		"COUNTRY" => array(
				"NAME" => GetMessage("AN_COUNTRY_NAME"),
				"DESCR" => GetMessage("AN_COUNTRY_DESCR"),
				"VALUE" => "COUNTRY",
				"TYPE" => "PROPERTY"
			),
		"PHONE" => array(
				"NAME" => GetMessage("AN_PHONE_NAME"),
				"DESCR" => GetMessage("AN_PHONE_DESCR"),
				"VALUE" => "PHONE",
				"TYPE" => "PROPERTY"
			),
		"FAX" => array(
				"NAME" => GetMessage("AN_FAX_NAME"),
				"DESCR" => GetMessage("AN_FAX_DESCR"),
				"VALUE" => "FAX",
				"TYPE" => "PROPERTY"
			),
		"EMAIL" => array(
				"NAME" => GetMessage("AN_EMAIL_NAME"),
				"DESCR" => GetMessage("AN_EMAIL_DESCR"),
				"VALUE" => "EMAIL",
				"TYPE" => "PROPERTY"
			),
		"SHIP_FIRST_NAME" => array(
				"NAME" => GetMessage("AN_SHIP_FIRST_NAME_NAME"),
				"DESCR" => GetMessage("AN_SHIP_FIRST_NAME_DESCR"),
				"VALUE" => "FIRST_NAME",
				"TYPE" => "PROPERTY"
			),
		"SHIP_LAST_NAME" => array(
				"NAME" => GetMessage("AN_SHIP_LAST_NAME_NAME"),
				"DESCR" => GetMessage("AN_SHIP_LAST_NAME_DESCR"),
				"VALUE" => "LAST_NAME",
				"TYPE" => "PROPERTY"
			),
		"SHIP_COMPANY" => array(
				"NAME" => GetMessage("AN_SHIP_COMPANY_NAME"),
				"DESCR" => GetMessage("AN_SHIP_COMPANY_DESCR"),
				"VALUE" => "COMPANY",
				"TYPE" => "PROPERTY"
			),
		"SHIP_ADDRESS" => array(
				"NAME" => GetMessage("AN_SHIP_ADDRESS_NAME"),
				"DESCR" => GetMessage("AN_SHIP_ADDRESS_DESCR"),
				"VALUE" => "ADDRESS",
				"TYPE" => "PROPERTY"
			),
		"SHIP_CITY" => array(
				"NAME" => GetMessage("AN_SHIP_CITY_NAME"),
				"DESCR" => GetMessage("AN_SHIP_CITY_DESCR"),
				"VALUE" => "CITY",
				"TYPE" => "PROPERTY"
			),
		"SHIP_STATE" => array(
				"NAME" => GetMessage("AN_SHIP_STATE_NAME"),
				"DESCR" => GetMessage("AN_SHIP_STATE_DESCR"),
				"VALUE" => "STATE",
				"TYPE" => "PROPERTY"
			),
		"SHIP_ZIP" => array(
				"NAME" => GetMessage("AN_SHIP_ZIP_NAME"),
				"DESCR" => GetMessage("AN_SHIP_ZIP_DESCR"),
				"VALUE" => "ZIP",
				"TYPE" => "PROPERTY"
			),
		"SHIP_COUNTRY" => array(
				"NAME" => GetMessage("AN_SHIP_COUNTRY_NAME"),
				"DESCR" => GetMessage("AN_SHIP_COUNTRY_DESCR"),
				"VALUE" => "COUNTRY",
				"TYPE" => "PROPERTY"
			)
	);
?>