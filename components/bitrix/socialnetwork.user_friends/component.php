<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */

use Bitrix\Socialnetwork\ComponentHelper;

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["ID"] = intval($arParams["ID"]);
if ($arParams["ID"] <= 0)
	$arParams["ID"] = intval($USER->GetID());

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] === "N" ? "N" : "Y");

if ($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "user_id";
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if ($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_FRIENDS_ADD"] = trim($arParams["PATH_TO_USER_FRIENDS_ADD"]);
if ($arParams["PATH_TO_USER_FRIENDS_ADD"] == '')
	$arParams["PATH_TO_USER_FRIENDS_ADD"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_friends_add&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_SEARCH"] = trim($arParams["PATH_TO_SEARCH"]);
if ($arParams["PATH_TO_SEARCH"] == '')
	$arParams["PATH_TO_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=search");

$arParams["PATH_TO_USER_FRIENDS_DELETE"] = trim($arParams["PATH_TO_USER_FRIENDS_DELETE"]);
if ($arParams["PATH_TO_USER_FRIENDS_DELETE"] == '')
	$arParams["PATH_TO_USER_FRIENDS_DELETE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_friends_delete&".$arParams["USER_VAR"]."=#user_id#");

$arParams["GROUP_ID"] = intval($arParams["GROUP_ID"]);
if ($arParams["GROUP_VAR"] == '')
	$arParams["GROUP_VAR"] = "group_id";

$arParams["PATH_TO_GROUP_REQUEST_USER"] = trim($arParams["PATH_TO_GROUP_REQUEST_USER"]);
if ($arParams["PATH_TO_GROUP_REQUEST_USER"] == '')
	$arParams["PATH_TO_GROUP_REQUEST_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_request_user&".$arParams["USER_VAR"]."=#user_id#&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_LOG"] = trim($arParams["PATH_TO_LOG"]);
if ($arParams["PATH_TO_LOG"] == '')
	$arParams["PATH_TO_LOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=log");

$arParams["ITEMS_COUNT"] = intval($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 30;

$tooltipParams = ComponentHelper::checkTooltipComponentParams($arParams);
$arParams['SHOW_FIELDS_TOOLTIP'] = $tooltipParams['SHOW_FIELDS_TOOLTIP'];
$arParams['USER_PROPERTY_TOOLTIP'] = $tooltipParams['USER_PROPERTY_TOOLTIP'];

if (!CSocNetUser::IsFriendsAllowed())
{
	$arResult["FatalError"] = GetMessage("SONET_C33_NO_FR_FUNC").". ";
}
else
{
	if ($arParams["ID"] > 0)
	{
		$dbUser = CUser::GetByID($arParams["ID"]);
		$arResult["User"] = $dbUser->GetNext();

		if (!is_array($arResult["User"]))
		{
			$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_USER").". ";
		}
		else
		{
			$arResult["CurrentUserPerms"] = CSocNetUserPerms::InitUserPerms($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], CSocNetUser::IsCurrentUserModuleAdmin());

			if ($arParams["SET_TITLE"] === "Y" || $arParams["SET_NAV_CHAIN"] !== "N")
			{
				if ($arParams["NAME_TEMPLATE"] == '')
					$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
				
				$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
					array("#NOBR#", "#/NOBR#"), 
					array("", ""), 
					$arParams["NAME_TEMPLATE"]
				);
				$bUseLogin = $arParams['SHOW_LOGIN'] !== "N" ? true : false;

				$arTmpUser = array(
						'NAME' => $arResult["User"]["~NAME"],
						'LAST_NAME' => $arResult["User"]["~LAST_NAME"],
						'SECOND_NAME' => $arResult["User"]["~SECOND_NAME"],
						'LOGIN' => $arResult["User"]["~LOGIN"],
					);
	
				$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arTmpUser, $bUseLogin);
			}
		
			if ($arParams["SET_TITLE"] === "Y")
				$APPLICATION->SetTitle($strTitleFormatted.": ".GetMessage("SONET_C33_PAGE_TITLE"));

			if ($arParams["SET_NAV_CHAIN"] !== "N")
			{
				$APPLICATION->AddChainItem($strTitleFormatted, CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["User"]["ID"])));
				$APPLICATION->AddChainItem(GetMessage("SONET_C33_PAGE_TITLE"));
			}

			if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["delete"] <> '' && $arResult["CurrentUserPerms"]["IsCurrentUser"] && check_bitrix_sessid())
			{
				$errorMessage = "";

				$arIDs = array();
				if ($errorMessage == '')
				{
					for ($i = 0; $i <= intval($_POST["max_count"]); $i++)
					{
						if ($_POST["checked_".$i] === "Y")
							$arIDs[] = intval($_POST["id_".$i]);
					}

					if (count($arIDs) <= 0)
						$errorMessage .= GetMessage("SONET_C33_NOT_SELECTED").". ";
				}

				if ($errorMessage == '')
				{
					foreach($arIDs as $user_id)
					{
						if (!CSocNetUserRelations::DeleteRelation($arResult["User"]["ID"], $user_id))
						{
							if ($e = $APPLICATION->GetException())
								$errorMessage .= $e->GetString();
						}
					}
				}

				if ($errorMessage <> '')
					$arResult["ErrorMessage"] = $errorMessage;
			}

			if ($arResult["CurrentUserPerms"] && $arResult["CurrentUserPerms"]["Operations"]["viewprofile"] && $arResult["CurrentUserPerms"]["Operations"]["viewfriends"])
			{
				$arNavParams = array("nPageSize" => $arParams["ITEMS_COUNT"], "bDescPageNumbering" => false);
				$arNavigation = CDBResult::GetNavParams($arNavParams);

				$arResult["Urls"]["Search"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_SEARCH"], array());

				$arResult["Urls"]["LogUsers"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LOG"], array());
				$arResult["Urls"]["LogUsers"] .= ((mb_strpos($arResult["Urls"]["LogUsers"], "?") !== false) ? "&" : "?")."flt_entity_type=".SONET_ENTITY_USER;
				$arResult["CanViewLog"] = ($arParams["GROUP_ID"] <= 0 && $arResult["User"]["ID"] == $GLOBALS["USER"]->GetID());

				$arResult["Friends"] = false;
				$dbFriends = CSocNetUserRelations::GetRelatedUsers($arResult["User"]["ID"], SONET_RELATIONS_FRIEND, $arNavParams);
				if ($dbFriends)
				{
					$arResult["Friends"] = array();
					$arResult["Friends"]["List"] = false;
					while ($arFriends = $dbFriends->GetNext())
					{
						if ($arResult["Friends"]["List"] == false)
							$arResult["Friends"]["List"] = array();

						$pref = ((intval($arResult["User"]["ID"]) == $arFriends["FIRST_USER_ID"]) ? "SECOND" : "FIRST");

						$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arFriends[$pref."_USER_ID"]));
						$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arFriends[$pref."_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

						if (!$arResult["CurrentUserPerms"]["IsCurrentUser"])
							$rel = CSocNetUserRelations::GetRelation($GLOBALS["USER"]->GetID(), $arFriends[$pref."_USER_ID"]);
						else
							$rel = SONET_RELATIONS_FRIEND;

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
							$arImage = CSocNetTools::InitImage($arFriends[$pref."_USER_PERSONAL_PHOTO"], 150, "/bitrix/images/socialnetwork/nopic_user_150.gif", 150, $pu, $canViewProfile);

						$gruLink = "";
						$bGruInvite = false;
						if ($arParams["GROUP_ID"] > 0)
						{
							if (CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arFriends[$pref."_USER_ID"], "invitegroup", CSocNetUser::IsCurrentUserModuleAdmin()))
							{
								$gruLink = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_REQUEST_USER"], array("user_id" => $arFriends[$pref."_USER_ID"], "group_id" => $arParams["GROUP_ID"]));
							}
						}

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
							"IS_ONLINE" => ($arFriends[$pref."_USER_IS_ONLINE"] === "Y"),
							"ADD_TO_FRIENDS_LINK" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_FRIENDS_ADD"], array("user_id" => $arFriends[$pref."_USER_ID"])),
							"DELETE_FROM_FRIENDS_LINK" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_FRIENDS_DELETE"], array("user_id" => $arFriends[$pref."_USER_ID"])),
							"CAN_ADD2FRIENDS" => (!$arResult["CurrentUserPerms"]["IsCurrentUser"] && !$rel && $arFriends[$pref."_USER_ID"] != $GLOBALS["USER"]->GetID()) ? true : false,
							"CAN_DELETE_FRIEND" => ($arResult["CurrentUserPerms"]["IsCurrentUser"] && $rel == SONET_RELATIONS_FRIEND) ? true : false,
							"REQUEST_GROUP_LINK" => $gruLink,

						);
					}
					$arResult["NAV_STRING"] = $dbFriends->GetPageNavStringEx($navComponentObject, GetMessage("SONET_C33_NAV"), "", false);
				}
			}
		}
	}
}

$this->IncludeComponentTemplate();
