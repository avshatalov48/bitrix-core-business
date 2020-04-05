<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?><?
$APPLICATION->IncludeComponent(
	"bitrix:ui.sidepanel.wrapper",
	"",
	[
		"POPUP_COMPONENT_NAME" => "bitrix:rest.hook",
		"POPUP_COMPONENT_TEMPLATE_NAME" => ".default",
		"POPUP_COMPONENT_PARAMS" => [
			"SEF_MODE" => "Y",
			"SEF_FOLDER" => SITE_DIR."marketplace/hook/",
			"COMPONENT_TEMPLATE" => ".default",
			"SEF_URL_TEMPLATES" => [
				"list" => "",
				"event_list" => "event/",
				"event_edit" => "event/#id#/",
				"ap_list" => "ap/",
				"ap_edit" => "ap/#id#/",
			]
		],
		"PAGE_MODE" => true,
		"RELOAD_GRID_AFTER_SAVE" => 'all'
	]
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>