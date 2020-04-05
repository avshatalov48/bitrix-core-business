<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "user";
include("util_menu.php");

if (isset($arResult["VARIABLES"]["user_id"]) && $USER->GetID() !== $arResult["VARIABLES"]["user_id"])
{
	ShowError(GetMessage("SONET_PASS_ACCESS_ERROR"));
	return;
}

$path = CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_USER"], array("user_id" => $arResult["VARIABLES"]["user_id"]));
$path = CHTTP::urlAddParams($path, array("otp" => "Y"));


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
			'POPUP_COMPONENT_NAME' => "bitrix:security.user.otp.init",
			"POPUP_COMPONENT_TEMPLATE_NAME" => "",
			"POPUP_COMPONENT_PARAMS" =>array(
				"SUCCESSFUL_URL" => $path
			),
			"POPUP_COMPONENT_PARENT" => $this->getComponent()
		)
	);
}
else
{
	$APPLICATION->IncludeComponent("bitrix:security.user.otp.init", "", array(
		"SUCCESSFUL_URL" => $path
	));
}
?>