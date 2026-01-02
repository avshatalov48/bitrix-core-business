<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION;
$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$APPLICATION->SetTitle("");
$APPLICATION->IncludeComponent(
	"bitrix:ui.sidepanel.wrapper",
	"",
	[
		"POPUP_COMPONENT_NAME" => "bitrix:rest.configuration",
		"POPUP_COMPONENT_TEMPLATE_NAME" => ".default",
		"POPUP_COMPONENT_PARAMS" => [
			"SEF_MODE" => "Y",
			'SEF_FOLDER' => \Bitrix\Rest\Marketplace\Url::getConfigurationUrl(),

		],
		"PAGE_MODE" => false,
		"USE_PADDING" => false,
		"RELOAD_GRID_AFTER_SAVE" => 'all',
		"USE_UI_TOOLBAR" => "Y"
	]
);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>