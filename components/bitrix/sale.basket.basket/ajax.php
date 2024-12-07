<?php

/** @global CMain $APPLICATION */

const STOP_STATISTICS = true;
const NO_AGENT_CHECK = true;
const NOT_CHECK_PERMISSIONS = true;

use Bitrix\Main\Loader;

if (isset($_REQUEST['site_id']) && is_string($_REQUEST['site_id']))
{
	$siteID = trim($_REQUEST['site_id']);
	if ($siteID !== '' && preg_match('/^[a-z0-9_]{2}$/i', $siteID) === 1)
	{
		define('SITE_ID', $siteID);
	}
}

if (isset($_REQUEST['site_template_id']) && is_string($_REQUEST['site_template_id']))
{
	$siteTemplateId = trim($_REQUEST['site_template_id']);

	if ($siteTemplateId !== '' && preg_match('/^[a-z0-9_]+$/i', $siteTemplateId))
	{
		define('SITE_TEMPLATE_ID', $siteTemplateId);
	}
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();

if (!check_bitrix_sessid() || !$request->isPost())
	return;

if (!Loader::includeModule('sale') || !Loader::includeModule('catalog'))
	return;

$params = array();
$template = '';

if ($request->get('via_ajax') === 'Y')
{
	$signer = new \Bitrix\Main\Security\Sign\Signer;
	try
	{
		$params = $signer->unsign($request->get('signedParamsString'), 'sale.basket.basket');
		$params = unserialize(base64_decode($params), ['allowed_classes' => false]);
	}
	catch (\Bitrix\Main\Security\Sign\BadSignatureException $e)
	{
		die('Bad signature.');
	}

	try
	{
		$template = $signer->unsign($request->get('template'), 'sale.basket.basket');
	}
	catch (Exception $e)
	{
		$template = '.default';
	}
}

global $APPLICATION;

$APPLICATION->IncludeComponent(
	'bitrix:sale.basket.basket',
	$template,
	$params
);
