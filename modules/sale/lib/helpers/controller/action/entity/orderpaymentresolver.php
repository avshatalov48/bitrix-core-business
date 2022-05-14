<?php

namespace Bitrix\Sale\Helpers\Controller\Action\Entity;

use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;

/**
 * Class helps to dynamically find or create order payments
 */
final class OrderPaymentResolver
{
	/**
	 * Finds first payment with sum equals order's total sum.
	 * If payment not found - creates one with specific payment system.
	 * You can skip specify payment system and use default (usually cash).
	 *
	 * @param int $orderId
	 * @param int|null $paySystemId
	 * @return Payment|null
	 */
	public static function findOrCreatePaymentEqualOrderSum(int $orderId, ?int $paySystemId = null): ?Payment
	{
		if ($payment = self::findPaymentEqualOrderSum($orderId))
		{
			return $payment;
		}

		$order = Order::load($orderId);
		if (!$order)
		{
			return null;
		}

		if (!$service = self::buildPaySystemService($order, $paySystemId))
		{
			return null;
		}

		$paymentSum = $order->getPrice();
		$paymentCollection = $order->getPaymentCollection();
		$payment = $paymentCollection->createItem($service);
		$result = $payment->setField('SUM', $paymentSum);

		$result = $result->isSuccess() ? $order->save() : $result;

		if ($result->isSuccess())
		{
			return $payment;
		}

		return null;
	}

	/**
	 * Finds first payment with sum equals order's total sum.
	 *
	 * @param int $orderId
	 * @return Payment|null
	 */
	private static function findPaymentEqualOrderSum(int $orderId): ?Payment
	{
		$order = Order::load($orderId);
		if (!$order)
		{
			return null;
		}

		$paymentSum = $order->getPrice();

		$filter = [
			'ORDER_ID' => $order->getId(),
			'SUM' => $paymentSum,
		];

		$paymentRow = Payment::getList([
			'filter' => $filter,
			'select' => ['ORDER_ID', 'ID'],
			'limit' => 1
		]);
		if ($paymentData = $paymentRow->fetch())
		{
			$paymentId = (int)$paymentData['ID'];
			/** @var ?Payment $payment */
			$payment = $order->getPaymentCollection()->getItemById($paymentId);

			return $payment;
		}
		return null;
	}

	/**
	 * Factory method creates pay system service object
	 *
	 * @param Order $order
	 * @param int|null $paySystemId
	 * @return PaySystem\Service|null
	 */
	private static function buildPaySystemService(Order $order, ?int $paySystemId = null): ?PaySystem\Service
	{
		if ($paySystemId === null)
		{
			$paySystemId = self::getDefaultPaySystemId($order);
		}

		return PaySystem\Manager::getObjectById($paySystemId);
	}

	/**
	 * Finds default payment system for specific order, respecting order restrictions.
	 * Returns cash if found, or first found otherwise.
	 *
	 * @param Order $order
	 * @return int
	 */
	private static function getDefaultPaySystemId(Order $order): int
	{
		$paySystem = [];
		$paySystemList = PaySystem\Manager::getListWithRestrictionsByOrder($order);

		foreach ($paySystemList as $item)
		{
			if ($item['ACTION_FILE'] === 'cash')
			{
				$paySystem = $item;
				break;
			}
		}

		if (!$paySystem)
		{
			$paySystem = current($paySystemList);
		}

		return (int)$paySystem['ID'];
	}
}
