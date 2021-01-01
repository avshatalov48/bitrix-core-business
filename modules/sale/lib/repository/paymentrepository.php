<?php

namespace Bitrix\Sale\Repository;

use Bitrix\Sale;
use Bitrix\Main;

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
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getById(int $id): ? Sale\Payment
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Payment $paymentClass */
		$paymentClass = $registry->getPaymentClassName();

		$paymentRow = $paymentClass::getList([
			'select' => ['ORDER_ID'],
			'filter' => [
				'=ID' => $id
			]
		])->fetch();
		if (!$paymentRow)
		{
			return null;
		}

		$orderClassName = $registry->getOrderClassName();

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
			if ($payment->getId() !== $id)
			{
				continue;
			}

			return $payment;
		}

		return null;
	}
}
