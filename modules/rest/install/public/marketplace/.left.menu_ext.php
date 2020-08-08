<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rest/install/public/marketplace/.left.menu_ext.php");

$arMenu = array();

$extranetSite = (
	\Bitrix\Main\Loader::includeModule('extranet')
	&& \CExtranet::isExtranetSite(SITE_ID)
);

if(
	!$extranetSite
	&& (
		SITE_TEMPLATE_ID == 'bitrix24'
		|| \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24')
	)
)
{
	$arMenu[] = Array(
		GetMessage("MENU_MARKETPLACE_ALL"),
		SITE_DIR."marketplace/",
		Array(),
		Array("menu_item_id" => "menu_marketplace"),
		""
	);
}

if(
	!$extranetSite
	&& CModule::IncludeModule("rest")
)
{
	if (\CRestUtil::isAdmin())
	{
		$arMenu[] = Array(
			GetMessage("MENU_MARKETPLACE_INSTALLED"),
			SITE_DIR."marketplace/installed/",
			Array(),
			Array("menu_item_id" => "menu_marketplace_installed"),
			""
		);
	}

	$arUserGroupCode = $USER->GetAccessCodes();
	$numLocalApps = 0;

	$arMenuApps = array();
	$dbApps = \Bitrix\Rest\AppTable::getList(array(
		'order' => array("ID" => "ASC"),
		'filter' => array("=ACTIVE" => \Bitrix\Rest\AppTable::ACTIVE),
		'select' => array(
			'ID', 'CODE', 'CLIENT_ID','STATUS', 'ACCESS', 'MENU_NAME' => 'LANG.MENU_NAME', 'MENU_NAME_DEFAULT' => 'LANG_DEFAULT.MENU_NAME', 'MENU_NAME_LICENSE' => 'LANG_LICENSE.MENU_NAME'
		)
	));
	while($arApp = $dbApps->fetch())
	{
		if($arApp['CODE'] === \CRestUtil::BITRIX_1C_APP_CODE)
		{
			continue;
		}

		if($arApp["STATUS"] == \Bitrix\Rest\AppTable::STATUS_LOCAL)
		{
			$numLocalApps++;
		}

		$lang = in_array(LANGUAGE_ID, array("ru", "en", "de"))
			? LANGUAGE_ID
			: \Bitrix\Main\Localization\Loc::getDefaultLang(LANGUAGE_ID);

		if(strlen($arApp["MENU_NAME"]) > 0 || strlen($arApp['MENU_NAME_DEFAULT']) > 0 || strlen($arApp['MENU_NAME_LICENSE']) > 0)
		{
			$appRightAvailable = false;
			if(\CRestUtil::isAdmin())
			{
				$appRightAvailable = true;
			}
			elseif(!empty($arApp["ACCESS"]))
			{
				$rights = explode(",", $arApp["ACCESS"]);
				foreach($rights as $rightID)
				{
					if(in_array($rightID, $arUserGroupCode))
					{
						$appRightAvailable = true;
						break;
					}
				}
			}
			else
			{
				$appRightAvailable = true;
			}

			if($appRightAvailable)
			{
				$appName = $arApp["MENU_NAME"];

				if(strlen($appName) <= 0)
				{
					$appName = $arApp['MENU_NAME_DEFAULT'];
				}

				if(strlen($appName) <= 0)
				{
					$appName = $arApp['MENU_NAME_LICENSE'];
				}

				$arMenuApps[] = Array(
					htmlspecialcharsbx($appName),
					\CRestUtil::getApplicationPage($arApp['ID'], 'ID', $arApp),
					Array(
						\CRestUtil::getApplicationPage($arApp['ID'], 'CODE', $arApp),
						\CRestUtil::getApplicationPage($arApp['ID'], 'CLIENT_ID', $arApp),
					),
					Array("is_application" => "Y", "app_id" => $arApp["ID"]),
					""
				);
			}
		}
	}
	if(\CRestUtil::isAdmin() && $numLocalApps > 0)
	{
		$arMenu[] = Array(
			GetMessage("MENU_MARKETPLACE_LOCAL"),
			SITE_DIR."marketplace/local/list/",
			Array(
				SITE_DIR."marketplace/local/edit/",
			),
			Array("menu_item_id" => "menu_marketplace_local"),
			""
		);
	}

	if ($USER->IsAuthorized())
	{
		$arMenu[] = Array(
			GetMessage("MENU_MARKETPLACE_HOOK"),
			SITE_DIR."marketplace/hook/",
			Array(),
			Array("menu_item_id" => "menu_marketplace_hook"),
			""
		);
	}

	$arMenu = array_merge($arMenu, $arMenuApps);
}


$aMenuLinks = array_merge($arMenu, $aMenuLinks);
