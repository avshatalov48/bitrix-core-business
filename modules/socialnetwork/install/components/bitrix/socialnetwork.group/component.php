<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CCacheManager $CACHE_MANAGER */
/** @global CUserTypeManager $USER_FIELD_MANAGER */
global $CACHE_MANAGER, $USER_FIELD_MANAGER;

use Bitrix\Main\Loader;

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["GROUP_ID"] = IntVal($arParams["GROUP_ID"]);

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "user_id";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["GROUP_VAR"])<=0)
	$arParams["GROUP_VAR"] = "group_id";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if (strlen($arParams["PATH_TO_GROUP"]) <= 0)
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_EDIT"] = trim($arParams["PATH_TO_GROUP_EDIT"]);
if (strlen($arParams["PATH_TO_GROUP_EDIT"]) <= 0)
	$arParams["PATH_TO_GROUP_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_edit&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_INVITE"] = trim($arParams["PATH_TO_GROUP_INVITE"]);
if (empty($arParams["PATH_TO_GROUP_INVITE"]))
{
	$parent = $this->getParent();
	if (is_object($parent) && strlen($parent->__name) > 0)
	{
		$arParams["PATH_TO_GROUP_INVITE"] = $parent->arResult["PATH_TO_GROUP_INVITE"];
	}
}

$arParams["PATH_TO_GROUP_CREATE"] = trim($arParams["PATH_TO_GROUP_CREATE"]);
if (strlen($arParams["PATH_TO_GROUP_CREATE"]) <= 0)
	$arParams["PATH_TO_GROUP_CREATE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_create&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP_REQUEST_SEARCH"] = trim($arParams["PATH_TO_GROUP_REQUEST_SEARCH"]);
if (strlen($arParams["PATH_TO_GROUP_REQUEST_SEARCH"]) <= 0)
	$arParams["PATH_TO_GROUP_REQUEST_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_request_search&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_USER_REQUEST_GROUP"] = trim($arParams["PATH_TO_USER_REQUEST_GROUP"]);
if (strlen($arParams["PATH_TO_USER_REQUEST_GROUP"]) <= 0)
	$arParams["PATH_TO_USER_REQUEST_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_request_group&".$arParams["USER_VAR"]."=#user_id#&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_REQUESTS"] = trim($arParams["PATH_TO_GROUP_REQUESTS"]);
if (strlen($arParams["PATH_TO_GROUP_REQUESTS"]) <= 0)
	$arParams["PATH_TO_GROUP_REQUESTS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_requests&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_REQUESTS_OUT"] = trim($arParams["PATH_TO_GROUP_REQUESTS_OUT"]);
if (strlen($arParams["PATH_TO_GROUP_REQUESTS_OUT"]) <= 0)
	$arParams["PATH_TO_GROUP_REQUESTS_OUT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_requests_out&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_MODS"] = trim($arParams["PATH_TO_GROUP_MODS"]);
if (strlen($arParams["PATH_TO_GROUP_MODS"]) <= 0)
	$arParams["PATH_TO_GROUP_MODS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_mods&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_USERS"] = trim($arParams["PATH_TO_GROUP_USERS"]);
if (strlen($arParams["PATH_TO_GROUP_USERS"]) <= 0)
	$arParams["PATH_TO_GROUP_USERS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_users&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_USER_LEAVE_GROUP"] = trim($arParams["PATH_TO_USER_LEAVE_GROUP"]);
if (strlen($arParams["PATH_TO_USER_LEAVE_GROUP"]) <= 0)
	$arParams["PATH_TO_USER_LEAVE_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_leave_group&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_FEATURES"] = trim($arParams["PATH_TO_GROUP_FEATURES"]);
if (strlen($arParams["PATH_TO_GROUP_FEATURES"]) <= 0)
	$arParams["PATH_TO_GROUP_FEATURES"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_features&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_SUBSCRIBE"] = trim($arParams["PATH_TO_GROUP_SUBSCRIBE"]);
if (strlen($arParams["PATH_TO_GROUP_SUBSCRIBE"]) <= 0)
	$arParams["PATH_TO_GROUP_SUBSCRIBE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_subscribe&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_DELETE"] = trim($arParams["PATH_TO_GROUP_DELETE"]);
if (strlen($arParams["PATH_TO_GROUP_DELETE"]) <= 0)
	$arParams["PATH_TO_GROUP_DELETE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_delete&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_BAN"] = trim($arParams["PATH_TO_GROUP_BAN"]);
if (strlen($arParams["PATH_TO_GROUP_BAN"]) <= 0)
	$arParams["PATH_TO_GROUP_BAN"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_ban&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_MESSAGE_TO_GROUP"] = trim($arParams["PATH_TO_MESSAGE_TO_GROUP"]);
if (strlen($arParams["PATH_TO_MESSAGE_TO_GROUP"]) <= 0)
	$arParams["PATH_TO_MESSAGE_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=message_to_group&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_SEARCH"] = trim($arParams["PATH_TO_SEARCH"]);
if (strlen($arParams["PATH_TO_SEARCH"]) <= 0)
	$arParams["PATH_TO_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=search");

$arParams["PATH_TO_GROUP_LOG"] = trim($arParams["PATH_TO_GROUP_LOG"]);
if (strlen($arParams["PATH_TO_GROUP_LOG"]) <= 0)
	$arParams["PATH_TO_GROUP_LOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group-log&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_CONPANY_DEPARTMENT"] = trim($arParams["PATH_TO_CONPANY_DEPARTMENT"]);
if (strlen($arParams["PATH_TO_CONPANY_DEPARTMENT"]) <= 0)
{
	$arParams["PATH_TO_CONPANY_DEPARTMENT"] = \Bitrix\Main\Config\Option::get('main', 'TOOLTIP_PATH_TO_CONPANY_DEPARTMENT', SITE_DIR."company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#");
}

$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
$arParams["SHORT_FORM"] = ($arParams["SHORT_FORM"] == "Y");

$arParams["ITEMS_COUNT"] = IntVal($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 6;
	
if (!array_key_exists("PATH_TO_USER_LOG", $arParams) || strlen($arParams["PATH_TO_USER_LOG"]) <= 0)
	$arParams["PATH_TO_USER_LOG"] = $arParams["~PATH_TO_USER_LOG"] = (IsModuleInstalled("intranet") ? "/company/personal/log/" : "/club/log/");

if (!array_key_exists("PATH_TO_POST", $arParams) || strlen($arParams["PATH_TO_POST"]) <= 0)
	$arParams["PATH_TO_POST"] = $arParams["~PATH_TO_POST"] = (IsModuleInstalled("intranet") ? "/company/personal/user/#user_id#/blog/#post_id#/" : "/club/personal/user/#user_id#/blog/#post_id#/");

$arParams["USE_MAIN_MENU"] = (isset($arParams["USE_MAIN_MENU"]) && $arParams["USE_MAIN_MENU"] == "Y" ? $arParams["USE_MAIN_MENU"] : false);

if (!isset($arParams["GROUP_PROPERTY"]) || !is_array($arParams["GROUP_PROPERTY"]))
	$arParams["GROUP_PROPERTY"] = array();

$arParams["NAME_TEMPLATE"] = $arParams["NAME_TEMPLATE"] ? $arParams["NAME_TEMPLATE"] : CSite::GetNameFormat();
$arParams["SHOW_LOGIN"] = $arParams["SHOW_LOGIN"] != "N" ? "Y" : "N";

// for bitrix:main.user.link
if (IsModuleInstalled('intranet'))
{
	$arTooltipFieldsDefault	= serialize(array(
		"EMAIL",
		"PERSONAL_MOBILE",
		"WORK_PHONE",
		"PERSONAL_ICQ",
		"PERSONAL_PHOTO",
		"PERSONAL_CITY",
		"WORK_COMPANY",
		"WORK_POSITION",
	));
	$arTooltipPropertiesDefault = serialize(array(
		"UF_DEPARTMENT",
		"UF_PHONE_INNER",
	));
}
else
{
	$arTooltipFieldsDefault = serialize(array(
		"PERSONAL_ICQ",
		"PERSONAL_BIRTHDAY",
		"PERSONAL_PHOTO",
		"PERSONAL_CITY",
		"WORK_COMPANY",
		"WORK_POSITION"
	));
	$arTooltipPropertiesDefault = serialize(array());
}

if (!array_key_exists("SHOW_FIELDS_TOOLTIP", $arParams))
	$arParams["SHOW_FIELDS_TOOLTIP"] = unserialize(COption::GetOptionString("socialnetwork", "tooltip_fields", $arTooltipFieldsDefault));
if (!array_key_exists("USER_PROPERTY_TOOLTIP", $arParams))
	$arParams["USER_PROPERTY_TOOLTIP"] = unserialize(COption::GetOptionString("socialnetwork", "tooltip_properties", $arTooltipPropertiesDefault));

if (strlen(trim($arParams["SEARCH_TAGS_PAGE_ELEMENTS"])) <= 0)
	$arParams["SEARCH_TAGS_PAGE_ELEMENTS"] = 100;
if (intval(trim($arParams["SEARCH_TAGS_PERIOD"])) <= 0)
	$arParams["SEARCH_TAGS_PERIOD"] = "";
if (intval(trim($arParams["SEARCH_TAGS_FONT_MAX"])) <= 0)
	$arParams["SEARCH_TAGS_FONT_MAX"] = "50";	
if (intval(trim($arParams["SEARCH_TAGS_FONT_MIN"])) <= 0)
	$arParams["SEARCH_TAGS_FONT_MIN"] = "10";
if (strlen(trim($arParams["SEARCH_TAGS_COLOR_NEW"])) <= 0)
	$arParams["SEARCH_TAGS_COLOR_NEW"] = "3E74E6";
if (strlen(trim($arParams["SEARCH_TAGS_COLOR_OLD"])) <= 0)
	$arParams["SEARCH_TAGS_COLOR_OLD"] = "C0C0C0";

$arParams['CAN_OWNER_EDIT_DESKTOP'] = (
	IsModuleInstalled("intranet")
		? ($arParams['CAN_OWNER_EDIT_DESKTOP'] != "Y" ? "N" : "Y")
		: ($arParams['CAN_OWNER_EDIT_DESKTOP'] != "N" ? "Y" : "N")
);

$arParams["GROUP_USE_BAN"] = $arParams["GROUP_USE_BAN"] != "N" ? "Y" : "N";

$arGroup = CSocNetGroup::GetByID($arParams["GROUP_ID"]);
if (
	!$arGroup 
	|| !is_array($arGroup) 
	|| $arGroup["ACTIVE"] != "Y" 
)
{
	$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_GROUP");
}
else
{
	$arResult["bExtranet"] = (
		Loader::includeModule("extranet")
		&& CExtranet::IsExtranetSite()
	);

	$arGroupSites = array();
	$rsGroupSite = CSocNetGroup::GetSite($arGroup["ID"]);
	while ($arGroupSite = $rsGroupSite->Fetch())
		$arGroupSites[] = $arGroupSite["LID"];

	if (!in_array(SITE_ID, $arGroupSites))
		$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_GROUP");
	else
	{
		$arResult["Subjects"] = array();
		$dbSubjects = CSocNetGroupSubject::GetList(
			array("SORT" => "ASC", "NAME" => "ASC"),
			array("SITE_ID" => SITE_ID),
			false,
			false,
			array("ID", "NAME")
		);
		while ($arSubject = $dbSubjects->GetNext())
			$arResult["Subjects"][$arSubject["ID"]] = $arSubject["NAME"];

		if (CModule::IncludeModule("extranet"))
		{
			$arExtranetUserID = array();

			$arFilter = Array(
				"GROUPS_ID" => array(CExtranet::GetExtranetUserGroupID()),
				"UF_DEPARTMENT" => false				
			); 

			$rsUsers = CUser::GetList(($by="ID"), ($order="asc"), $arFilter);
			while($arUser = $rsUsers->Fetch())
				$arExtranetUserID[] = $arUser["ID"];
		}

		$arResult["Group"] = $arGroup;

		$arResult["HideArchiveLinks"] =
			$arResult["Group"]["CLOSED"] == "Y" &&
			COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") != "Y";

		$arResult["CurrentUserPerms"] = CSocNetUserToGroup::InitUserPerms($USER->GetID(), $arResult["Group"], CSocNetUser::IsCurrentUserModuleAdmin());
		if (in_array($arResult["CurrentUserPerms"]["UserRole"], array(SONET_ROLES_OWNER, SONET_ROLES_MODERATOR, SONET_ROLES_USER)))
			$arResult["bSubscribed"] = CSocNetSubscription::IsUserSubscribed($USER->GetID(), "SG".$arParams["GROUP_ID"]);
		else
			$arResult["bSubscribed"] = false;

		if (
			$arResult["Group"]["VISIBLE"] == "Y"  
			&& !$arResult["bExtranet"] 
			&& !$arResult["HideArchiveLinks"]
			&& (
				!$arResult["CurrentUserPerms"]["UserRole"] 
				|| ($arResult["CurrentUserPerms"]["UserRole"] == SONET_ROLES_REQUEST && $arResult["CurrentUserPerms"]["InitiatedByType"] == SONET_INITIATED_BY_GROUP)
			) 
		)
		{
			$arResult["bUserCanRequestGroup"] = true;
			$arResult["bDescriptionOpen"] = true;
		}
		elseif ($USER->IsAuthorized())
		{
			$arUserOptions = CUserOptions::GetOption("socialnetwork", "sonet_group_description", array(), $USER->GetID());
			if (isset($arUserOptions["state"]))
			{
				$arResult["bDescriptionOpen"] = ($arUserOptions["state"] == "Y");
			}
		}
		else
		{
			$arResult["bDescriptionOpen"] = true;
		}

		//display flag to show information when the group request is sent
		if ($arResult["CurrentUserPerms"]["UserRole"] == SONET_ROLES_REQUEST && $arResult["Group"]["VISIBLE"] == "Y" && !$arResult["HideArchiveLinks"])
			$arResult["bShowRequestSentMessage"] = ($arResult["CurrentUserPerms"]["InitiatedByType"] == SONET_INITIATED_BY_GROUP) ? "G" : "U";

		if (!$arResult["CurrentUserPerms"] || !$arResult["CurrentUserPerms"]["UserCanViewGroup"])
		{
			$arResult["FatalError"] = GetMessage("SONET_C5_NO_PERMS").".";
		}
		else
		{
			$arResult["Urls"]["Edit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_EDIT"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["Invite"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_INVITE"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["View"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["UserRequestGroup"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_REQUEST_GROUP"], array("group_id" => $arResult["Group"]["ID"], "user_id" => $USER->GetID()));
			$arResult["Urls"]["GroupRequestSearch"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_REQUEST_SEARCH"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupRequests"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_REQUESTS"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupRequestsOut"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_REQUESTS_OUT"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupMods"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_MODS"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupUsers"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_USERS"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["UserLeaveGroup"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_LEAVE_GROUP"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupDelete"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_DELETE"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["Features"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_FEATURES"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupBan"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BAN"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["UserSearch"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_SEARCH"], array());
			$arResult["Urls"]["Subscribe"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_SUBSCRIBE"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["MessageToGroup"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGE_TO_GROUP"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupLog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_LOG"], array("group_id" => $arResult["Group"]["ID"]));

			if ($arParams["SET_TITLE"]=="Y")
				$APPLICATION->SetTitle($arResult["Group"]["NAME"]);

			if (!$arParams["SHORT_FORM"] && $arParams["SET_NAV_CHAIN"] != "N")
				$APPLICATION->AddChainItem($arResult["Group"]["NAME"]);

			$iSize = 300;
			if ($arParams["SHORT_FORM"])
				$iSize = 100;

			if (intval($arResult["Group"]["IMAGE_ID"]) <= 0)
				$arResult["Group"]["IMAGE_ID"] = COption::GetOptionInt("socialnetwork", "default_group_picture", false, SITE_ID);

			$arImage = CSocNetTools::InitImage($arResult["Group"]["IMAGE_ID"], $iSize, "/bitrix/images/socialnetwork/nopic_group_100.gif", 100, "", false);
			$arResult["Group"]["IMAGE_ID_FILE"] = $arImage["FILE"];
			$arResult["Group"]["IMAGE_ID_IMG"] = $arImage["IMG"];

			$arResult["GroupProperties"] = array("SHOW" => "N", "DATA" => array());

			if (count($arParams["GROUP_PROPERTY"]) > 0)
			{
				$arUserFields = $USER_FIELD_MANAGER->GetUserFields("SONET_GROUP", $arResult["Group"]["ID"], LANGUAGE_ID);
				foreach ($arUserFields as $fieldName => $arUserField)
				{
					if (!in_array($fieldName, $arParams["GROUP_PROPERTY"]))
					{
						continue;
					}

					$arUserField["EDIT_FORM_LABEL"] = StrLen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
					$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
					$arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
					$arUserField["PROPERTY_VALUE_LINK"] = "";

					$arResult["GroupProperties"]["DATA"][$fieldName] = $arUserField;
				}
				if (count($arResult["GroupProperties"]["DATA"]) > 0)
				{
					$arResult["GroupProperties"]["SHOW"] = "Y";
				}
			}

			if (!$arParams["SHORT_FORM"])
			{
				// OWNER
				$arResult["Owner"] = false;
				$dbOwners = CSocNetUserToGroup::GetList(
					array("ROLE" => "ASC"),
					array("GROUP_ID" => $arResult["Group"]["ID"], "<=ROLE" => SONET_ROLES_OWNER, "USER_ACTIVE" => "Y"),
					false,
					array("nTopCount" => 1),
					array("ID", "USER_ID", "ROLE", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_PERSONAL_PHOTO", "USER_PERSONAL_GENDER", "USER_WORK_POSITION")
				);
				if ($dbOwners)
				{
					while ($arOwner = $dbOwners->GetNext())
					{
						$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arOwner["USER_ID"]));
						$canViewProfile = CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arOwner["USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

						if (intval($arParams["THUMBNAIL_LIST_SIZE"]) > 0)
						{
							if (intval($arOwner["USER_PERSONAL_PHOTO"]) <= 0)
							{
								switch ($arOwner["USER_PERSONAL_GENDER"])
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
								$arOwner["USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
							}	
							$arImage = CSocNetTools::InitImage($arOwner["USER_PERSONAL_PHOTO"], $arParams["THUMBNAIL_LIST_SIZE"], "/bitrix/images/socialnetwork/nopic_30x30.gif", 30, $pu, $canViewProfile);
						}
						else // old 
							$arImage = CSocNetTools::InitImage($arOwner["USER_PERSONAL_PHOTO"], 50, "/bitrix/images/socialnetwork/nopic_user_50.gif", 50, $pu, $canViewProfile);

						$arResult["Owner"] = array(
							"ID" => $arOwner["ID"],
							"USER_ID" => $arOwner["USER_ID"],
							"USER_NAME" => $arOwner["USER_NAME"],
							"USER_LAST_NAME" => $arOwner["USER_LAST_NAME"],
							"USER_SECOND_NAME" => $arOwner["USER_SECOND_NAME"],
							"USER_WORK_POSITION" => $arOwner["USER_WORK_POSITION"],
							"USER_LOGIN" => $arOwner["USER_LOGIN"],
							"USER_PERSONAL_PHOTO" => $arOwner["USER_PERSONAL_PHOTO"],
							"USER_PERSONAL_PHOTO_FILE" => $arImage["FILE"],
							"USER_PERSONAL_PHOTO_IMG" => $arImage["IMG"],
							"USER_PROFILE_URL" => $pu,
							"SHOW_PROFILE_LINK" => $canViewProfile,
							"USER_IS_EXTRANET" => ($arExtranetUserID && in_array($arOwner["USER_ID"], $arExtranetUserID) ? "Y" : "N")
						);
					}
				}

				// MODERATORS
				$arResult["Moderators"] = false;
				$dbModerators = CSocNetUserToGroup::GetList(
					array("ROLE" => "ASC"),
					array("GROUP_ID" => $arResult["Group"]["ID"], "<=ROLE" => SONET_ROLES_MODERATOR, "USER_ACTIVE" => "Y"),
					false,
					array("nTopCount" => $arParams["ITEMS_COUNT"]),
					array("ID", "USER_ID", "ROLE", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_PERSONAL_PHOTO", "USER_PERSONAL_GENDER", "USER_WORK_POSITION")
				);
				if ($dbModerators)
				{
					$arResult["Moderators"] = array();

					$arResult["Moderators"]["List"] = false;
					while ($arModerators = $dbModerators->GetNext())
					{
						if ($arResult["Moderators"]["List"] == false)
							$arResult["Moderators"]["List"] = array();

						$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arModerators["USER_ID"]));
						$canViewProfile = CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arModerators["USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

						if (intval($arParams["THUMBNAIL_LIST_SIZE"]) > 0)
						{
							if (intval($arModerators["USER_PERSONAL_PHOTO"]) <= 0)
							{
								switch ($arModerators["USER_PERSONAL_GENDER"])
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
								$arModerators["USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
							}	
							$arImage = CSocNetTools::InitImage($arModerators["USER_PERSONAL_PHOTO"], $arParams["THUMBNAIL_LIST_SIZE"], "/bitrix/images/socialnetwork/nopic_30x30.gif", 30, $pu, $canViewProfile);
						}
						else // old 
							$arImage = CSocNetTools::InitImage($arModerators["USER_PERSONAL_PHOTO"], 50, "/bitrix/images/socialnetwork/nopic_user_50.gif", 50, $pu, $canViewProfile);

						$arResult["Moderators"]["List"][] = array(
							"ID" => $arModerators["ID"],
							"USER_ID" => $arModerators["USER_ID"],
							"USER_NAME" => $arModerators["USER_NAME"],
							"USER_LAST_NAME" => $arModerators["USER_LAST_NAME"],
							"USER_SECOND_NAME" => $arModerators["USER_SECOND_NAME"],
							"USER_WORK_POSITION" => $arModerators["USER_WORK_POSITION"],
							"USER_LOGIN" => $arModerators["USER_LOGIN"],
							"USER_PERSONAL_PHOTO" => $arModerators["USER_PERSONAL_PHOTO"],
							"USER_PERSONAL_PHOTO_FILE" => $arImage["FILE"],
							"USER_PERSONAL_PHOTO_IMG" => $arImage["IMG"],
							"USER_PROFILE_URL" => $pu,
							"SHOW_PROFILE_LINK" => $canViewProfile,
							"USER_IS_EXTRANET" => ($arExtranetUserID && in_array($arModerators["USER_ID"], $arExtranetUserID) ? "Y" : "N")
						);
					}
				}

				// MEMBERS
				$arResult["Members"] = false;
				$dbMembers = CSocNetUserToGroup::GetList(
					array("RAND" => "ASC"),
					array("GROUP_ID" => $arResult["Group"]["ID"], "<=ROLE" => SONET_ROLES_USER, "USER_ACTIVE" => "Y"),
					false,
					array("nTopCount" => $arParams["ITEMS_COUNT"]),
					array("ID", "USER_ID", "ROLE", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_PERSONAL_PHOTO", "USER_PERSONAL_GENDER", "USER_WORK_POSITION")
				);
				if ($dbMembers)
				{
					$arResult["Members"] = array();

					$arResult["Members"]["List"] = false;
					while ($arMembers = $dbMembers->GetNext())
					{
						if ($arResult["Members"]["List"] == false)
							$arResult["Members"]["List"] = array();

						$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arMembers["USER_ID"]));
						$canViewProfile = CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arMembers["USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

						if (intval($arParams["THUMBNAIL_LIST_SIZE"]) > 0)
						{
							if (intval($arMembers["USER_PERSONAL_PHOTO"]) <= 0)
							{
								switch ($arMembers["USER_PERSONAL_GENDER"])
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
								$arMembers["USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
							}				
							$arImage = CSocNetTools::InitImage($arMembers["USER_PERSONAL_PHOTO"], $arParams["THUMBNAIL_LIST_SIZE"], "/bitrix/images/socialnetwork/nopic_30x30.gif", 30, $pu, $canViewProfile);
						}
						else // old 
							$arImage = CSocNetTools::InitImage($arMembers["USER_PERSONAL_PHOTO"], 50, "/bitrix/images/socialnetwork/nopic_user_50.gif", 50, $pu, $canViewProfile);					

						$arResult["Members"]["List"][] = array(
							"ID" => $arMembers["ID"],
							"USER_ID" => $arMembers["USER_ID"],
							"USER_NAME" => $arMembers["USER_NAME"],
							"USER_LAST_NAME" => $arMembers["USER_LAST_NAME"],
							"USER_SECOND_NAME" => $arMembers["USER_SECOND_NAME"],
							"USER_WORK_POSITION" => $arMembers["USER_WORK_POSITION"],
							"USER_LOGIN" => $arMembers["USER_LOGIN"],
							"USER_PERSONAL_PHOTO" => $arMembers["USER_PERSONAL_PHOTO"],
							"USER_PERSONAL_PHOTO_FILE" => $arImage["FILE"],
							"USER_PERSONAL_PHOTO_IMG" => $arImage["IMG"],
							"USER_PROFILE_URL" => $pu,
							"SHOW_PROFILE_LINK" => $canViewProfile,
							"USER_IS_EXTRANET" => ($arExtranetUserID && in_array($arMembers["USER_ID"], $arExtranetUserID) ? "Y" : "N")
						);
					}
				}

				// DEPARTMENTS
				if (
					!empty($arResult["Group"]["UF_SG_DEPT"])
					&& is_array($arResult["Group"]["UF_SG_DEPT"])
					&& \Bitrix\Main\Loader::includeModule('intranet')
				)
				{
					$arDepartments = CIntranetUtils::GetDepartmentsData($arResult["Group"]["UF_SG_DEPT"]);
					if (!empty($arDepartments))
					{
						$arResult["GroupDepartments"] = array();
						foreach($arDepartments as $departmentId => $departmentName)
						{
							$arResult["GroupDepartments"][] = array(
								"ID" => $departmentId,
								"NAME" => $departmentName,
								"URL" => str_replace('#ID#', $departmentId, $arParams["PATH_TO_CONPANY_DEPARTMENT"])
							);
						}
					}
				}

				$arResult["ActiveFeatures"] = CSocNetFeatures::getActiveFeaturesNames(SONET_ENTITY_GROUP, $arResult["Group"]["ID"]);

				//Blog
				$arResult["BLOG"] = array("SHOW" => false, "TITLE" => GetMessage("SONET_C6_BLOG_T"));
				if(array_key_exists("blog", $arResult["ActiveFeatures"]) && (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "blog", "view_post", CSocNetUser::IsCurrentUserModuleAdmin()) || $APPLICATION->GetGroupRight("forum") >= "W") && CModule::IncludeModule("blog"))
				{
					$arResult["BLOG"]["SHOW"] = true;
					if (StrLen($arResult["ActiveFeatures"]["blog"]) > 0)
					{
						$arResult["BLOG"]["TITLE"] = $arResult["ActiveFeatures"]["blog"];
					}
				}

				$arResult["forum"] = array("SHOW" => false, "TITLE" => GetMessage("SONET_C6_FORUM_T"));
				if(array_key_exists("forum", $arResult["ActiveFeatures"]) && (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "forum", "view", CSocNetUser::IsCurrentUserModuleAdmin()) || $APPLICATION->GetGroupRight("forum") >= "W") && CModule::IncludeModule("forum"))
				{
					$arResult["forum"]["SHOW"] = true;
					if (StrLen($arResult["ActiveFeatures"]["forum"]) > 0)
						$arResult["forum"]["TITLE"] = $arResult["ActiveFeatures"]["forum"];
				}

				$arResult["tasks"] = array("SHOW" => false, "TITLE" => GetMessage("SONET_C6_TASKS_T"));
				if(array_key_exists("tasks", $arResult["ActiveFeatures"]) && (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "tasks", "view", CSocNetUser::IsCurrentUserModuleAdmin()) || $APPLICATION->GetGroupRight("intranet") >= "W") && CModule::IncludeModule("intranet"))
				{
					$arResult["tasks"]["SHOW"] = true;
					if (StrLen($arResult["ActiveFeatures"]["tasks"]) > 0)
						$arResult["tasks"]["TITLE"] = $arResult["ActiveFeatures"]["tasks"];
				}
			}
		}
	}
}

$this->IncludeComponentTemplate();

return $arResult["Group"];
?>