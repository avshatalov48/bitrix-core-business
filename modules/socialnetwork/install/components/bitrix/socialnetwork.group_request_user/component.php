<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["USER_ID"] = intval($arParams["USER_ID"]);
$arParams["GROUP_ID"] = intval($arParams["GROUP_ID"]);

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if ($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "user_id";
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if ($arParams["GROUP_VAR"] == '')
	$arParams["GROUP_VAR"] = "group_id";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if ($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if ($arParams["PATH_TO_GROUP"] == '')
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");

if ($arParams["NAME_TEMPLATE"] == '')
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;

if (!$USER->IsAuthorized())
	$arResult["NEED_AUTH"] = "Y";
else
{
	$dbUser = CUser::GetByID($arParams["USER_ID"]);
	$arResult["User"] = $dbUser->GetNext();

	if (!is_array($arResult["User"]))
		$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_USER");
	else
	{
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
			$APPLICATION->SetTitle($strTitleFormatted.": ".GetMessage("SONET_C11_TITLE"));

		$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["User"]["ID"]));

		if ($arParams["SET_NAV_CHAIN"] != "N")
		{
			$APPLICATION->AddChainItem($strTitleFormatted, $arResult["Urls"]["User"]);
			$APPLICATION->AddChainItem(GetMessage("SONET_C11_TITLE"));
		}

		$arGroup = CSocNetGroup::GetByID($arParams["GROUP_ID"]);

		if (
			!$arGroup 
			|| !is_array($arGroup) 
			|| $arGroup["ACTIVE"] != "Y" 
		)
			$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_GROUP");
		else
		{
			$arGroupSites = array();
			$rsGroupSite = CSocNetGroup::GetSite($arGroup["ID"]);
			while ($arGroupSite = $rsGroupSite->Fetch())
				$arGroupSites[] = $arGroupSite["LID"];

			if (!in_array(SITE_ID, $arGroupSites))
				$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_GROUP");
			else
			{
				$arResult["Group"] = $arGroup;

				$arResult["Urls"]["Group"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["Group"]["ID"]));

				$arResult['CurrentUserPerms'] = \Bitrix\Socialnetwork\Helper\Workgroup::getPermissions([
					'groupId' => $arGroup['ID'],
				]);

				if (!$arResult["CurrentUserPerms"] || !$arResult["CurrentUserPerms"]["UserCanInitiate"])
					$arResult["FatalError"] = GetMessage("SONET_C11_NO_PERMS").". ";
				else
				{
					$arResult["IsCurrentUser"] = ($GLOBALS["USER"]->GetID() == $arResult["User"]["ID"]);
					$arResult["CurrentUserRelation"] = CSocNetUserRelations::GetRelation($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"]);

					$arResult["CurrentUserPerms"]["ViewProfile"] = ($arResult["IsCurrentUser"] || CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin()));
					$arResult["CurrentUserPerms"]["InviteGroup"] = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], "invitegroup", CSocNetUser::IsCurrentUserModuleAdmin());

					$user2groupRelation = CSocNetUserToGroup::GetUserRole($arResult["User"]["ID"], $arResult["Group"]["ID"]);

					if ($arResult["IsCurrentUser"])
						$arResult["FatalError"] = GetMessage("SONET_C11_ERR_SELF").". ";
					elseif (!$arResult["CurrentUserPerms"]["InviteGroup"])
						$arResult["FatalError"] = GetMessage("SONET_C11_BAD_USER").". ";
					elseif ($user2groupRelation)
						$arResult["FatalError"] = GetMessage("SONET_C11_BAD_RELATION").". ";
					else
					{
						$arResult["ShowForm"] = "Input";
						if ($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["save"] <> '' && check_bitrix_sessid())
						{
							$errorMessage = "";

							if ($_POST["MESSAGE"] == '')
								$errorMessage .= GetMessage("SONET_C11_NO_MESSAGE").". ";

							if (
								$errorMessage == ''
								&& !CSocNetUserToGroup::SendRequestToJoinGroup($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], $arResult["Group"]["ID"], $_POST["MESSAGE"])
								&& ($e = $APPLICATION->GetException())
							)
								$errorMessage .= $e->GetString();

							if ($errorMessage <> '')
								$arResult["ErrorMessage"] = $errorMessage;
							else
								$arResult["ShowForm"] = "Confirm";
						}
					}
				}
			}
		}
	}
}
$this->IncludeComponentTemplate();
?>