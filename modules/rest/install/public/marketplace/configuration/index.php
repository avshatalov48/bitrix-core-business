<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
if ($request->get('inSlider') === 'N')
{
	$APPLICATION->includeComponent(
		'bitrix:rest.configuration',
		'',
		array(
			"SEF_MODE" => "Y",
			'SEF_FOLDER' => \Bitrix\Rest\Marketplace\Url::getConfigurationUrl(),
		),
		false, array('HIDE_ICONS' => 'Y')
	);
}
else
{
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
			"RELOAD_GRID_AFTER_SAVE" => 'all'
		]
	);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
}
?>