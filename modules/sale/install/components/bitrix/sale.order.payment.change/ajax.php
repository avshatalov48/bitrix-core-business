<?php

const STOP_STATISTICS = true;
const NO_KEEP_STATISTIC = 'Y';
const NO_AGENT_STATISTIC = 'Y';
const DisableEventsCheck = true;
const BX_SECURITY_SHOW_MESSAGE = true;
const NOT_CHECK_PERMISSIONS = true;

$siteId = isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID']) ? $_REQUEST['SITE_ID'] : '';
$siteId = mb_substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();

if (!check_bitrix_sessid() && !$request->isPost())
{
	die();
}

$orderData = $request->getPostList()->toArray();
$templateName = $request->get("templateName");
if(empty($templateName))
{
	$templateName = "";
}

$params['ACCOUNT_NUMBER'] = (string)($orderData['accountNumber'] ?? '');
$params['AJAX_DISPLAY'] = 'Y';
$params['PAYMENT_NUMBER'] = (string)($orderData['paymentNumber'] ?? '');
$params['NEW_PAY_SYSTEM_ID'] = (string)($orderData['paySystemId'] ?? '');
$params['ALLOW_INNER'] = (string)($orderData['inner'] ?? '');
$params['ONLY_INNER_FULL'] = (string)($orderData['onlyInnerFull'] ?? '');
$params['PATH_TO_PAYMENT'] = htmlspecialcharsbx(trim((string)($orderData['pathToPayment'] ?? '')));
$params['REFRESH_PRICES'] = ($orderData['refreshPrices'] ?? 'N') === 'Y' ? 'Y' : 'N';
$params['RETURN_URL'] = trim((string)($orderData['returnUrl'] ?? ''));
$params['INNER_PAYMENT_SUM'] = (string)($orderData['paymentSum'] ?? 0);

CBitrixComponent::includeComponentClass("bitrix:sale.order.payment.change");

$orderPayment = new SaleOrderPaymentChange();
$orderPayment->initComponent('bitrix:sale.order.payment.change');
$orderPayment->includeComponent($templateName, $params, null);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
