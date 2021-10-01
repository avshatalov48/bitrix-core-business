<?php

namespace Bitrix\Sale\Repository;

use Bitrix\Sale;

/**
 * Class PaymentRepository
 * @package Bitrix\Sale\Repository
 * @internal
 */
final class PaymentRepository
{
	/** @var PaymentRepository */
	private static $instance;

	/**
	 * PaymentRepository constructor.
	 */
	private function __construct()
	{}

	/**
	 * @return PaymentRepository
	 */
	public static function getInstance(): PaymentRepository
	{
		if (is_null(static::$instance))
		{
			static::$instance = new self();
		}

		return static::$instance;
	}

	/**
	 * @param int $id
	 * @return Sale\Payment|null
	 */
	public function getById(int $id): ?Sale\Payment
	{
		/** @var Sale\Payment $paymentClass */
		$paymentClass = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER)->getPaymentClassName();

		$paymentRow = $paymentClass::getList([
			'select' => ['ID', 'ORDER_ID'],
			'filter' => [
				'=ID' => $id
			]
		])->fetch();
		if (!$paymentRow)
		{
			return null;
		}

		return static::getInstance()->getByRow($paymentRow);
	}

	/**
	 * @param array $ids
	 * @return array
	 */
	public function getByIds(array $ids): array
	{
		$result = [];

		/** @var Sale\Payment $paymentClass */
		$paymentClass = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER)->getPaymentClassName();

		$paymentList = $paymentClass::getList([
			'select' => ['ID', 'ORDER_ID'],
			'filter' => [
				'=ID' => $ids
			]
		]);

		while ($paymentRow = $paymentList->fetch())
		{
			$payment = static::getInstance()->getByRow($paymentRow);
			if (is_null($payment))
			{
				continue;
			}

			$result[] = $payment;
		}

		return $result;
	}

	/**
	 * @param array $paymentRow
	 * @return Sale\Payment|null
	 */
	private function getByRow(array $paymentRow): ?Sale\Payment
	{
		$orderClassName = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER)->getOrderClassName();

		/** @var Sale\Order $orderClassName */
		$order = $orderClassName::load($paymentRow['ORDER_ID']);
		if ($order === null)
		{
			return null;
		}

		$paymentCollection = $order->getPaymentCollection();

		/** @var Sale\Payment $payment */
		foreach ($paymentCollection as $payment)
		{
			if ($payment->getId() !== (int)$paymentRow['ID'])
			{
				continue;
			}

			return $payment;
		}

		return null;
	}
}
