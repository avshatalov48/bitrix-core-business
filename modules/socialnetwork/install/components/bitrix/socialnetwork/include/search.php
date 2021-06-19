<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arParams["SEARCH_FILTER_NAME"] = (
	isset($arParams["SEARCH_FILTER_NAME"])
	&& $arParams["SEARCH_FILTER_NAME"] <> ''
		? $arParams["SEARCH_FILTER_NAME"]
		: "sonet_search_filter"
);

$arParams["SEARCH_FILTER_DATE_NAME"] = (
	isset($arParams["SEARCH_FILTER_DATE_NAME"])
	&& $arParams["SEARCH_FILTER_DATE_NAME"] <> ''
		? $arParams["SEARCH_FILTER_DATE_NAME"]
		: "sonet_search_filter_date"
);

$arParams["SEARCH_RESTART"] = (
	isset($arParams["SEARCH_RESTART"])
	&& $arParams["SEARCH_RESTART"] == "Y"
		? $arParams["SEARCH_RESTART"]
		: "N"
);

$arParams["SEARCH_USE_LANGUAGE_GUESS"] = (
	isset($arParams["SEARCH_USE_LANGUAGE_GUESS"])
	&& $arParams["SEARCH_USE_LANGUAGE_GUESS"] == "N"
		? $arParams["SEARCH_USE_LANGUAGE_GUESS"]
		: "Y"
);

global ${$arParams["SEARCH_FILTER_NAME"]}, $sonet_search_settings;
$sonet_search_filter = array();
$EntityType = (mb_strpos($componentPage, "group_content_search") !== false ? SONET_ENTITY_GROUP : SONET_ENTITY_USER);

$sFilterDateTo = $_REQUEST[$arParams["SEARCH_FILTER_DATE_NAME"]."_to"];
if ($arr = ParseDateTime($_REQUEST[$arParams["SEARCH_FILTER_DATE_NAME"]."_to"]))
{
	if (
		intval($arr["HH"]) == 0
		&& intval($arr["MI"]) == 0
		&& intval($arr["SS"]) == 0
	)
	{
		$arr["HH"] = "23";
		$arr["MI"] = "59";
		$arr["SS"] = "59";
		$sDateTime = $arr["DD"].".".$arr["MM"].".".$arr["YYYY"]." ".$arr["HH"].":".$arr["MI"].":".$arr["SS"];
		$stmp = MakeTimeStamp($sDateTime, "DD.MM.YYYY HH:MI:SS");
		$sFilterDateTo = ConvertTimeStamp($stmp, "FULL");
	}
}

if ($_REQUEST[$arParams["SEARCH_FILTER_NAME"]] <> '')
	$sonet_search_filter["SONET_FEATURE"] = $_REQUEST[$arParams["SEARCH_FILTER_NAME"]];
if ($_REQUEST[$arParams["SEARCH_FILTER_DATE_NAME"]."_from"] <> '')
	$sonet_search_filter[">=DATE_CHANGE"] = $_REQUEST[$arParams["SEARCH_FILTER_DATE_NAME"]."_from"];
if ($sFilterDateTo <> '')
	$sonet_search_filter["<=DATE_CHANGE"] = $sFilterDateTo;

$sonet_search_settings = array(
	"PHOTO_IBLOCK_TYPE" => ($EntityType == SONET_ENTITY_GROUP ? $arParams["PHOTO_GROUP_IBLOCK_TYPE"] : $arParams["PHOTO_USER_IBLOCK_TYPE"]),
	"PHOTO_IBLOCK_ID" => ($EntityType == SONET_ENTITY_GROUP ? $arParams["PHOTO_GROUP_IBLOCK_ID"] : $arParams["PHOTO_USER_IBLOCK_ID"]),
	"FILES_IBLOCK_TYPE" => ($EntityType == SONET_ENTITY_GROUP ? $arParams["FILES_GROUP_IBLOCK_TYPE"] : $arParams["FILES_USER_IBLOCK_TYPE"]),
	"FILES_IBLOCK_ID" => ($EntityType == SONET_ENTITY_GROUP ? $arParams["FILES_GROUP_IBLOCK_ID"] : $arParams["FILES_USER_IBLOCK_ID"]),
	"CALENDAR_IBLOCK_TYPE" => $arParams["CALENDAR_IBLOCK_TYPE"],
	"CALENDAR_IBLOCK_ID" => ($EntityType == SONET_ENTITY_GROUP ? $arParams["CALENDAR_GROUP_IBLOCK_ID"] : $arParams["CALENDAR_USER_IBLOCK_ID"]),
);

AddEventHandler("search", "OnSearchPrepareFilter", Array("CSocNetSearchComponent", "OnSearchPrepareFilterHandler"));

class CSocNetSearchComponent
{
	public static function OnSearchPrepareFilterHandler($strSearchContentAlias, $field, $val)
	{
		if($field == "SONET_FEATURE")
		{
			$feature = false;
			if(!is_array($val))
				$feature = trim($val);

			if($feature)
			{
				switch($feature)
				{
					case "forum":
						return " ".$strSearchContentAlias."MODULE_ID = 'forum'";
					case "blog":
						return " ".$strSearchContentAlias."MODULE_ID = 'blog'";
					case "tasks":
						return " ".$strSearchContentAlias."MODULE_ID = 'tasks'";
					case "photo":
						$iblock_type = $GLOBALS["sonet_search_settings"]["PHOTO_IBLOCK_TYPE"];
						$iblock_id = $GLOBALS["sonet_search_settings"]["PHOTO_IBLOCK_ID"];
						if ($iblock_type <> '' && intval($iblock_id) > 0)
							return " ".$strSearchContentAlias."MODULE_ID = 'socialnetwork' AND ".$strSearchContentAlias."PARAM1 = '".$iblock_type."' AND ".$strSearchContentAlias."PARAM2 = ".$iblock_id;
						else
							return " 1=0";
					case "files":
						$iblock_type = $GLOBALS["sonet_search_settings"]["FILES_IBLOCK_TYPE"];
						$iblock_id = $GLOBALS["sonet_search_settings"]["FILES_IBLOCK_ID"];
						if ($iblock_type <> '' && intval($iblock_id) > 0)
							return " ".$strSearchContentAlias."MODULE_ID = 'socialnetwork' AND ".$strSearchContentAlias."PARAM1 = '".$iblock_type."' AND ".$strSearchContentAlias."PARAM2 = ".$iblock_id;
						else
							return " 1=0";
					default:
						return " 1=0";
				}
			}
			else
				return "";
		}
		else
			return "";
	}
}

if (mb_strpos($componentPage, "user_content_search") === false)
{
	$arGroup = CSocNetGroup::GetByID($arResult["VARIABLES"]["group_id"]);
	$APPLICATION->AddChainItem($arGroup["NAME"], CComponentEngine::MakePathFromTemplate(htmlspecialcharsbx($arResult["PATH_TO_GROUP"]), array("group_id" => $arGroup["ID"])));
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
	
	$APPLICATION->AddChainItem($strTitleFormatted, CComponentEngine::MakePathFromTemplate(htmlspecialcharsbx($arResult["PATH_TO_USER"]), array("user_id" => $arUser["ID"])));
}

$feature = "search";
$arEntityActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames(((mb_strpos($componentPage, "user_content_search") === false) ? SONET_ENTITY_GROUP : SONET_ENTITY_USER), ((mb_strpos($componentPage, "user_content_search") === false) ? $arResult["VARIABLES"]["group_id"] : $arResult["VARIABLES"]["user_id"]));
$strFeatureTitle = ((array_key_exists($feature, $arEntityActiveFeatures) && $arEntityActiveFeatures[$feature] <> '') ? $arEntityActiveFeatures[$feature] : GetMessage("SONET_CONTENT_SEARCH_CHAIN"));

if (mb_strpos($componentPage, "user_content_search") === false)
	$url = CComponentEngine::MakePathFromTemplate(htmlspecialcharsbx($arResult["PATH_TO_GROUP_CONTENT_SEARCH"]), array("group_id" => $arResult["VARIABLES"]["group_id"]));
else
	$url = CComponentEngine::MakePathFromTemplate(htmlspecialcharsbx($arResult["PATH_TO_USER_CONTENT_SEARCH"]), array("user_id" => $arResult["VARIABLES"]["user_id"]));

$APPLICATION->AddChainItem($strFeatureTitle, $url);
?>