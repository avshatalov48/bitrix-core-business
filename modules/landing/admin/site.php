<?php
define('ADMIN_SECTION', false);
define('B24CONNECTOR_SKIP', true);
if (
	isset($_GET['template']) &&
	preg_match('/^[a-z0-9_]+$/i', $_GET['template'])
)
{
	define('SITE_TEMPLATE_ID', $_GET['template']);
}
else
{
	define('SITE_TEMPLATE_ID', 'landing24');
}
if (
	isset($_GET['site']) &&
	preg_match('/^[a-z0-9_]+$/i', $_GET['site'])
)
{
	define('SITE_ID', $_GET['site']);
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

use \Bitrix\Main\Application;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\SiteTable;
use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Page\AssetLocation;
use \Bitrix\Landing\Domain;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Rights;

Loc::loadMessages(__FILE__);
Loader::includeModule('landing');
define('ADMIN_MODULE_NAME', 'landing');

/** @var \CMain $APPLICATION */

// vars
$request = Application::getInstance()->getContext()->getRequest();
$server = Application::getInstance()->getContext()->getServer();
$application = Manager::getApplication();
$site = $request->get('site');
$siteId = $request->get('siteId');
$landing = $request->get('id');
$cmp = $request->get('cmp');
$isFrame = $request->get('IFRAME') == 'Y';
$isAjax = $request->get('IS_AJAX') == 'Y';
$storeEnabled = !Manager::isB24() && Manager::isStoreEnabled();
$actionFolder = 'folderId';
$type = 'SMN';
$siteTemplate = Manager::getTemplateId($site);

define('SMN_SITE_ID', $site);

// refresh block repo
\Bitrix\Landing\Block::getRepository();

// check module rights
if ($application->getGroupRight('landing') < 'W')
{
	$application->authForm(Loc::getMessage('ACCESS_DENIED'));
}

// detect Site
$filter = [
	'=TYPE' => $type,
	'CHECK_PERMISSIONS' => 'N'
];
if ($siteId)
{
	$filter['ID'] = $siteId;
}
else if ($site)
{
	$filter['=SMN_SITE_ID'] = $site;
}
else
{
	$filter['ID'] = -1;
}

$rights = [];
$res = Site::getList([
	 'select' => [
		'ID', 'SMN_SITE_ID'
	 ],
	 'filter' => $filter
 ]);
if ($row = $res->fetch())
{
	$siteId = $row['ID'];
	$site = $row['SMN_SITE_ID'];
	$rights = Rights::getOperationsForSite($siteId);
	if (!in_array(Rights::ACCESS_TYPES['read'], $rights))
	{
		require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');
		\showError(Loc::getMessage('LANDING_ADMIN_SITE_ACCESS_DENIED'));
		require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
	}
}
else
{
	if (
		$site &&
		($siteRow = SiteTable::getById($site)->fetch())
	)
	{
		// create site if not exist
		$res = Site::add(array(
			'TITLE' => $siteRow['NAME'],
			'SMN_SITE_ID' => $site,
			'TYPE' => $type,
			'DOMAIN_ID' => !Manager::isB24() ? Domain::getCurrentId() : ' ',
			'CODE' => mb_strtolower(\randString(10))
		));
		if ($res->isSuccess())
		{
			$siteId = $res->getId();
			$rights = Rights::getOperationsForSite($siteId);
		}
		else
		{
			require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');
			foreach ($res->getErrors() as $error)
			{
				\showError($error->getMessage());
			}
			require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
			die();
		}
	}
	else
	{
		require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');
		\showError(Loc::getMessage('LANDING_ADMIN_SITE_NOT_FOUND'));
		require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
	}
}

// paths
$landingsPage = 'landing_site.php?lang=' . LANGUAGE_ID . '&site=' . $site;

$siteSettings = $landingsPage . '&cmp=landing_settings';
$landingSettings = $landingsPage . '&cmp=landing_settings&id=#landing_edit#';

$editPage = $landingsPage . '&cmp=landing_edit&id=#landing_edit#';
$editPage .= ($siteTemplate ? '&template=' . $siteTemplate : '');

$editFolder = $landingsPage . '&cmp=folder_edit&' . $actionFolder . '=#folder_edit#';
$editFolder .= ($siteTemplate ? '&template=' . $siteTemplate : '');

$designPage = $landingsPage . '&cmp=landing_edit&id=#landing_edit#&componentTemplate=design';
$designPage .= ($siteTemplate ? '&template=' . $siteTemplate : '');

$editSite = $landingsPage . '&cmp=site_edit';
$editSite .= ($siteTemplate ? '&template=' . $siteTemplate : '');

$designSite = $landingsPage . '&cmp=site_edit&componentTemplate=design';
$designSite .= ($siteTemplate ? '&template=' . $siteTemplate : '');

$editCookies = $landingsPage . '&cmp=cookies_edit';
$editCookies .= ($siteTemplate ? '&template=' . $siteTemplate : '');

$viewPage ='landing_view.php?lang=' . LANGUAGE_ID . '&id=#landing_edit#&site=' . $site . '&template=' . $siteTemplate;

if ($isFrame)
{
	Asset::getInstance()->addCSS(
		'/bitrix/components/bitrix/landing.start/templates/.default/style.css'
	);
	Asset::getInstance()->addCSS(
		'/bitrix/components/bitrix/landing.filter/templates/.default/style.css'
	);
	Asset::getInstance()->addJS(
		'/bitrix/components/bitrix/landing.start/templates/.default/script.js'
	);
	include $server->getDocumentRoot() .
			'/bitrix/modules/landing/install/components/bitrix/landing.start/templates/.default/slider_header.php';
}
else if (!$isAjax)
{
	require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');
	// js scripts
	$application->showHeadStrings();
	$application->showHeadScripts();
	$application->showCSS();
}

// emulate site_id
Asset::getInstance()->addString(
	'<script>
			BX.message({SITE_ID: \'' . \CUtil::jsEscape($site) . '\'});
		</script>',
	false,
	AssetLocation::AFTER_CSS
);

// content area
echo '<div class="landing-content-title-admin">';

if (!$cmp && !$isFrame)
{
	// create buttons
	if (!Rights::hasAccessForSite($siteId, Rights::ACCESS_TYPES['edit']))
	{
		$buttons = [];
	}
	else if ($storeEnabled)
	{
		$buttons = array(
			array(
				'LINK' => '#',
				'TITLE' => Loc::getMessage('LANDING_ADMIN_ACTION_ADD')
			),
			array(
				'LINK' => str_replace('#landing_edit#', 0, $editPage) . '&type=PAGE',
				'TITLE' => Loc::getMessage('LANDING_ADMIN_ACTION_ADD_PAGE')
			),
			array(
				'LINK' => str_replace('#landing_edit#', 0, $editPage) . '&type=STORE',
				'TITLE' => Loc::getMessage('LANDING_ADMIN_ACTION_ADD_STORE')
			)
		);
	}
	else
	{
		$buttons = array(
			array(
				'LINK' => str_replace('#landing_edit#', 0, $editPage) . '&type=PAGE',
				'TITLE' => Loc::getMessage('LANDING_ADMIN_ACTION_ADD_ONE')
			)
		);
	}

	// settings menu
	$settingsLink = [];
	if (in_array(Rights::ACCESS_TYPES['sett'], $rights))
	{
		$settingsLink[] = [
			'TITLE' => Loc::getMessage('LANDING_ADMIN_ACTION_SETTINGS'),
			'LINK' => $siteSettings
		];
	}

	$folderId = $request->get($actionFolder);

	// folder
	if ($folderId)
	{
		$settingsLink[] = [
			'TITLE' => Loc::getMessage('LANDING_TPL_FOLDER_EDIT'),
			'LINK' => str_replace('#folder_edit#', $folderId, $editFolder)
		];
	}

	$APPLICATION->IncludeComponent(
		'bitrix:landing.filter',
		'.default',
		array(
			'FILTER_TYPE' => 'LANDING',
			'TYPE' => $type,
			'SETTING_LINK' => $settingsLink,
			'BUTTONS' => $buttons,
			'FOLDER_ID' => $folderId,
			'FOLDER_SITE_ID' => $siteId
		),
		false
	);
}

echo '</div>';

if ($isAjax)
{
	\Bitrix\Landing\Manager::getApplication()->restartBuffer();
}

echo '<div id="workarea-content" class="landing-content-admin">';

$component = null;

if ($cmp == 'landing_edit')
{
	if ($landing > 0)
	{
		$componentTemplate = $request->get('componentTemplate');
		if ($componentTemplate === 'design')
		{
			$APPLICATION->IncludeComponent(
				'bitrix:landing.landing_edit',
				'design',
				array(
					'TYPE' => $type,
					'SITE_ID' => $siteId,
					'LANDING_ID' => $landing,
					'PAGE_URL_LANDINGS' => $landingsPage,
					'PAGE_URL_LANDING_VIEW' => $viewPage,
					'PAGE_URL_SITE_EDIT' => $editSite
				),
				$component
			);
		}
		else
		{
			$APPLICATION->IncludeComponent(
				'bitrix:landing.landing_edit',
				'.default',
				array(
					'TYPE' => $type,
					'SITE_ID' => $siteId,
					'LANDING_ID' => $landing,
					'PAGE_URL_LANDINGS' => $landingsPage,
					'PAGE_URL_LANDING_VIEW' => $viewPage,
					'PAGE_URL_SITE_EDIT' => $editSite
				),
				$component
			);
		}
	}
	else
	{
		$createType = $request->get('type');
		if (!$createType)
		{
			$createType = 'PAGE';
		}
		if ($tpl = $request->get('tpl'))
		{
			$APPLICATION->IncludeComponent(
				'bitrix:landing.demo_preview',
				'.default',
				array(
					'TYPE' => $createType,
					'CODE' => $tpl,
					'SITE_ID' => $siteId,
					'PAGE_URL_BACK' => $landingsPage,
					'SITE_WORK_MODE' => 'Y',
					'LANG_ID' => LANGUAGE_ID,
					'ADMIN_SECTION' => 'Y',
					'ACTION_FOLDER' => $actionFolder,
				),
				$component
			);
		}
		else
		{
			$APPLICATION->IncludeComponent(
				'bitrix:landing.demo',
				'.default',
				array(
					'TYPE' => $createType,
					'ACTION_FOLDER' => $actionFolder,
					'SITE_ID' => $siteId,
					'PAGE_URL_SITES' => $landingsPage,
					'PAGE_URL_LANDING_VIEW' => $viewPage,
					'SITE_WORK_MODE' => 'Y'
				),
				$component
			);
		}
	}
}
elseif ($cmp == 'site_edit')
{
	$tpl = $request->get('tpl');
	$componentTemplate = $request->get('componentTemplate');
	if ($componentTemplate === 'design')
	{
		$APPLICATION->IncludeComponent(
			'bitrix:landing.site_edit',
			'design',
			array(
				'TYPE' => $type,
				'SITE_ID' => $siteId,
				'PAGE_URL_SITES' => '',
				'PAGE_URL_LANDING_VIEW' => $viewPage,
				'PAGE_URL_SITE_COOKIES' => $editCookies,
				'TEMPLATE' => $tpl
			),
			$component
		);
	}
	else
	{
		$APPLICATION->IncludeComponent(
			'bitrix:landing.site_edit',
			'.default',
			array(
				'TYPE' => $type,
				'SITE_ID' => $siteId,
				'PAGE_URL_SITES' => '',
				'PAGE_URL_LANDING_VIEW' => $viewPage,
				'PAGE_URL_SITE_COOKIES' => $editCookies,
				'TEMPLATE' => $tpl
			),
			$component
		);
	}
}
elseif ($cmp == 'landing_settings')
{
	$pages = [
		'PAGE_URL_SITE_EDIT' => $editSite,
		'PAGE_URL_SITE_DESIGN' => $designSite,
	];
	if ($storeEnabled)
	{
		$uriSettCatalog = new \Bitrix\Main\Web\Uri($editSite);
		$uriSettCatalog->addParams(['tpl' => 'catalog']);
		$pages['PAGE_URL_CATALOG_EDIT'] = $uriSettCatalog->getUri();
		unset($uriSettCatalog);
	}

	$componentParams = [
		'POPUP_COMPONENT_NAME' => 'bitrix:landing.settings',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'SITE_ID' => $siteId,
			'TYPE' => $storeEnabled ? 'STORE' : $type,
			'PAGES' => $pages,
		],
		'USE_PADDING' => false,
		'PAGE_MODE' => false,
		'CLOSE_AFTER_SAVE' => false,
		'RELOAD_GRID_AFTER_SAVE' => false,
		'RELOAD_PAGE_AFTER_SAVE' => true,
	];
	if ($landing > 0)
	{
		$componentParams['POPUP_COMPONENT_PARAMS']['LANDING_ID'] = $landing;
		$componentParams['POPUP_COMPONENT_PARAMS']['PAGES']['PAGE_URL_LANDING_EDIT'] = $editPage;
		$componentParams['POPUP_COMPONENT_PARAMS']['PAGES']['PAGE_URL_LANDING_DESIGN'] = $designPage;
	}

	$APPLICATION->includeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		$componentParams,
	);
}
elseif ($cmp == 'folder_edit')
{
	$APPLICATION->IncludeComponent(
		'bitrix:landing.folder_edit',
		'.default',
		array(
			'TYPE' => $type,
			'FOLDER_ID' => $request->get($actionFolder),
			'ACTION_FOLDER' => $actionFolder,
			'PAGE_URL_LANDING_EDIT' => $editPage,
			'PAGE_URL_LANDING_VIEW' => $viewPage
		),
		$component
	);
}
elseif ($cmp == 'cookies_edit')
{
	$APPLICATION->IncludeComponent(
		'bitrix:landing.site_cookies',
		'.default',
		array(
			'TYPE' => $type,
			'SITE_ID' => $siteId
		),
		$component
	);
}
else
{
	$APPLICATION->IncludeComponent(
		'bitrix:landing.landings',
		'.default',
		[
			'TYPE' => $type,
			'SITE_ID' => $siteId,
			'ACTION_FOLDER' => $actionFolder,
			'PAGE_URL_LANDING_EDIT' => $editPage,
			'PAGE_URL_LANDING_VIEW' => $viewPage,
			'PAGE_URL_LANDING_DESIGN' => $designPage,
			'PAGE_URL_FOLDER_EDIT' => $editFolder,
			'PAGE_URL_LANDING_SETTINGS' => $landingSettings,
		],
		false
	);
}

echo '</div>';

if ($isFrame)
{
	include $server->getDocumentRoot() .
			'/bitrix/modules/landing/install/components/bitrix/landing.start/templates/.default/slider_footer.php';
}
else if (!$isAjax)
{
	require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin_before.php');
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin_after.php');
