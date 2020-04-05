<?
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";
if (strLen($arParams["USER_VAR"]) <= 0)
	$arParams["USER_VAR"] = "user_id";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if (strlen($arParams["PATH_TO_USER"]) <= 0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_SEARCH"] = trim($arParams["PATH_TO_SEARCH"]);
if (strlen($arParams["PATH_TO_SEARCH"]) <= 0)
	$arParams["PATH_TO_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=search");

$arParams["PATH_TO_SEARCH_INNER"] = trim($arParams["PATH_TO_SEARCH_INNER"]);
if (strlen($arParams["PATH_TO_SEARCH_INNER"]) <= 0)
	$arParams["PATH_TO_SEARCH_INNER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=search");

$arParams["PATH_TO_USER_FRIENDS_ADD"] = trim($arParams["PATH_TO_USER_FRIENDS_ADD"]);
if(strlen($arParams["PATH_TO_USER_FRIENDS_ADD"])<=0)
	$arParams["PATH_TO_USER_FRIENDS_ADD"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_friends_add&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGE_FORM"] = trim($arParams["PATH_TO_MESSAGE_FORM"]);
if (strlen($arParams["PATH_TO_MESSAGE_FORM"]) <= 0)
	$arParams["PATH_TO_MESSAGE_FORM"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=message_form&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGES_CHAT"] = trim($arParams["PATH_TO_MESSAGES_CHAT"]);
if (strlen($arParams["PATH_TO_MESSAGES_CHAT"]) <= 0)
	$arParams["PATH_TO_MESSAGES_CHAT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_chat&".$arParams["USER_VAR"]."=#user_id#");

$arParams["SHOW_USERS_WITHOUT_FILTER_SET"] = ($arParams["SHOW_USERS_WITHOUT_FILTER_SET"] == "Y" ? "Y" : "N");

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

if ($arParams['CACHE_TYPE'] == 'A')
	$arParams['CACHE_TYPE'] = COption::GetOptionString("main", "component_cache_on", "Y");

$arParams["ITEMS_COUNT"] = IntVal($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 20;

$arParams['SHOW_YEAR'] = $arParams['SHOW_YEAR'] == 'Y' ? 'Y' : ($arParams['SHOW_YEAR'] == 'M' ? 'M' : 'N');

$arParams["DATE_TIME_FORMAT"] = Trim($arParams["DATE_TIME_FORMAT"]);
$arParams["DATE_TIME_FORMAT"] = ((StrLen($arParams["DATE_TIME_FORMAT"]) <= 0) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

if (strlen($arParams["NAME_TEMPLATE"]) <= 0)
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;


if (!isset($arParams["USER_FIELDS_SEARCH_SIMPLE"]) || !is_array($arParams["USER_FIELDS_SEARCH_SIMPLE"]))
	$arParams["USER_FIELDS_SEARCH_SIMPLE"] = array();
if (!isset($arParams["USER_PROPERTIES_SEARCH_SIMPLE"]) || !is_array($arParams["USER_PROPERTIES_SEARCH_SIMPLE"]))
	$arParams["USER_PROPERTIES_SEARCH_SIMPLE"] = array();
if (!isset($arParams["USER_FIELDS_SEARCH_ADV"]) || !is_array($arParams["USER_FIELDS_SEARCH_ADV"]))
	$arParams["USER_FIELDS_SEARCH_ADV"] = array();
if (!isset($arParams["USER_PROPERTIES_SEARCH_ADV"]) || !is_array($arParams["USER_PROPERTIES_SEARCH_ADV"]))
	$arParams["USER_PROPERTIES_SEARCH_ADV"] = array();

if (!isset($arParams["USER_FIELDS_LIST"]) || !is_array($arParams["USER_FIELDS_LIST"]))
	$arParams["USER_FIELDS_LIST"] = array();
if (!isset($arParams["USER_PROPERTY_LIST"]) || !is_array($arParams["USER_PROPERTY_LIST"]))
	$arParams["USER_PROPERTY_LIST"] = array();
if (empty($arParams["USER_PROPERTY_LIST"]) && isset($arParams["USER_PROPERTIES_LIST"]) && !empty($arParams["USER_PROPERTIES_LIST"]))
	$arParams["USER_PROPERTY_LIST"] = $arParams["USER_PROPERTIES_LIST"];

if (!isset($arParams["USER_FIELDS_SEARCHABLE"]) || !is_array($arParams["USER_FIELDS_SEARCHABLE"]))
	$arParams["USER_FIELDS_SEARCHABLE"] = array();
if (!isset($arParams["USER_PROPERTY_SEARCHABLE"]) || !is_array($arParams["USER_PROPERTY_SEARCHABLE"]))
	$arParams["USER_PROPERTY_SEARCHABLE"] = array();

foreach ($arParams["USER_FIELDS_SEARCH_SIMPLE"] as $value)
{
	if (!in_array($value, $arParams["USER_FIELDS_SEARCHABLE"]))
		$arParams["USER_FIELDS_SEARCHABLE"][] = $value;
}
foreach ($arParams["USER_FIELDS_SEARCH_ADV"] as $value)
{
	if (!in_array($value, $arParams["USER_FIELDS_SEARCHABLE"]))
		$arParams["USER_FIELDS_SEARCHABLE"][] = $value;
}
foreach ($arParams["USER_PROPERTIES_SEARCH_SIMPLE"] as $value)
{
	if (!in_array($value, $arParams["USER_PROPERTY_SEARCHABLE"]))
		$arParams["USER_PROPERTY_SEARCHABLE"][] = $value;
}
foreach ($arParams["USER_PROPERTIES_SEARCH_ADV"] as $value)
{
	if (!in_array($value, $arParams["USER_PROPERTY_SEARCHABLE"]))
		$arParams["USER_PROPERTY_SEARCHABLE"][] = $value;
}

if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle(GetMessage("SONET_C241_PAGE_TITLE"));

if ($arParams["SET_NAV_CHAIN"] != "N")
	$APPLICATION->AddChainItem(GetMessage("SONET_C241_PAGE_TITLE"));

$arResult["Urls"]["UserSearch"] = (\Bitrix\Main\ModuleManager::isModuleInstalled('intranet') ? $APPLICATION->GetCurPage() : CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_SEARCH_INNER"], array()));
$arResult["Params"]["UserSearch"] = array();
if (StrPos($arResult["Urls"]["UserSearch"], "?") !== false)
{
	$str = SubStr($arResult["Urls"]["UserSearch"], StrPos($arResult["Urls"]["UserSearch"], "?") + 1);
	$arStr = Explode("&", $str);
	foreach ($arStr as $str)
	{
		$str = Trim($str);
		$p = StrPos($str, "=");
		if (StrLen($str) > 0 && $p !== false)
			$arResult["Params"]["UserSearch"][htmlspecialcharsbx(SubStr($str, 0, $p))] = htmlspecialcharsbx(SubStr($str, $p + 1));
	}
}
$arResult["Urls"]["ViewList"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("current_view=list", array("current_view")));
$arResult["Urls"]["ViewIcon"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("current_view=icon", array("current_view")));
$arResult["Urls"]["ViewBigIcon"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("current_view=bigicon", array("current_view")));

$currentFilter = ($_REQUEST['current_filter'] == 'adv' ? 'adv' : 'simple');
$arResult['CURRENT_FILTER'] = $currentFilter;

$currentView = (array_key_exists("current_view", $_REQUEST) ? $_REQUEST["current_view"] : $_SESSION["SONET_SEARCH_current_view"]);
if (!in_array($currentView, array("icon", "bigicon", "list")))
	$currentView = "list";
$_SESSION["SONET_SEARCH_current_view"] = $currentView;
$arResult['CURRENT_VIEW'] = $currentView;


$arResult["SEARCH_RESULT"] = Array();

$arNavParams = array("nPageSize" => $arParams["ITEMS_COUNT"], "bShowAll" => false, "bDescPageNumbering" => false);
$arNavigation = CDBResult::GetNavParams($arNavParams);

$by = "LAST_NAME";
$order = "asc";
$arFilter = array("ACTIVE" => "Y");
$arResult["ShowResults"] = ($arParams["SHOW_USERS_WITHOUT_FILTER_SET"] == "Y");

//****************   INIT   FILTER   ******************************************************************//

$arUserProps = array(
	"ID" => GetMessage("SONET_C241_ID"),
	"LOGIN" => GetMessage("SONET_C241_LOGIN"),
	"NAME" => GetMessage("SONET_C241_NAME"),
	"SECOND_NAME" => GetMessage("SONET_C241_SECOND_NAME"),
	"LAST_NAME" => GetMessage("SONET_C241_LAST_NAME"),
	"EMAIL" => GetMessage("SONET_C241_EMAIL"),
	"LAST_LOGIN" => GetMessage("SONET_C241_LAST_LOGIN"),
	"DATE_REGISTER" => GetMessage("SONET_C241_DATE_REGISTER"),
	"LID" => GetMessage("SONET_C241_LID"),

	"PERSONAL_BIRTHDAY" => GetMessage("SONET_C241_PERSONAL_BIRTHDAY"),
	"PERSONAL_BIRTHDAY_YEAR" => GetMessage("SONET_C241_PERSONAL_BIRTHDAY_YEAR"),
	"PERSONAL_BIRTHDAY_DAY" => GetMessage("SONET_C241_PERSONAL_BIRTHDAY_DAY"),

	"PERSONAL_PROFESSION" => GetMessage("SONET_C241_PERSONAL_PROFESSION"),
	"PERSONAL_WWW" => GetMessage("SONET_C241_PERSONAL_WWW"),
	"PERSONAL_ICQ" => GetMessage("SONET_C241_PERSONAL_ICQ"),
	"PERSONAL_GENDER" => GetMessage("SONET_C241_PERSONAL_GENDER"),
	"PERSONAL_PHOTO" => GetMessage("SONET_C241_PERSONAL_PHOTO"),
	"PERSONAL_NOTES" => GetMessage("SONET_C241_PERSONAL_NOTES"),

	"PERSONAL_PHONE" => GetMessage("SONET_C241_PERSONAL_PHONE"),
	"PERSONAL_FAX" => GetMessage("SONET_C241_PERSONAL_FAX"),
	"PERSONAL_MOBILE" => GetMessage("SONET_C241_PERSONAL_MOBILE"),
	"PERSONAL_PAGER" => GetMessage("SONET_C241_PERSONAL_PAGER"),

	"PERSONAL_COUNTRY" => GetMessage("SONET_C241_PERSONAL_COUNTRY"),
	"PERSONAL_STATE" => GetMessage("SONET_C241_PERSONAL_STATE"),
	"PERSONAL_CITY" => GetMessage("SONET_C241_PERSONAL_CITY"),
	"PERSONAL_ZIP" => GetMessage("SONET_C241_PERSONAL_ZIP"),
	"PERSONAL_STREET" => GetMessage("SONET_C241_PERSONAL_STREET"),
	"PERSONAL_MAILBOX" => GetMessage("SONET_C241_PERSONAL_MAILBOX"),

	"WORK_COMPANY" => GetMessage("SONET_C241_WORK_COMPANY"),
	"WORK_DEPARTMENT" => GetMessage("SONET_C241_WORK_DEPARTMENT"),
	"WORK_POSITION" => GetMessage("SONET_C241_WORK_POSITION"),
	"WORK_WWW" => GetMessage("SONET_C241_WORK_WWW"),
	"WORK_PROFILE" => GetMessage("SONET_C241_WORK_PROFILE"),
	"WORK_LOGO" => GetMessage("SONET_C241_WORK_LOGO"),
	"WORK_NOTES" => GetMessage("SONET_C241_WORK_NOTES"),

	"WORK_PHONE" => GetMessage("SONET_C241_WORK_PHONE"),
	"WORK_FAX" => GetMessage("SONET_C241_WORK_FAX"),
	"WORK_PAGER" => GetMessage("SONET_C241_WORK_PAGER"),

	"WORK_COUNTRY" => GetMessage("SONET_C241_WORK_COUNTRY"),
	"WORK_STATE" => GetMessage("SONET_C241_WORK_STATE"),
	"WORK_CITY" => GetMessage("SONET_C241_WORK_CITY"),
	"WORK_ZIP" => GetMessage("SONET_C241_WORK_ZIP"),
	"WORK_STREET" => GetMessage("SONET_C241_WORK_STREET"),
	"WORK_MAILBOX" => GetMessage("SONET_C241_WORK_MAILBOX"),
);

$arResTmp = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
$arUserCustomProps = array();
if (!empty($arResTmp))
{
	foreach ($arResTmp as $key => $value)
	{
		if (in_array($value["FIELD_NAME"], $arParams["USER_PROPERTY_SEARCHABLE"]))
			$arUserCustomProps[StrToUpper($value["FIELD_NAME"])] = $value;
	}
}

foreach ($_REQUEST as $key => $value)
{
	if (StrToLower(SubStr($key, 0, 4)) != "flt_")
		continue;
	if (!is_array($value) && StrLen($value) <= 0 || is_array($value) && count($value) <= 0)
		continue;

	$keyTmp = StrToUpper(SubStr($key, 4));
	if (array_key_exists($keyTmp, $arUserProps))
	{
		if (in_array($keyTmp, $arParams["USER_FIELDS_SEARCHABLE"]))
		{
			if (!in_array($keyTmp, $arParams["USER_FIELDS_SEARCH_SIMPLE"]))
				$arParams["USER_FIELDS_SEARCH_SIMPLE"][] = $keyTmp;
			if (!in_array($keyTmp, $arParams["USER_FIELDS_SEARCH_ADV"]))
				$arParams["USER_FIELDS_SEARCH_ADV"][] = $keyTmp;
		}
	}
	elseif (array_key_exists($keyTmp, $arUserCustomProps))
	{
		if (in_array($keyTmp, $arParams["USER_PROPERTY_SEARCHABLE"]))
		{
			if (!in_array($keyTmp, $arParams["USER_PROPERTIES_SEARCH_SIMPLE"]))
				$arParams["USER_PROPERTIES_SEARCH_SIMPLE"][] = $keyTmp;
			if (!in_array($keyTmp, $arParams["USER_PROPERTIES_SEARCH_ADV"]))
				$arParams["USER_PROPERTIES_SEARCH_ADV"][] = $keyTmp;
		}
	}
}

$arResult["UserFieldsSearchSimple"] = array();
$arResult["UserFieldsSearchAdv"] = array();
if (count($arParams["USER_FIELDS_SEARCH_SIMPLE"]) > 0 || count($arParams["USER_FIELDS_SEARCH_ADV"]) > 0)
{
	foreach ($arUserProps as $userFieldName => $userFieldTitle)
	{
		if (in_array($userFieldName, $arParams["USER_FIELDS_SEARCHABLE"])
			&& (in_array($userFieldName, $arParams["USER_FIELDS_SEARCH_SIMPLE"])
				|| in_array($userFieldName, $arParams["USER_FIELDS_SEARCH_ADV"])))
		{
			$requestName = StrToLower("FLT_".$userFieldName);
			$arVal = array(
				"VALUE" => htmlspecialcharsex(array_key_exists($requestName, $_REQUEST) ? $_REQUEST[$requestName] : ""),
				"NAME" => $requestName,
				"TITLE" => $userFieldTitle,
			);

			switch ($userFieldName)
			{
				case 'LAST_LOGIN':
				case 'DATE_REGISTER':
				case 'PERSONAL_BIRTHDAY':
					$arVal["TYPE"] = "calendar";
					break;

				case 'PERSONAL_GENDER':
					$arVal["TYPE"] = "select";
					$arVal["VALUES"] = array("M" => GetMessage("SONET_C241_MALE"), "F" => GetMessage("SONET_C241_FEMALE"));
					break;

				case 'PERSONAL_COUNTRY':
				case 'WORK_COUNTRY':
					$arVal["TYPE"] = "select";
					$arVal["VALUES"] = array();
					$arCountriesTmp = GetCountryArray(LANGUAGE_ID);
					$tmpCnt = count($arCountriesTmp["reference_id"]);
					for ($i = 0; $i < $tmpCnt; $i++)
						$arVal["VALUES"][$arCountriesTmp["reference_id"][$i]] = $arCountriesTmp["reference"][$i];
					break;

				default:
					$arVal["TYPE"] = "string";
					break;
			}

			if (in_array($userFieldName, $arParams["USER_FIELDS_SEARCH_SIMPLE"]))
				$arResult["UserFieldsSearchSimple"][$userFieldName] = $arVal;
			if (in_array($userFieldName, $arParams["USER_FIELDS_SEARCH_ADV"]))
				$arResult["UserFieldsSearchAdv"][$userFieldName] = $arVal;
		}
	}
}

$arResult["UserPropertiesSearchSimple"] = array();
$arResult["UserPropertiesSearchAdv"] = array();
if (count($arParams["USER_PROPERTIES_SEARCH_SIMPLE"]) > 0 || count($arParams["USER_PROPERTIES_SEARCH_ADV"]) > 0)
{
	foreach ($arUserCustomProps as $fieldName => $arUserField)
	{
		if (in_array($fieldName, $arParams["USER_PROPERTY_SEARCHABLE"]))
		{
			$arUserField["EDIT_FORM_LABEL"] = StrLen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
			$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
			$arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
			$arUserField["FIELD_NAME"] = StrToLower("FLT_".$fieldName);
			$arUserField["~FIELD_NAME"] = StrToLower("FLT_".$fieldName);
			if (in_array($fieldName, $arParams["USER_PROPERTIES_SEARCH_SIMPLE"]))
				$arResult["UserPropertiesSearchSimple"][$fieldName] = $arUserField;
			if (in_array($fieldName, $arParams["USER_PROPERTIES_SEARCH_ADV"]))
				$arResult["UserPropertiesSearchAdv"][$fieldName] = $arUserField;
		}
	}
}

//****************   MAKE   FILTER   ******************************************************************//

$bFilter = false;

foreach ($_REQUEST as $key => $value)
{
	if (StrToLower(SubStr($key, 0, 4)) != "flt_")
		continue;
	if (Is_Array($value))
	{
		if (Count($value) <= 0)
			continue;

		$value1 = $value;
		$value = array();
		foreach ($value1 as $val)
		{
			if (Is_Array($val))
			{
				foreach($val as $tmpkey => $tmpval)
				{
					$tmpval = preg_replace('#[\(\)]#', '', $tmpval);
					$val[$tmpkey] = $tmpval;
				}
			}
			else
			{
				$val = preg_replace('#[\(\)]#', '', $val);
			}

			
			if (
				(Is_Array($val) && Count($val) > 0)
				|| (!Is_Array($val) && StrLen($val) > 0)
			)
				$value[] = $val;
		}

		if (Count($value) <= 0)
			continue;
	}
	else
	{
		$value = preg_replace('#[\(\)]#', '', $value);
		if (StrLen($value) <= 0)
			continue;
	}

	$keyTmp = StrToUpper(SubStr($key, 4));
	if ($keyTmp == "FIO")
	{
		$arFilter["NAME"] = $value;
		$arResult["ShowResults"] = true;
		$bFilter = true;
	}
	elseif (array_key_exists($keyTmp, $arUserProps))
	{
		if (in_array($keyTmp, $arParams["USER_FIELDS_SEARCHABLE"]))
		{
			if ($keyTmp == "PERSONAL_BIRTHDAY")
			{
				$arFilter["PERSONAL_BIRTHDAY_1"] = $value;
				$arFilter["PERSONAL_BIRTHDAY_2"] = $value;
			}
			elseif ($keyTmp == "PERSONAL_BIRTHDAY_YEAR")
			{
				$arFilter["PERSONAL_BIRTHDAY_1"] = ConvertTimeStamp(mktime(0, 0, 0, 0, 0, $value), "SHORT", SITE_ID);
				$arFilter["PERSONAL_BIRTHDAY_2"] = ConvertTimeStamp(mktime(0, 0, 0, 12, 31, $value), "SHORT", SITE_ID);
			}
			elseif ($keyTmp == "PERSONAL_BIRTHDAY_DAY")
			{
				$arFilter["PERSONAL_BIRTHDAY_DATE"] = $value;
			}
			else
			{
				$arFilter[$keyTmp] = $value;
			}
			$arResult["ShowResults"] = true;
			$bFilter = true;
		}
	}
	elseif (array_key_exists($keyTmp, $arUserCustomProps))
	{
		if (in_array($keyTmp, $arParams["USER_PROPERTY_SEARCHABLE"]))
		{
			if ($arUserCustomProps[$keyTmp]["SHOW_FILTER"] == "I")
				$arFilter["=".$keyTmp] = $value;
			elseif ($arUserCustomProps[$keyTmp]["SHOW_FILTER"] == "S")
				$arFilter["%".$keyTmp] = $value;
			else
				$arFilter[$keyTmp] = $value;

			$arResult["ShowResults"] = true;
			$bFilter = true;
		}
	}
}

//*****************************************************************************************************//
// activation rating
CRatingsComponentsMain::GetShowRating($arParams);

if ($arResult["ShowResults"])
{
	$arMonths_r = array();
	for ($i = 1; $i <= 12; $i++)
		$arMonths_r[$i] = ToLower(GetMessage('MONTH_'.$i.'_S'));

	$iSize = 150;
	if ($arResult['CURRENT_VIEW'] == "icon")
		$iSize = 100;



	if (!$bFilter)
	{
		$cache_id = $this->GetName().'|'.$arParams['ITEMS_COUNT'].'|'.$by.'|'.$order.'|'.$iSize.'|'.$arParams["PATH_TO_USER"].'|'.$arParams["PATH_TO_USER_FRIENDS_ADD"].'|'.$arParams["PATH_TO_MESSAGE_FORM"].'|'.$arParams["PATH_TO_MESSAGES_CHAT"].'|'.$arParams['NAME_TEMPLATE'].'|'.$bUseLogin.'|'.$arParams['SHOW_YEAR'].'|'.implode(';', $arParams['USER_FIELDS_LIST']).'|'.implode(';', $arParams['USER_PROPERTY_LIST']).CDBResult::NavStringForCache($arParams['ITEMS_COUNT'], false);
		$obCache = new CPHPCache();
	}

	if ($arParams["SHOW_RATING"] == 'Y' && array_key_exists("RATING_ID", $arParams) && intval($arParams["RATING_ID"]) > 0)
	{
		$db_rating = CRatings::GetByID($arParams["RATING_ID"]);
		if ($arRating = $db_rating->GetNext())
			$arResult["RATING"]["NAME"] = $arRating["NAME"];
	}

	if (!$bFilter && $obCache->InitCache($arParams['CACHE_TIME'], $cache_id))
	{
		$vars = $obCache->GetVars();
		$arResult['SEARCH_RESULT'] = $vars['SEARCH_RESULT'];
		$arResult['NAV_STRING'] = $vars['NAV_STRING'];

		// recaclulating some user data
		foreach($arResult['SEARCH_RESULT'] as $i => $arUser)
		{
			$arUser["SHOW_PROFILE_LINK"] = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUser["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());
			$arUser["CAN_MESSAGE"] = (
				$GLOBALS["USER"]->IsAuthorized() 
				&& ($GLOBALS["USER"]->GetID() != $arUser["ID"]) 
				&& ($arUser["ACTIVE"] != "N")				
				&& (
					IsModuleInstalled("im") 
					|| CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUser["ID"], "message", CSocNetUser::IsCurrentUserModuleAdmin())
				)
			);
			$arUser["IS_ONLINE"] = ($arUser["IS_ONLINE"] == "Y");

			if ($GLOBALS["USER"]->IsAuthorized() && ($GLOBALS["USER"]->GetID() != $arUser["ID"]) && CSocNetUser::IsFriendsAllowed())
			{
				$rel = CSocNetUserRelations::GetRelation($GLOBALS["USER"]->GetID(), $arUser["ID"]);
				$arUser["CAN_ADD2FRIENDS"] = (!$rel && $arUser["ID"] != $GLOBALS["USER"]->GetID()) ? true : false;
			}
			else
				$arUser["CAN_ADD2FRIENDS"] = false;

			$arResult['SEARCH_RESULT'][$i] = $arUser;
		}

	}
	else
	{
		$arListParam = array(
			"NAV_PARAMS" => $arNavParams,
		);
		if ($arParams["ALLOW_RATING_SORT"] == 'Y')
			$by="RATING_".$arParams["RATING_ID"];

		if ($arParams["SHOW_RATING"] == 'Y')
			$arListParam["SELECT"][]="RATING_".$arParams["RATING_ID"];

		$dbUsers = CUser::GetList(
			$by,
			$order="desc",
			$arFilter,
			$arListParam
		);

		while ($arUser = $dbUsers->GetNext())
		{
			$arUser["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUser["ID"]));

			$arUser["SHOW_PROFILE_LINK"] = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUser["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());
			$arUser["CAN_MESSAGE"] = ($GLOBALS["USER"]->IsAuthorized() && ($GLOBALS["USER"]->GetID() != $arUser["ID"]) && (IsModuleInstalled("im") || CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUser["ID"], "message", CSocNetUser::IsCurrentUserModuleAdmin())));

			if (intval($arUser["PERSONAL_PHOTO"]) <= 0)
			{
				switch ($arUser["PERSONAL_GENDER"])
				{
					case "M":
						$suffix = "male";
						break;
					case "F":
						$suffix = "female";
							break;
					default:
						$suffix = "unknown";
				}
				$arUser["PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
			}
			$arImage = CSocNetTools::InitImage($arUser["PERSONAL_PHOTO"], $iSize, "/bitrix/images/socialnetwork/nopic_user_".$iSize.".gif", $iSize, $arUser["URL"], $arUser["SHOW_PROFILE_LINK"]);

			$arUser["IMAGE_FILE"] = $arImage["FILE"];
			$arUser["IMAGE_IMG"] = $arImage["IMG"];

			$arUser["NAME_FORMATED"] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arUser, $bUseLogin);

			$arUser["IS_ONLINE"] = ($arUser["IS_ONLINE"] == "Y");

			if ($GLOBALS["USER"]->IsAuthorized() && ($GLOBALS["USER"]->GetID() != $arUser["ID"]) && CSocNetUser::IsFriendsAllowed())
			{
				$rel = CSocNetUserRelations::GetRelation($GLOBALS["USER"]->GetID(), $arUser["ID"]);
				$arUser["CAN_ADD2FRIENDS"] = (!$rel && $arUser["ID"] != $GLOBALS["USER"]->GetID()) ? true : false;
			}
			else
			{
				$arUser["CAN_ADD2FRIENDS"] = false;
			}

			$arUser["ADD_TO_FRIENDS_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_FRIENDS_ADD"], array("user_id" => $arUser["ID"]));
			$arUser["MESSAGE_FORM_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGE_FORM"], array("user_id" => $arUser["ID"]));
			$arUser["MESSAGE_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGES_CHAT"], array("user_id" => $arUser["ID"]));

			$arUser["UserFieldsMain"] = array("SHOW" => "N", "DATA" => array());
			if (count($arParams["USER_FIELDS_LIST"]) > 0)
			{
				foreach ($arUser as $userFieldName => $userFieldValue)
				{
					if (in_array($userFieldName, $arParams["USER_FIELDS_LIST"]))
					{
						$val = $userFieldValue;
						switch ($userFieldName)
						{
							case 'EMAIL':
								if (StrLen($val) > 0)
									$val = '<a href="mailto:'.$val.'">'.$val.'</a>';
								break;

							case 'PERSONAL_WWW':
							case 'WORK_WWW':
								if (StrLen($val) > 0)
								{
									$valLink = $val;
									if (StrToLower(SubStr($val, 0, StrLen("http://"))) != "http://")
										$valLink = "http://".$val;
									$val = '<a href="'.$valLink.'" target="_blank">'.$val.'</a>';
								}
								break;

							case 'PERSONAL_COUNTRY':
							case 'WORK_COUNTRY':
								if (StrLen($val) > 0)
									$val = GetCountryByID($val);
								break;

							case 'PERSONAL_ICQ':
								if (StrLen($val) > 0)
									$val = $val.' <img src="http://web.icq.com/whitepages/online?icq='.$val.'&img=5" alt="" />';
								break;

							case 'PERSONAL_PHONE':
							case 'PERSONAL_FAX':
							case 'PERSONAL_MOBILE':
							case 'WORK_PHONE':
							case 'WORK_FAX':
								if (StrLen($val) > 0)
								{
									$valEncoded = preg_replace('/[^\d\+]+/', '', $val);
									$val = '<a href="callto:'.$valEncoded.'">'.$val.'</a>';
								}
								break;

							case 'PERSONAL_GENDER':
								$val = (($val == 'F') ? GetMessage("SONET_C241_FEMALE") : (($val == 'M') ? GetMessage("SONET_C241_MALE") : ""));
								break;

							case 'PERSONAL_BIRTHDAY':
								if (StrLen($val) > 0)
								{
									$arBirthdayTmp = CSocNetTools::Birthday($val, $arUser['PERSONAL_GENDER'], $arParams['SHOW_YEAR']);
									$val = $arBirthdayTmp["DATE"];
								}
								break;


							default:
								break;
						}

						$arUser["UserFieldsMain"]["DATA"][$userFieldName] = array("NAME" => GetMessage("SONET_C241_".$userFieldName), "VALUE" => $val);
					}
				}
				if (count($arUser["UserFieldsMain"]["DATA"]) > 0)
					$arUser["UserFieldsMain"]["SHOW"] = "Y";
			}

			// USER PROPERIES
			$arUser["UserPropertiesMain"] = array("SHOW" => "N", "DATA" => array());
			if (count($arParams["USER_PROPERTY_LIST"]) > 0)
			{
				$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", $arUser["ID"], LANGUAGE_ID);
				foreach ($arUserFields as $fieldName => $arUserField)
				{
					if (in_array($fieldName, $arParams["USER_PROPERTY_LIST"]))
					{
						$arUserField["EDIT_FORM_LABEL"] = StrLen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
						$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
						$arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
						$arUser["UserPropertiesMain"]["DATA"][$fieldName] = $arUserField;
					}
				}
				if (count($arUser["UserPropertiesMain"]["DATA"]) > 0)
					$arUser["UserPropertiesMain"]["SHOW"] = "Y";
			}

			$arResult["SEARCH_RESULT"][] = $arUser;
		}

		$arResult["NAV_STRING"] = $dbUsers->GetPageNavStringEx($navComponentObject, GetMessage("SONET_C241_NAV"), "", false);

		if (!$bFilter)
		{
			$obCache->StartDataCache();
			$obCache->EndDataCache(array(
				'SEARCH_RESULT' => $arResult['SEARCH_RESULT'],
				'NAV_STRING' => $arResult['NAV_STRING'],
			));
		}

	}
}

$this->IncludeComponentTemplate();
?>