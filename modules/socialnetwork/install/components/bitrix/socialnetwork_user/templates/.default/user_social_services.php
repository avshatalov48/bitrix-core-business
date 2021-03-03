<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "user_social_services";

$commonSecurityPage = CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_USER_COMMON_SECURITY"], array("user_id" => $arResult["VARIABLES"]["user_id"]));

$componentTemplate = (!\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24')? '':'bitrix24');

$APPLICATION->setTitle(\Bitrix\Main\Localization\Loc::getMessage("SONET_USER_SOCSERV_PAGE_TITLE"));

$uri = new \Bitrix\Main\Web\Uri($APPLICATION->GetCurPage());
if (isset($_REQUEST['IFRAME']) && $_REQUEST['IFRAME'] == 'Y')
{
	$uri->addParams(['IFRAME' => 'Y', 'IFRAME_TYPE' => 'SIDE_SLIDER']);
}

$APPLICATION->IncludeComponent(
	"bitrix:ui.sidepanel.wrapper",
	"",
	array(
		"PAGE_MODE_OFF_BACK_URL" => $commonSecurityPage.'?page=auth',
		'POPUP_COMPONENT_NAME' => "bitrix:socserv.auth.split",
		"POPUP_COMPONENT_TEMPLATE_NAME" => $componentTemplate,
		"POPUP_COMPONENT_PARAMS" => array(
			"SHOW_PROFILES" => "Y",
			"CAN_DELETE" => "Y",
			"BACKURL" => $uri->getUri()
		),
		"POPUP_COMPONENT_PARENT" => $this->getComponent()
	)
);
?>