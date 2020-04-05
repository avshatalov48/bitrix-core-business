<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["USER_ID"] = IntVal($arParams["USER_ID"]);
if ($arParams["USER_ID"] <= 0)
	$arParams["USER_ID"] = IntVal($USER->GetID());

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if (strLen($arParams["USER_VAR"]) <= 0)
	$arParams["USER_VAR"] = "user_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if (strlen($arParams["PATH_TO_USER"]) <= 0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGES_INPUT"] = trim($arParams["PATH_TO_MESSAGES_INPUT"]);
if(strlen($arParams["PATH_TO_MESSAGES_INPUT"])<=0)
	$arParams["PATH_TO_MESSAGES_INPUT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_input");

$arParams["PATH_TO_MESSAGES_OUTPUT"] = trim($arParams["PATH_TO_MESSAGES_OUTPUT"]);
if(strlen($arParams["PATH_TO_MESSAGES_OUTPUT"])<=0)
	$arParams["PATH_TO_MESSAGES_OUTPUT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_output");

$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);

$arParams["MESSAGE_ID"] = IntVal($arParams["MESSAGE_ID"]);

if (strlen($arParams["NAME_TEMPLATE"]) <= 0)		
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;

if (!$USER->IsAuthorized())
{	
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	$dbUser = CUser::GetByID($arParams["USER_ID"]);
	$arResult["User"] = $dbUser->GetNext();

	if (!is_array($arResult["User"]))
	{
		$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_USER").". ";
	}
	else
	{
		$arResult["IsCurrentUser"] = ($GLOBALS["USER"]->GetID() == $arResult["User"]["ID"]);
		$arResult["CurrentUserRelation"] = CSocNetUserRelations::GetRelation($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"]);

		$arResult["CurrentUserPerms"]["ViewProfile"] = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());
		$arResult["CurrentUserPerms"]["ModifyUser"] = ($GLOBALS["USER"]->GetID() == $arResult["User"]["ID"] || CSocNetUser::IsCurrentUserModuleAdmin());
		$arResult["CurrentUserPerms"]["Message"] = (IsModuleInstalled("im") || CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], "message", CSocNetUser::IsCurrentUserModuleAdmin()));

		$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["MessagesInput"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGES_INPUT"], array());
		$arResult["Urls"]["MessagesOutput"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGES_OUTPUT"], array());

		$arResult["User"]["NAME_FORMATTED"] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arResult["User"], $bUseLogin);	
		
		if ($arParams["SET_TITLE"] == "Y" || $arParams["SET_NAV_CHAIN"] != "N")
		{
			$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
				array("#NOBR#", "#/NOBR#"), 
				array("", ""), 
				$arParams["NAME_TEMPLATE"]
			);
			$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arResult["User"], $bUseLogin);
		}

		if ($arParams["SET_TITLE"] == "Y")
			$APPLICATION->SetTitle($strTitleFormatted.": ".GetMessage("SONET_C26_PAGE_TITLE"));

		if ($arParams["SET_NAV_CHAIN"] != "N")
		{
			$APPLICATION->AddChainItem($strTitleFormatted, $arResult["Urls"]["User"]);
			$APPLICATION->AddChainItem(GetMessage("SONET_C26_PAGE_TITLE"));
		}

		if ($arResult["IsCurrentUser"])
		{
			$arResult["FatalError"] = GetMessage("SONET_C26_SELF").". ";
		}
		elseif (!$arResult["CurrentUserPerms"]["Message"])
		{
			$arResult["FatalError"] = GetMessage("SONET_C26_PERM_MESS").". ";
		}
		else
		{
			$arResult["ShowForm"] = "Input";
			if ($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["save"]) > 0 && check_bitrix_sessid())
			{
				$errorMessage = "";

				if (strlen($_POST["POST_MESSAGE"]) <= 0)
					$errorMessage .= GetMessage("SONET_C26_NO_TEXT").". ";

				if (strlen($errorMessage) <= 0)
				{
					if (CSocNetMessages::CreateMessage($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], $_POST["POST_MESSAGE"]))
					{
						if ($arParams["MESSAGE_ID"] > 0)
							CSocNetMessages::MarkMessageRead($GLOBALS["USER"]->GetID(), $arParams["MESSAGE_ID"]);
					}
					else
					{
						if ($e = $APPLICATION->GetException())
							$errorMessage .= $e->GetString();
					}
				}

				if (strlen($errorMessage) > 0)
					$arResult["ErrorMessage"] = $errorMessage;
				else
					$arResult["ShowForm"] = "Confirm";
			}

			$arResult["PrintSmilesList"] = CSocNetSmile::PrintSmilesList(3, LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);
		}
	}
}
$this->IncludeComponentTemplate();
?>