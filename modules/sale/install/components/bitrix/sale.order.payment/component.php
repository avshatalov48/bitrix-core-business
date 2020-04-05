<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->setFramemode(false);

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}

global $APPLICATION, $USER;

$APPLICATION->RestartBuffer();

$bUseAccountNumber = \Bitrix\Sale\Integration\Numerator\NumeratorOrder::isUsedNumeratorForOrder();

$ORDER_ID = urldecode(urldecode($_REQUEST["ORDER_ID"]));
$paymentId = isset($_REQUEST["PAYMENT_ID"]) ? $_REQUEST["PAYMENT_ID"] : '';
$hash = isset($_REQUEST["HASH"]) ? $_REQUEST["HASH"] : null;

$registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);
/** @var \Bitrix\Sale\Order $orderClassName */
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
				"ACCOUNT_NUMBER" => $ORDER_ID
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
		$realOrderId = intval($ORDER_ID);
	}

	$checkedBySession = in_array($realOrderId, $_SESSION['SALE_ORDER_ID']);
}

if ($bUseAccountNumber && !$arOrder)
{
	$arFilter = array(
		"LID" => SITE_ID,
		"ACCOUNT_NUMBER" => $ORDER_ID
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
		"ID" => $ORDER_ID
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
	/** @var \Bitrix\Sale\Payment|null $paymentItem */
	$paymentItem = null;

	/** @var \Bitrix\Sale\Order $order */
	$order = $orderClassName::load($arOrder['ID']);

	if ($order)
	{
		$guestStatuses = \Bitrix\Main\Config\Option::get("sale", "allow_guest_order_view_status", "");
		$guestStatuses = (strlen($guestStatuses) > 0) ?  unserialize($guestStatuses) : array();

		if (
			!\Bitrix\Sale\OrderStatus::isAllowPay($order->getField('STATUS_ID'))
			||
			(
				!empty($hash)
				&& (
					$order->getHash() !== $hash
					||
					!\Bitrix\Sale\Helpers\Order::isAllowGuestView($order)
				)
			)
		)
		{
			LocalRedirect('/');
			return;
		}

		/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
		$paymentCollection = $order->getPaymentCollection();

		if ($paymentCollection)
		{
			if ($paymentId)
			{
				$data = \Bitrix\Sale\PaySystem\Manager::getIdsByPayment($paymentId);

				if ($data[1] > 0)
					$paymentItem = $paymentCollection->getItemById($data[1]);
			}

			if ($paymentItem === null)
			{
				/** @var \Bitrix\Sale\Payment $item */
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
				$service = \Bitrix\Sale\PaySystem\Manager::getObjectById($paymentItem->getPaymentSystemId());
				if ($service)
				{
					$context = \Bitrix\Main\Application::getInstance()->getContext();

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