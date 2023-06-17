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

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] === "N" ? "N" : "Y");
$bAutoSubscribe = (array_key_exists("USE_AUTOSUBSCRIBE", $arParams) && $arParams["USE_AUTOSUBSCRIBE"] === "N" ? false : true);

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

$arParams["ITEMS_COUNT"] = intval($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 30;

$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);

$arParams['NAME_TEMPLATE'] = $arParams['NAME_TEMPLATE'] ? $arParams['NAME_TEMPLATE'] : CSite::GetNameFormat();
$bUseLogin = $arParams['SHOW_LOGIN'] !== "N" ? true : false;

$tooltipParams = ComponentHelper::checkTooltipComponentParams($arParams);
$arParams['SHOW_FIELDS_TOOLTIP'] = $tooltipParams['SHOW_FIELDS_TOOLTIP'];
$arParams['USER_PROPERTY_TOOLTIP'] = $tooltipParams['USER_PROPERTY_TOOLTIP'];

if (!$GLOBALS["USER"]->IsAuthorized())
{
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	/***********************  ACTIONS  *******************************/
	if ($_REQUEST["EventType"] === "FriendRequest" && check_bitrix_sessid() && intval($_REQUEST["eventID"]) > 0)
	{
		$errorMessage = "";

		if (isset($_REQUEST["action"]) && $_REQUEST["action"] === "add")
		{
			if (!CSocNetUserRelations::ConfirmRequestToBeFriend($GLOBALS["USER"]->GetID(), intval($_REQUEST["eventID"]), $bAutoSubscribe))
			{
				if ($e = $APPLICATION->GetException())
					$errorMessage .= $e->GetString();
			}
		}
		elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] === "reject")
		{
			if (!CSocNetUserRelations::RejectRequestToBeFriend($GLOBALS["USER"]->GetID(), intval($_REQUEST["eventID"])))
			{
				if ($e = $APPLICATION->GetException())
					$errorMessage .= $e->GetString();
			}
		}

		if ($errorMessage <> '')
			$arResult["ErrorMessage"] = $errorMessage;

		if ($_REQUEST["action"] <> '' && $_REQUEST["backurl"] <> '' && $arResult["ErrorMessage"] == '')
			LocalRedirect($_REQUEST["backurl"]);
	}
	elseif ($_REQUEST["EventType"] === "GroupRequest" && check_bitrix_sessid() && intval($_REQUEST["eventID"]) > 0)
	{
		$errorMessage = "";

		if (isset($_REQUEST["action"]) && $_REQUEST["action"] === "add")
		{
			if (!CSocNetUserToGroup::UserConfirmRequestToBeMember($GLOBALS["USER"]->GetID(), intval($_REQUEST["eventID"]), $bAutoSubscribe))
			{
				if ($e = $APPLICATION->GetException())
					$errorMessage .= $e->GetString();
			}
		}
		elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] === "reject")
		{
			if (!CSocNetUserToGroup::UserRejectRequestToBeMember($GLOBALS["USER"]->GetID(), intval($_REQUEST["eventID"])))
			{
				if ($e = $APPLICATION->GetException())
					$errorMessage .= $e->GetString();
			}
		}

		if ($errorMessage <> '')
			$arResult["ErrorMessage"] = $errorMessage;

		if ($_REQUEST["action"] <> '' && $_REQUEST["backurl"] <> '' && $arResult["ErrorMessage"] == '')
			LocalRedirect($_REQUEST["backurl"]);
	}
	/*********************  END ACTIONS  *****************************/

	if ($arParams["SET_TITLE"] === "Y")
		$APPLICATION->SetTitle(GetMessage("SONET_C29_PAGE_TITLE"));

	if ($arParams["SET_NAV_CHAIN"] !== "N")
		$APPLICATION->AddChainItem(GetMessage("SONET_C29_PAGE_TITLE"));

	$parser = new CSocNetTextParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);
	$arResult["Events"] = false;

	$dbUserRequests = CSocNetUserRelations::GetList(
		array("DATE_UPDATE" => "ASC"),
		array(
			"SECOND_USER_ID" => $GLOBALS["USER"]->GetID(),
			"RELATION" => SONET_RELATIONS_REQUEST
		),
		false,
		false,
		array("ID", "FIRST_USER_ID", "MESSAGE", "FIRST_USER_NAME", "DATE_UPDATE", "FIRST_USER_LAST_NAME", "FIRST_USER_SECOND_NAME", "FIRST_USER_LOGIN", "FIRST_USER_PERSONAL_PHOTO", "FIRST_USER_PERSONAL_GENDER", "FIRST_USER_IS_ONLINE")
	);
	while ($arUserRequests = $dbUserRequests->GetNext())
	{
		if ($arResult["Events"] == false)
			$arResult["Events"] = array();
		$arEventTmp["EventType"] = "FriendRequest";

		$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserRequests["FIRST_USER_ID"]));
		$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUserRequests["FIRST_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

		if (intval($arUserRequests["FIRST_USER_PERSONAL_PHOTO"]) <= 0)
		{
			switch ($arUserRequests["FIRST_USER_PERSONAL_GENDER"])
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
			$arUserRequests["FIRST_USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
		}
		$arImage = CSocNetTools::InitImage($arUserRequests["FIRST_USER_PERSONAL_PHOTO"], 150, "/bitrix/images/socialnetwork/nopic_user_150.gif", 150, $pu, $canViewProfile);

		$arTmpUser = array(
			"NAME" => $arUserRequests["FIRST_USER_NAME"],
			"LAST_NAME" => $arUserRequests["FIRST_USER_LAST_NAME"],
			"SECOND_NAME" => $arUserRequests["FIRST_USER_SECOND_NAME"],
			"LOGIN" => $arUserRequests["FIRST_USER_LOGIN"],
		);
		$strNameFormatted = CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin);

		$arEventTmp["Event"] = array(
			"ID" => $arUserRequests["ID"],
			"USER_ID" => $arUserRequests["FIRST_USER_ID"],
			"USER_NAME" => $arUserRequests["FIRST_USER_NAME"],
			"USER_LAST_NAME" => $arUserRequests["FIRST_USER_LAST_NAME"],
			"USER_SECOND_NAME" => $arUserRequests["FIRST_USER_SECOND_NAME"],
			"USER_LOGIN" => $arUserRequests["FIRST_USER_LOGIN"],
			"USER_NAME_FORMATTED" => $strNameFormatted,
			"USER_PERSONAL_PHOTO" => $arUserRequests["FIRST_USER_PERSONAL_PHOTO"],
			"USER_PERSONAL_PHOTO_FILE" => $arImage["FILE"],
			"USER_PERSONAL_PHOTO_IMG" => $arImage["IMG"],
			"USER_PROFILE_URL" => $pu,
			"SHOW_PROFILE_LINK" => $canViewProfile,
			"IS_ONLINE" => ($arUserRequests["FIRST_USER_IS_ONLINE"] === "Y"),
			"DATE_UPDATE" => $arUserRequests["DATE_UPDATE"],
			"MESSAGE" => $parser->convert(
				$arUserRequests["~MESSAGE"],
				false,
				array(),
				array(
					"HTML" => "N",
					"ANCHOR" => "Y",
					"BIU" => "Y",
					"IMG" => "Y",
					"LIST" => "Y",
					"QUOTE" => "Y",
					"CODE" => "Y",
					"FONT" => "Y",
					"SMILES" => "Y",
					"UPLOAD" => "N",
					"NL2BR" => "N"
				)
			),
		);

		$arEventTmp["Urls"]["FriendAdd"] = htmlspecialcharsbx($APPLICATION->GetCurUri("EventType=FriendRequest&eventID=".$arUserRequests["ID"]."&action=add&".bitrix_sessid_get()."&backurl=".urlencode($GLOBALS["APPLICATION"]->GetCurPageParam("", array("EventType", "eventID", "action")))));
		$arEventTmp["Urls"]["FriendReject"] = htmlspecialcharsbx($APPLICATION->GetCurUri("EventType=FriendRequest&eventID=".$arUserRequests["ID"]."&action=reject&".bitrix_sessid_get()."&backurl=".urlencode($GLOBALS["APPLICATION"]->GetCurPageParam("", array("EventType", "eventID", "action")))));

		$arResult["Events"][] = $arEventTmp;
	}


	$dbUserRequests = CSocNetUserToGroup::GetList(
		array("DATE_CREATE" => "ASC"),
		array(
			"USER_ID" => $GLOBALS["USER"]->GetID(),
			"ROLE" => SONET_ROLES_REQUEST,
			"INITIATED_BY_TYPE" => SONET_INITIATED_BY_GROUP,
		),
		false,
		false,
		array("ID", "INITIATED_BY_USER_ID", "MESSAGE", "INITIATED_BY_USER_NAME", "DATE_CREATE", "INITIATED_BY_USER_LAST_NAME", "INITIATED_BY_USER_SECOND_NAME", "INITIATED_BY_USER_LOGIN", "INITIATED_BY_USER_PHOTO", "GROUP_ID", "GROUP_NAME", "GROUP_IMAGE_ID", "GROUP_VISIBLE")
	);
	while ($arUserRequests = $dbUserRequests->GetNext())
	{
		if ($arResult["Events"] == false)
			$arResult["Events"] = array();
		$arEventTmp["EventType"] = "GroupRequest";

		$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserRequests["INITIATED_BY_USER_ID"]));
		$canViewProfileU = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUserRequests["INITIATED_BY_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

		$arImage = CSocNetTools::InitImage($arUserRequests["INITIATED_BY_USER_PHOTO"], 150, "/bitrix/images/socialnetwork/nopic_user_150.gif", 150, $pu, $canViewProfileU);

		$pg = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arUserRequests["GROUP_ID"]));
		$canViewProfileG = (CSocNetUser::IsCurrentUserModuleAdmin() || ($arUserRequests["GROUP_VISIBLE"] === "Y"));

		if (intval($arUserRequests["GROUP_IMAGE_ID"]) <= 0)
			$arUserRequests["GROUP_IMAGE_ID"] = COption::GetOptionInt("socialnetwork", "default_group_picture", false, SITE_ID);

		$arImageG = CSocNetTools::InitImage($arUserRequests["GROUP_IMAGE_ID"], 150, "/bitrix/images/socialnetwork/nopic_group_150.gif", 150, $pg, $canViewProfileG);

		$arTmpUser = array(
			"NAME" => $arUserRequests["INITIATED_BY_USER_NAME"],
			"LAST_NAME" => $arUserRequests["INITIATED_BY_USER_LAST_NAME"],
			"SECOND_NAME" => $arUserRequests["INITIATED_BY_USER_SECOND_NAME"],
			"LOGIN" => $arUserRequests["INITIATED_BY_USER_LOGIN"],
		);
		$strNameFormatted = CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin);

		$arEventTmp["Event"] = array(
			"ID" => $arUserRequests["ID"],
			"USER_ID" => $arUserRequests["INITIATED_BY_USER_ID"],
			"USER_NAME" => $arUserRequests["INITIATED_BY_USER_NAME"],
			"USER_LAST_NAME" => $arUserRequests["INITIATED_BY_USER_LAST_NAME"],
			"USER_SECOND_NAME" => $arUserRequests["INITIATED_BY_USER_SECOND_NAME"],
			"USER_LOGIN" => $arUserRequests["INITIATED_BY_USER_LOGIN"],
			"USER_NAME_FORMATTED" => $strNameFormatted,
			"USER_PERSONAL_PHOTO" => $arUserRequests["INITIATED_BY_USER_PHOTO"],
			"USER_PERSONAL_PHOTO_FILE" => $arImage["FILE"],
			"USER_PERSONAL_PHOTO_IMG" => $arImage["IMG"],
			"USER_PROFILE_URL" => $pu,
			"SHOW_PROFILE_LINK" => $canViewProfileU,
			"DATE_CREATE" => $arUserRequests["DATE_CREATE"],
			"GROUP_NAME" => $arUserRequests["GROUP_NAME"],
			"GROUP_IMAGE_ID" => $arUserRequests["GROUP_IMAGE_ID"],
			"GROUP_IMAGE_ID_FILE" => $arImageG["FILE"],
			"GROUP_IMAGE_ID_IMG" => $arImageG["IMG"],
			"GROUP_PROFILE_URL" => $pg,
			"SHOW_GROUP_LINK" => $canViewProfileG,
			"MESSAGE" => $parser->convert(
				$arUserRequests["~MESSAGE"],
				false,
				array(),
				array(
					"HTML" => "N",
					"ANCHOR" => "Y",
					"BIU" => "Y",
					"IMG" => "Y",
					"LIST" => "Y",
					"QUOTE" => "Y",
					"CODE" => "Y",
					"FONT" => "Y",
					"SMILES" => "Y",
					"UPLOAD" => "N",
					"NL2BR" => "N"
				)
			),
		);

		$arEventTmp["Urls"]["FriendAdd"] = htmlspecialcharsbx($APPLICATION->GetCurUri("EventType=GroupRequest&eventID=".$arUserRequests["ID"]."&action=add&".bitrix_sessid_get()."&backurl=".urlencode($GLOBALS["APPLICATION"]->GetCurPageParam("", array("EventType", "eventID", "action")))));
		$arEventTmp["Urls"]["FriendReject"] = htmlspecialcharsbx($APPLICATION->GetCurUri("EventType=GroupRequest&eventID=".$arUserRequests["ID"]."&action=reject&".bitrix_sessid_get()."&backurl=".urlencode($GLOBALS["APPLICATION"]->GetCurPageParam("", array("EventType", "eventID", "action")))));

		$arResult["Events"][] = $arEventTmp;
	}

	$this->IncludeComponentTemplate();
}
