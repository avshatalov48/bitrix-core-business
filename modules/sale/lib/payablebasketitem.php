<?php

namespace Bitrix\Sale;

use Bitrix\Main;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class PayableBasketItem
 * @package Bitrix\Sale
 */
class PayableBasketItem extends PayableItem
{
	public static function getRegistryEntity()
	{
		return Registry::ENTITY_PAYABLE_BASKET_ITEM;
	}

	public static function getEntityType() : string
	{
		return Registry::ENTITY_BASKET_ITEM;
	}

	public static function create(PayableItemCollection $collection, Internals\CollectableEntity $entity)
	{
		if (!$entity instanceof BasketItem)
		{
			throw new Main\SystemException(
				Main\Localization\Loc::getMessage(
					'SALE_PAYABLE_ITEM_INCOMPATIBLE_TYPE',
					['#CLASS#' => BasketItem::class]
				)
			);
		}

		return parent::create($collection, $entity);
	}

	public function getEntityObject()
	{
		if ($this->item === null)
		{
			/** @var Payment $payment */
			$payment = $this->collection->getPayment();

			$this->item = $payment->getOrder()->getBasket()->getItemById(
				$this->getField('ENTITY_ID')
			);
		}

		return $this->item;
	}

	protected function onFieldModify($name, $oldValue, $value)
	{
		if ($name === 'QUANTITY')
		{
			$quantity = $this->getEntityObject()->getQuantity();
			if ($quantity < $value)
			{
				$result = new Result();

				return $result->addError(
					new Main\Error(
						Main\Localization\Loc::getMessage('SALE_PAYABLE_ITEM_QUANTITY_ERROR')
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
		return 'SalePayableBasketItemEntity';
	}
}