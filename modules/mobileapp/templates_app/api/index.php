<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->IncludeComponent(
	'bitrix:mobileapp.demoapi',
	'.default',
	array("DEMO_PAGE_ID" =>
		$_REQUEST["page"],
		"APP_DIR"=>"/#folder#/"
	),
	false,
	Array('HIDE_ICONS' => 'Y'));


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>