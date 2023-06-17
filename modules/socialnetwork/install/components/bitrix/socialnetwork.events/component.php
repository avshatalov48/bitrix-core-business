<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Socialnetwork\ComponentHelper;

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["USER_ID"] = intval($arParams["USER_ID"]);
if ($arParams["USER_ID"] <= 0)
	$arParams["USER_ID"] = intval($USER->GetID());

if ($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "user_id";
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if ($arParams["MESSAGE_VAR"] == '')
	$arParams["MESSAGE_VAR"] = "message_id";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if ($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGE_FORM"] = trim($arParams["PATH_TO_MESSAGE_FORM"]);
if ($arParams["PATH_TO_MESSAGE_FORM"] == '')
	$arParams["PATH_TO_MESSAGE_FORM"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=message_form&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGE_FORM_MESS"] = trim($arParams["PATH_TO_MESSAGE_FORM_MESS"]);
if ($arParams["PATH_TO_MESSAGE_FORM_MESS"] == '')
	$arParams["PATH_TO_MESSAGE_FORM_MESS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=message_form_mess&".$arParams["USER_VAR"]."=#user_id#&".$arParams["MESSAGE_VAR"]."=#message_id#");

$arParams["PATH_TO_MESSAGES_CHAT"] = trim($arParams["PATH_TO_MESSAGES_CHAT"]);
if ($arParams["PATH_TO_MESSAGES_CHAT"] == '')
	$arParams["PATH_TO_MESSAGES_CHAT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_chat&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);

$bAutoSubscribe = (array_key_exists("USE_AUTOSUBSCRIBE", $arParams) && $arParams["USE_AUTOSUBSCRIBE"] === "N" ? false : true);

$tooltipParams = ComponentHelper::checkTooltipComponentParams($arParams);
$arParams['SHOW_FIELDS_TOOLTIP'] = $tooltipParams['SHOW_FIELDS_TOOLTIP'];
$arParams['USER_PROPERTY_TOOLTIP'] = $tooltipParams['USER_PROPERTY_TOOLTIP'];

if ($GLOBALS["USER"]->IsAuthorized())
{
	/***********************  ACTIONS  *******************************/
	if (
		isset($_REQUEST["EventType"])
		&& $_REQUEST["EventType"] === "FriendRequest"
		&& check_bitrix_sessid()
		&& isset($_REQUEST["eventID"])
		&& intval($_REQUEST["eventID"]) > 0
	)
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
	}
	elseif (
		isset($_REQUEST["EventType"])
		&& $_REQUEST["EventType"] === "GroupRequest"
		&& check_bitrix_sessid()
		&& isset($_REQUEST["eventID"])
		&& intval($_REQUEST["eventID"]) > 0
	)
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
	}
	elseif (
		isset($_REQUEST["EventType"])
		&& $_REQUEST["EventType"] === "Message"
		&& check_bitrix_sessid()
		&& isset($_REQUEST["eventID"])
		&& intval($_REQUEST["eventID"]) > 0
	)
	{
		$errorMessage = "";

		if (isset($_REQUEST["action"]) && $_REQUEST["action"] === "close")
		{
			if (!CSocNetMessages::MarkMessageRead($GLOBALS["USER"]->GetID(), intval($_REQUEST["eventID"])))
			{
				if ($e = $APPLICATION->GetException())
					$errorMessage .= $e->GetString();
			}
		}

		if ($errorMessage <> '')
			$arResult["ErrorMessage"] = $errorMessage;
	}
	elseif (
		isset($_REQUEST["EventType"])
		&& $_REQUEST["EventType"] === "Message"
		&& check_bitrix_sessid()
		&& isset($_REQUEST["userID"])
		&& intval($_REQUEST["userID"]) > 0
	)
	{
		$errorMessage = "";

		if (isset($_REQUEST["action"]) && $_REQUEST["action"] === "ban")
		{
			if (!CSocNetUserRelations::BanUser($GLOBALS["USER"]->GetID(), intval($_REQUEST["userID"])))
			{
				if ($e = $APPLICATION->GetException())
					$errorMessage .= $e->GetString();
			}
		}

		if ($errorMessage <> '')
			$arResult["ErrorMessage"] = $errorMessage;
	}
	/*********************  END ACTIONS  *****************************/

	$parser = new CSocNetTextParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);
	$bFound = false;

	if (!$bFound)
	{
		$dbUserRequests = CSocNetUserRelations::GetList(
			array("DATE_UPDATE" => "ASC"),
			array(
				"SECOND_USER_ID" => $GLOBALS["USER"]->GetID(),
				"RELATION" => SONET_RELATIONS_REQUEST
			),
			false,
			array("nTopCount" => 1),
			array("ID", "FIRST_USER_ID", "MESSAGE", "FIRST_USER_NAME", "DATE_UPDATE", "FIRST_USER_LAST_NAME", "FIRST_USER_SECOND_NAME", "FIRST_USER_LOGIN", "FIRST_USER_PERSONAL_PHOTO", "FIRST_USER_PERSONAL_GENDER", "FIRST_USER_IS_ONLINE")
		);
		if ($arUserRequests = $dbUserRequests->GetNext())
		{
			if (!empty($arUserRequests['GROUP_NAME']))
			{
				$arUserRequests['GROUP_NAME'] = \Bitrix\Main\Text\Emoji::decode($arUserRequests['GROUP_NAME']);
			}

			$bFound = true;
			$arResult["EventType"] = "FriendRequest";

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

			$arResult["Event"] = array(
				"ID" => $arUserRequests["ID"],
				"USER_ID" => $arUserRequests["FIRST_USER_ID"],
				"USER_NAME" => $arUserRequests["FIRST_USER_NAME"],
				"USER_LAST_NAME" => $arUserRequests["FIRST_USER_LAST_NAME"],
				"USER_SECOND_NAME" => $arUserRequests["FIRST_USER_SECOND_NAME"],
				"USER_LOGIN" => $arUserRequests["FIRST_USER_LOGIN"],
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

			$arResult["Urls"]["FriendAdd"] = htmlspecialcharsbx($APPLICATION->GetCurUri("EventType=FriendRequest&eventID=".$arUserRequests["ID"]."&action=add&".bitrix_sessid_get().""));
			$arResult["Urls"]["FriendReject"] = htmlspecialcharsbx($APPLICATION->GetCurUri("EventType=FriendRequest&eventID=".$arUserRequests["ID"]."&action=reject&".bitrix_sessid_get().""));
		}
	}


	if (!$bFound)
	{
		$dbUserRequests = CSocNetUserToGroup::GetList(
			array("DATE_CREATE" => "ASC"),
			array(
				"USER_ID" => $GLOBALS["USER"]->GetID(),
				"ROLE" => SONET_ROLES_REQUEST,
				"INITIATED_BY_TYPE" => SONET_INITIATED_BY_GROUP,
			),
			false,
			array("nTopCount" => 1),
			array("ID", "INITIATED_BY_USER_ID", "MESSAGE", "INITIATED_BY_USER_NAME", "DATE_CREATE", "INITIATED_BY_USER_LAST_NAME", "INITIATED_BY_USER_SECOND_NAME", "INITIATED_BY_USER_LOGIN", "INITIATED_BY_USER_PHOTO", "INITIATED_BY_USER_GENDER", "GROUP_ID", "GROUP_NAME", "GROUP_IMAGE_ID", "GROUP_VISIBLE")
		);
		if ($arUserRequests = $dbUserRequests->GetNext())
		{
			$bFound = true;
			$arResult["EventType"] = "GroupRequest";

			$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserRequests["INITIATED_BY_USER_ID"]));
			$canViewProfileU = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUserRequests["INITIATED_BY_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

			if (intval($arUserRequests["INITIATED_BY_USER_PHOTO"]) <= 0)
			{
				switch ($arUserRequests["INITIATED_BY_USER_GENDER"])
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
				$arUserRequests["INITIATED_BY_USER_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
			}
			$arImage = CSocNetTools::InitImage($arUserRequests["INITIATED_BY_USER_PHOTO"], 150, "/bitrix/images/socialnetwork/nopic_user_150.gif", 150, $pu, $canViewProfileU);

			$pg = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arUserRequests["GROUP_ID"]));
			$canViewProfileG = (CSocNetUser::IsCurrentUserModuleAdmin() || ($arUserRequests["GROUP_VISIBLE"] === "Y"));

			if (intval($arUserRequests["GROUP_IMAGE_ID"]) <= 0)
				$arUserRequests["GROUP_IMAGE_ID"] = COption::GetOptionInt("socialnetwork", "default_group_picture", false, SITE_ID);

			$arImageG = CSocNetTools::InitImage($arUserRequests["GROUP_IMAGE_ID"], 150, "/bitrix/images/socialnetwork/nopic_group_150.gif", 150, $pg, $canViewProfileG);

			$arResult["Event"] = array(
				"ID" => $arUserRequests["ID"],
				"USER_ID" => $arUserRequests["INITIATED_BY_USER_ID"],
				"USER_NAME" => $arUserRequests["INITIATED_BY_USER_NAME"],
				"USER_LAST_NAME" => $arUserRequests["INITIATED_BY_USER_LAST_NAME"],
				"USER_SECOND_NAME" => $arUserRequests["INITIATED_BY_USER_SECOND_NAME"],
				"USER_LOGIN" => $arUserRequests["INITIATED_BY_USER_LOGIN"],
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

			$arResult["Urls"]["FriendAdd"] = htmlspecialcharsbx($APPLICATION->GetCurUri("EventType=GroupRequest&eventID=".$arUserRequests["ID"]."&action=add&".bitrix_sessid_get().""));
			$arResult["Urls"]["FriendReject"] = htmlspecialcharsbx($APPLICATION->GetCurUri("EventType=GroupRequest&eventID=".$arUserRequests["ID"]."&action=reject&".bitrix_sessid_get().""));
		}
	}


	if (!$bFound)
	{
		$dbUserRequests = CSocNetMessages::GetList(
			array("DATE_CREATE" => "ASC"),
			array(
				"TO_USER_ID" => $GLOBALS["USER"]->GetID(),
				"DATE_VIEW" => "",
				"TO_DELETED" => "N"
			),
			false,
			array("nTopCount" => 1),
			array("ID", "FROM_USER_ID", "TITLE", "MESSAGE", "DATE_CREATE", "MESSAGE_TYPE", "FROM_USER_NAME", "FROM_USER_LAST_NAME", "FROM_USER_SECOND_NAME", "FROM_USER_LOGIN", "FROM_USER_PERSONAL_PHOTO", "FROM_USER_PERSONAL_GENDER", "FROM_USER_IS_ONLINE")
		);
		if ($arUserRequests = $dbUserRequests->GetNext())
		{
			$bFound = true;
			$arResult["EventType"] = "Message";

			$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserRequests["FROM_USER_ID"]));
			$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUserRequests["FROM_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());
			$canAnsver =  (IsModuleInstalled("im") || CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUserRequests["FROM_USER_ID"], "message", CSocNetUser::IsCurrentUserModuleAdmin()));

			if (intval($arUserRequests["FROM_USER_PERSONAL_PHOTO"]) <= 0)
			{
				switch ($arUserRequests["FROM_USER_PERSONAL_GENDER"])
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
				$arUserRequests["FROM_USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
			}
			$arImage = CSocNetTools::InitImage($arUserRequests["FROM_USER_PERSONAL_PHOTO"], 150, "/bitrix/images/socialnetwork/nopic_user_150.gif", 150, $pu, $canViewProfile);

			$arResult["Event"] = array(
				"ID" => $arUserRequests["ID"],
				"USER_ID" => $arUserRequests["FROM_USER_ID"],
				"USER_NAME" => $arUserRequests["FROM_USER_NAME"],
				"USER_LAST_NAME" => $arUserRequests["FROM_USER_LAST_NAME"],
				"USER_SECOND_NAME" => $arUserRequests["FROM_USER_SECOND_NAME"],
				"USER_LOGIN" => $arUserRequests["FROM_USER_LOGIN"],
				"USER_PERSONAL_PHOTO" => $arUserRequests["FROM_USER_PERSONAL_PHOTO"],
				"USER_PERSONAL_PHOTO_FILE" => $arImage["FILE"],
				"USER_PERSONAL_PHOTO_IMG" => $arImage["IMG"],
				"USER_PROFILE_URL" => $pu,
				"SHOW_PROFILE_LINK" => $canViewProfile,
				"IS_ONLINE" => ($arUserRequests["FROM_USER_IS_ONLINE"] === "Y"),
				"DATE_CREATE" => $arUserRequests["DATE_CREATE"],
				"MESSAGE_TYPE" => $arUserRequests["MESSAGE_TYPE"],
				"TITLE" => $arUserRequests["TITLE"],
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

			$arResult["Urls"]["Reply"]["Show"] = ($arUserRequests["MESSAGE_TYPE"] == SONET_MESSAGE_PRIVATE && $canAnsver);
			$arResult["Urls"]["Reply"]["Link"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGE_FORM_MESS"], array("user_id" => $arUserRequests["FROM_USER_ID"], "message_id" => $arUserRequests["ID"]));

			$arResult["Urls"]["Chat"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGES_CHAT"], array("user_id" => $arUserRequests["FROM_USER_ID"]));

			$arResult["Urls"]["Close"] = htmlspecialcharsbx($APPLICATION->GetCurUri("EventType=Message&eventID=".$arUserRequests["ID"]."&action=close&".bitrix_sessid_get().""));
			$arResult["Urls"]["Ban"]["Show"] = ($arUserRequests["MESSAGE_TYPE"] == SONET_MESSAGE_PRIVATE && !CSocNetUser::IsUserModuleAdmin($arUserRequests["FROM_USER_ID"]));
			$arResult["Urls"]["Ban"]["Link"] = htmlspecialcharsbx($APPLICATION->GetCurUri("EventType=Message&userID=".$arUserRequests["FROM_USER_ID"]."&action=ban&".bitrix_sessid_get().""));
		}
	}

	$this->IncludeComponentTemplate();
}

