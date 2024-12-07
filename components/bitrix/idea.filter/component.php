<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("idea"))
	return false;
/**
 * @var array $arParams
 * @var CMain $APPLICATION
 */
$arResult = array();

if(!array_key_exists("SELECTED_STATUS", $arParams) || !$arParams["SELECTED_STATUS"])
	$arParams["SELECTED_STATUS"] = "";

$arParams["SELECTED_USER_ID"] = intval($arParams["SELECTED_USER_ID"]);

if(!array_key_exists("IDEA_SORT_ORDER", $_SESSION))
	$_SESSION["IDEA_SORT_ORDER"] = "DATE_PUBLISH";

$arParams["SET_NAV_CHAIN"] = $arParams["SET_NAV_CHAIN"] == "Y";

$arStatusList = CIdeaManagment::getInstance()->Idea()->GetStatusList();
if(!$arStatusList)
	return false;

$arUrlParams = array();
if($arParams["SELECTED_USER_ID"]>0)
	$arUrlParams['user_id'] = $arParams["SELECTED_USER_ID"];
if(is_array($arParams["CATEGORIES"]) && array_key_exists("CATEGORY_1", $arParams["CATEGORIES"]) && $arParams["CATEGORIES"]["CATEGORY_1"] <> '')
	$arUrlParams['category_1'] = mb_strtolower($arParams["CATEGORIES"]["CATEGORY_1"]);
if(is_array($arParams["CATEGORIES"]) && array_key_exists("CATEGORY_2", $arParams["CATEGORIES"]) && $arParams["CATEGORIES"]["CATEGORY_2"] <> '')
	$arUrlParams['category_2'] = mb_strtolower($arParams["CATEGORIES"]["CATEGORY_2"]);

foreach($arStatusList as $key=>$arStatus)
{
	$arUrlParams['status_code'] = mb_strtolower($arStatus["XML_ID"]);
	$arStatusList[$key]["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_CATEGORY_WITH_STATUS"], $arUrlParams);
	$arStatusList[$key]["SELECTED"] = ($arParams["SELECTED_STATUS"] <> '' && mb_strtolower($arStatus["XML_ID"])===mb_strtolower($arParams["SELECTED_STATUS"]));
	if($arParams["SET_NAV_CHAIN"] && $arStatusList[$key]["SELECTED"])
		$APPLICATION->AddChainItem($arStatus["VALUE"], $arStatusList[$key]["URL"]);
}

$arResult["STATUSES"] = $arStatusList;
$arResult["SORT_ORDER"] = $_SESSION["IDEA_SORT_ORDER"];

$this->IncludeComponentTemplate();
?>