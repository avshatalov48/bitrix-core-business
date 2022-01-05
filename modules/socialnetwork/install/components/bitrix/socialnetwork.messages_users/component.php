<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

use Bitrix\Socialnetwork\ComponentHelper;

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["USER_ID"] = intval($arParams["USER_ID"]);

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] === "N" ? "N" : "Y");

if ($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "user_id";
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if ($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGE_FORM"] = trim($arParams["PATH_TO_MESSAGE_FORM"]);
if ($arParams["PATH_TO_MESSAGE_FORM"] == '')
	$arParams["PATH_TO_MESSAGE_FORM"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=message_form&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGES_CHAT"] = trim($arParams["PATH_TO_MESSAGES_CHAT"]);
if ($arParams["PATH_TO_MESSAGES_CHAT"] == '')
	$arParams["PATH_TO_MESSAGES_CHAT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_chat&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGES_USERS"] = trim($arParams["PATH_TO_MESSAGES_USERS"]);
if ($arParams["PATH_TO_MESSAGES_USERS"] == '')
	$arParams["PATH_TO_MESSAGES_USERS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_users");

$arParams["PATH_TO_MESSAGES_USERS_MESSAGES"] = trim($arParams["PATH_TO_MESSAGES_USERS_MESSAGES"]);
if ($arParams["PATH_TO_MESSAGES_USERS_MESSAGES"] == '')
	$arParams["PATH_TO_MESSAGES_USERS_MESSAGES"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_users_messages&".$arParams["USER_VAR"]."=#user_id#");

$arParams["ITEMS_COUNT"] = intval($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 30;

$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);

$tooltipParams = ComponentHelper::checkTooltipComponentParams($arParams);
$arParams['SHOW_FIELDS_TOOLTIP'] = $tooltipParams['SHOW_FIELDS_TOOLTIP'];
$arParams['USER_PROPERTY_TOOLTIP'] = $tooltipParams['USER_PROPERTY_TOOLTIP'];

if (!$GLOBALS["USER"]->IsAuthorized())
{	
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	$arNavParams = array("nPageSize" => $arParams["ITEMS_COUNT"], "bDescPageNumbering" => false);
	$arNavigation = CDBResult::GetNavParams($arNavParams);

	/***********************  ACTIONS  *******************************/
	if ($_REQUEST["action"] === "ban" && check_bitrix_sessid() && intval($_REQUEST["userID"]) > 0)
	{
		$errorMessage = "";

		if (!CSocNetUserRelations::BanUser($GLOBALS["USER"]->GetID(), intval($_REQUEST["userID"])))
		{
			if ($e = $APPLICATION->GetException())
				$errorMessage .= $e->GetString();
		}

		if ($errorMessage <> '')
			$arResult["ErrorMessage"] = $errorMessage;
	}
	/*********************  END ACTIONS  *****************************/

	if ($arParams["SET_TITLE"] === "Y")
		$APPLICATION->SetTitle(GetMessage("SONET_C30_PAGE_TITLE"));

	if ($arParams["SET_NAV_CHAIN"] !== "N")
		$APPLICATION->AddChainItem(GetMessage("SONET_C30_PAGE_TITLE"));

	$arResult["Events"] = false;

	$dbMessages = CSocNetMessages::GetMessagesUsers($GLOBALS["USER"]->GetID(), $arNavParams);
	while ($arMessages = $dbMessages->GetNext())
	{
		if ($arResult["Events"] == false)
			$arResult["Events"] = array();

		$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arMessages["ID"]));
		$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arMessages["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());
		$canAnsver = (
			($arMessages["ACTIVE"] !== "N")
			&& (
				IsModuleInstalled("im") 
				|| CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arMessages["ID"], "message", CSocNetUser::IsCurrentUserModuleAdmin())
			)
		);

		$relation = CSocNetUserRelations::GetRelation($GLOBALS["USER"]->GetID(), $arMessages["ID"]);

		if (intval($arMessages["PERSONAL_PHOTO"]) <= 0)
		{
			switch ($arMessages["PERSONAL_GENDER"])
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
			$arMessages["PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
		}
		$arImage = CSocNetTools::InitImage($arMessages["PERSONAL_PHOTO"], 100, "/bitrix/images/socialnetwork/nopic_user_100.gif", 100, $pu, $canViewProfile);

		$arResult["Events"][] = array(
			"USER_ID" => $arMessages["ID"],
			"USER_NAME" => $arMessages["NAME"],
			"USER_LAST_NAME" => $arMessages["LAST_NAME"],
			"USER_SECOND_NAME" => $arMessages["SECOND_NAME"],
			"USER_LOGIN" => $arMessages["LOGIN"],
			"USER_PERSONAL_PHOTO" => $arMessages["PERSONAL_PHOTO"],
			"USER_PERSONAL_PHOTO_FILE" => $arImage["FILE"],
			"USER_PERSONAL_PHOTO_IMG" => $arImage["IMG"],
			"USER_PROFILE_URL" => $pu,
			"SHOW_PROFILE_LINK" => $canViewProfile,
			"SHOW_ANSWER_LINK" => $canAnsver,
			"ANSWER_LINK" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGE_FORM"], array("user_id" => $arMessages["ID"])),
			"CHAT_LINK" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGES_CHAT"], array("user_id" => $arMessages["ID"])),
			"BAN_LINK" => htmlspecialcharsbx($APPLICATION->GetCurUri("userID=".$arMessages["ID"]."&action=ban&".bitrix_sessid_get()."")),
			"SHOW_BAN_LINK" => (!CSocNetUser::IsUserModuleAdmin($arMessages["ID"]) && $arMessages["ID"] != $GLOBALS["USER"]->GetID() && (!$relation || $relation != SONET_RELATIONS_BAN)),
			"IS_ONLINE" => ($arMessages["IS_ONLINE"] === "Y"),
			"TOTAL" => $arMessages["TOTAL"],
			"MAX_DATE" => $arMessages["MAX_DATE"],
			"MAX_DATE_FORMAT" => $arMessages["MAX_DATE_FORMAT"],
			"UNREAD" => $arMessages["UNREAD"],
			"USER_MESSAGES_LINK" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGES_USERS_MESSAGES"], array("user_id" => $arMessages["ID"])),
		);
	}

	$arResult["NAV_STRING"] = $dbMessages->GetPageNavStringEx($navComponentObject, GetMessage("SONET_C30_NAV"), "", false);
	$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
	$arResult["NAV_RESULT"] = $dbMessages;
}

$this->IncludeComponentTemplate();
