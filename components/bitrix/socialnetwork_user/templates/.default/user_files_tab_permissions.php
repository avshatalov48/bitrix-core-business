<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?><?
$arCurFileInfo = pathinfo(__FILE__);
$langfile = trim(preg_replace("'[\\\\/]+'", "/", ($arCurFileInfo['dirname']."/lang/".LANGUAGE_ID."/".$arCurFileInfo['basename'])));
__IncludeLang($langfile);
$sTabName =  'tab_permissions';

$sCurrentTab = (isset($_GET[$arParams["FORM_ID"].'_active_tab']) ? $_GET[$arParams["FORM_ID"].'_active_tab']: '');
$_GET[$arParams["FORM_ID"].'_active_tab'] = $sTabName;

if ($arResult['VARIABLES']['SECTION_ID'] > 0)
{
	$entityType = 'SECTION';
	$entityID = $arResult['VARIABLES']['SECTION_ID'];
} else {
	$entityType = 'ELEMENT';
	$entityID = $arResult['VARIABLES']['ELEMENT_ID'];
}

$arComponentParams = Array(
	"IBLOCK_ID"		=> $arParams["IBLOCK_ID"],
	"OBJECT"		=> $arParams["OBJECT"],
	"ENTITY_TYPE"	=> $entityType,
	"ENTITY_ID"		=> $entityID,
	"PERMISSION" 	=> $arParams["PERMISSION"], 
	"CHECK_CREATOR" => $arParams["CHECK_CREATOR"],
	"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
	"FORM_ID" 		=> $arParams["FORM_ID"],
	"TAB_ID" 		=> 'tab_permissions',
	"USER_VIEW_URL" => $arResult["URL_TEMPLATES"]["user_view"], 
	"SET_TITLE"	=>	"N",
	"SET_NAV_CHAIN"	=>	"N",
	"MERGE_VIEW" => "Y",
	"CACHE_TYPE"	=>	$arParams["CACHE_TYPE"],
	"CACHE_TIME"	=>	$arParams["CACHE_TIME"]
);

if (isset($arParams["OBJECT"]->attributes['user_id']))
{
	$arComponentParams['SOCNET_TYPE'] = 'user';
	$arComponentParams['SOCNET_ID'] = $arParams["OBJECT"]->attributes['user_id'];
}
elseif (isset($arParams["OBJECT"]->attributes['group_id']))
{
	$arComponentParams['SOCNET_TYPE'] = 'group';
	$arComponentParams['SOCNET_ID'] = $arParams["OBJECT"]->attributes['group_id'];
}

$APPLICATION->IncludeComponent("bitrix:webdav.iblock.rights", ".default", $arComponentParams, $component, array("HIDE_ICONS" => "Y"));

unset($_GET[$arParams["FORM_ID"].'_active_tab']);
if ($sCurrentTab !== '') 
    $_GET[$arParams["FORM_ID"].'_active_tab'] = $sCurrentTab;
?>
