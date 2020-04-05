<?php

define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define("BX_SECURITY_SHOW_MESSAGE", true);

$siteId = isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID']) ? $_REQUEST['SITE_ID'] : '';
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);

if (!check_bitrix_sessid() && !$request->isPost())
{
	die();
}

$orderData = $request->get("orderData");

$params['ACCOUNT_NUMBER'] = $orderData['order'];
$params['PAYMENT_NUMBER'] = $orderData['payment'];
$params['PATH_TO_PAYMENT'] = strlen($orderData['path_to_payment']) > 0 ? htmlspecialcharsbx($orderData['path_to_payment']) : "";
$params['REFRESH_PRICES'] = ($orderData['refresh_prices'] === 'Y') ? 'Y' : 'N';
if (CBXFeatures::IsFeatureEnabled('SaleAccounts'))
{
	$params['ALLOW_INNER'] = $orderData['allow_inner'];
	$params['ONLY_INNER_FULL'] = $orderData['only_inner_full'];
}
else
{
	$params['ALLOW_INNER'] = "N";
	$params['ONLY_INNER_FULL'] = "Y";
}
	
CBitrixComponent::includeComponentClass("bitrix:sale.order.payment.change");

$orderPayment = new SaleOrderPaymentChange();
$orderPayment->initComponent('bitrix:sale.order.payment.change');
$orderPayment->includeComponent("", $params, null);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
?>