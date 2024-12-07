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

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();

if (!check_bitrix_sessid() && !$request->isPost())
{
	die();
}

$orderData = $request->get("orderData");
$templateName = $request->get("templateName");
if(empty($templateName))
{
	$templateName = "";
}

$params = [];
$params['ACCOUNT_NUMBER'] = (string)($orderData['order'] ?? '');
$params['PAYMENT_NUMBER'] = (string)($orderData['payment'] ?? '');
$params['PATH_TO_PAYMENT'] = htmlspecialcharsbx((string)($orderData['path_to_payment'] ?? ''));
$params['REFRESH_PRICES'] = ($orderData['refresh_prices'] ?? 'N') === 'Y' ? 'Y' : 'N';
$params['RETURN_URL'] = (string)($orderData['return_url'] ?? '');
if (CBXFeatures::IsFeatureEnabled('SaleAccounts'))
{
	$params['ALLOW_INNER'] = (string)($orderData['allow_inner'] ?? '');
	$params['ONLY_INNER_FULL'] = (string)($orderData['only_inner_full'] ?? '');
}
else
{
	$params['ALLOW_INNER'] = 'N';
	$params['ONLY_INNER_FULL'] = 'Y';
}

CBitrixComponent::includeComponentClass("bitrix:sale.order.payment.change");

$orderPayment = new SaleOrderPaymentChange();
$orderPayment->initComponent('bitrix:sale.order.payment.change');
$orderPayment->includeComponent($templateName, $params, null);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
