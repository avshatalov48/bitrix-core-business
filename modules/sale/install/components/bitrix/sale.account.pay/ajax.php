<?php

define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define("BX_SECURITY_SHOW_MESSAGE", true);
define('NOT_CHECK_PERMISSIONS', true);

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
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);
if (!check_bitrix_sessid())
{
	die();
}
$signer = new \Bitrix\Main\Security\Sign\Signer;
try
{
	$params = $signer->unsign(base64_decode($request->get('signedParamsString')), 'sale.account.pay');
	$params = unserialize($params);
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
$orderPayment->includeComponent($params["TEMPLATE_PATH"], $params, null);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
?>