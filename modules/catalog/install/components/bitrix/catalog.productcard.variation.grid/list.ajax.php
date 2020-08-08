<?php

use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Main\Loader;

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

/**
 * @global CUser $USER
 */

global $USER, $APPLICATION;

if (!isset($USER) || !is_object($USER) || !$USER->IsAuthorized() || !check_bitrix_sessid())
{
	die();
}

if (!Loader::includeModule('catalog'))
{
	die();
}

$APPLICATION->ShowAjaxHead();
CUtil::JSPostUnescape();

$componentName = 'bitrix:catalog.productcard.variation.grid';
$request = Bitrix\Main\Context::getCurrent()->getRequest();

if ($request->get('signedParameters'))
{
	$params = ParameterSigner::unsignParameters($componentName, $request->get('signedParameters'));
	$params['IBLOCK_ID'] = (int)($params['IBLOCK_ID'] ?? 0);
	$params['PRODUCT_ID'] = (int)($params['PRODUCT_ID'] ?? 0);
}
else
{
	$params = [];
}

$componentClass = CBitrixComponent::includeComponentClass($componentName);
/** @var \CatalogProductVariationGridComponent $component */
$component = new $componentClass();

if ($component->initComponent($componentName))
{
	$component->arParams = $component->onPrepareComponentParams($params);

	if ($component->isAjaxGridAction($request))
	{
		$component->doAjaxGridAction($request);
	}

	$component->executeComponent();
}

CMain::FinalActions();
die();