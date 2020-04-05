<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/payment_forward_calc.php"));

$psTitle = GetMessage("SPFP_DTITLE");
$psDescription = GetMessage("SPFP_DDESCR");
$isAvailable = \Bitrix\Sale\PaySystem\Manager::HANDLER_AVAILABLE_FALSE;

$arPSCorrespondence = array(
		"SELLER_NAME" => array(
				"NAME" => GetMessage("SPFP_FIO"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_ADDRESS" => array(
				"NAME" => GetMessage("SPFP_ADDRESS"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_ZIP" => array(
				"NAME" => GetMessage("SPFP_ZIP"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_INN" => array(
				"NAME" => GetMessage("SPFP_INN"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_BANK_NAME" => array(
				"NAME" => GetMessage("SPFP_BANK"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_BANK_BIK" => array(
				"NAME" => GetMessage("SPFP_BIK"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_BANK_KOR" => array(
				"NAME" => GetMessage("SPFP_KS"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),

		"SELLER_ACC" => array(
				"NAME" => GetMessage("SPFP_ACC"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),


);

?>