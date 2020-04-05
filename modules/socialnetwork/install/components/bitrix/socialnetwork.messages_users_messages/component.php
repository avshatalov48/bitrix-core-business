<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (IsModuleInstalled("im"))
	LocalRedirect('/?IM_HISTORY='.IntVal($arParams["USER_ID"]), false, "301 Moved permanently");

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["USER_ID"] = IntVal($arParams["USER_ID"]);

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if (strLen($arParams["USER_VAR"]) <= 0)
	$arParams["USER_VAR"] = "user_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";
if (strLen($arParams["MESSAGE_VAR"]) <= 0)
	$arParams["MESSAGE_VAR"] = "message_id";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if (strlen($arParams["PATH_TO_USER"]) <= 0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGE_FORM"] = trim($arParams["PATH_TO_MESSAGE_FORM"]);
if (strlen($arParams["PATH_TO_MESSAGE_FORM"]) <= 0)
	$arParams["PATH_TO_MESSAGE_FORM"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=message_form&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGE_FORM_MESS"] = trim($arParams["PATH_TO_MESSAGE_FORM_MESS"]);
if (strlen($arParams["PATH_TO_MESSAGE_FORM_MESS"]) <= 0)
	$arParams["PATH_TO_MESSAGE_FORM_MESS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=message_form_mess&".$arParams["USER_VAR"]."=#user_id#&".$arParams["MESSAGE_VAR"]."=#message_id#");

$arParams["PATH_TO_MESSAGES_CHAT"] = trim($arParams["PATH_TO_MESSAGES_CHAT"]);
if (strlen($arParams["PATH_TO_MESSAGES_CHAT"]) <= 0)
	$arParams["PATH_TO_MESSAGES_CHAT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_chat&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGES_USERS"] = trim($arParams["PATH_TO_MESSAGES_USERS"]);
if (strlen($arParams["PATH_TO_MESSAGES_USERS"]) <= 0)
	$arParams["PATH_TO_MESSAGES_USERS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_users");

$arParams["PATH_TO_MESSAGES_USERS_MESSAGES"] = trim($arParams["PATH_TO_MESSAGES_USERS_MESSAGES"]);
if (strlen($arParams["PATH_TO_MESSAGES_USERS_MESSAGES"]) <= 0)
	$arParams["PATH_TO_MESSAGES_USERS_MESSAGES"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_users_messages&".$arParams["USER_VAR"]."=#user_id#");

$arParams["ITEMS_COUNT"] = IntVal($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 20;

$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);

if (!$GLOBALS["USER"]->IsAuthorized())
{	
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	$dbUser = CUser::GetByID($arParams["USER_ID"]);
	$arResult["User"] = $dbUser->GetNext();

	if (!is_array($arResult["User"]))
	{
		$arResult["FatalError"] = GetMessage("SONET_C31_NO_USER").".";
	}
	else
	{
		$arNavParams = array("nPageSize" => $arParams["ITEMS_COUNT"], "bDescPageNumbering" => false, "bShowAll" => false);
		$arNavigation = CDBResult::GetNavParams($arNavParams);

		/***********************  ACTIONS  *******************************/
		if ($_REQUEST["action"] == "ban" && check_bitrix_sessid() && IntVal($_REQUEST["userID"]) > 0)
		{
			$errorMessage = "";

			if (!CSocNetUserRelations::BanUser($GLOBALS["USER"]->GetID(), IntVal($_REQUEST["userID"])))
			{
				if ($e = $APPLICATION->GetException())
					$errorMessage .= $e->GetString();
			}

			if (strlen($errorMessage) > 0)
				$arResult["ErrorMessage"] = $errorMessage;
		}
		if ($_REQUEST["action"] == "close" && check_bitrix_sessid() && IntVal($_REQUEST["eventID"]) > 0)
		{
			$errorMessage = "";

			if (!CSocNetMessages::MarkMessageRead($GLOBALS["USER"]->GetID(), IntVal($_REQUEST["eventID"])))
			{
				if ($e = $APPLICATION->GetException())
					$errorMessage .= $e->GetString();
			}

			if (strlen($errorMessage) > 0)
				$arResult["ErrorMessage"] = $errorMessage;
		}
		if ($_REQUEST["action"] == "delete" && check_bitrix_sessid() && IntVal($_REQUEST["eventID"]) > 0)
		{
			$errorMessage = "";

			if (!CSocNetMessages::DeleteMessage(IntVal($_REQUEST["eventID"]), $GLOBALS["USER"]->GetID()))
			{
				if ($e = $APPLICATION->GetException())
					$errorMessage .= $e->GetString();
			}

			if (strlen($errorMessage) > 0)
				$arResult["ErrorMessage"] = $errorMessage;
		}
	
		if ($_SERVER["REQUEST_METHOD"]=="POST" && (strlen($_POST["do_read"]) > 0 || strlen($_POST["do_delete"]) > 0 || (array_key_exists("do_delete_all_flag", $_POST) && $_POST["do_delete_all_flag"] == "Y")) && check_bitrix_sessid())
		{
			$errorMessage = "";

			$arIDs = array();
			if (strlen($errorMessage) <= 0 && $_POST["do_delete_all_flag"] != "Y")
			{
				for ($i = 0; $i <= IntVal($_POST["max_count"]); $i++)
				{
					if ($_POST["checked_".$i] == "Y")
						$arIDs[] = IntVal($_POST["id_".$i]);
				}

				if (count($arIDs) <= 0)
					$errorMessage .= GetMessage("SONET_C31_NOT_SELECTED").". ";
			}

			if (strlen($errorMessage) <= 0)
			{
				if (strlen($_POST["do_read"]) > 0)
				{
					if (!CSocNetMessages::MarkMessageReadMultiple($GLOBALS["USER"]->GetID(), $arIDs))
					{
						if ($e = $APPLICATION->GetException())
							$errorMessage .= $e->GetString();
					}
				}
				elseif (strlen($_POST["do_delete"]) > 0)
				{
					if (!CSocNetMessages::DeleteMessageMultiple($GLOBALS["USER"]->GetID(), $arIDs))
					{
						if ($e = $APPLICATION->GetException())
							$errorMessage .= $e->GetString();
					}
				}
				elseif ($_POST["do_delete_all_flag"] == "Y")
				{
					if (!CSocNetMessages::DeleteConversation($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"]))
					{
						if ($e = $APPLICATION->GetException())
							$errorMessage .= $e->GetString();
					}
				}				
			}

			if (strlen($errorMessage) > 0)
				$arResult["ErrorMessage"] = $errorMessage;
		}
		/*********************  END ACTIONS  *****************************/

		if (strlen($arParams["NAME_TEMPLATE"]) <= 0)		
			$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
		$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;

		$arTmpUser = array(
					'NAME' => $arResult["User"]["~NAME"],
					'LAST_NAME' => $arResult["User"]["~LAST_NAME"],
					'SECOND_NAME' => $arResult["User"]["~SECOND_NAME"],
					'LOGIN' => $arResult["User"]["~LOGIN"],
				);

		$arResult["User"]["NAME_FORMATTED"] = CUser::FormatName($arParams["NAME_TEMPLATE"], $arTmpUser, $bUseLogin);
		
		if ($arParams["SET_TITLE"] == "Y" || $arParams["SET_NAV_CHAIN"] != "N")
		{
			$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
				array("#NOBR#", "#/NOBR#"), 
				array("", ""), 
				$arParams["NAME_TEMPLATE"]
			);
			$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arTmpUser, $bUseLogin);
		}
		
		if ($arParams["SET_TITLE"] == "Y")
			$APPLICATION->SetTitle($strTitleFormatted.": ".GetMessage("SONET_C31_PAGE_TITLE"));

		$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Message"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGE_FORM"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Chat"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGES_CHAT"], array("user_id" => $arResult["User"]["ID"]));

		if ($arParams["SET_NAV_CHAIN"] != "N")
		{
			$APPLICATION->AddChainItem($strTitleFormatted, $arResult["Urls"]["User"]);
			$APPLICATION->AddChainItem(GetMessage("SONET_C31_PAGE_TITLE"));
		}

		$arResult["IS_ONLINE"] = ($arResult["User"]["IS_ONLINE"] == "Y");

		$arResult["CanViewProfile"] = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());
		$arResult["CanMessage"] = (
			($arResult["User"]["ACTIVE"] != "N")
			&& (IsModuleInstalled("im") || CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], "message", CSocNetUser::IsCurrentUserModuleAdmin()))
		);
		$arResult["UsersRelation"] = CSocNetUserRelations::GetRelation($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"]);

		$arResult["Urls"]["BanLink"] = htmlspecialcharsbx($APPLICATION->GetCurUri("userID=".$arResult["User"]["ID"]."&action=ban&".bitrix_sessid_get().""));
		$arResult["ShowBanLink"] = (!CSocNetUser::IsUserModuleAdmin($arResult["User"]["ID"]) && $arResult["User"]["ID"] != $GLOBALS["USER"]->GetID() && (!$arResult["UsersRelation"] || $arResult["UsersRelation"] != SONET_RELATIONS_BAN));

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
		$arImage = CSocNetTools::InitImage($arResult["User"]["PERSONAL_PHOTO"], 150, "/bitrix/images/socialnetwork/nopic_user_150.gif", 150, $arResult["Urls"]["User"], $arResult["CanViewProfile"]);

		$arResult["User"]["PERSONAL_PHOTO_FILE"] = $arImage["FILE"];
		$arResult["User"]["PERSONAL_PHOTO_IMG"] = $arImage["IMG"];

		$parser = new CSocNetTextParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);

		$arResult["Events"] = false;

		$dbMessages = CSocNetMessages::GetMessagesForChat($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], false, $arNavParams);
		while ($arMessages = $dbMessages->GetNext())
		{
			if ($arResult["Events"] == false)
				$arResult["Events"] = array();

			$arResult["Events"][] = array(
				"WHO" => $arMessages["WHO"],
				"ID" => $arMessages["ID"],
				"TITLE" => $arMessages["TITLE"],
				"DATE_VIEW" => $arMessages["DATE_VIEW"],
				"DATE_CREATE" => $arMessages["DATE_CREATE"],
				"DATE_CREATE_FMT" => $arMessages["DATE_CREATE_FMT"],
				"DATE_CREATE_FORMAT" => $arMessages["DATE_CREATE_FORMAT"],
				"IS_READ" => (StrLen($arMessages["DATE_VIEW"]) > 0 || $arMessages["WHO"] == "OUT"),
				"READ_LINK" => htmlspecialcharsbx($APPLICATION->GetCurUri("eventID=".$arMessages["ID"]."&action=close&".bitrix_sessid_get()."")),
				"DELETE_LINK" => htmlspecialcharsbx($APPLICATION->GetCurUri("eventID=".$arMessages["ID"]."&action=delete&".bitrix_sessid_get()."")),
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

		$arResult["NAV_STRING"] = $dbMessages->GetPageNavStringEx($navComponentObject, GetMessage("SONET_C31_NAV"), "", false);
		$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
		$arResult["NAV_RESULT"] = $dbMessages;
	}
}
//echo "<pre>".print_r($arResult, true)."</pre>";
$this->IncludeComponentTemplate();
?>