<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Sale\Internals\CollectableEntity;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class PayableShipmentItem
 * @package Bitrix\Sale
 */
class PayableShipmentItem extends PayableItem
{
	public static function getRegistryEntity() : string
	{
		return Registry::ENTITY_PAYABLE_SHIPMENT;
	}

	public static function getEntityType() : string
	{
		return Registry::ENTITY_SHIPMENT;
	}

	public function getEntityObject()
	{
		if ($this->item === null)
		{
			/** @var Payment $payment */
			$payment = $this->collection->getPayment();

			$this->item = $payment->getOrder()->getShipmentCollection()->getItemById(
				$this->getField('ENTITY_ID')
			);
		}

		return $this->item;
	}

	public static function create(PayableItemCollection $collection, CollectableEntity $entity)
	{
		if (!$entity instanceof Shipment)
		{
			throw new Main\SystemException(
				Main\Localization\Loc::getMessage(
					'SALE_PAYABLE_ITEM_INCOMPATIBLE_TYPE',
					['#CLASS#' => Shipment::class]
				)
			);
		}

		$item = parent::create($collection, $entity);

		$item->setFieldNoDemand('QUANTITY', 1);

		return $item;
	}

	protected function onFieldModify($name, $oldValue, $value)
	{
		if ($name === 'QUANTITY')
		{
			if ($value !== 1)
			{
				$result = new Result();

				return $result->addError(
					new Main\Error(
						Main\Localization\Loc::getMessage('SALE_PAYABLE_ITEM_SHIPMENT_QUANTITY_ERROR')
					)
				);
			}
		}

		return parent::onFieldModify($name, $oldValue, $value);
	}

	/**
	 * @return null|string
	 * @internal
	 *
	 */
	public static function getEntityEventName()
	{
		return 'SalePayableShipmentEntity';
	}

}