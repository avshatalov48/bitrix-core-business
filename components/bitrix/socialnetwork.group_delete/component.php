<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

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
	ShowError(Loc::getMessage('SONET_MODULE_NOT_INSTALL'));
	return;
}

$arResult['IS_IFRAME'] = (
	($_REQUEST['IFRAME'] ?? '') === 'Y'
	|| $arParams['IFRAME'] === 'Y'
);

$arParams['GROUP_ID'] = (int)$arParams['GROUP_ID'];

$arParams['SET_NAV_CHAIN'] = ($arParams['SET_NAV_CHAIN'] === 'N' ? 'N' : 'Y');

if ($arParams["USER_VAR"] == '')
{
	$arParams["USER_VAR"] = "user_id";
}
if ($arParams["PAGE_VAR"] == '')
{
	$arParams["PAGE_VAR"] = "page";
}
if ($arParams["GROUP_VAR"] == '')
{
	$arParams["GROUP_VAR"] = "group_id";
}

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if ($arParams["PATH_TO_USER"] == '')
{
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
}
$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if ($arParams["PATH_TO_GROUP"] == '')
{
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arResult['PageTitle'] = '';

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
		|| $arGroup['ACTIVE'] !== 'Y'
	)
		$arResult['FatalError'] = Loc::getMessage('SONET_P_USER_NO_GROUP');
	else
	{
		$arGroupSites = array();
		$rsGroupSite = CSocNetGroup::GetSite($arGroup["ID"]);
		while ($arGroupSite = $rsGroupSite->Fetch())
			$arGroupSites[] = $arGroupSite["LID"];

		if (!in_array(SITE_ID, $arGroupSites))
		{
			$arResult['FatalError'] = Loc::getMessage('SONET_P_USER_NO_GROUP');
		}
		else
		{
			$arResult["Group"] = $arGroup;

			$arResult['CurrentUserPerms'] = \Bitrix\Socialnetwork\Helper\Workgroup::getPermissions([
				'groupId' => $arGroup['ID'],
			]);

			$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $USER->GetID()));
			$arResult["Urls"]["Group"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupsList"] = \Bitrix\Socialnetwork\ComponentHelper::getWorkgroupSEFUrl();

			$pageTitle = (
				$arResult['Group']['PROJECT'] === 'Y'
					? Loc::getMessage('SONET_C9_TITLE_PROJECT')
					: Loc::getMessage('SONET_C9_TITLE')
			);
			$strTitleFormatted = $arResult["Group"]["NAME"];

			if ($arParams['SET_TITLE'] === 'Y')
			{
				if ($arResult['IS_IFRAME'])
				{
					$arResult['PageTitle'] = $pageTitle;
					$APPLICATION->SetPageProperty("PageSubtitle", $strTitleFormatted);
				}
				else
				{
					$arResult['PageTitle'] = $strTitleFormatted . ': ' . $pageTitle;
				}
				$APPLICATION->SetTitle($arResult['PageTitle']);
			}

			if ($arParams['SET_NAV_CHAIN'] !== 'N')
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
					$_SERVER['REQUEST_METHOD'] === 'POST'
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

					if (isset($_REQUEST['ajax_request']) && $_REQUEST['ajax_request'] === 'Y')
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

$this->IncludeComponentTemplate();
