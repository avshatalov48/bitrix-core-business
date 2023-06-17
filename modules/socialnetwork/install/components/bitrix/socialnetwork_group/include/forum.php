<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponent $component */
/** @var array $arParams */
/** @var array $arResult */
/** @var array $arDefaultUrlTemplates404 */
/** @var string $componentPage */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$userId = ($arResult["VARIABLES"]["user_id"] ?? 0);
$UID = ($userId > 0 ? $userId : $GLOBALS["USER"]->GetID());
foreach ($arDefaultUrlTemplates404 as $url => $value)
{
	if (mb_strpos($url, "user_forum") === false && mb_strpos($url, "group_forum") === false)
		continue;
	$arResult["~PATH_TO_".mb_strtoupper($url)] = str_replace(
		array(
			"#user_id#",
			"#group_id#",
			"#topic_id#",
			"#message_id#",
			"#action#"),
		array(
			$UID,
			($arResult["VARIABLES"]["group_id"] ?? 0),
			"#TID#",
			"#MID#",
			"#ACTION#"),
	$arResult["PATH_TO_".mb_strtoupper($url)]);

}
$arResult["~PATH_TO_USER"] = str_replace("#user_id#", "#UID#", (empty($arResult["PATH_TO_USER"]) ? $arParams["PATH_TO_USER"] ?? '' : $arResult["PATH_TO_USER"] ?? ''));
$arResult["~PATH_TO_GROUP"] = str_replace("#group_id#", "#GID#", $arResult["PATH_TO_GROUP"]);
if ($componentPage == "user_forum_message")
	$componentPage = "user_forum_topic";
elseif ($componentPage == "user_forum_message_edit")
	$componentPage = "user_forum_topic";
elseif ($componentPage == "group_forum_message")
	$componentPage = "group_forum_topic";
elseif ($componentPage == "group_forum_message_edit")
	$componentPage = "group_forum_topic";

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["FID"] = intval($arParams["FORUM_ID"] ?? null);
	$arParams["USE_DESC_PAGE"] = (($arParams["USE_DESC_PAGE"] ?? '') == "N" ? "N" : "Y");
	$arParams["SOCNET_GROUP_ID"] = (int) ($arParams["SOCNET_GROUP_ID"] ?? 0);
	$arParams["USER_ID"] = (int) (($arParams["USER_ID"] ?? 0) > 0 ? $arParams["USER_ID"] : $USER->GetID());
/***************** ADDITIONAL **************************************/
	$arParams["PAGEN"] = intval(($GLOBALS["NavNum"] ?? 0) + 1);
	//$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = "forum"; 
	$arParams["PAGE_NAVIGATION_WINDOW"] = 5;
	$arParams["PAGE_NAVIGATION_SHOW_ALL"] = "N";

	$arParams["TOPICS_PER_PAGE"] = (int) (
		($arParams["TOPICS_PER_PAGE"] ?? 0) > 0
			? $arParams["TOPICS_PER_PAGE"]
			: COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10")
	);
	$arParams["MESSAGES_PER_PAGE"] = (int) (
		($arParams["MESSAGES_PER_PAGE"] ?? 0) > 0
			? $arParams["MESSAGES_PER_PAGE"]
			: COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10")
	);
	$arParams["~DATE_TIME_FORMAT"] = trim($arParams["DATE_TIME_FORMAT"] ?? '');
	$arParams["DATE_TIME_FORMAT"] = (empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	if (empty($arParams["DATE_FORMAT"]) && !empty($arParams["~DATE_TIME_FORMAT"])) {
		$res = CComponentUtil::GetDateFormatField();
		foreach($res["VALUES"] as $date => $k) {
			if (substr_compare($date, $arParams["~DATE_TIME_FORMAT"], 0, mb_strlen($date), true) == 0) {
				$arParams["DATE_FORMAT"] = $date;
				break;
			}
		}
	}
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);

	$arParams["WORD_LENGTH"] = (int) ($arParams["WORD_LENGTH"] ?? 0);
	$arParams["IMAGE_SIZE"] = ((int)
		($arParams["IMAGE_SIZE"] ?? 0) > 0
		? $arParams["IMAGE_SIZE"]
		: 300
	);

	$ajaxType = ($arParams["AJAX_TYPE"] ?? '');
	$ajaxCall = ($arParams["AJAX_CALL"] ?? '');
	$arParams["AJAX_TYPE"] = ($ajaxType === "Y" ? "Y" : "N");
	$arParams["AJAX_CALL"] = (($ajaxCall === "Y" && $ajaxType == "Y") ? "Y" : "N");
	$arParams["FORUM_AJAX_POST"] = ($ajaxCall === "Y" ? "N" : "Y");

/***************** STANDART ****************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"] ?? null);
	else
		$arParams["CACHE_TIME"] = 0;
	$arParams["SET_TITLE"] = (($arParams["SET_TITLE"] ?? null) == "N" ? "N" : "Y");
/***************** TEMPATES ****************************************/
/*	$arParams["SHOW_TAGS"] = "N"; 
	$arParams["FILES_COUNT"] = "N"; 
	$arParams["SMILES_COUNT"] = "N"; 
*/
/********************************************************************
				/Input params
********************************************************************/
if (mb_strpos($componentPage, "user_forum") === false && mb_strpos($componentPage, "group_forum") === false)
	return 1;

/************** CSS ************************************************/
$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');

$arThemes = array();
$sTemplateDirFull = preg_replace("'[\\\\/]+'", "/", $_SERVER['DOCUMENT_ROOT']."/bitrix/components/bitrix/forum/templates/.default/themes/");
$dir = $sTemplateDirFull;
if (is_dir($dir) && $directory = opendir($dir)):

	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arThemes[] = $file;
	}
	closedir($directory);
endif;

$arParams["FORUM_THEME"] = trim($arParams["FORUM_THEME"] ?? '');
$sPathTheme = str_replace(array("\\", "//"), "/", $_SERVER['DOCUMENT_ROOT']."/".$arParams["FORUM_THEME"]."/");
if (in_array($arParams["FORUM_THEME"], $arThemes)):
//
elseif (is_file($sPathTheme."style.css")):
	$arParams["FORUM_THEME"] = $sPathTheme;
else:
	$arParams["FORUM_THEME"] = (in_array("white", $arThemes) ? "white" : $arThemes[0]);
endif;
/********************************************************************
				/Input params
********************************************************************/
if (in_array($arParams["FORUM_THEME"], $arThemes)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS("/bitrix/components/bitrix/forum/templates/.default/themes/".$arParams["FORUM_THEME"]."/style.css");
else:
	$GLOBALS['APPLICATION']->SetAdditionalCSS($arParams["FORUM_THEME"]."/style.css");
endif;
/************** Page navigation ************************************/
$feature = "forum";
$arEntityActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames(((mb_strpos($componentPage, "user_forum") === false) ? SONET_ENTITY_GROUP : SONET_ENTITY_USER), ((mb_strpos($componentPage, "user_forum") === false) ? $arResult["VARIABLES"]["group_id"] : $arResult["VARIABLES"]["user_id"]));
$strFeatureTitle = ((array_key_exists($feature, $arEntityActiveFeatures) && $arEntityActiveFeatures[$feature] <> '') ? $arEntityActiveFeatures[$feature] : (mb_strpos($componentPage, "user_forum") === false ? GetMessage("FL_FORUM_GROUP_CHAIN") : GetMessage("FL_FORUM_USER_CHAIN")));

$url = "";
if (mb_strpos($componentPage, "user_forum") === false)
{
	$APPLICATION->AddChainItem($arResult['groupFields']['NAME'], \CComponentEngine::makePathFromTemplate($arResult['~PATH_TO_GROUP'], array(
		'GID' => $arResult['groupFields']['ID'],
	)));
	$title_short = $strFeatureTitle;
	$title = str_replace('#PAGE_TITLE#', $strFeatureTitle, $arResult['PAGES_TITLE_TEMPLATE']);
	$url = \CComponentEngine::makePathFromTemplate($arResult['~PATH_TO_GROUP_FORUM'], [
		'GID' => $arResult['groupFields']['ID'],
	]);
}
else
{
	$dbUser = CUser::GetByID($arResult["VARIABLES"]["user_id"]);
	$arUser = $dbUser->Fetch();

	if ($arParams["NAME_TEMPLATE"] == '')
		$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
			
	$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
		array("#NOBR#", "#/NOBR#"), 
		array("", ""), 
		$arParams["NAME_TEMPLATE"]
	);

	$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;
	$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arUser, $bUseLogin);

	$APPLICATION->AddChainItem($strTitleFormatted, CComponentEngine::MakePathFromTemplate($arResult["~PATH_TO_USER"], array("UID" => $arUser["ID"])));
	$title_short = $strFeatureTitle;
	$title = $strTitleFormatted.": ".$strFeatureTitle;
	$url = CComponentEngine::MakePathFromTemplate($arResult["~PATH_TO_USER_FORUM"], array("UID" => $arUser["ID"]));
}
$APPLICATION->AddChainItem($strFeatureTitle, $url);
if ($arParams["SET_TITLE"] != "N")
{
	if ($arParams["HIDE_OWNER_IN_TITLE"] == "Y")
	{
		$APPLICATION->SetPageProperty("title", $title);
		$APPLICATION->SetTitle($title_short);
	}
	else
	{
		$APPLICATION->SetTitle($title);
	}
}

return 1;
