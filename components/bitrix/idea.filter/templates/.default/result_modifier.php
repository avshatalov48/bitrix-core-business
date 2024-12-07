<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
__IncludeLang(__DIR__."/lang/".LANGUAGE_ID."/result_modifier.php");

$arUrlParams = array();
if($arParams["SELECTED_USER_ID"]>0)
	$arUrlParams['user_id'] = $arParams["SELECTED_USER_ID"];
if(is_array($arParams["CATEGORIES"]) && array_key_exists("CATEGORY_1", $arParams["CATEGORIES"]) && $arParams["CATEGORIES"]["CATEGORY_1"] <> '')
	$arUrlParams['category_1'] = mb_strtolower($arParams["CATEGORIES"]["CATEGORY_1"]);
if(is_array($arParams["CATEGORIES"]) && array_key_exists("CATEGORY_2", $arParams["CATEGORIES"]) && $arParams["CATEGORIES"]["CATEGORY_2"] <> '')
	$arUrlParams['category_2'] = mb_strtolower($arParams["CATEGORIES"]["CATEGORY_2"]);

$arResult["STATUSES"]["ALL"] = array(
	"VALUE" => GetMessage("IDEA_STATUS_ALL"),
	"SELECTED" => $arParams["SELECTED_STATUS"] == '',
	"URL" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_CATEGORY"], $arUrlParams)
);
?>