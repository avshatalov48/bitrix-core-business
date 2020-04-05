<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["GROUP_ID"] = IntVal($arParams["GROUP_ID"]);

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if (strLen($arParams["USER_VAR"]) <= 0)
	$arParams["USER_VAR"] = "user_id";
if (strLen($arParams["GROUP_VAR"]) <= 0)
	$arParams["GROUP_VAR"] = "group_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if (strlen($arParams["PATH_TO_USER"]) <= 0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if (strlen($arParams["PATH_TO_GROUP"]) <= 0)
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");
$arParams["PATH_TO_GROUP_MODS"] = trim($arParams["PATH_TO_GROUP_MODS"]);
if(strlen($arParams["PATH_TO_GROUP_MODS"])<=0)
	$arParams["PATH_TO_GROUP_MODS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_mods&".$arParams["GROUP_VAR"]."=#group_id#");
$arParams["PATH_TO_GROUP_USERS"] = trim($arParams["PATH_TO_GROUP_USERS"]);
if(strlen($arParams["PATH_TO_GROUP_USERS"])<=0)
	$arParams["PATH_TO_GROUP_USERS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_users&".$arParams["GROUP_VAR"]."=#group_id#");
if(strlen($arParams["PATH_TO_GROUP_REQUEST_SEARCH"])<=0)
	$arParams["PATH_TO_GROUP_REQUEST_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_request_search&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["ITEMS_COUNT"] = IntVal($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 30;

$arParams['NAME_TEMPLATE'] = $arParams['NAME_TEMPLATE'] ? $arParams['NAME_TEMPLATE'] : CSite::GetNameFormat();
$arParams["NAME_TEMPLATE_WO_NOBR"] = str_replace(
			array("#NOBR#", "#/NOBR#"), 
			array("", ""), 
			$arParams["NAME_TEMPLATE"]
		);
$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;
	
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

$arParams["GROUP_USE_BAN"] = $arParams["GROUP_USE_BAN"] != "N" ? "Y" : "N";

$arGroup = CSocNetGroup::GetByID($arParams["GROUP_ID"]);

if (
	!$arGroup 
	|| !is_array($arGroup) 
	|| $arGroup["ACTIVE"] != "Y" 
)
	$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_GROUP").". ";
else
{
	$arGroupSites = array();
	$rsGroupSite = CSocNetGroup::GetSite($arGroup["ID"]);
	while ($arGroupSite = $rsGroupSite->Fetch())
		$arGroupSites[] = $arGroupSite["LID"];

	if (!in_array(SITE_ID, $arGroupSites))
		$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_GROUP");
	else
	{
		$arResult["Group"] = $arGroup;

		$arResult["CurrentUserPerms"] = CSocNetUserToGroup::InitUserPerms($GLOBALS["USER"]->GetID(), $arResult["Group"], CSocNetUser::IsCurrentUserModuleAdmin());

		if ($arResult["CurrentUserPerms"]["UserCanModifyGroup"])
			$APPLICATION->AddHeadScript('/bitrix/js/main/admin_tools.js');	
		
		if (!$arResult["CurrentUserPerms"] || !$arResult["CurrentUserPerms"]["UserCanViewGroup"])
			$arResult["FatalError"] = GetMessage("SONET_C25_NO_PERMS").". ";
		else
		{
			$arNavParams = array("nPageSize" => $arParams["ITEMS_COUNT"], "bDescPageNumbering" => false, "bShowAll"=>false);
			$arNavigation = CDBResult::GetNavParams($arNavParams);

			$arResult["Urls"]["Group"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupMods"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_MODS"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupUsers"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_USERS"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupInvite"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_REQUEST_SEARCH"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_EDIT"], array("group_id" => $arResult["Group"]["ID"]));

			if ($arParams["SET_TITLE"] == "Y")
				$APPLICATION->SetTitle($arResult["Group"]["NAME"].": ".GetMessage("SONET_C25_PAGE_TITLE"));

			if ($arParams["SET_NAV_CHAIN"] != "N")
			{
				$APPLICATION->AddChainItem($arResult["Group"]["NAME"], $arResult["Urls"]["Group"]);
				$APPLICATION->AddChainItem(GetMessage("SONET_C25_PAGE_TITLE"));
			}

			if ($_SERVER["REQUEST_METHOD"]=="POST" 
				&& (
					($arResult["CurrentUserPerms"]["UserCanModifyGroup"] && strlen($_POST["save"]) > 0)
					||
					($arResult["CurrentUserPerms"]["UserCanModifyGroup"] && strlen($_POST["exclude"]) > 0)
					|| 
						(($arResult["CurrentUserPerms"]["UserCanModerateGroup"] || $arResult["CurrentUserPerms"]["UserCanModifyGroup"]) && strlen($_POST["ban"]) > 0)
				) && check_bitrix_sessid())
			{
				$errorMessage = "";

				$arIDs = array();
				if (strlen($errorMessage) <= 0)
				{
					for ($i = 0; $i <= IntVal($_POST["max_count"]); $i++)
					{
						if ($_POST["checked_".$i] == "Y")
							$arIDs[] = IntVal($_POST["id_".$i]);
					}

					if (count($arIDs) <= 0)
						$errorMessage .= GetMessage("SONET_C25_NOT_SELECTED").". ";
				}

				if (strlen($errorMessage) <= 0)
				{
					if (strlen($_POST["save"]) > 0 && $arResult["CurrentUserPerms"]["UserCanModifyGroup"])
					{
						if (
							!CSocNetUserToGroup::TransferMember2Moderator($GLOBALS["USER"]->GetID(), $arResult["Group"]["ID"], $arIDs, CSocNetUser::IsCurrentUserModuleAdmin())
							&& ($e = $APPLICATION->GetException())
						)
							$errorMessage .= $e->GetString();
					}
					elseif (strlen($_POST["ban"]) > 0 && ($arResult["CurrentUserPerms"]["UserCanModerateGroup"] || $arResult["CurrentUserPerms"]["UserCanModifyGroup"]))
					{
						if (
							!CSocNetUserToGroup::BanMember($GLOBALS["USER"]->GetID(), $arResult["Group"]["ID"], $arIDs, CSocNetUser::IsCurrentUserModuleAdmin())
							&& ($e = $APPLICATION->GetException())
						)
							$errorMessage .= $e->GetString();
					}
					elseif (strlen($_POST["exclude"]) > 0 && $arResult["CurrentUserPerms"]["UserCanModifyGroup"])
					{
						foreach($arIDs as $relation_id)
						{
							$arRelation = CSocNetUserToGroup::GetByID($relation_id);
							if (!$arRelation)
								continue;

							if (!CSocNetUserToGroup::Delete($arRelation["ID"], true))
							{
								if ($e = $APPLICATION->GetException())
									$errorMessage .= $e->GetString();
								if (strLen($errorMessage) <= 0)
									$errorMessage .= GetMessage("SONET_25_CANT_DELETE_INVITATION").$arRelation["ID"];
							}
						}
					}
				}

				if (strlen($errorMessage) > 0)
					$arResult["ErrorMessage"] = $errorMessage;
			}

			$arResult["Users"] = false;
			$dbRequests = CSocNetUserToGroup::GetList(
				array("USER_LAST_NAME" => "ASC", "USER_NAME" => "ASC"),
				array(
					"GROUP_ID" => $arResult["Group"]["ID"],
					"<=ROLE" => SONET_ROLES_USER,
					"USER_ACTIVE" => "Y"
				),
				false,
				$arNavParams,
				array("ID", "USER_ID", "ROLE", "DATE_CREATE", "DATE_UPDATE", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_PERSONAL_PHOTO", "USER_PERSONAL_GENDER", "USER_IS_ONLINE")
			);
			if ($dbRequests)
			{
				$arResult["Users"] = array();
				$arResult["Users"]["List"] = false;
				while ($arRequests = $dbRequests->GetNext())
				{
					if ($arResult["Users"]["List"] == false)
						$arResult["Users"]["List"] = array();

					$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arRequests["USER_ID"]));
					$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arRequests["USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

					if (intval($arParams["THUMBNAIL_LIST_SIZE"]) > 0)
					{
						if (intval($arRequests["USER_PERSONAL_PHOTO"]) <= 0)
						{
							switch ($arRequests["USER_PERSONAL_GENDER"])
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
							$arRequests["USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
						}					
						$arImage = CSocNetTools::InitImage($arRequests["USER_PERSONAL_PHOTO"], $arParams["THUMBNAIL_LIST_SIZE"], "/bitrix/images/socialnetwork/nopic_30x30.gif", 30, $pu, $canViewProfile);
					}
					else // old 
						$arImage = CSocNetTools::InitImage($arRequests["USER_PERSONAL_PHOTO"], 150, "/bitrix/images/socialnetwork/nopic_user_150.gif", 150, $pu, $canViewProfile);

					$arTmpUser = array(
						"NAME" => $arRequests["USER_NAME"],
						"LAST_NAME" => $arRequests["USER_LAST_NAME"],
						"SECOND_NAME" => $arRequests["USER_SECOND_NAME"],
						"LOGIN" => $arRequests["USER_LOGIN"],
					);
					$NameFormatted = CUser::FormatName($arParams['NAME_TEMPLATE_WO_NOBR'], $arTmpUser, $bUseLogin);
					
					$arResult["Users"]["List"][] = array(
						"ID" => $arRequests["ID"],
						"USER_ID" => $arRequests["USER_ID"],
						"USER_NAME" => $arRequests["USER_NAME"],
						"USER_LAST_NAME" => $arRequests["USER_LAST_NAME"],
						"USER_SECOND_NAME" => $arRequests["USER_SECOND_NAME"],
						"USER_LOGIN" => $arRequests["USER_LOGIN"],
						"USER_NAME_FORMATTED" => $NameFormatted,
						"USER_PERSONAL_PHOTO" => $arRequests["USER_PERSONAL_PHOTO"],
						"USER_PERSONAL_PHOTO_FILE" => $arImage["FILE"],
						"USER_PERSONAL_PHOTO_IMG" => $arImage["IMG"],
						"USER_PROFILE_URL" => $pu,
						"SHOW_PROFILE_LINK" => $canViewProfile,
						"IS_ONLINE" => ($arRequests["USER_IS_ONLINE"] == "Y"),
						"IS_MODERATOR" => ($arRequests["ROLE"] != SONET_ROLES_USER),
						"IS_OWNER" => ($arRequests["ROLE"] == SONET_ROLES_OWNER),
					);
				}
				$arResult["NAV_STRING"] = $dbRequests->GetPageNavStringEx($navComponentObject, GetMessage("SONET_C25_NAV"), "", false);
			}
			
			if ($arResult["CurrentUserPerms"]["UserCanModifyGroup"])
			{
				$js = '/bitrix/js/main/popup_menu.js';
				$GLOBALS['APPLICATION']->AddHeadString('<script type="text/javascript" src="'.$js.'?v='.filemtime($_SERVER['DOCUMENT_ROOT'].$js).'"></script>');
				$GLOBALS["APPLICATION"]->AddHeadString('<link rel="stylesheet" type="text/css" href="/bitrix/themes/.default/pubstyles.css" />');
			}
		}
	}
}

$this->IncludeComponentTemplate();
?>