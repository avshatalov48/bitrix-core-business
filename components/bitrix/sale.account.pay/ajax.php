<?php

const STOP_STATISTICS = true;
const NO_KEEP_STATISTIC = "Y";
const NO_AGENT_STATISTIC = "Y";
const DisableEventsCheck = true;
const BX_SECURITY_SHOW_MESSAGE = true;
const NOT_CHECK_PERMISSIONS = true;

$siteId = isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID']) ? $_REQUEST['SITE_ID'] : '';
$siteId = mb_substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
if (!check_bitrix_sessid())
{
	die();
}
$signer = new \Bitrix\Main\Security\Sign\Signer;
try
{
	$params = $signer->unsign(base64_decode($request->get('signedParamsString')), 'sale.account.pay');
	$params = unserialize($params, ['allowed_classes' => false]);
	$params['AJAX_DISPLAY'] = "Y";
}
catch (\Bitrix\Main\Security\Sign\BadSignatureException $e)
{
	die();
}

CBitrixComponent::includeComponentClass("bitrix:sale.account.pay");
$templateName = $request->get("templateName");
if(empty($templateName))
{
	$templateName = "";
}

$orderPayment = new SaleAccountPay();
$orderPayment->initComponent('bitrix:sale.account.pay');
$orderPayment->includeComponent($templateName, $params, null);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
