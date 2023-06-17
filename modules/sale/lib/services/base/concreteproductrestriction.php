<?php

namespace Bitrix\Sale\Services\Base;

use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\Restrictions\Base;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class ConcreteProductRestriction
 * Abstract restriction by concrete products
 * @package Bitrix\Sale\Services\Base
 */
abstract class ConcreteProductRestriction extends Restriction
{
	/**
	 * Return js object name that must have 'addRestrictionByConcreteProduct(nodeId, id, name)'
	 * and 'deleteRestrictionByConcreteProduct(nodeId, id, name)' functions
	 * @return string
	 */
	abstract protected static function getJsHandler() : string;

	/**
	 * Return array of basket items from $entity
	 * @param Entity $entity
	 * @return array BasketItem objects
	 */
	abstract protected static function getBasketItems(Entity $entity) : array;


	/**
	 * Retrieves from the $entity an array of concrete product IDs
	 * that constrain the system and returns it
	 * @param Entity $entity
	 * @return array
	 */
	public static function extractParams(Entity $entity) : array
	{
		$basketItems = static::getBasketItems($entity);
		$productIds = [];

		/** @var BasketItem $basketItem */
		foreach ($basketItems as $basketItem)
		{
			if ($basketItem->getField('MODULE') != 'catalog')
			{
				continue;
			}

			$productIds[] = (int)$basketItem->getField('PRODUCT_ID');
		}

		return $productIds;
	}

	/**
	 * Returns the restriction title
	 * @return string
	 */
	public static function getClassTitle() : string
	{
		return Loc::getMessage("SALE_BASE_RESTRICTION_BY_PRODUCT");
	}

	public static function getOnApplyErrorMessage(): string
	{
		return Loc::getMessage('SALE_BASE_RESTRICTION_BY_PRODUCT_ON_APPLY_ERROR_MSG');
	}

	/**
	 * Compares the list of basket items with the list of items
	 * that restrict the system and returns a boolean result
	 * @param $basketItemsIds
	 * @param array $restrictionParams
	 * @param int $serviceId
	 * @return bool
	 */
	public static function check($basketItemsIds, array $restrictionParams, $serviceId = 0) : bool
	{
		if (
			empty($basketItemsIds)
			|| !is_array($basketItemsIds)
			|| empty($restrictionParams["PRODUCTS"])
			|| !is_array($restrictionParams["PRODUCTS"])
		)
		{
			return true;
		}

		$allowedItemsIds = $restrictionParams['PRODUCTS'];

		$productsListSize = count($basketItemsIds);
		for ($i = 0; $i < $productsListSize; $i++)
		{
			if (!in_array($basketItemsIds[$i], $allowedItemsIds))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns array of 'CONCRETE_PRODUCT' restriction params
	 * @param int $entityId
	 * @return array
	 */
	public static function getParamsStructure($entityId = 0) : array
	{
		return [
			"PRODUCTS" => [
				"TYPE" => "CONCRETE_PRODUCT",
				"JS_HANDLER" => static::getJsHandler(),
				"FORM_NAME" => "PRODUCTS_IDS",
				"LABEL" => Loc::getMessage("SALE_BASE_RESTRICTION_BY_PRODUCT_LST_LABEL"),
				"ID" => 'sale-admin-concrete-product-restriction',
			],
		];
	}
}