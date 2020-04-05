<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Sale;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class CreditCheck
 * @package Bitrix\Sale\Cashbox
 */

class CreditCheck extends Check
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'credit';
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Main\Localization\Loc::getMessage('SALE_CASHBOX_CREDIT_NAME');
	}

	/**
	 * @return string
	 */
	public static function getCalculatedSign()
	{
		return static::CALCULATED_SIGN_INCOME;
	}

	/**
	 * @return string
	 */
	public static function getSupportedEntityType()
	{
		return static::SUPPORTED_ENTITY_TYPE_SHIPMENT;
	}

	/**
	 * @param array $entities
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectNotFoundException
	 */
	public function setEntities(array $entities)
	{
		parent::setEntities($entities);

		foreach ($entities as $entity)
		{
			if ($entity instanceof Sale\Shipment)
			{
				$this->setField('SHIPMENT_ID', $entity->getId());

				if (!$this->getField('CURRENCY'))
				{
					$this->setField('CURRENCY', $entity->getOrder()->getCurrency());
				}

				$sum = $entity->getPrice();
				$shipmentItemCollection = $entity->getShipmentItemCollection();

				/** @var Sale\ShipmentItem $item */
				foreach ($shipmentItemCollection as $item)
				{
					$basketItem = $item->getBasketItem();
					$sum += Sale\PriceMaths::roundPrecision($item->getQuantity() * $basketItem->getPrice());
				}

				$this->setField('SUM', $sum);
			}
		}
	}

	/**
	 * @return array
	 */
	protected function extractDataInternal()
	{
		$result = parent::extractDataInternal();

		$totalSum = 0;
		if (isset($result['PRODUCTS']))
		{
			foreach ($result['PRODUCTS'] as $item)
				$totalSum += $item['SUM'];
		}

		if (isset($result['DELIVERY']))
		{
			foreach ($result['DELIVERY'] as $item)
				$totalSum += $item['SUM'];
		}

		$result['PAYMENTS'] = array(
			array(
				'TYPE' => static::PAYMENT_TYPE_CREDIT,
				'SUM' => $totalSum
			)
		);

		$result['TOTAL_SUM'] = $totalSum;

		return $result;
	}

	/**
	 * @return string
	 */
	public static function getSupportedRelatedEntityType()
	{
		return static::SUPPORTED_ENTITY_TYPE_NONE;
	}

}