<?php

use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\AccessController;

define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

$siteID = isset($_REQUEST['site']) ? mb_substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site']), 0, 2) : '';
if ($siteID !== '')
{
	define('SITE_ID', $siteID);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
Header('Content-Type: text/html; charset='.LANG_CHARSET);

if (!Loader::includeModule('catalog') || !check_bitrix_sessid())
{
	die();
}

if (
	!AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ)
	|| !AccessController::getCurrent()->check(ActionDictionary::ACTION_STORE_VIEW)
)
{
	die();
}

global $APPLICATION;
$APPLICATION->ShowAjaxHead();

$componentName = 'bitrix:catalog.store.document.product.list';
$request = Context::getCurrent()->getRequest();

$params = [];

if ($request->get('signedParameters'))
{
	$params = ParameterSigner::unsignParameters($componentName, $request->get('signedParameters'));
}

$params['REQUEST'] = $request->getValues();

$useProductsFromRequest = filter_var($request->get('useProductsFromRequest'), FILTER_VALIDATE_BOOLEAN);
if ($useProductsFromRequest !== false)
{
	$params['PRODUCTS'] = $request->get('products');
}
else
{
	unset($params['PRODUCTS']);
}

$APPLICATION->IncludeComponent(
	$componentName,
	'.default',
	$params,
	null,
	[
		'HIDE_ICONS' => 'Y'
	]
);

CMain::FinalActions();