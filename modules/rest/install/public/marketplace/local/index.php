<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?><?$APPLICATION->IncludeComponent(
	"bitrix:rest.marketplace.localapp", 
	".default", 
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => SITE_DIR."marketplace/local/",
		"COMPONENT_TEMPLATE" => ".default",
		"APPLICATION_URL" => SITE_DIR."marketplace/app/#id#/",
		"SEF_URL_TEMPLATES" => array(
			"index" => "",
			"list" => "list/",
			"edit" => "edit/#id#/",
		)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>