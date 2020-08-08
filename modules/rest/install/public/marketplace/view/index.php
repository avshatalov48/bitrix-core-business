<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php $APPLICATION->IncludeComponent(
	"bitrix:ui.sidepanel.wrapper",
	"",
	array(
		"POPUP_COMPONENT_NAME" => "bitrix:app.layout",
		"POPUP_COMPONENT_TEMPLATE_NAME" => ".default",
		"POPUP_COMPONENT_PARAMS" => array(
			"COMPONENT_TEMPLATE" => ".default",
			"SEF_MODE" => "Y",
			"IS_SLIDER" => "Y",
			"APP_VIEW" => $_GET['APP'],
			"SEF_FOLDER" => SITE_DIR."marketplace/view/",
			"USE_PADDING" => 'N'
		),
		"USE_PADDING" => false,
		"PAGE_MODE" => false,
		"USE_UI_TOOLBAR" => "N",
		"PLAIN_VIEW" => (\CRestUtil::isSlider() ? "Y" : "N")
	)
);?>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>