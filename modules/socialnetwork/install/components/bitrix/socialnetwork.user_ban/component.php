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

if ($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "user_id";
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] === "N" ? "N" : "Y");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if ($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["ITEMS_COUNT"] = intval($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 30;

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
	if ($_REQUEST["action"] === "clear_ban" && check_bitrix_sessid() && intval($_REQUEST["eventID"]) > 0)
	{
		$errorMessage = "";

		if (!CSocNetUserRelations::UnBanMember($GLOBALS["USER"]->GetID(), intval($_REQUEST["eventID"])))
		{
			if ($e = $APPLICATION->GetException())
				$errorMessage .= $e->GetString();
		}

		if ($errorMessage <> '')
			$arResult["ErrorMessage"] = $errorMessage;
	}
	elseif ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["delete"] <> '' && check_bitrix_sessid())
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
				$errorMessage .= GetMessage("SONET_C32_NOT_SELECTED").". ";
		}

		if ($errorMessage == '')
		{
			foreach($arIDs as $ban_id)
			{
				if (!CSocNetUserRelations::UnBanMember($GLOBALS["USER"]->GetID(), $ban_id))
				{
					if ($e = $APPLICATION->GetException())
						$errorMessage .= $e->GetString();
				}
			}
		}

		if ($errorMessage <> '')
			$arResult["ErrorMessage"] = $errorMessage;
	}

	/*********************  END ACTIONS  *****************************/

	if ($arParams["SET_TITLE"] === "Y")
		$APPLICATION->SetTitle(GetMessage("SONET_C32_PAGE_TITLE"));

	if ($arParams["SET_NAV_CHAIN"] !== "N")
		$APPLICATION->AddChainItem(GetMessage("SONET_C32_PAGE_TITLE"));

	$arResult["Ban"] = false;
	$dbBan = CSocNetUserRelations::GetRelatedUsers($GLOBALS["USER"]->GetID(), SONET_RELATIONS_BAN, $arNavParams);
	if ($dbBan)
	{
		$arResult["Ban"] = array();
		$arResult["Ban"]["List"] = false;
		while ($arBan = $dbBan->GetNext())
		{
			if ($arResult["Ban"]["List"] == false)
				$arResult["Ban"]["List"] = array();

			$pref = (($GLOBALS["USER"]->GetID() == $arBan["FIRST_USER_ID"]) ? "SECOND" : "FIRST");

			$bInitiated = ((($GLOBALS["USER"]->GetID() == $arBan["FIRST_USER_ID"]) && ($arBan["INITIATED_BY"] === "F"))
				|| (($GLOBALS["USER"]->GetID() == $arBan["SECOND_USER_ID"]) && ($arBan["INITIATED_BY"] === "S")));

			$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arBan[$pref."_USER_ID"]));
			$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arBan[$pref."_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

			if (intval($arParams["THUMBNAIL_LIST_SIZE"]) > 0)
			{
				if (intval($arBan[$pref."_USER_PERSONAL_PHOTO"]) <= 0)
				{
					switch ($arBan[$pref."_USER_PERSONAL_GENDER"])
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
					$arBan[$pref."_USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
				}
				$arImage = CSocNetTools::InitImage($arBan[$pref."_USER_PERSONAL_PHOTO"], $arParams["THUMBNAIL_LIST_SIZE"], "/bitrix/images/socialnetwork/nopic_30x30.gif", 30, $pu, $canViewProfile);
			}
			else // old 
				$arImage = CSocNetTools::InitImage($arBan[$pref."_USER_PERSONAL_PHOTO"], 150, "/bitrix/images/socialnetwork/nopic_user_150.gif", 150, $pu, $canViewProfile);

			$arResult["Ban"]["List"][] = array(
				"ID" => $arBan["ID"],
				"USER_ID" => $arBan[$pref."_USER_ID"],
				"USER_NAME" => $arBan[$pref."_USER_NAME"],
				"USER_LAST_NAME" => $arBan[$pref."_USER_LAST_NAME"],
				"USER_SECOND_NAME" => $arBan[$pref."_USER_SECOND_NAME"],
				"USER_LOGIN_NAME" => $arBan[$pref."_USER_LOGIN_NAME"],
				"USER_PERSONAL_PHOTO" => $arBan[$pref."_USER_PERSONAL_PHOTO"],
				"USER_PERSONAL_PHOTO_FILE" => $arImage["FILE"],
				"USER_PERSONAL_PHOTO_IMG" => $arImage["IMG"],
				"USER_PROFILE_URL" => $pu,
				"SHOW_PROFILE_LINK" => $canViewProfile,
				"DELETE_FROM_BAN_LINK" => htmlspecialcharsbx($APPLICATION->GetCurUri("eventID=".$arBan["ID"]."&action=clear_ban&".bitrix_sessid_get()."")),
				"CAN_DELETE_BAN" => $bInitiated,
			);
		}
		$arResult["NAV_STRING"] = $dbBan->GetPageNavStringEx($navComponentObject, GetMessage("SONET_C32_NAV"), "", false);
	}
}

$this->IncludeComponentTemplate();
