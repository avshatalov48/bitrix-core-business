<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Main\ModuleManager;

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @global CCacheManager $CACHE_MANAGER */
/** @global CUserTypeManager $USER_FIELD_MANAGER */

global $USER_FIELD_MANAGER, $CACHE_MANAGER;

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["ID"] = intval($arParams["ID"]);
if ($arParams["ID"] <= 0)
	$arParams["ID"] = intval($USER->GetID());

if (($arParams["USER_VAR"] ?? null) == '')
	$arParams["USER_VAR"] = "user_id";
if (($arParams["PAGE_VAR"] ?? null) == '')
	$arParams["PAGE_VAR"] = "page";
if (($arParams["GROUP_VAR"] ?? null) == '')
	$arParams["GROUP_VAR"] = "group_id";

$arParams["SHOW_YEAR"] = $arParams["SHOW_YEAR"] === "Y" ? "Y" : ($arParams["SHOW_YEAR"] === "M" ? "M" : "N");
// activation rating
CRatingsComponentsMain::GetShowRating($arParams);

$arParams["SET_NAV_CHAIN"] = (($arParams["SET_NAV_CHAIN"] ?? null) === "N" ? "N" : "Y");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_FRIENDS"] = trim($arParams["PATH_TO_USER_FRIENDS"] ?? '');
if($arParams["PATH_TO_USER_FRIENDS"] == '')
	$arParams["PATH_TO_USER_FRIENDS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_friends&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_FRIENDS_ADD"] = trim($arParams["PATH_TO_USER_FRIENDS_ADD"] ?? '');
if($arParams["PATH_TO_USER_FRIENDS_ADD"] == '')
	$arParams["PATH_TO_USER_FRIENDS_ADD"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_friends_add&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_FRIENDS_DELETE"] = trim($arParams["PATH_TO_USER_FRIENDS_DELETE"] ?? '');
if($arParams["PATH_TO_USER_FRIENDS_DELETE"] == '')
	$arParams["PATH_TO_USER_FRIENDS_DELETE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_friends_delete&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_SEARCH"] = trim($arParams["PATH_TO_SEARCH"] ?? '');
if ($arParams["PATH_TO_SEARCH"] == '')
	$arParams["PATH_TO_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=search");

$arParams["PATH_TO_LOG"] = trim($arParams["PATH_TO_LOG"] ?? '');
if ($arParams["PATH_TO_LOG"] == '')
	$arParams["PATH_TO_LOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=log");

$arParams["PATH_TO_ACTIVITY"] = trim($arParams["PATH_TO_ACTIVITY"] ?? '');
if ($arParams["PATH_TO_ACTIVITY"] == '')
	$arParams["PATH_TO_ACTIVITY"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=activity&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_SUBSCRIBE"] = trim($arParams["PATH_TO_SUBSCRIBE"] ?? '');
if ($arParams["PATH_TO_SUBSCRIBE"] == '')
	$arParams["PATH_TO_SUBSCRIBE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=subscribe");

$arParams["PATH_TO_SEARCH_INNER"] = trim($arParams["PATH_TO_SEARCH_INNER"] ?? '');
if ($arParams["PATH_TO_SEARCH_INNER"] == '')
	$arParams["PATH_TO_SEARCH_INNER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=search");

$arParams["PATH_TO_USER_GROUPS"] = trim($arParams["PATH_TO_USER_GROUPS"] ?? '');
if($arParams["PATH_TO_USER_GROUPS"] == '')
	$arParams["PATH_TO_USER_GROUPS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_groups&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"] ?? '');
if ($arParams["PATH_TO_GROUP"] == '')
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_EDIT"] = trim($arParams["PATH_TO_GROUP_EDIT"] ?? '');
if ($arParams["PATH_TO_GROUP_EDIT"] == '')
	$arParams["PATH_TO_GROUP_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_edit&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_CREATE"] = trim($arParams["PATH_TO_GROUP_CREATE"] ?? '');
if ($arParams["PATH_TO_GROUP_CREATE"] == '')
	$arParams["PATH_TO_GROUP_CREATE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_create&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_EDIT"] = trim($arParams["PATH_TO_USER_EDIT"] ?? '');
if($arParams["PATH_TO_USER_EDIT"] == '')
	$arParams["PATH_TO_USER_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_profile_edit&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGE_FORM"] = trim($arParams["PATH_TO_MESSAGE_FORM"] ?? '');
if ($arParams["PATH_TO_MESSAGE_FORM"] == '')
	$arParams["PATH_TO_MESSAGE_FORM"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=message_form&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGES_CHAT"] = trim($arParams["PATH_TO_MESSAGES_CHAT"] ?? '');
if ($arParams["PATH_TO_MESSAGES_CHAT"] == '')
	$arParams["PATH_TO_MESSAGES_CHAT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_chat&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_VIDEO_CALL"] = trim($arParams["PATH_TO_VIDEO_CALL"] ?? '');
if ($arParams["PATH_TO_VIDEO_CALL"] == '')
	$arParams["PATH_TO_VIDEO_CALL"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=video_call&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGES_USERS_MESSAGES"] = trim($arParams["PATH_TO_MESSAGES_USERS_MESSAGES"] ?? '');
if ($arParams["PATH_TO_MESSAGES_USERS_MESSAGES"] == '')
	$arParams["PATH_TO_MESSAGES_USERS_MESSAGES"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_users_messages&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_FEATURES"] = trim($arParams["PATH_TO_USER_FEATURES"] ?? '');
if ($arParams["PATH_TO_USER_FEATURES"] == '')
	$arParams["PATH_TO_USER_FEATURES"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_features&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_SUBSCRIBE"] = trim($arParams["PATH_TO_USER_SUBSCRIBE"] ?? '');
if ($arParams["PATH_TO_USER_SUBSCRIBE"] == '')
	$arParams["PATH_TO_USER_SUBSCRIBE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_subscribe&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_SETTINGS_EDIT"] = trim($arParams["PATH_TO_USER_SETTINGS_EDIT"] ?? '');
if ($arParams["PATH_TO_USER_SETTINGS_EDIT"] == '')
	$arParams["PATH_TO_USER_SETTINGS_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_settings_edit&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP_REQUEST_GROUP_SEARCH"] = trim($arParams["PATH_TO_GROUP_REQUEST_GROUP_SEARCH"] ?? '');
if ($arParams["PATH_TO_GROUP_REQUEST_GROUP_SEARCH"] == '')
	$arParams["PATH_TO_GROUP_REQUEST_GROUP_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_request_group_search&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_CONPANY_DEPARTMENT"] = trim($arParams["PATH_TO_CONPANY_DEPARTMENT"] ?? '');
if ($arParams["PATH_TO_CONPANY_DEPARTMENT"] == '')
	$arParams["PATH_TO_CONPANY_DEPARTMENT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=conpany_department&department=#ID#");

$arParams["PATH_TO_USER_SECURITY"] = trim($arParams["PATH_TO_USER_SECURITY"] ?? '');
if ($arParams["PATH_TO_USER_SECURITY"] == '')
	$arParams["PATH_TO_USER_SECURITY"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_security&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_PASSWORDS"] = trim($arParams["PATH_TO_USER_PASSWORDS"] ?? '');
if ($arParams["PATH_TO_USER_PASSWORDS"] == '')
	$arParams["PATH_TO_USER_PASSWORDS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_passwords&".$arParams["USER_VAR"]."=#user_id#");

if (Loader::includeModule('dav'))
{
	$arParams["PATH_TO_USER_SYNCHRONIZE"] = trim($arParams["PATH_TO_USER_SYNCHRONIZE"] ?? '');
	if ($arParams["PATH_TO_USER_SYNCHRONIZE"] == '')
		$arParams["PATH_TO_USER_SYNCHRONIZE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_synchronize&".$arParams["USER_VAR"]."=#user_id#");
}

$arParams["PATH_TO_USER_CODES"] = trim($arParams["PATH_TO_USER_CODES"] ?? '');
if ($arParams["PATH_TO_USER_CODES"] == '')
	$arParams["PATH_TO_USER_CODES"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_codes&".$arParams["USER_VAR"]."=#user_id#");

$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"] ?? null) ? CDatabase::DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
$arParams["SHORT_FORM"] = ($arParams["SHORT_FORM"] ?? null) === "Y";

if (!isset($arParams["USER_PROPERTY_MAIN"]) || !is_array($arParams["USER_PROPERTY_MAIN"]))
	$arParams["USER_PROPERTY_MAIN"] = array();
if (!isset($arParams["USER_PROPERTY_CONTACT"]) || !is_array($arParams["USER_PROPERTY_CONTACT"]))
	$arParams["USER_PROPERTY_CONTACT"] = array();
if (!isset($arParams["USER_PROPERTY_PERSONAL"]) || !is_array($arParams["USER_PROPERTY_PERSONAL"]))
	$arParams["USER_PROPERTY_PERSONAL"] = array();

if (!isset($arParams["USER_FIELDS_MAIN"]) || !is_array($arParams["USER_FIELDS_MAIN"]))
	$arParams["USER_FIELDS_MAIN"] = array();
if (!isset($arParams["USER_FIELDS_CONTACT"]) || !is_array($arParams["USER_FIELDS_CONTACT"]))
	$arParams["USER_FIELDS_CONTACT"] = array();
if (!isset($arParams["USER_FIELDS_PERSONAL"]) || !is_array($arParams["USER_FIELDS_PERSONAL"]))
	$arParams["USER_FIELDS_PERSONAL"] = array();

if (
	!isset($arParams["SONET_USER_FIELDS_SEARCHABLE"])
	|| !is_array($arParams["SONET_USER_FIELDS_SEARCHABLE"])
)
{
	$arParams["SONET_USER_FIELDS_SEARCHABLE"] = array();
}

if (
	!isset($arParams["SONET_USER_PROPERTY_SEARCHABLE"])
	|| !is_array($arParams["SONET_USER_PROPERTY_SEARCHABLE"])
)
{
	$arParams["SONET_USER_PROPERTY_SEARCHABLE"] = array();
}

if (!empty($arParams["SONET_USER_PROPERTY_SEARCHABLE"]))
{
	$curVal = serialize($arParams["SONET_USER_PROPERTY_SEARCHABLE"]);

	$tmpVal = COption::GetOptionString("socialnetwork", "user_property_searchable", false, SITE_ID);
	if (
		!$tmpVal
		|| $tmpVal != $curVal
	)
	{
		COption::SetOptionString("socialnetwork", "user_property_searchable", $curVal, false, SITE_ID);
	}
}


$arParams["PATH_TO_GROUP_SEARCH"] = trim($arParams["PATH_TO_GROUP_SEARCH"] ?? '');
if ($arParams["PATH_TO_GROUP_SEARCH"] == '')
	$arParams["PATH_TO_GROUP_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_search");

$arParams["ITEMS_COUNT"] = intval($arParams["ITEMS_COUNT"] ?? null);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 6;

$arParams["USE_MAIN_MENU"] = (isset($arParams["USE_MAIN_MENU"]) && $arParams["USE_MAIN_MENU"] === "Y" ? $arParams["USE_MAIN_MENU"] : false);

$tooltipParams = ComponentHelper::checkTooltipComponentParams($arParams);
$arParams['SHOW_FIELDS_TOOLTIP'] = $tooltipParams['SHOW_FIELDS_TOOLTIP'];
$arParams['USER_PROPERTY_TOOLTIP'] = $tooltipParams['USER_PROPERTY_TOOLTIP'];

if (IsModuleInstalled("intranet"))
	$arParams['CAN_OWNER_EDIT_DESKTOP'] = ($arParams['CAN_OWNER_EDIT_DESKTOP'] ?? null) !== "Y" ? "N" : "Y";
else
	$arParams['CAN_OWNER_EDIT_DESKTOP'] = ($arParams['CAN_OWNER_EDIT_DESKTOP'] ?? null) !== "N" ? "Y" : "N";

if ($arParams["ID"] <= 0)
{
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	$arListParams = array("SELECT" => array("UF_*"));

	if ($arParams["SHOW_RATING"] === 'Y' && array_key_exists("RATING_ID", $arParams))
	{
		if (is_array($arParams["RATING_ID"]) && count($arParams["RATING_ID"]) > 0)
		{
			$arParams["RATING_ID_ARR"] = $arParams["RATING_ID"];
			$arParams["RATING_ID"] = $arParams["RATING_ID_ARR"][0];

			foreach($arParams["RATING_ID_ARR"] as $rating_id)
			{
				if (intval($rating_id) > 0)
				{
					$db_rating = CRatings::GetByID($rating_id);
					if ($arRating = $db_rating->GetNext())
						$arResult["RatingMultiple"][$rating_id] = array("NAME" => $arRating["NAME"]);

					$arListParams["SELECT"][] = "RATING_".$rating_id;
				}
			}
			$arResult["Rating"]["NAME"] = $arResult["RatingMultiple"][$arParams["RATING_ID"]]["NAME"];
		}
		elseif (intval($arParams["RATING_ID"]) > 0)
		{
			$db_rating = CRatings::GetByID($arParams["RATING_ID"]);
			if ($arRating = $db_rating->GetNext())
				$arResult["Rating"]["NAME"] = $arRating["NAME"];

			$arListParams["SELECT"][] = "RATING_".$arParams["RATING_ID"];
		}

		$arFilter = array("ID_EQUAL_EXACT"=>$arParams["ID"]);
		if (!IsModuleInstalled("intranet"))
		{
			$arFilter["ACTIVE"] = "Y";
		}

		$dbUser = CUser::GetList("id", "asc", $arFilter, $arListParams);
		$arResult["User"] = $dbUser->GetNext();
	}
	else
	{
		$dbUser = CUser::GetByID($arParams["ID"]);
		$arResult["User"] = $dbUser->GetNext();
		if (
			!IsModuleInstalled("intranet")
			&& $arResult["User"]["ACTIVE"] !== "Y"
		)
		{
			$arResult["User"] = false;
		}
	}

	if (!is_array($arResult["User"]))
	{
		$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_USER").". ";
	}
	else
	{
		$arContext = ComponentHelper::getUrlContext();
		$arParams['PATH_TO_USER_EDIT'] = ComponentHelper::addContextToUrl($arParams['PATH_TO_USER_EDIT'], $arContext);

		if (!CSocNetUser::CanProfileView($USER->GetID(), $arResult["User"], SITE_ID, $arContext))
		{
			return false;
		}

		$arResult["CurrentUserPerms"] = CSocNetUserPerms::InitUserPerms(
			$USER->GetID(), 
			$arResult["User"]["ID"], 
			CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, !IsModuleInstalled("bitrix24"))
		);

		if (
			CModule::IncludeModule('extranet') 
			&& CExtranet::IsExtranetSite()
		)
		{
			$arResult["CurrentUserPerms"]["Operations"]["viewfriends"] = false;
		}

		if (IsModuleInstalled("im"))
		{
			$arResult["CurrentUserPerms"]["Operations"]["message"] = true;
		}

		$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Edit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_EDIT"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Friends"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_FRIENDS"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["FriendsAdd"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_FRIENDS_ADD"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["FriendsDelete"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_FRIENDS_DELETE"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Groups"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_GROUPS"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Search"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_SEARCH"], array());
		$arResult["Urls"]["GroupsAdd"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_CREATE"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["MessageForm"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGE_FORM"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Features"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_FEATURES"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["UserRequests"] = CComponentEngine::MakePathFromTemplate(
			$arParams["PATH_TO_USER_REQUESTS"] ?? null,
			["user_id" => $arResult["User"]["ID"]]
		);
		$arResult["Urls"]["Subscribe"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_SUBSCRIBE"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["SubscribeList"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_SUBSCRIBE"], array());
		$arResult["Urls"]["MessageChat"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGES_CHAT"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["UserMessages"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGES_USERS_MESSAGES"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Settings"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_SETTINGS_EDIT"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["RequestGroup"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_REQUEST_GROUP_SEARCH"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["GroupSearch"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_SEARCH"], array());

		$arResult["Urls"]["ExternalMail"] = CComponentEngine::MakePathFromTemplate(
			$arParams["PATH_TO_EXTMAIL"] ?? null,
			[]
		);
		$arResult["Urls"]["ExternalMail"] .= ((mb_strpos($arResult["Urls"]["ExternalMail"], "?") !== false) ? "&" : "?")."page=home";

		$arResult["Urls"]["Log"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LOG"], array());

		$arResult["Urls"]["LogGroups"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LOG"], array());
		$arResult["Urls"]["LogGroups"] .= ((mb_strpos($arResult["Urls"]["LogGroups"], "?") !== false) ? "&" : "?")."flt_entity_type=".SONET_ENTITY_GROUP;

		$arResult["Urls"]["LogUsers"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LOG"], array());
		$arResult["Urls"]["LogUsers"] .= ((mb_strpos($arResult["Urls"]["LogUsers"], "?") !== false) ? "&" : "?")."flt_entity_type=".SONET_ENTITY_USER;

		$arResult["Urls"]["Activity"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_ACTIVITY"], array());

		$arResult["Urls"]["VideoCall"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_VIDEO_CALL"], array("user_id" => $arResult["User"]["ID"]));

		$arResult["Urls"]["Security"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_SECURITY"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Passwords"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_PASSWORDS"], array("user_id" => $arResult["User"]["ID"]));

		if ($arParams["PATH_TO_USER_SYNCHRONIZE"])
		{
			$arResult["Urls"]["Synchronize"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_SYNCHRONIZE"], array("user_id" => $arResult["User"]["ID"]));
		}

		$arResult["Urls"]["Codes"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_CODES"], array("user_id" => $arResult["User"]["ID"]));

		$arResult["User"]["TYPE"] = '';

		if (
			$arResult["User"]["EXTERNAL_AUTH_ID"] === 'email'
			&& IsModuleInstalled('mail')
		)
		{
			$arResult["User"]["TYPE"] = 'email';
		}
		elseif (in_array($arResult["User"]["EXTERNAL_AUTH_ID"], ComponentHelper::checkPredefinedAuthIdList(array('bot', 'imconnector', 'replica'))))
		{
			$arResult["User"]["TYPE"] = $arResult["User"]["EXTERNAL_AUTH_ID"];
			$arResult["CurrentUserPerms"]["Operations"]["modifyuser_main"] = false;
			$arResult["CurrentUserPerms"]["Operations"]["modifyuser"] = false;
		}


		elseif (($arResult["User"]["IS_EXTRANET"] ?? null) === "Y")
		{
			$arResult["User"]["TYPE"] = 'extranet';
		}

		$arResult["ALLOW_CREATE_GROUP"] = (\Bitrix\Socialnetwork\Helper\Workgroup\Access::canCreate());

		$arResult["IS_ONLINE"] = ($arResult["User"]["IS_ONLINE"] === "Y");

		if (CModule::IncludeModule('intranet'))
		{
			$arResult['IS_HONOURED'] = CIntranetUtils::IsUserHonoured($arResult["User"]["ID"]);
			$arResult['IS_ABSENT'] = CIntranetUtils::IsUserAbsent(
				$arResult["User"]["ID"],
				$arParams['CALENDAR_USER_IBLOCK_ID'] ?? null
			);

			//departments and managers
			$obCache = new CPHPCache;
			$path = "/user_card_".intval($arResult["User"]["ID"] / TAGGED_user_card_size);

			if (
				($arParams["CACHE_TIME"] ?? null) == 0
				|| $obCache->StartDataCache(
					$arParams["CACHE_TIME"] ?? null,
					$arResult["User"]["ID"],
					$path
				)
			)
			{
				if (($arParams["CACHE_TIME"] ?? null) > 0 && defined("BX_COMP_MANAGED_CACHE"))
				{
					$CACHE_MANAGER->StartTagCache($path);
					$CACHE_MANAGER->RegisterTag("USER_CARD_".intval($arResult["User"]["ID"] / TAGGED_user_card_size));
				}

				//departments
				$arResult['DEPARTMENTS'] = array();
				$dbRes = CIntranetUtils::GetSubordinateDepartmentsList($arResult["User"]["ID"]);
				while ($arRes = $dbRes->GetNext())
				{
					$arRes['URL'] = str_replace('#ID#', $arRes['ID'], $arParams['PATH_TO_CONPANY_DEPARTMENT']);

					$arResult['DEPARTMENTS'][$arRes['ID']] = $arRes;
					$arResult['DEPARTMENTS'][$arRes['ID']]['EMPLOYEE_COUNT'] = 0;

					$rsUsers = CIntranetUtils::getDepartmentEmployees(array($arRes['ID']), true, false, 'Y', array('ID'));
					while($arUser = $rsUsers->Fetch())
					{
						if($arUser['ID'] <> $arResult["User"]["ID"]) //self
						{
							$arResult['DEPARTMENTS'][$arRes['ID']]['EMPLOYEE_COUNT'] ++;
						}
					}
				}

				//managers
				$arResult['MANAGERS'] = CIntranetUtils::GetDepartmentManager($arResult["User"]["UF_DEPARTMENT"], $arResult["User"]["ID"], true);

				if (($arParams["CACHE_TIME"] ?? null) > 0)
				{
					$obCache->EndDataCache(array(
						'DEPARTMENTS' => $arResult['DEPARTMENTS'],
						'MANAGERS' => $arResult['MANAGERS'],
					));
					if(defined("BX_COMP_MANAGED_CACHE"))
					{
						$CACHE_MANAGER->EndTagCache();
					}
				}
			}
			elseif($arParams["CACHE_TIME"] > 0)
			{
				$vars = $obCache->GetVars();
				$arResult['DEPARTMENTS'] = $vars['DEPARTMENTS'];
				$arResult['MANAGERS'] = $vars['MANAGERS'];
			}
			
			if (
				CModule::IncludeModule("extranet")
				&& CExtranet::IsExtranetSite()
				&& !CExtranet::IsIntranetUser()
			)
			{
				$arResult['MANAGERS'] = array();
			}
		}
		if (CModule::IncludeModule('mail'))
		{
			$arResult['User']['MAILBOXES'] = array();

			$dbMailbox = \Bitrix\Mail\MailboxTable::getList(array(
				'filter' => array(
					'=LID' => SITE_ID,
					'=ACTIVE' => 'Y',
					'=USER_ID' => $arParams['ID'],
					'=SERVER_TYPE' => 'imap',
				),
				'order' => array(
					'ID' => 'ASC',
				),
			));
			while ($mailbox = $dbMailbox->fetch())
			{
				// filter public
				\Bitrix\Mail\MailboxTable::normalizeEmail($mailbox);
				if (mb_strpos($mailbox['EMAIL'], '@') !== false)
				{
					$arResult['User']['MAILBOXES'][] = $mailbox['EMAIL'];
				}
			}

			$arResult['User']['MAILBOX'] = end($arResult['User']['MAILBOXES']);

			if (
				$arParams['ID'] == intval($USER->GetID())
				&& method_exists('Bitrix\Mail\User','getForwardTo')
			)
			{
				$arResult['User']['EMAIL_FORWARD_TO'] = array();
				if (ModuleManager::isModuleInstalled('blog'))
				{
					$res = Bitrix\Mail\User::getForwardTo(SITE_ID, $arParams['ID'], 'BLOG_POST');
					if (is_array($res))
					{
						list($emailForwardTo) = $res;
						if ($emailForwardTo)
						{
							$arResult['User']['EMAIL_FORWARD_TO']['BLOG_POST'] = $emailForwardTo;
						}
					}
				}

				if (ModuleManager::isModuleInstalled('tasks'))
				{
					$res = Bitrix\Mail\User::getForwardTo(SITE_ID, $arParams['ID'], 'TASKS_TASK');
					if (is_array($res))
					{
						list($emailForwardTo) = $res;
						if ($emailForwardTo)
						{
							$arResult['User']['EMAIL_FORWARD_TO']['TASKS_TASK'] = $emailForwardTo;
						}
					}
				}
			}
		}
		if ($arResult["User"]['PERSONAL_BIRTHDAY'] <> '')
		{
			$arBirthDate = ParseDateTime($arResult["User"]['PERSONAL_BIRTHDAY'], CSite::GetDateFormat('SHORT'));
			$arResult['IS_BIRTHDAY'] = (intval($arBirthDate['MM']) == date('n') && intval($arBirthDate['DD']) == date('j'));
		}

		if ($arParams["NAME_TEMPLATE"] == '')
		{
			$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
		}

		$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
			array("#NOBR#", "#/NOBR#"),
			array("", ""),
			$arParams["NAME_TEMPLATE"]
		);
		$bUseLogin = ($arParams['SHOW_LOGIN'] ?? null) !== "N" ? true : false;

		$arTmpUser = array(
				"NAME" => $arResult["User"]["~NAME"],
				"LAST_NAME" => $arResult["User"]["~LAST_NAME"],
				"SECOND_NAME" => $arResult["User"]["~SECOND_NAME"],
				"LOGIN" => $arResult["User"]["~LOGIN"],
		);

		$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arTmpUser, $bUseLogin);

		if (($arParams["SET_TITLE"] ?? null) === "Y")
		{
			$APPLICATION->SetTitle($strTitleFormatted);
		}

		if (!$arParams["SHORT_FORM"] && $arParams["SET_NAV_CHAIN"] !== "N")
			$APPLICATION->AddChainItem($strTitleFormatted);

		$arResult["User"]["NAME_FORMATTED"] = CUser::FormatName($arParams["NAME_TEMPLATE"], $arTmpUser, $bUseLogin);

		if (intval($arParams["AVATAR_SIZE"] ?? null) > 0)
		{
			$iSize = $arParams["AVATAR_SIZE"];
		}
		elseif ($arParams["SHORT_FORM"])
		{
			$iSize = 150;
		}
		else
		{
			$iSize = 300;
		}

		if (intval($arResult["User"]["PERSONAL_PHOTO"]) <= 0)
		{
			switch ($arResult["User"]["PERSONAL_GENDER"])
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
			$arResult["User"]["PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
		}

		$arImage = CSocNetTools::InitImage($arResult["User"]["PERSONAL_PHOTO"], $iSize, "/bitrix/images/socialnetwork/nopic_user_150.gif", 150, "", false);

		$arResult["User"]["PersonalPhotoFile"] = $arImage["FILE"];
		$arResult["User"]["PersonalPhotoImg"] = $arImage["IMG"];

		$bIntranet = (IsModuleInstalled('intranet') && (!CModule::IncludeModule("extranet") || !CExtranet::IsExtranetSite()));

		if ($arResult["CurrentUserPerms"]["Operations"]["viewprofile"])
		{
			$arResult["User"]["PERSONAL_LOCATION"] = GetCountryByID($arResult["User"]["PERSONAL_COUNTRY"]);
			if ($arResult["User"]["PERSONAL_LOCATION"] <> '' && $arResult["User"]["PERSONAL_CITY"] <> '')
				$arResult["User"]["PERSONAL_LOCATION"] .= ", ";
			$arResult["User"]["PERSONAL_LOCATION"] .= $arResult["User"]["PERSONAL_CITY"];

			$arResult["User"]["WORK_LOCATION"] = GetCountryByID($arResult["User"]["WORK_COUNTRY"]);
			if ($arResult["User"]["WORK_LOCATION"] <> '' && $arResult["User"]["WORK_CITY"] <> '')
				$arResult["User"]["WORK_LOCATION"] .= ", ";
			$arResult["User"]["WORK_LOCATION"] .= $arResult["User"]["WORK_CITY"];

			$arResult["Sex"] = array(
				"M" => GetMessage("SONET_P_USER_SEX_M"),
				"F" => GetMessage("SONET_P_USER_SEX_F"),
			);

			if ($arResult["User"]["PERSONAL_WWW"] <> '')
				$arResult["User"]["PERSONAL_WWW"] = ((mb_strpos($arResult["User"]["PERSONAL_WWW"], "http") === false) ? "http://" : "").$arResult["User"]["PERSONAL_WWW"];

			$arResult["UserFieldsMain"] = array("SHOW" => "N", "DATA" => array());
			$arResult["UserFieldsContact"] = array("SHOW" => "N", "DATA" => array());
			$arResult["UserFieldsPersonal"] = array("SHOW" => "N", "DATA" => array());

			$arMonths_r = array();
			for ($i = 1; $i <= 12; $i++)
				$arMonths_r[$i] = mb_strtolower(GetMessage('MONTH_'.$i.'_S'));

			if (count($arParams["USER_FIELDS_MAIN"]) > 0
				|| count($arParams["USER_FIELDS_CONTACT"]) > 0
				|| count($arParams["USER_FIELDS_PERSONAL"]) > 0)
			{
				$arUserFieldsMap = CSocNetUser::GetFieldsMap(true);
				$arUserTmp = $arResult["User"];
				$arResult["User"] = array();

				foreach ($arUserFieldsMap as $mapValue)
					if (array_key_exists($mapValue, $arUserTmp))
						$arResult["User"][$mapValue] = $arUserTmp[$mapValue];

				foreach ($arUserTmp as $key => $value)
					if (!array_key_exists($key, $arResult["User"]))
						$arResult["User"][$key] = $value;

				$userFields = CSocNetUser::GetFields();

				foreach ($arResult["User"] as $userFieldName => $userFieldValue)
				{
					if (in_array($userFieldName, $arParams["USER_FIELDS_MAIN"])
						|| in_array($userFieldName, $arParams["USER_FIELDS_CONTACT"])
						|| in_array($userFieldName, $arParams["USER_FIELDS_PERSONAL"]))
					{
						$val = $userFieldValue;
						$strSearch = "";
						switch ($userFieldName)
						{
							case 'EMAIL':
								$arEmails_tmp = array();
								if ($val <> '')
									$arEmails_tmp[] = '<a href="mailto:'.$val.'">'.$val.'</a>';
								if (!empty($arResult['User']['MAILBOXES']))
								{
									foreach ($arResult['User']['MAILBOXES'] as $item)
									{
										if (mb_strtolower($item) != mb_strtolower($val))
										{
											$arEmails_tmp[] = '<a href="mailto:'.$item.'">'.$item.'</a>';
										}
									}
								}
								$val = implode(', ', $arEmails_tmp);
								break;

							case 'PERSONAL_WWW':
							case 'WORK_WWW':
								if ($val <> '')
								{
									$valLink = $val;
									if(mb_strpos($val, "http") === false)
										$valLink = "http://".$val;
									$val = '<noindex><a href="'.$valLink.'" target="_blank" rel="nofollow">'.$val.'</a></noindex>';
								}
								break;

							case 'PERSONAL_COUNTRY':
							case 'WORK_COUNTRY':
								if ($val <> '')
								{
									if (in_array($userFieldName, $arParams["SONET_USER_FIELDS_SEARCHABLE"]))
										$strSearch = $arParams["PATH_TO_SEARCH_INNER"].(mb_strpos($arParams["PATH_TO_SEARCH_INNER"], "?") !== false? "&" : "?")."flt_".mb_strtolower($userFieldName)."=".UrlEncode($val);
									$val = GetCountryByID($val);
								}
								break;

							case 'PERSONAL_ICQ':
								if ($val <> '')
									$val = $val.'<!-- <img src="http://web.icq.com/whitepages/online?icq='.$val.'&img=5" alt="" />-->';
								break;

							case 'PERSONAL_PHONE':
							case 'PERSONAL_FAX':
							case 'PERSONAL_MOBILE':
							case 'WORK_PHONE':
							case 'WORK_FAX':
								if ($val <> '')
								{
									$valEncoded = preg_replace('/[^\d\+]+/', '', $val);
									$val = '<a href="callto:'.$valEncoded.'">'.$val.'</a>';
								}
								break;

							case 'PERSONAL_GENDER':
								if (in_array($userFieldName, $arParams["SONET_USER_FIELDS_SEARCHABLE"]))
									$strSearch = $arParams["PATH_TO_SEARCH_INNER"].(mb_strpos($arParams["PATH_TO_SEARCH_INNER"], "?") !== false? "&" : "?")."flt_".mb_strtolower($userFieldName)."=".UrlEncode($val);
								$val = (($val === 'F') ? GetMessage("SONET_P_USER_SEX_F") : (($val === 'M') ? GetMessage("SONET_P_USER_SEX_M") : ""));
								break;

							case 'PERSONAL_BIRTHDAY':
								if ($val <> '')
								{
									$arBirthdayTmp = CSocNetTools::Birthday($val, $arResult["User"]['PERSONAL_GENDER'], $arParams['SHOW_YEAR']);
									if (in_array($userFieldName, $arParams["SONET_USER_FIELDS_SEARCHABLE"]))
										$strSearch = $arParams["PATH_TO_SEARCH_INNER"].(mb_strpos($arParams["PATH_TO_SEARCH_INNER"], "?") !== false ? "&" : "?")."flt_personal_birthday_day=".UrlEncode($arBirthdayTmp["MONTH"]."-".$arBirthdayTmp["DAY"]);
									$val = $arBirthdayTmp["DATE"];
								}
								break;

							case 'WORK_LOGO':
								if (intval($val) > 0)
								{
									$iSize = 150;
									$arImage = CSocNetTools::InitImage($val, $iSize, "/bitrix/images/1.gif", 1, "", false);
									$val = $arImage["IMG"];
								}
								break;

							case 'TIME_ZONE':
								if($arResult["User"]["AUTO_TIME_ZONE"] !== "N")
									continue 2;
								break;

							case 'LAST_LOGIN':
								if ($val <> '')
								{
									$val = CUser::FormatLastActivityDate(MakeTimeStamp($val));
								}
								break;
							case 'LAST_ACTIVITY_DATE':
								if ($val <> '')
								{
									$val = CUser::FormatLastActivityDate(MakeTimeStamp($val, 'YYYY-MM-DD HH:MI:SS'));
								}
								break;

							case 'DATE_REGISTER':
								if ($val <> '')
								{
									$val = FormatDateFromDB($val, $arParams["DATE_TIME_FORMAT"], true);
								}
								break;

							default:
								if (in_array($userFieldName, $arParams["SONET_USER_FIELDS_SEARCHABLE"]))
									$strSearch = $arParams["PATH_TO_SEARCH_INNER"].(mb_strpos($arParams["PATH_TO_SEARCH_INNER"], "?") !== false? "&" : "?")."flt_".mb_strtolower($userFieldName)."=".UrlEncode($val);
								break;
						}

						if (in_array($userFieldName, $arParams["USER_FIELDS_MAIN"]))
							$arResult["UserFieldsMain"]["DATA"][$userFieldName] = array("NAME" => $userFields[$userFieldName], "VALUE" => $val, "SEARCH" => $strSearch);
						if (in_array($userFieldName, $arParams["USER_FIELDS_CONTACT"]))
							$arResult["UserFieldsContact"]["DATA"][$userFieldName] = array("NAME" => $userFields[$userFieldName], "VALUE" => $val, "SEARCH" => $strSearch);
						if (in_array($userFieldName, $arParams["USER_FIELDS_PERSONAL"]))
							$arResult["UserFieldsPersonal"]["DATA"][$userFieldName] = array("NAME" => $userFields[$userFieldName], "VALUE" => $val, "SEARCH" => $strSearch);
					}
				}
				if (count($arResult["UserFieldsMain"]["DATA"]) > 0)
					$arResult["UserFieldsMain"]["SHOW"] = "Y";
				if (count($arResult["UserFieldsContact"]["DATA"]) > 0)
					$arResult["UserFieldsContact"]["SHOW"] = "Y";
				if (count($arResult["UserFieldsPersonal"]["DATA"]) > 0)
					$arResult["UserFieldsPersonal"]["SHOW"] = "Y";
			}

			// USER PROPERIES
			$arResult["UserPropertiesMain"] = array("SHOW" => "N", "DATA" => array());
			$arResult["UserPropertiesContact"] = array("SHOW" => "N", "DATA" => array());
			$arResult["UserPropertiesPersonal"] = array("SHOW" => "N", "DATA" => array());
			if (count($arParams["USER_PROPERTY_MAIN"]) > 0
				|| count($arParams["USER_PROPERTY_CONTACT"]) > 0
				|| count($arParams["USER_PROPERTY_PERSONAL"]) > 0)
			{
				$arUserFields = $USER_FIELD_MANAGER->GetUserFields("USER", $arResult["User"]["ID"], LANGUAGE_ID);
				foreach ($arUserFields as $fieldName => $arUserField)
				{
					$arUserField["EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"] <> '' ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
					$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
					$arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];

					$arUserField["PROPERTY_VALUE_LINK"] = "";
					if (in_array($arUserField["FIELD_NAME"], $arParams["SONET_USER_PROPERTY_SEARCHABLE"]))
					{
						if ($arUserField["FIELD_NAME"] === "UF_DEPARTMENT" && IsModuleInstalled("intranet"))
						{
							$arUserField["PROPERTY_VALUE_LINK"] = $arParams["PATH_TO_SEARCH_INNER"].(mb_strpos($arParams["PATH_TO_SEARCH_INNER"], "?") !== false ? "&" : "?")."set_filter_structure=Y&structure_".$arUserField["FIELD_NAME"]."=#VALUE#";
						}
						elseif (IsModuleInstalled("intranet"))
						{
							$arUserField["PROPERTY_VALUE_LINK"] = $arParams["PATH_TO_SEARCH"].(mb_strpos($arParams["PATH_TO_SEARCH"], "?") !== false? "&" : "?")."flt_".mb_strtolower($arUserField["FIELD_NAME"])."=#VALUE#";
						}
						else
						{
							$arUserField["PROPERTY_VALUE_LINK"] = $arParams["PATH_TO_SEARCH_INNER"].(mb_strpos($arParams["PATH_TO_SEARCH_INNER"], "?") !== false? "&" : "?")."flt_".mb_strtolower($arUserField["FIELD_NAME"])."=#VALUE#";
						}
					}
					elseif ($bIntranet)
					{
						$arUserField['SETTINGS']['SECTION_URL'] = $arParams["PATH_TO_CONPANY_DEPARTMENT"];
					}

					if (in_array($fieldName, $arParams["USER_PROPERTY_MAIN"]))
					{
						$arResult["UserPropertiesMain"]["DATA"][$fieldName] = $arUserField;
					}

					if (in_array($fieldName, $arParams["USER_PROPERTY_CONTACT"]))
					{
						$arResult["UserPropertiesContact"]["DATA"][$fieldName] = $arUserField;
					}

					if (in_array($fieldName, $arParams["USER_PROPERTY_PERSONAL"]))
					{
						$arResult["UserPropertiesPersonal"]["DATA"][$fieldName] = $arUserField;
					}
				}
				if (count($arResult["UserPropertiesMain"]["DATA"]) > 0)
					$arResult["UserPropertiesMain"]["SHOW"] = "Y";
				if (count($arResult["UserPropertiesContact"]["DATA"]) > 0)
					$arResult["UserPropertiesContact"]["SHOW"] = "Y";
				if (count($arResult["UserPropertiesPersonal"]["DATA"]) > 0)
					$arResult["UserPropertiesPersonal"]["SHOW"] = "Y";
			}

			if (!$arParams["SHORT_FORM"])
			{
				// USER FRIENDS
				$arResult["Friends"] = false;
				if (CSocNetUser::IsFriendsAllowed() && $arResult["CurrentUserPerms"]["Operations"]["viewfriends"])
				{
					$dbFriends = CSocNetUserRelations::GetRelatedUsers($arResult["User"]["ID"], SONET_RELATIONS_FRIEND, array("nTopCount" => $arParams["ITEMS_COUNT"]));
					if ($dbFriends)
					{
						$arResult["Friends"] = array();
						$arResult["Friends"]["Count"] = CSocNetUserRelations::GetList(array(), array("USER_ID" => $arResult["User"]["ID"], "RELATION" => SONET_RELATIONS_FRIEND), array());

						$arResult["Friends"]["List"] = false;
						while ($arFriends = $dbFriends->GetNext())
						{
							if ($arResult["Friends"]["List"] == false)
								$arResult["Friends"]["List"] = array();

							$pref = ((intval($arResult["User"]["ID"]) == $arFriends["FIRST_USER_ID"]) ? "SECOND" : "FIRST");

							$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arFriends[$pref."_USER_ID"]));
							$canViewProfile = CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arFriends[$pref."_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

							if (intval($arParams["THUMBNAIL_LIST_SIZE"]) > 0)
							{
								if (intval($arFriends[$pref."_USER_PERSONAL_PHOTO"]) <= 0)
								{
									switch ($arFriends[$pref."_USER_PERSONAL_GENDER"])
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
									$arFriends[$pref."_USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
								}
								$arImage = CSocNetTools::InitImage($arFriends[$pref."_USER_PERSONAL_PHOTO"], $arParams["THUMBNAIL_LIST_SIZE"], "/bitrix/images/socialnetwork/nopic_30x30.gif", 30, $pu, $canViewProfile);
							}
							else // old
								$arImage = CSocNetTools::InitImage($arFriends[$pref."_USER_PERSONAL_PHOTO"], 50, "/bitrix/images/socialnetwork/nopic_user_50.gif", 50, $pu, $canViewProfile);


							$arResult["Friends"]["List"][] = array(
								"ID" => $arFriends["ID"],
								"USER_ID" => $arFriends[$pref."_USER_ID"],
								"USER_NAME" => $arFriends[$pref."_USER_NAME"],
								"USER_LAST_NAME" => $arFriends[$pref."_USER_LAST_NAME"],
								"USER_SECOND_NAME" => $arFriends[$pref."_USER_SECOND_NAME"],
								"USER_LOGIN" => $arFriends[$pref."_USER_LOGIN"],
								"USER_PERSONAL_PHOTO" => $arFriends[$pref."_USER_PERSONAL_PHOTO"],
								"USER_PERSONAL_PHOTO_FILE" => $arImage["FILE"],
								"USER_PERSONAL_PHOTO_IMG" => $arImage["IMG"],
								"USER_PROFILE_URL" => $pu,
								"SHOW_PROFILE_LINK" => $canViewProfile,
							);
						}
					}
				}


				// USER GROUPS
				$arResult["Groups"] = false;
				if ($arResult["CurrentUserPerms"]["Operations"]["viewgroups"])
				{
					$arGroupFilter = array(
						"USER_ID" => $arResult["User"]["ID"],
						"<=ROLE" => SONET_ROLES_USER,
						"GROUP_SITE_ID" => SITE_ID,
						"GROUP_ACTIVE" => "Y"
					);

					if (COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") !== "Y")
					{
						$arGroupFilter["GROUP_CLOSED"] = "N";
					}

					if (
						CModule::IncludeModule('extranet') 
						&& CExtranet::IsExtranetSite()
					)
					{
						if (
							!$USER->IsAdmin() 
							&& !CSocNetUser::IsCurrentUserModuleAdmin()
						)
						{
							$arGroupFilterMy = array(
								"USER_ID" => $USER->GetID(),
								"<=ROLE" => SONET_ROLES_USER,
								"GROUP_SITE_ID" => SITE_ID,
								"GROUP_ACTIVE" => "Y"
							);

							$dbGroups = CSocNetUserToGroup::GetList(
								array(),
								$arGroupFilterMy,
								false,
								false,
								array("GROUP_ID")
							);

							$arMyGroups = array();
							while ($arGroups = $dbGroups->GetNext())
							{
								$arMyGroups[] = $arGroups["GROUP_ID"];
							}

							$arGroupFilter["GROUP_ID"] = $arMyGroups;
						}
					}
					else
					{
						if (
							$arResult["User"]["ID"] != $USER->GetID() 
							&& !CSocNetUser::IsCurrentUserModuleAdmin()
						)
						{
							$arGroupsVisibleID = array();

							$arGroupFilterVisible = array(
								"CHECK_PERMISSIONS" => $USER->GetID(),
								"ACTIVE" => "Y",
								"SITE_ID" => SITE_ID
							);

							$dbGroups = CSocNetGroup::GetList(
								array("ID" => "ASC"), 
								$arGroupFilterVisible, 
								false, 
								false, 
								array("ID")
							);

							while($arGroup = $dbGroups->Fetch())
							{
								$arGroupsVisibleID[] = $arGroup["ID"];
							}
							
							if (!empty($arGroupsVisibleID))
							{
								$arGroupFilter["GROUP_ID"] = $arGroupsVisibleID;
							}
							else
							{
								$arGroupFilter["GROUP_VISIBLE"] = "Y";
							}
						}
					}

					$dbGroups = CSocNetUserToGroup::GetList(
						array("GROUP_DATE_ACTIVITY" => "DESC", "GROUP_NAME" => "ASC"),
						$arGroupFilter,
						false,
						false,
						array("ID", "GROUP_ID", "GROUP_NAME")
					);

					if ($dbGroups)
					{
						$arResult["Groups"] = array();
						$arResult["Groups"]["Count"] = 0;
						$arResult["Groups"]["List"] = false;
						$arResult["Groups"]["ListFull"] = false;
						while ($arGroups = $dbGroups->GetNext())
						{
							if ($arResult["Groups"]["ListFull"] == false)
								$arResult["Groups"]["ListFull"] = array();
							$arResult["Groups"]["Count"]++;
							$arResult["Groups"]["ListFull"][] = array(
								"ID" => $arGroups["ID"],
								"GROUP_ID" => $arGroups["GROUP_ID"],
								"GROUP_NAME" => $arGroups["GROUP_NAME"],
								"GROUP_URL" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arGroups["GROUP_ID"])),
							);
						}
						if (is_array($arResult["Groups"]["ListFull"]))
						{
							$arResult["Groups"]["List"] = array_slice($arResult["Groups"]["ListFull"], 0, $arParams["ITEMS_COUNT"]);
						}
					}

				}

				//Blog
				$arResult["ActiveFeatures"] = CSocNetFeatures::GetActiveFeaturesNames(SONET_ENTITY_USER, $arResult["User"]["ID"]);

				$arResult["BLOG"] = array("SHOW" => false, "TITLE" => GetMessage("SONET_C39_BLOG_TITLE"));
				if(array_key_exists("blog", $arResult["ActiveFeatures"]) && (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_USER, $arResult["User"]["ID"], "blog", "view_post", CSocNetUser::IsCurrentUserModuleAdmin()) || CMain::GetGroupRight("blog") >= "W") && CModule::IncludeModule("blog"))
				{
					$arResult["BLOG"]["SHOW"] = true;
					if ($arResult["ActiveFeatures"]["blog"] <> '')
						$arResult["BLOG"]["TITLE"] = $arResult["ActiveFeatures"]["blog"];
				}

				$arResult["forum"] = array("SHOW" => false, "TITLE" => GetMessage("SONET_C39_FORUM_TITLE"));
				if(array_key_exists("forum", $arResult["ActiveFeatures"]) && (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_USER, $arResult["User"]["ID"], "forum", "view", CSocNetUser::IsCurrentUserModuleAdmin())  || CMain::GetGroupRight("forum") >= "W") && CModule::IncludeModule("forum"))
				{
					$arResult["forum"]["SHOW"] = true;
					if ($arResult["ActiveFeatures"]["forum"] <> '')
						$arResult["forum"]["TITLE"] = $arResult["ActiveFeatures"]["forum"];
				}

				$arResult["tasks"] = array("SHOW" => false, "TITLE" => GetMessage("SONET_C39_TASKS_TITLE"));
				if(array_key_exists("tasks", $arResult["ActiveFeatures"]) && (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_USER, $arResult["User"]["ID"], "tasks", "view", CSocNetUser::IsCurrentUserModuleAdmin())  || CMain::GetGroupRight("intranet") >= "W") && CModule::IncludeModule("intranet"))
				{
					$arResult["tasks"]["SHOW"] = true;
					if ($arResult["ActiveFeatures"]["tasks"] <> '')
						$arResult["tasks"]["TITLE"] = $arResult["ActiveFeatures"]["tasks"];
				}
			}
		}

		if (
			array_key_exists("RatingMultiple", $arResult)
			&& count($arResult["RatingMultiple"]) > 0
		)
			foreach($arParams["RATING_ID_ARR"] as $rating_id)
				if (array_key_exists($rating_id, $arResult["RatingMultiple"]))
					$arResult["RatingMultiple"][$rating_id]["VALUE"] = $arResult["User"]["RATING_".$rating_id."_CURRENT_VALUE"];

		//otp
		if (CModule::IncludeModule("security") && Bitrix\Security\Mfa\Otp::isOtpEnabled())
		{
			$arResult["User"]["OTP"]["IS_ENABLED"] = "Y";
			$arResult["User"]["OTP"]["IS_MANDATORY"] = !CSecurityUser::IsUserSkipMandatoryRights($arResult["User"]["ID"]);
			$arResult["User"]["OTP"]["IS_ACTIVE"] = CSecurityUser::IsUserOtpActive($arResult["User"]["ID"]);
			$arResult["User"]["OTP"]["IS_EXIST"] = CSecurityUser::IsUserOtpExist($arResult["User"]["ID"]);
			$arResult["User"]["OTP"]["ARE_RECOVERY_CODES_ENABLED"] = Bitrix\Security\Mfa\Otp::isRecoveryCodesEnabled();

			$dateDeactivate = CSecurityUser::GetDeactivateUntil($arResult["User"]["ID"]);
			$arResult["User"]["OTP"]["NUM_LEFT_DAYS"] = ($dateDeactivate) ? FormatDate("ddiff", time()-60*60*24,  MakeTimeStamp($dateDeactivate) - 1) : "";
		}
		else
		{
			$arResult["User"]["OTP"]["IS_ENABLED"] = "N";
		}
	}
}

$this->IncludeComponentTemplate();

return array(
	"ID" => (isset($arResult["User"]) && isset($arResult["User"]["ID"]) ? intval($arResult["User"]["ID"]) : false),
	"NAME" => $arResult["User"]["NAME_FORMATTED"],
);
