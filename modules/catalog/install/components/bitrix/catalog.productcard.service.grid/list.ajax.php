<?php

use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;

const NO_KEEP_STATISTIC = 'Y';
const NO_AGENT_STATISTIC = 'Y';
const NO_AGENT_CHECK = true;
const PUBLIC_AJAX_MODE = true;
const DisableEventsCheck = true;

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

$componentName = 'bitrix:catalog.productcard.service.grid';
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

$rows = $request->getPost('rows');
if (is_array($rows))
{
	$params['~ROWS'] = $rows;
}

$componentClass = CBitrixComponent::includeComponentClass($componentName);
/** @var \CatalogProductServiceGridComponent $component */
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
$errors = $component->getErrors();
if (!empty($errors))
{
	$messages = [];

	foreach ($errors as $error)
	{
		$messages[] = [
			'TYPE' => Bitrix\Main\Grid\MessageType::ERROR,
			'TEXT' => $error->getMessage(),
		];
	}

	$APPLICATION->RestartBuffer();
	CMain::FinalActions(Json::encode(['messages' => $messages]));
}
CMain::FinalActions();
