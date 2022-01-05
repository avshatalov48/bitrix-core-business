<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var \CMain $APPLICATION */
/** @var array $arParams */

if (in_array($this->getTemplatePage(), ['site_domain', 'site_domain_switch', 'site_cookies', 'notes']))
{
	\CMain::finalActions();
}

use \Bitrix\Main\Localization\Loc;

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

Loc::loadMessages(dirname(__FILE__) . '/template.php');

$templatePage = $this->getTemplatePage();
$disableFrame = $templatePage === 'landing_view';

// iframe footer
if ($request->get('IFRAME') == 'Y' && !$disableFrame)
{
	include 'slider_footer.php';
	\CMain::finalActions();
	die();
}
// ajax
elseif ($request->get('IS_AJAX') == 'Y')
{
	\CMain::finalActions();
	die();
}

// below this line only menu
if ($arParams['SHOW_MENU'] != 'Y')
{
	return;
}

// menu items
$menuItems = [
	[
		'TEXT' => ($title = Loc::getMessage('LANDING_TPL_MENU_SITES_' . $arParams['TYPE']))
					? $title
					: Loc::getMessage('LANDING_TPL_MENU_SITES'),
		'URL' => $arParams['PAGE_URL_SITES'],
		'ID' => 'default',
		'IS_ACTIVE' => 0,
		'COUNTER' => 0,
		'COUNTER_ID' => 'default'
	]
];
if (\Bitrix\Landing\Site\Type::isPublicScope())
{
	$menuItems[] = [
		'TEXT' => Loc::getMessage('LANDING_TPL_MENU_FORMS'),
		'URL' => SITE_DIR . 'crm/webform/',
		'ID' => 'webform',
		'IS_ACTIVE' => 0,
		'COUNTER' => 0,
		'COUNTER_ID' => 'webform'
	];
	$menuItems[] = [
		'TEXT' => Loc::getMessage('LANDING_TPL_MENU_TRACKING'),
		'URL' => SITE_DIR . 'crm/tracking/',
		'ID' => 'tracking',
		'IS_ACTIVE' => 0,
		'COUNTER' => 0,
		'COUNTER_ID' => 'tracking'
	];
	$menuItems[] = [
		'TEXT' => Loc::getMessage('LANDING_TPL_MENU_MARKETING'),
		'URL' => SITE_DIR . 'marketing/?marketing_title=Y',
		'ID' => 'marketing',
		'IS_ACTIVE' => 0,
		'COUNTER' => 0,
		'COUNTER_ID' => 'marketing'
	];
}
$menuItems[] = [
	'TEXT' => Loc::getMessage('LANDING_TPL_MENU_AGREEMENT'),
	'URL' => '#',
	'ON_CLICK' => 'landingAgreementPopup();',
	'ID' => 'agreement',
	'IS_ACTIVE' => 0,
	'COUNTER' => 0,
	'COUNTER_ID' => 'agreement'
];
if (\Bitrix\Landing\Rights::isAdmin())
{
	$menuItems[] = [
		'TEXT' => Loc::getMessage('LANDING_TPL_MENU_RIGHTS'),
		'URL' => $arParams['PAGE_URL_ROLES'],
		'ID' => 'roles',
		'IS_ACTIVE' => 0,
		'COUNTER' => 0,
		'COUNTER_ID' => 'roles',
		'PAGE' => ['roles', 'role_edit'],
		'IS_DISABLED' => true
	];
}
$page = $this->getTemplatePage();
$menuItems = array_values($menuItems);

// set active menu item
$setActive = false;
foreach ($menuItems as &$menuItem)
{
	if (
		isset($menuItem['PAGE']) &&
		is_array($menuItem['PAGE']) &&
		in_array($page, $menuItem['PAGE'])
	)
	{
		$menuItem['IS_ACTIVE'] = 1;
		$setActive = true;
	}
}
unset($menuItem);
if (!$setActive)
{
	$menuItems[0]['IS_ACTIVE'] = 1;
}

// place menu
$this->getTemplate()->setViewTarget('above_pagetitle', 100);
$menuId = 'sites';
$APPLICATION->IncludeComponent(
	'bitrix:main.interface.buttons',
	'',
	array(
		'ID' => 'sites_v2',
		'ITEMS' => $menuItems
	)
);
$this->getTemplate()->endViewTarget();

if ($templatePage === 'landing_view')
{
	return;
}

\Bitrix\Main\UI\Extension::load(['sidepanel']);
?>
<script>
	BX.ready(function()
	{
		BX.SidePanel.Instance.bindAnchors({
			rules: [
				{
					condition: ['<?= SITE_DIR?>marketing/'],
					options: {
						allowChangeHistory: false
					}
				}
			]
		});
	});
</script>
