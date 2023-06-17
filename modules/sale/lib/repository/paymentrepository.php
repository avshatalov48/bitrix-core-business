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
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param int $id
	 * @return Sale\Payment|null
	 */
	public function getById(int $id): ?Sale\Payment
	{
		$paymentList = $this->getList([
			'select' => ['ID', 'ORDER_ID'],
			'filter' => [
				'=ID' => $id
			],
		]);

		return $paymentList[0] ?? null;
	}

	/**
	 * @param array $ids
	 * @return array
	 */
	public function getByIds(array $ids): array
	{
		return $this->getList([
			'select' => ['ID', 'ORDER_ID'],
			'filter' => [
				'@ID' => $ids
			],
		]);
	}

	/**
	 * @param array $parameters
	 * @return array
	 */
	public function getList(array $parameters): array
	{
		$result = [];

		/** @var Sale\Payment $paymentClass */
		$paymentClass = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER)->getPaymentClassName();

		$paymentList = $paymentClass::getList($parameters);
		while ($paymentRow = $paymentList->fetch())
		{
			$payment = $this->getByRow($paymentRow);
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

		$paymentId = (int)$paymentRow['ID'];
		if ($paymentId > 0)
		{
			/** @var Sale\Payment $payment */
			$payment = $order->getPaymentCollection()->getItemById($paymentRow['ID']);

			return $payment;
		}

		return null;
	}
}
