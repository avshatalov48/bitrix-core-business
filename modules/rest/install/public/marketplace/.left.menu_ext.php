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

	$arMenuApps = array();
	$dbApps = \Bitrix\Rest\AppTable::getList(
		[
			'order' => [
				"ID" => "ASC"
			],
			'filter' => [
				"=ACTIVE" => \Bitrix\Rest\AppTable::ACTIVE
			],
			'select' => [
				'ID',
				'CODE',
				'CLIENT_ID',
				'STATUS',
				'ACTIVE',
				'ACCESS',
				'MENU_NAME' => 'LANG.MENU_NAME',
				'MENU_NAME_DEFAULT' => 'LANG_DEFAULT.MENU_NAME',
				'MENU_NAME_LICENSE' => 'LANG_LICENSE.MENU_NAME',
			],
		]
	);
	foreach ($dbApps->fetchCollection() as $app)
	{
		$arApp = [
			'ID' => $app->getId(),
			'CODE' => $app->getCode(),
			'ACTIVE' => $app->getActive(),
			'CLIENT_ID' => $app->getClientId(),
			'ACCESS' => $app->getAccess(),
			'MENU_NAME' => !is_null($app->getLang()) ? $app->getLang()->getMenuName() : '',
			'MENU_NAME_DEFAULT' => !is_null($app->getLangDefault()) ? $app->getLangDefault()->getMenuName() : '',
			'MENU_NAME_LICENSE' => !is_null($app->getLangLicense()) ? $app->getLangLicense()->getMenuName() : ''
		];

		if($arApp['CODE'] === \CRestUtil::BITRIX_1C_APP_CODE)
		{
			continue;
		}

		$lang = in_array(LANGUAGE_ID, array("ru", "en", "de"))
			? LANGUAGE_ID
			: \Bitrix\Main\Localization\Loc::getDefaultLang(LANGUAGE_ID);

		if ($arApp["MENU_NAME"] === '' && $arApp['MENU_NAME_DEFAULT'] === '' && $arApp['MENU_NAME_LICENSE'] === '')
		{
			$app->fillLangAll();
			if (!is_null($app->getLangAll()))
			{
				$langList = [];
				foreach ($app->getLangAll() as $appLang)
				{
					if ($appLang->getMenuName() !== '')
					{
						$langList[$appLang->getLanguageId()] = $appLang->getMenuName();
					}
				}

				if ($langList[$lang])
				{
					$arApp["MENU_NAME"] = $langList[$lang];
				}
				elseif ($langList['en'])
				{
					$arApp["MENU_NAME"] = $langList['en'];
				}
				elseif (count($langList) > 0)
				{
					$arApp["MENU_NAME"] = reset($langList);
				}
			}
		}

		if($arApp["MENU_NAME"] <> '' || $arApp['MENU_NAME_DEFAULT'] <> '' || $arApp['MENU_NAME_LICENSE'] <> '')
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

				if($appName == '')
				{
					$appName = $arApp['MENU_NAME_DEFAULT'];
				}

				if($appName == '')
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

	if ($USER->IsAuthorized())
	{
		$urlDevOps = \Bitrix\Rest\Url\DevOps::getInstance()->getIndexUrl();
		$arMenu[] = [
			GetMessage("REST_MENU_MARKETPLACE_DEVOPS"),
			$urlDevOps,
			[],
			[
				"menu_item_id" => "menu_marketplace_hook"
			],
			"",
		];
	}

	$arMenu = array_merge($arMenu, $arMenuApps);
}


$aMenuLinks = array_merge($arMenu, $aMenuLinks);
