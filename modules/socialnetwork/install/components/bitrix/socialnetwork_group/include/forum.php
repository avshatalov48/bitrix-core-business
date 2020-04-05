<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$UID = ($arResult["VARIABLES"]["user_id"] > 0 ? $arResult["VARIABLES"]["user_id"] : $GLOBALS["USER"]->GetID());
foreach ($arDefaultUrlTemplates404 as $url => $value)
{
	if (strPos($url, "user_forum") === false && strPos($url, "group_forum") === false)
		continue;
	$arResult["~PATH_TO_".strToUpper($url)] = str_replace(
		array(
			"#user_id#",
			"#group_id#",
			"#topic_id#",
			"#message_id#",
			"#action#"),
		array(
			$UID,
			$arResult["VARIABLES"]["group_id"],
			"#TID#",
			"#MID#",
			"#ACTION#"),
	$arResult["PATH_TO_".strToUpper($url)]);

}
$arResult["~PATH_TO_USER"] = str_replace("#user_id#", "#UID#", (empty($arResult["PATH_TO_USER"]) ? $arParams["PATH_TO_USER"] : $arResult["PATH_TO_USER"]));
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
	$arParams["FID"] = intVal($arParams["FORUM_ID"]);
/*	$arParams["TID"] = intVal($arParams["TID"]);
	$arParams["MID"] = intVal($arParams["MID"]);
	$arParams["PAGE_NAME"] = trim($arParams["PAGE_NAME"]);
	$arParams["MESSAGE_TYPE"] = strToUpper($arParams["MESSAGE_TYPE"]);
	$arParams["bVarsFromForm"] = ($arParams["bVarsFromForm"] == "Y" || $arParams["bVarsFromForm"] === true ? "Y" : "N");
*/

	$arParams["USE_DESC_PAGE"] = ($arParams["USE_DESC_PAGE"] == "N" ? "N" : "Y");
	$arParams["SOCNET_GROUP_ID"] = intVal($arParams["SOCNET_GROUP_ID"]);
	$arParams["USER_ID"] = intVal(intVal($arParams["USER_ID"]) > 0 ? $arParams["USER_ID"] : $USER->GetID());
/***************** URL *********************************************/
/*	$URL_NAME_DEFAULT = array(
			"topic_list" => "PAGE_NAME=topic_list&FID=#FID#",
			"topic" => "PAGE_NAME=topic&FID=#FID#&TID=#TID#",
			"topic_edit" => "PAGE_NAME=topic_edit&FID=#FID#&TID=#TID#&MID=#MID#&MESSAGE_TYPE=#MESSAGE_TYPE#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"group" => "PAGE_NAME=group&GID=#GID#", 
			"user" => "PAGE_NAME=user&UID=#UID#");
*/
/***************** ADDITIONAL **************************************/
	$arParams["PAGEN"] = intVal($GLOBALS["NavNum"] + 1);
	//$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = "forum"; 
	$arParams["PAGE_NAVIGATION_WINDOW"] = 5;
	$arParams["PAGE_NAVIGATION_SHOW_ALL"] = "N";

	$arParams["TOPICS_PER_PAGE"] = intVal($arParams["TOPICS_PER_PAGE"] > 0 ? $arParams["TOPICS_PER_PAGE"] : COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"));
	$arParams["MESSAGES_PER_PAGE"] = intVal($arParams["MESSAGES_PER_PAGE"] > 0 ? $arParams["MESSAGES_PER_PAGE"] : COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"));
	$arParams["~DATE_TIME_FORMAT"] = trim($arParams["DATE_TIME_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = (empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	if (empty($arParams["DATE_FORMAT"]) && !empty($arParams["~DATE_TIME_FORMAT"])) {
		$res = CComponentUtil::GetDateFormatField();
		foreach($res["VALUES"] as $date => $k) {
			if (substr_compare($date, $arParams["~DATE_TIME_FORMAT"], 0, strlen($date), true) == 0) {
				$arParams["DATE_FORMAT"] = $date;
				break;
			}
		}
	}
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);

	$arParams["WORD_LENGTH"] = intVal($arParams["WORD_LENGTH"]);
	$arParams["IMAGE_SIZE"] = (intVal($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 300);

	$arParams["AJAX_TYPE"] = ($arParams["AJAX_TYPE"] == "Y" ? "Y" : "N");
	$arParams["AJAX_CALL"] = (($_REQUEST["AJAX_CALL"] == "Y" && $arParams["AJAX_TYPE"] == "Y") ? "Y" : "N");
	$arParams["FORUM_AJAX_POST"] = ($arParams["AJAX_CALL"] == "Y" ? "N" : "Y");

/***************** STANDART ****************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/***************** TEMPATES ****************************************/
/*	$arParams["SHOW_TAGS"] = "N"; 
	$arParams["FILES_COUNT"] = "N"; 
	$arParams["SMILES_COUNT"] = "N"; 
*/
/********************************************************************
				/Input params
********************************************************************/
if (strPos($componentPage, "user_forum") === false && strPos($componentPage, "group_forum") === false)
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

$arParams["FORUM_THEME"] = trim($arParams["FORUM_THEME"]);
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
$arEntityActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames(((strpos($componentPage, "user_forum") === false) ? SONET_ENTITY_GROUP : SONET_ENTITY_USER), ((strpos($componentPage, "user_forum") === false) ? $arResult["VARIABLES"]["group_id"] : $arResult["VARIABLES"]["user_id"]));	
$strFeatureTitle = ((array_key_exists($feature, $arEntityActiveFeatures) && StrLen($arEntityActiveFeatures[$feature]) > 0) ? $arEntityActiveFeatures[$feature] : (strpos($componentPage, "user_forum") === false ? GetMessage("FL_FORUM_GROUP_CHAIN") : GetMessage("FL_FORUM_USER_CHAIN")));
$title = $strFeatureTitle;

$url = "";
if (strpos($componentPage, "user_forum") === false)
{
	$arGroup = CSocNetGroup::GetByID($arResult["VARIABLES"]["group_id"]);
	$APPLICATION->AddChainItem($arGroup["NAME"], CComponentEngine::MakePathFromTemplate($arResult["~PATH_TO_GROUP"], array("GID" => $arGroup["ID"])));
	$title_short = $title;
	$title = $arGroup["NAME"].": ".$title;
	$url = CComponentEngine::MakePathFromTemplate($arResult["~PATH_TO_GROUP_FORUM"], array("GID" => $arGroup["ID"]));
}
else
{
	$dbUser = CUser::GetByID($arResult["VARIABLES"]["user_id"]);
	$arUser = $dbUser->Fetch();

	if (strlen($arParams["NAME_TEMPLATE"]) <= 0)	
		$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
			
	$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
		array("#NOBR#", "#/NOBR#"), 
		array("", ""), 
		$arParams["NAME_TEMPLATE"]
	);

	$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;
	$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arUser, $bUseLogin);

//	$arUserName = trim($arUser["NAME"]." ".$arUser["LAST_NAME"]);
//	$arUserName = empty($arUserName) ? $arUser["LOGIN"] : $arUserName;

	$APPLICATION->AddChainItem($strTitleFormatted, CComponentEngine::MakePathFromTemplate($arResult["~PATH_TO_USER"], array("UID" => $arUser["ID"])));
	$title_short = $title;
	$title = $strTitleFormatted.": ".$title;
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
?>