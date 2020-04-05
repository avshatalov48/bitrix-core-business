<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if ($arParams['AJAX_POST']=='Y' && in_array($arParams['ACTION'], array('REPLY', 'VIEW')))
	ob_start();

if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/gray/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;

$GLOBALS["APPLICATION"]->AddHeadScript("/bitrix/js/main/utils.js");
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/components/bitrix/forum.interface/templates/.default/script.js"></script>', true);

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["component"] = $component;
$arParams["SHOW_MAIL"] = (($arParams["SEND_MAIL"] <= "A" || ($arParams["SEND_MAIL"] <= "E" && !$GLOBALS['USER']->IsAuthorized())) ? "N" : "Y");
$arParams["SHOW_RSS"] = ($arParams["SHOW_RSS"] == "N" ? "N" : "Y");
$arParams["SHOW_VOTE"] = ($arParams["SHOW_VOTE"] == "Y" ? "Y" : "N");
$arParams["VOTE_TEMPLATE"] = (strlen(trim($arParams["VOTE_TEMPLATE"])) > 0 ? trim($arParams["VOTE_TEMPLATE"]) : "light");
$arParams["VOTE_CHANNEL_ID"] = (intval($arParams["VOTE_CHANNEL_ID"]) > 0 ? $arParams["VOTE_CHANNEL_ID"] : 1);

if ($arParams["SHOW_RSS"] == "Y"):
	$arParams["SHOW_RSS"] = (!$USER->IsAuthorized() ? "Y" : (CForumNew::GetUserPermission($arParams["FID"], array(2)) > "A" ? "Y" : "N"));
	if ($arParams["SHOW_RSS"] == "Y"):
		$APPLICATION->AddHeadString('<link rel="alternate" type="application/rss+xml" href="'.$arResult["URL"]["RSS"].'" />');
	endif;
endif;
$arParams["SHOW_NAME_LINK"] = ($arParams["SHOW_NAME_LINK"] == "N" ? "N" : "Y");
$arParams["FIRST_MESSAGE_ID"] = $arResult["MESSAGE_FIRST"]["ID"];
$arParams["ATTACH_MODE"] = array("NAME", "THUMB");
$arParams["ATTACH_SIZE"] = $arParams["IMAGE_SIZE"];
/********************************************************************
				/Input params
********************************************************************/
include(str_replace(array("\\", "//"), "/", dirname(__FILE__)."/template_message.php"));
CUtil::InitJSCore(array("ajax", "fx", "viewer"));
?>