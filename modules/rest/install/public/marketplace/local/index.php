<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?><?$APPLICATION->IncludeComponent(
	"bitrix:ui.sidepanel.wrapper",
	"",
	array(
		"POPUP_COMPONENT_NAME" => "bitrix:rest.marketplace.localapp",
		"POPUP_COMPONENT_TEMPLATE_NAME" => ".default",
		"POPUP_COMPONENT_PARAMS" => array(
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
		"USE_UI_TOOLBAR" => "Y",
		"USE_PADDING" => true,
		"PAGE_MODE" => true,
		"RELOAD_GRID_AFTER_SAVE" => true
	),
	$component
);
?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>