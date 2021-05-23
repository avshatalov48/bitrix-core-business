<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?php
$pageId = "user";

if (isset($arResult["VARIABLES"]["user_id"]) && $USER->GetID() !== $arResult["VARIABLES"]["user_id"])
{
	ShowError(GetMessage("SONET_PASS_ACCESS_ERROR"));
	return;
}

if (
	\Bitrix\Main\ModuleManager::isModuleInstalled("intranet")
	&& SITE_TEMPLATE_ID == "bitrix24"
	&& \Bitrix\Main\Context::getCurrent()->getRequest()->get('IFRAME') === 'Y'
)
{
	$APPLICATION->IncludeComponent(
		"bitrix:ui.sidepanel.wrapper",
		"",
		array(
			'POPUP_COMPONENT_NAME' => "bitrix:dav.synchronize_settings",
			"POPUP_COMPONENT_TEMPLATE_NAME" => "",
			"POPUP_COMPONENT_PARAMS" => array(),
		)
	);
}
else
{
	include("util_menu.php");
	$APPLICATION->IncludeComponent("bitrix:dav.synchronize_settings", "", array());
}
