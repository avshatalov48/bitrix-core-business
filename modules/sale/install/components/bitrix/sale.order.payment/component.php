<?php

use Bitrix\Main,
	Bitrix\Sale;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

$this->setFramemode(false);

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}

global $APPLICATION, $USER;

$APPLICATION->RestartBuffer();

$bUseAccountNumber = Sale\Integration\Numerator\NumeratorOrder::isUsedNumeratorForOrder();

$orderId = urldecode(urldecode($_REQUEST["ORDER_ID"]));
$paymentId = $_REQUEST["PAYMENT_ID"] ?? '';
$hash = $_REQUEST["HASH"] ?? null;
$returnUrl = $_REQUEST["RETURN_URL"] ?? '';

$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
/** @var Sale\Order $orderClassName */
$orderClassName = $registry->getOrderClassName();

$arOrder = false;
$checkedBySession = false;
if (!$USER->IsAuthorized() && is_array($_SESSION['SALE_ORDER_ID']) && empty($hash))
{
	$realOrderId = 0;

	if ($bUseAccountNumber)
	{
		$dbRes = $orderClassName::getList([
			'filter' => [
				"LID" => SITE_ID,
				"ACCOUNT_NUMBER" => $orderId
			],
			'order' => [
				"DATE_UPDATE" => "DESC"
			]
		]);
		$arOrder = $dbRes->fetch();
		if ($arOrder)
		{
			$realOrderId = intval($arOrder["ID"]);
		}
	}
	else
	{
		$realOrderId = intval($orderId);
	}

	$checkedBySession = in_array($realOrderId, $_SESSION['SALE_ORDER_ID']);
}

if ($bUseAccountNumber && !$arOrder)
{
	$arFilter = array(
		"LID" => SITE_ID,
		"ACCOUNT_NUMBER" => $orderId
	);

	if (empty($hash))
	{
		$arFilter["USER_ID"] = intval($USER->GetID());
	}

	$dbRes = $orderClassName::getList([
		'filter' => $arFilter,
		'order' => [
			"DATE_UPDATE" => "DESC"
		]
	]);

	$arOrder = $dbRes->fetch();
}

if (!$arOrder)
{
	$arFilter = array(
		"LID" => SITE_ID,
		"ID" => $orderId
	);
	if (!$checkedBySession && empty($hash))
		$arFilter["USER_ID"] = intval($USER->GetID());

	$dbRes = $orderClassName::getList([
		'filter' => $arFilter,
		'order' => [
			"DATE_UPDATE" => "DESC"
		]
	]);

	$arOrder = $dbRes->fetch();
}

if ($arOrder)
{
	/** @var Sale\Payment|null $paymentItem */
	$paymentItem = null;

	/** @var Sale\Order $order */
	$order = $orderClassName::load($arOrder['ID']);

	if ($order)
	{
		$guestStatuses = Main\Config\Option::get("sale", "allow_guest_order_view_status", "");
		$guestStatuses = ($guestStatuses <> '') ?  unserialize($guestStatuses) : array();

		if (
			!Sale\OrderStatus::isAllowPay($order->getField('STATUS_ID'))
			||
			(
				!empty($hash)
				&& (
					$order->getHash() !== $hash
					||
					!Sale\Helpers\Order::isAllowGuestView($order)
				)
			)
		)
		{
			LocalRedirect('/');
			return;
		}

		/** @var Sale\PaymentCollection $paymentCollection */
		$paymentCollection = $order->getPaymentCollection();

		if ($paymentCollection)
		{
			if ($paymentId)
			{
				$data = Sale\PaySystem\Manager::getIdsByPayment($paymentId);

				if ($data[1] > 0)
					$paymentItem = $paymentCollection->getItemById($data[1]);
			}

			if ($paymentItem === null)
			{
				/** @var Sale\Payment $item */
				foreach ($paymentCollection as $item)
				{
					if (!$item->isInner() && !$item->isPaid())
					{
						$paymentItem = $item;
						break;
					}
				}
			}

			if ($paymentItem !== null)
			{
				$service = Sale\PaySystem\Manager::getObjectById($paymentItem->getPaymentSystemId());
				if ($service)
				{
					$context = Main\Application::getInstance()->getContext();

					if ($returnUrl)
					{
						$service->getContext()->setUrl($returnUrl);
					}

					$result = $service->initiatePay($paymentItem, $context->getRequest());
					if (!$result->isSuccess())
					{
						echo implode('<br>', $result->getErrorMessages());
					}

					if($service->getField('ENCODING') != '')
					{
						define("BX_SALE_ENCODING", $service->getField('ENCODING'));

						AddEventHandler("main", "OnEndBufferContent", "ChangeEncoding");
						function ChangeEncoding($content)
						{
							global $APPLICATION;
							header("Content-Type: text/html; charset=".BX_SALE_ENCODING);
							$content = $APPLICATION->ConvertCharset($content, SITE_CHARSET, BX_SALE_ENCODING);
							$content = str_replace("charset=".SITE_CHARSET, "charset=".BX_SALE_ENCODING, $content);
						}
					}
				}
			}
		}
	}
}
else
{
	ShowError(GetMessage('SOP_ORDER_NOT_FOUND'));
}