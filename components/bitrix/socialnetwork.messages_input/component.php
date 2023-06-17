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
	$arParams["PATH_TO_MESSAGE_FORM_MESS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_chat&".$arParams["USER_VAR"]."=#user_id#&".$arParams["MESSAGE_VAR"]."=#message_id#");

$arParams["PATH_TO_MESSAGES_INPUT"] = trim($arParams["PATH_TO_MESSAGES_INPUT"]);
if ($arParams["PATH_TO_MESSAGES_INPUT"] == '')
	$arParams["PATH_TO_MESSAGES_INPUT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_input");

$arParams["PATH_TO_MESSAGES_INPUT_USER"] = trim($arParams["PATH_TO_MESSAGES_INPUT_USER"]);
if ($arParams["PATH_TO_MESSAGES_INPUT_USER"] == '')
	$arParams["PATH_TO_MESSAGES_INPUT_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_input_user&".$arParams["USER_VAR"]."=#user_id#");

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
	$arNavParams = array("nPageSize" => $arParams["ITEMS_COUNT"], "bDescPageNumbering" => true, "bShowAll" => false);
	$arNavigation = CDBResult::GetNavParams($arNavParams);

	/***********************  ACTIONS  *******************************/
	if ($_REQUEST["action"] === "close" && check_bitrix_sessid() && intval($_REQUEST["eventID"]) > 0)
	{
		$errorMessage = "";

		if (!CSocNetMessages::MarkMessageRead($GLOBALS["USER"]->GetID(), intval($_REQUEST["eventID"])))
		{
			if ($e = $APPLICATION->GetException())
				$errorMessage .= $e->GetString();
		}

		if ($errorMessage <> '')
			$arResult["ErrorMessage"] = $errorMessage;
	}
	if ($_REQUEST["action"] === "delete" && check_bitrix_sessid() && intval($_REQUEST["eventID"]) > 0)
	{
		$errorMessage = "";

		if (!CSocNetMessages::DeleteMessage(intval($_REQUEST["eventID"]), $GLOBALS["USER"]->GetID()))
		{
			if ($e = $APPLICATION->GetException())
				$errorMessage .= $e->GetString();
		}

		if ($errorMessage <> '')
			$arResult["ErrorMessage"] = $errorMessage;
	}
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
	if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["do_read"] <> '' || $_POST["do_delete"] <> '') && check_bitrix_sessid())
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
				$errorMessage .= GetMessage("SONET_C27_NO_SELECTED").". ";
		}

		if ($errorMessage == '')
		{
			if (!empty($_POST["do_read"]))
			{
				if (!CSocNetMessages::MarkMessageReadMultiple($GLOBALS["USER"]->GetID(), $arIDs))
				{
					if ($e = $APPLICATION->GetException())
						$errorMessage .= $e->GetString();
				}
			}
			elseif (!empty($_POST["do_delete"]))
			{
				if (!CSocNetMessages::DeleteMessageMultiple($GLOBALS["USER"]->GetID(), $arIDs))
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

	$arResult["Urls"]["ReadAll"] = htmlspecialcharsbx($APPLICATION->GetCurUri("action=close&".bitrix_sessid_get().""));

	if ($arParams["SET_TITLE"] === "Y")
		$APPLICATION->SetTitle(GetMessage("SONET_C27_PAGE_TITLE"));

	if ($arParams["SET_NAV_CHAIN"] !== "N")
		$APPLICATION->AddChainItem(GetMessage("SONET_C27_PAGE_TITLE"));

	$parser = new CSocNetTextParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);
	$arResult["Events"] = false;

	$arFilter = array(
		"TO_USER_ID" => $GLOBALS["USER"]->GetID(),
		"MESSAGE_TYPE" => SONET_MESSAGE_PRIVATE,
		"TO_DELETED" => "N",
	);
	if ($arParams["USER_ID"] > 0 && $arParams["USER_ID"] != $GLOBALS["USER"]->GetID())
		$arFilter["FROM_USER_ID"] = $arParams["USER_ID"];

	$dbMessages = CSocNetMessages::GetList(
		array("DATE_CREATE" => "DESC"),
		$arFilter,
		false,
		$arNavParams,
		array("ID", "FROM_USER_ID", "TITLE", "MESSAGE", "DATE_CREATE", "DATE_VIEW", "MESSAGE_TYPE", "FROM_USER_NAME", "FROM_USER_LAST_NAME", "FROM_USER_SECOND_NAME", "FROM_USER_LOGIN_NAME", "FROM_USER_PERSONAL_PHOTO", "FROM_USER_PERSONAL_GENDER")
	);
	while ($arMessages = $dbMessages->GetNext())
	{
		if ($arResult["Events"] == false)
			$arResult["Events"] = array();

		$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arMessages["FROM_USER_ID"]));
		$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arMessages["FROM_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());
		$canAnsver = (IsModuleInstalled("im") || CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arMessages["FROM_USER_ID"], "message", CSocNetUser::IsCurrentUserModuleAdmin()));

		$relation = CSocNetUserRelations::GetRelation($GLOBALS["USER"]->GetID(), $arMessages["FROM_USER_ID"]);

		if (intval($arMessages["FROM_USER_PERSONAL_PHOTO"]) <= 0)
		{
			switch ($arMessages["FROM_USER_PERSONAL_GENDER"])
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
			$arMessages["FROM_USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
		}
		$arImage = CSocNetTools::InitImage($arMessages["FROM_USER_PERSONAL_PHOTO"], 150, "/bitrix/images/socialnetwork/nopic_user_150.gif", 150, $pu, $canViewProfile);

		$arResult["Events"][] = array(
			"ID" => $arMessages["ID"],
			"USER_ID" => $arMessages["FROM_USER_ID"],
			"USER_NAME" => $arMessages["FROM_USER_NAME"],
			"USER_LAST_NAME" => $arMessages["FROM_USER_LAST_NAME"],
			"USER_SECOND_NAME" => $arMessages["FROM_USER_SECOND_NAME"],
			"USER_LOGIN" => $arMessages["FROM_USER_LOGIN"],
			"USER_PERSONAL_PHOTO" => $arMessages["FROM_USER_PERSONAL_PHOTO"],
			"USER_PERSONAL_PHOTO_FILE" => $arImage["FILE"],
			"USER_PERSONAL_PHOTO_IMG" => $arImage["IMG"],
			"USER_PROFILE_URL" => $pu,
			"SHOW_PROFILE_LINK" => $canViewProfile,
			"SHOW_ANSWER_LINK" => $canAnsver,
			"ANSWER_LINK" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGE_FORM_MESS"], array("user_id" => $arMessages["FROM_USER_ID"], "message_id" => $arMessages["ID"])),
			"READ_LINK" => htmlspecialcharsbx($APPLICATION->GetCurUri("eventID=".$arMessages["ID"]."&action=close&".bitrix_sessid_get()."")),
			"DELETE_LINK" => htmlspecialcharsbx($APPLICATION->GetCurUri("eventID=".$arMessages["ID"]."&action=delete&".bitrix_sessid_get()."")),
			"BAN_LINK" => htmlspecialcharsbx($APPLICATION->GetCurUri("userID=".$arMessages["FROM_USER_ID"]."&action=ban&".bitrix_sessid_get()."")),
			"SHOW_BAN_LINK" => ((!$relation || $relation != SONET_RELATIONS_BAN) && !CSocNetUser::IsUserModuleAdmin($arMessages["FROM_USER_ID"])),
			"ALL_USER_MESSAGES_LINK" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGES_INPUT_USER"], array("user_id" => $arMessages["FROM_USER_ID"])),
			"DATE_CREATE" => $arMessages["DATE_CREATE"],
			"IS_READ" => ($arMessages["DATE_VIEW"] <> ''),
			"TITLE" => $arMessages["TITLE"],
			"MESSAGE" => $parser->convert(
				$arMessages["~MESSAGE"],
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
	}

	$arResult["NAV_STRING"] = $dbMessages->GetPageNavStringEx($navComponentObject, GetMessage("SONET_C27_NAV"), "", false);
	$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
	$arResult["NAV_RESULT"] = $dbMessages;
}

$this->IncludeComponentTemplate();
