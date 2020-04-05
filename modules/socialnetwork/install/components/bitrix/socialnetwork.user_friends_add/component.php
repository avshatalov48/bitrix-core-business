<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["ID"] = IntVal($arParams["ID"]);
if ($arParams["ID"] <= 0)
	$arParams["ID"] = IntVal($USER->GetID());

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if (strLen($arParams["USER_VAR"]) <= 0)
	$arParams["USER_VAR"] = "user_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if (strlen($arParams["PATH_TO_USER"]) <= 0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

if (strlen($arParams["NAME_TEMPLATE"]) <= 0)		
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;
	
if (!CSocNetUser::IsFriendsAllowed())
{
	$arResult["FatalError"] = GetMessage("SONET_C34_NO_FR_FUNC").". ";
}
else
{
	if (!$GLOBALS["USER"]->IsAuthorized())
	{	
		$arResult["NEED_AUTH"] = "Y";
	}
	else
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

			if (!$arResult["CurrentUserPerms"]["IsCurrentUser"])
				$arResult["CurrentUserRelation"] = CSocNetUserRelations::GetRelation($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"]);

			$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["User"]["ID"]));
			$arResult["User"]["NAME_FORMATTED"] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arResult["User"], $bUseLogin, false);

			if ($arParams["SET_TITLE"] == "Y" || $arParams["SET_NAV_CHAIN"] != "N")
			{
				$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
					array("#NOBR#", "#/NOBR#"), 
					array("", ""), 
					$arParams["NAME_TEMPLATE"]
				);
				$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arResult["User"], $bUseLogin, false);	
			}				
			
			if ($arParams["SET_TITLE"] == "Y")
				$APPLICATION->SetTitle($strTitleFormatted.": ".GetMessage("SONET_C34_PAGE_TITLE"));

			if ($arParams["SET_NAV_CHAIN"] != "N")
			{
				$APPLICATION->AddChainItem($strTitleFormatted, $arResult["Urls"]["User"]);
				$APPLICATION->AddChainItem(GetMessage("SONET_C34_PAGE_TITLE"));
			}

			if ($arResult["CurrentUserPerms"]["IsCurrentUser"])
			{
				$arResult["FatalError"] = GetMessage("SONET_C34_SELF").". ";
			}
			elseif ($arResult["CurrentUserRelation"] == SONET_RELATIONS_FRIEND)
			{
				$arResult["FatalError"] = GetMessage("SONET_C34_ALREADY_FRIEND").". ";
			}
			elseif ($arResult["CurrentUserRelation"] == SONET_RELATIONS_REQUEST)
			{
				$arResult["FatalError"] = GetMessage("SONET_C34_ALREADY_SEND").". ";
			}
			elseif ($arResult["CurrentUserRelation"] == SONET_RELATIONS_BAN && !IsModuleInstalled("im"))
			{
				$arResult["FatalError"] = GetMessage("SONET_C34_IN_BLACK").". ";
			}
			else
			{
				$arResult["ShowForm"] = "Input";
				if ($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["save"]) > 0 && check_bitrix_sessid())
				{
					$errorMessage = "";

					if (strlen($_POST["MESSAGE"]) <= 0)
						$errorMessage .= GetMessage("SONET_C34_NO_TEXT").". ";

					if (strlen($errorMessage) <= 0)
					{
						if (!CSocNetUserRelations::SendRequestToBeFriend($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], $_POST["MESSAGE"]))
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
			}
		}
	}
}
$this->IncludeComponentTemplate();
?>