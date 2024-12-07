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

use Bitrix\Main\Localization\Loc;

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arResult['IS_IFRAME'] = (
	($_REQUEST['IFRAME'] ?? null) === 'Y'
	|| $arParams['IFRAME'] === 'Y'
);

$arParams['USER_ID'] = (int)$USER->GetID();
$arParams['GROUP_ID'] = (int)$arParams["GROUP_ID"];
$arParams['SET_NAV_CHAIN'] = ($arParams['SET_NAV_CHAIN'] === 'N' ? 'N' : 'Y');

if ($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "user_id";
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if ($arParams["GROUP_VAR"] == '')
	$arParams["GROUP_VAR"] = "group_id";

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
	{
		$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_GROUP").". ";
	}
	else
	{
		$arGroupSites = array();
		$rsGroupSite = CSocNetGroup::GetSite($arGroup["ID"]);
		while ($arGroupSite = $rsGroupSite->Fetch())
		{
			$arGroupSites[] = $arGroupSite["LID"];
		}

		if (!in_array(SITE_ID, $arGroupSites))
		{
			$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_GROUP");
		}
		else
		{
			$arResult["Group"] = $arGroup;
			$arResult["CurrentUserPerms"] = \Bitrix\Socialnetwork\Helper\Workgroup::getPermissions([
				'groupId' => $arGroup['ID'],
			]);

			$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $USER->GetID()));
			$arResult["Urls"]["Group"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupsList"] = \Bitrix\Socialnetwork\ComponentHelper::getWorkgroupSEFUrl();

			if ($arParams['SET_TITLE'] === 'Y')
			{
				$arResult['PageTitle'] = Loc::getMessage('SONET_C37_PAGE_TITLE');
				$APPLICATION->SetTitle($arResult['PageTitle']);
			}

			if ($arParams['SET_NAV_CHAIN'] !== 'N')
			{
				$APPLICATION->AddChainItem($arResult["Group"]["NAME"], $arResult["Urls"]["Group"]);
				$APPLICATION->AddChainItem(GetMessage("SONET_C37_PAGE_TITLE"));
			}

			if ($arResult["CurrentUserPerms"]["UserIsOwner"])
			{
				$arResult["FatalError"] = GetMessage("SONET_C37_IS_OWNER").". ";
			}
			elseif (!$arResult["CurrentUserPerms"]["UserIsMember"])
			{
				$arResult["FatalError"] = GetMessage("SONET_C37_NOT_MEMBER").". ";
			}
			elseif (isset($arResult["CurrentUserPerms"]["UserIsAutoMember"]) && $arResult["CurrentUserPerms"]["UserIsAutoMember"])
			{
				$arResult["FatalError"] = GetMessage("SONET_C37_IS_AUTO_MEMBER").". ";
			}
			else
			{
				if ($arParams['SET_TITLE'] === 'Y')
				{
					$arResult['PageTitle'] = (
						$arResult['Group']['PROJECT'] === 'Y'
							? Loc::getMessage('SONET_C37_PAGE_TITLE_PROJECT')
							: Loc::getMessage('SONET_C37_PAGE_TITLE')
					);
					if (!$arResult['IS_IFRAME'])
					{
						$arResult['PageTitle'] = $arResult['Group']['NAME'] . ': ' . $arResult['PageTitle'];
					}
					$APPLICATION->SetTitle($arResult['PageTitle']);

					if ($arResult['IS_IFRAME'])
					{
						$APPLICATION->SetPageProperty('PageSubtitle', $arResult["Group"]["NAME"]);
					}
				}

				$arResult["ShowForm"] = "Input";
				if (
					$_SERVER['REQUEST_METHOD'] === 'POST'
					&& !empty($_POST["save"])
					&& check_bitrix_sessid()
				)
				{
					$errorMessage = "";

					if ($errorMessage == '')
					{
						if (!CSocNetUserToGroup::DeleteRelation($USER->GetID(), $arResult["Group"]["ID"]))
						{
							if ($e = $APPLICATION->GetException())
							{
								$errorMessage .= $e->GetString();
							}
						}
					}

					if ($errorMessage <> '')
					{
						$arResult["ErrorMessage"] = $errorMessage;
					}
					else
					{
						$arResult["ShowForm"] = "Confirm";
					}

					if (isset($_POST['ajax_request']) && $_POST['ajax_request'] === 'Y')
					{
						$APPLICATION->RestartBuffer();
						echo CUtil::PhpToJsObject(array(
							'MESSAGE' => ($errorMessage <> '' ? 'ERROR' : 'SUCCESS'),
							'ERROR_MESSAGE' => ($errorMessage <> '' ? $errorMessage : ''),
							'URL' => ($errorMessage <> '' ? '' : $arResult["Urls"]["GroupsList"]),
						));
						require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
						die();
					}
				}
			}
		}
	}
}

$this->IncludeComponentTemplate();
