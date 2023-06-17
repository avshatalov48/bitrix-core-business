<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["USER_ID"] = intval($arParams["USER_ID"]);
if ($arParams["USER_ID"] <= 0)
	$arParams["USER_ID"] = $GLOBALS["USER"]->GetID();

if ($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "user_id";
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if ($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if ($arParams["NAME_TEMPLATE"] == '')
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;
$bExtranetInstalled = IsModuleInstalled("extranet");

$arResult["FatalError"] = "";

if (!$GLOBALS["USER"]->IsAuthorized())
{	
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	if ($arParams["USER_ID"] <= 0)
		$arResult["FatalError"] = GetMessage("SONET_C40_NO_USER_ID").".";

	if ($arResult["FatalError"] == '')
	{
		$dbUser = CUser::GetByID($arParams["USER_ID"]);
		$arResult["User"] = $dbUser->GetNext();

		if (is_array($arResult["User"]))
		{
			$arResult["CurrentUserPerms"] = CSocNetUserPerms::InitUserPerms($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], CSocNetUser::IsCurrentUserModuleAdmin());

			if ($arResult["CurrentUserPerms"]["Operations"]["modifyuser"])
			{
				$arResult["Features"] = array();

				global $arSocNetUserOperations;
				foreach ($arSocNetUserOperations as $feature => $perm)
				{
					if (
						IsModuleInstalled("im") 
						&& $feature == "message"
					)
					{
						continue;
					}

					$perm = CSocNetUserPerms::GetOperationPerms($arResult["User"]["ID"], $feature);
					if (
						$bExtranetInstalled
						&& ($perm == SONET_RELATIONS_TYPE_ALL)
					)
					{
						$perm = SONET_RELATIONS_TYPE_AUTHORIZED;
					}

					$arResult["Features"][$feature] = $perm;
				}
			}
			else
			{
				$arResult["FatalError"] = GetMessage("SONET_C40_NO_PERMS").".";
			}
		}
		else
		{
			$arResult["FatalError"] = GetMessage("SONET_C40_NO_USER").".";
		}
	}

	if ($arResult["FatalError"] == '')
	{
		$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["User"]["ID"]));

		$arTmpUser = array(
				'NAME' => $arResult["User"]["~NAME"],
				'LAST_NAME' => $arResult["User"]["~LAST_NAME"],
				'SECOND_NAME' => $arResult["User"]["~SECOND_NAME"],
				'LOGIN' => $arResult["User"]["~LOGIN"],
			);
	
		$arResult["User"]["NAME_FORMATTED"] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin);	
		
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
			$APPLICATION->SetTitle($strTitleFormatted.": ".GetMessage("SONET_C40_PAGE_TITLE"));

		if ($arParams["SET_NAV_CHAIN"] != "N")
		{
			$APPLICATION->AddChainItem($strTitleFormatted, $arResult["Urls"]["User"]);
			$APPLICATION->AddChainItem(GetMessage("SONET_C40_PAGE_TITLE"));
		}

		$arResult["ShowForm"] = "Input";

		if ($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["save"] <> '' && check_bitrix_sessid())
		{
			$errorMessage = "";

			foreach ($arResult["Features"] as $feature => $perm)
			{
				$idTmp = CSocNetUserPerms::SetPerm(
					$arResult["User"]["ID"],
					$feature,
					$_REQUEST[$feature."_perm"]
				);
				if (!$idTmp)
				{
					if ($e = $APPLICATION->GetException())
						$errorMessage .= $e->GetString();
				}
			}

			if ($errorMessage <> '')
			{
				$arResult["ErrorMessage"] = $errorMessage;
			}
			else
			{
				if (!empty($_REQUEST['backurl']))
				{
					LocalRedirect($_REQUEST['backurl']);
				}
				else
				{
					LocalRedirect($arResult["Urls"]["User"]);
				}
			}
		}

		if ($arResult["ShowForm"] == "Input")
		{
			if (CSocNetUser::IsFriendsAllowed())
			{
				$arResult["PermsVar"] = array(
					SONET_RELATIONS_TYPE_NONE => GetMessage("SONET_C40_NOBODY"),
					SONET_RELATIONS_TYPE_FRIENDS => GetMessage("SONET_C40_ONLY_FRIENDS"),
					SONET_RELATIONS_TYPE_AUTHORIZED => GetMessage("SONET_C40_AUTHORIZED"),
				);
			}
			else
			{
				$arResult["PermsVar"] = array(
					SONET_RELATIONS_TYPE_NONE => GetMessage("SONET_C40_NOBODY"),
					SONET_RELATIONS_TYPE_AUTHORIZED => GetMessage("SONET_C40_AUTHORIZED"),					
				);
			}

			if (!$bExtranetInstalled)
			{
				$arResult["PermsVar"][SONET_RELATIONS_TYPE_ALL] = GetMessage("SONET_C40_ALL");
			}
		}
	}
}

$this->IncludeComponentTemplate();
?>