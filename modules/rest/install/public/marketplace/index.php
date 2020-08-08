<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?><?$APPLICATION->IncludeComponent(
	"bitrix:ui.sidepanel.wrapper",
	"",
	array(
		"POPUP_COMPONENT_NAME" => "bitrix:rest.marketplace",
		"POPUP_COMPONENT_TEMPLATE_NAME" => ".default",
		"POPUP_COMPONENT_PARAMS" => array(
			"SEF_MODE" => "Y",
			"SEF_FOLDER" => SITE_DIR."marketplace/",
			"APPLICATION_URL" => SITE_DIR."marketplace/app/#id#/",
			"SEF_URL_TEMPLATES" => array(
				//"top" => "",
				"category" => "category/#category#/",
				"detail" => "detail/#app#/",
				"placement_app" => "view/#APP#/",
				"placement" => "placement/#PLACEMENT_ID#/",
				"search" => "search/",
				"buy" => "buy/",
				"updates" => "updates/",
				"installed" => "installed/",
			)
		),
		"USE_UI_TOOLBAR" => "Y",
		"USE_PADDING" => false,
		"PAGE_MODE" => false
	),
	$component
);
?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>