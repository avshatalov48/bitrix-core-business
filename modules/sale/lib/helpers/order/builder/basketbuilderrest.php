<?php


namespace Bitrix\Sale\Helpers\Order\Builder;
use Bitrix\Main\Error;
use Bitrix\Sale\Basket\RefreshFactory;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketPropertyItemBase;

/**
 * Class BasketBuilderRest
 * @package Bitrix\Sale\Helpers\Order\Builder
 * @internal
 */
final class BasketBuilderRest extends BasketBuilder
{
	protected function getDelegate($orderId)
	{
		return (int)$orderId > 0 ? new BasketBuildeRestExist($this) : new BasketBuilderNew($this);
	}

	public static function isBasketItemNew($basketCode)
	{
		return (mb_strpos($basketCode, 'n') === 0);
	}
	// переопределяем родительский метод,
	// как временное решение т.к. в админке не поддерживается работа с корзиной в которой одинаковый товар
	protected function getExistsItem($moduleId, $productId, array $properties = array())
	{
		return null;
	}

	public function preliminaryDataPreparation()
	{
		return $this;
	}

	public function itemsDataPreparation()
	{
		foreach($this->formData["PRODUCT"] as $basketCode => $productData)
		{
			if($productData["IS_SET_ITEM"] == "Y")
				continue;

			if(!isset($productData["PROPS"]) || !is_array($productData["PROPS"]))
				$productData["PROPS"] = array();

			if(self::isBasketItemNew($basketCode) == true)
			{
				$item = $this->createItem($basketCode, $productData);
			}
			else
			{
				/** @var BasketItem $item */
				$item = $this->getItemFromBasket($basketCode, $productData);

				if(is_null($item))
				{
					$this->builder->getErrorsContainer()->addError(new Error('basketItem - is not exists ['.$basketCode.']'));
					throw new BuildingException();
				}
			}

			foreach ($productData["PROPS"] as &$prop)
			{
				unset($prop['BASKET_ID']); // bug \Bitrix\Sale\BasketPropertiesCollectionBase::redefine()
			}

			if(!empty($productData["PROPS"]) && is_array($productData["PROPS"]))
			{
				/** @var \Bitrix\Sale\BasketPropertiesCollection $property */
				$property = $item->getPropertyCollection();

				if(!$property->isPropertyAlreadyExists($productData["PROPS"]))
					$property->setProperty($productData["PROPS"]);
			}
		}

		return $this;
	}

	public function basketCodeMap()
	{
		return $this;
	}

	public function setItemsFields()
	{
		$basket = $this->getBasket();

		/** @var BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			$basketCode = $basketItem->getBasketCode();
			if(isset($this->formData['PRODUCT'][$basketCode]))
			{
				$itemFields = $this->formData['PRODUCT'][$basketCode];

				if(isset($itemFields['OFFER_ID']))
				{
					$itemFields['PRODUCT_ID'] = $itemFields['OFFER_ID'];
				}

				if(isset($itemFields['PRICE']))
				{
					$itemFields['CUSTOM_PRICE'] = 'Y';
				}

				$fields = array_intersect_key($itemFields, array_flip($basketItem::getAvailableFields()));

				$r = $basketItem->setFields($fields);
				if($r->isSuccess() == false)
				{
					$this->getErrorsContainer()->addErrors($r->getErrors());
					throw new BuildingException();
				}
			}
		}

		return $this;
	}

	public function finalActions()
	{
		$basket = $this->getBasket();

		/** @var BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			if(self::isBasketItemNew($basketItem->getBasketCode()))
			{
				$strategy = RefreshFactory::createSingle($basketItem->getBasketCode());
				$basket->refresh($strategy);
			}
		}
		return $this;
	}
}