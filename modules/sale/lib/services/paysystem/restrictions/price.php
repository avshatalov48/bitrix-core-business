<?php

namespace Bitrix\Sale\Services\PaySystem\Restrictions;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Order;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\Services\Base;
use Bitrix\Sale\Payment;

Loc::loadMessages(__FILE__);

class Price extends Base\Restriction
{
	/**
	 * @param $params
	 * @param array $restrictionParams
	 * @param int $serviceId
	 * @return bool
	 */
	public static function check($params, array $restrictionParams, $serviceId = 0)
	{
		$maxValue = static::getPrice($params, $restrictionParams['MAX_VALUE']);
		$minValue = static::getPrice($params, $restrictionParams['MIN_VALUE']);
		$price = (float)$params['PRICE_PAYMENT'];

		if ($maxValue > 0 && $minValue > 0)
		{
			return ($maxValue >= $price) && ($minValue <= $price);
		}

		if ($maxValue > 0)
		{
			return $maxValue >= $price;
		}

		if ($minValue >= 0)
		{
			return $minValue <= $price;
		}

		return false;
	}

	/**
	 * @param Entity $entity
	 * @return array
	 */
	protected static function extractParams(Entity $entity)
	{
		$orderPrice = null;
		$paymentPrice = null;

		if ($entity instanceof Payment)
		{
			/** @var PaymentCollection $collection */
			$collection = $entity->getCollection();
			/** @var Order $order */
			$order = $collection->getOrder();

			$orderPrice = $order->getPrice();
			$paymentPrice = $entity->getField('SUM');
		}

		return array(
			'PRICE_PAYMENT' => $paymentPrice,
			'PRICE_ORDER' => $orderPrice,
		);
	}

	/**
	 * @param $entityParams
	 * @param $paramValue
	 * @return float
	 */
	protected static function getPrice($entityParams, $paramValue)
	{
		return (float)$paramValue;
	}

	/**
	 * @return mixed
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage('SALE_PS_RESTRICTIONS_BY_PRICE');
	}

	/**
	 * @return mixed
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage('SALE_PS_RESTRICTIONS_BY_PRICE_DESC');
	}

	public static function getOnApplyErrorMessage(): string
	{
		return Loc::getMessage('SALE_PS_RESTRICTIONS_BY_PRICE_ON_APPLY_ERROR_MSG');
	}

	/**
	 * @param $entityId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getParamsStructure($entityId = 0)
	{
		return array(
			"MIN_VALUE" => array(
				'TYPE' => 'NUMBER',
				'DEFAULT' => 0,
				'LABEL' => Loc::getMessage("SALE_PS_RESTRICTIONS_BY_PRICE_TYPE_MORE")
			),
			"MAX_VALUE" => array(
				'TYPE' => 'NUMBER',
				'DEFAULT' => 0,
				'LABEL' => Loc::getMessage("SALE_PS_RESTRICTIONS_BY_PRICE_TYPE_LESS")
			)
		);
	}

	/**
	 * @param Payment $payment
	 * @param $params
	 * @return array
	 * @throws ArgumentTypeException
	 */
	public static function getRange(Payment $payment, $params)
	{
		if ($payment instanceof Payment)
		{
			$p = static::extractParams($payment);
			return array(
				'MAX' => static::getPrice($p, $params['MAX_VALUE']),
				'MIN' => static::getPrice($p, $params['MIN_VALUE']),
			);
		}

		throw new ArgumentTypeException('');
	}

	/**
	 * @param $mode
	 * @return int
	 */
	public static function getSeverity($mode)
	{
		return Manager::SEVERITY_SOFT;
	}
}