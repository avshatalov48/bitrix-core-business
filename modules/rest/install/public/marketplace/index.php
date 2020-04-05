<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
$arResult['SLIDER'] = \CRestUtil::isSlider();
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:rest.marketplace", 
	".default", 
	array(
		"SEF_FOLDER" => SITE_DIR."marketplace/",
		"SEF_MODE" => "Y",
		"COMPONENT_TEMPLATE" => ".default",
		"APPLICATION_URL" => SITE_DIR."marketplace/app/#id#/",
		"SEF_URL_TEMPLATES" => array(
			//"top" => "",
			"category" => "category/#category#/",
			"detail" => "detail/#app#/",
			"search" => "search/",
			"buy" => "buy/",
			"updates" => "updates/",
			"installed" => "installed/",
		)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>