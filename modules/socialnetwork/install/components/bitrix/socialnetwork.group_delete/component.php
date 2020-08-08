<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use \Bitrix\Main\Localization\Loc;

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arResult["IS_IFRAME"] = ($_REQUEST["IFRAME"] == "Y");

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

if (!$USER->IsAuthorized())
{
	$arResult["NEED_AUTH"] = "Y";
}
else
{
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
		{
			$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_GROUP");
		}
		else
		{

			$arResult["Group"] = $arGroup;

			$arResult["CurrentUserPerms"] = CSocNetUserToGroup::InitUserPerms($USER->GetID(), $arResult["Group"], CSocNetUser::IsCurrentUserModuleAdmin());

			$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $USER->GetID()));
			$arResult["Urls"]["Group"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupsList"] = \Bitrix\Socialnetwork\ComponentHelper::getWorkgroupSEFUrl();

			$pageTitle = Loc::getMessage($arResult["Group"]["PROJECT"] == "Y" ? "SONET_C9_TITLE_PROJECT" : "SONET_C9_TITLE");
			$strTitleFormatted = $arResult["Group"]["NAME"];

			if ($arParams["SET_TITLE"] == "Y")
			{
				if ($arResult['IS_IFRAME'])
				{
					$APPLICATION->SetTitle($pageTitle);
					$APPLICATION->SetPageProperty("PageSubtitle", $strTitleFormatted);
				}
				else
				{
					$APPLICATION->SetTitle($strTitleFormatted.": ".$pageTitle);
				}
			}

			if ($arParams["SET_NAV_CHAIN"] != "N")
			{
				$APPLICATION->AddChainItem($strTitleFormatted, $arResult["Urls"]["Group"]);
				$APPLICATION->AddChainItem($pageTitle);
			}

			if (!$arResult["CurrentUserPerms"]["UserCanModifyGroup"])
			{
				$arResult["FatalError"] = Loc::getMessage("SONET_C9_NO_PERMS").". ";
			}
			else
			{
				$arResult["ShowForm"] = "Input";

				if (
					$_SERVER["REQUEST_METHOD"] == "POST"
					&& $_POST["save"] <> ''
					&& check_bitrix_sessid()
				)
				{
					$errorMessage = "";

					if (
						($errorMessage == '')
						&& !CSocNetGroup::Delete($arResult["Group"]["ID"]) 
						&& ($e = $APPLICATION->GetException())
					)
					{
						$errorMessage .= $e->GetString();
					}

					if ($_REQUEST["ajax_request"] == "Y")
					{
						$APPLICATION->RestartBuffer();
						echo CUtil::PhpToJsObject(array(
							'MESSAGE' => ($errorMessage <> '' ? 'ERROR' : 'SUCCESS'),
							'ERROR_MESSAGE' => ($errorMessage <> '' ? $errorMessage : ''),
							'URL' => ($errorMessage <> '' ? '' : $arResult["Urls"]["GroupsList"])
						));
						require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
						die();
					}
					else
					{
						if ($errorMessage <> '')
						{
							$arResult["ErrorMessage"] = $errorMessage;
						}
						else
						{
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