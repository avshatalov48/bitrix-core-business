<?php

namespace Bitrix\Sale\Services\Base;

use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\Restrictions\Base;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\Entity;

Loc::getMessage(__FILE__);

abstract class ProductCategoryRestriction extends Restriction
{
	/**
	 * Return js object name that must have 'addRestrictionProductSection(id, name, nodeId)'
	 * and 'deleteRestrictionProductSection(id, nodeId)' functions
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
	 * Retrieves from the $entity an array
	 * @param Entity $entity
	 * @return array
	 */
	public static function extractParams(Entity $entity) : array
	{
		if (!\Bitrix\Main\Loader::includeModule('catalog'))
		{
			return [];
		}

		$basketItems = static::getBasketItems($entity);

		$productIds = [];

		/** @var BasketItem $basketItem */
		foreach ($basketItems as $basketItem)
		{
			if ($basketItem->getField('MODULE') != 'catalog')
			{
				continue;
			}

			$productId = (int)$basketItem->getField('PRODUCT_ID');
			$productInfo = \CCatalogSKU::getProductInfo($productId);

			$candidate = $productInfo['ID'] ?? $productId;

			if (!in_array($candidate, $productIds))
			{
				$productIds[] = $candidate;
			}
		}

		return self::getCategoriesItems($productIds);
	}

	/**
	 *Returns the restriction title
	 * @return string
	 */
	public static function getClassTitle() : string
	{
		return Loc::getMessage('SALE_BASE_RESTRICTION_BY_CATEGORY');
	}

	public static function getOnApplyErrorMessage(): string
	{
		return Loc::getMessage('SALE_BASE_RESTRICTION_BY_CATEGORY_ON_APPLY_ERROR_MSG');
	}

	/**
	 * Compares the list of categories of items in basket with the list of categories
	 * that restrict entity and returns true if all basket categories exist in restriction list
	 * @param array $categoriesList array of categories Ids that are in the basket
	 * @param array $restrictionParams
	 * @param int $deliveryId
	 * @return bool
	 */
	public static function check($categoriesList, array $restrictionParams, $deliveryId = 0) : bool
	{
		if (
			empty($categoriesList)
			|| !is_array($categoriesList)
			|| empty($restrictionParams["CATEGORIES"])
			|| !is_array($restrictionParams["CATEGORIES"])
		)
		{
			return true;
		}

		foreach ($categoriesList as $productId => $productCategories)
		{
			if (!is_array($productCategories) || empty($productCategories))
			{
				continue;
			}

			$isProductFromCategory = false;

			foreach ($productCategories as $categoryId)
			{
				$categoryPath = self::getCategoriesPath($categoryId);

				if (array_intersect($categoryPath, $restrictionParams["CATEGORIES"]))
				{
					$isProductFromCategory =  true;
					break;
				}
			}

			if (!$isProductFromCategory)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Extracts the IDs of categories belonging to products in $productIds array
	 * @param array $productIds array of product Ids
	 * @return array array of categories belonging to products
	 */
	protected static function getCategoriesItems(array $productIds) : array
	{
		if (!\Bitrix\Main\Loader::includeModule('iblock'))
		{
			return [];
		}

		$groupsIds = [];

		$res = \CIBlockElement::GetElementGroups($productIds, true, ['ID', 'IBLOCK_ELEMENT_ID']);

		while ($group = $res->Fetch())
		{
			if (!is_array($groupsIds[$group['IBLOCK_ELEMENT_ID']]))
			{
				$groupsIds[$group['IBLOCK_ELEMENT_ID']] = [];
			}

			if (!in_array($group['ID'], $groupsIds[$group['IBLOCK_ELEMENT_ID']]))
			{
				$groupsIds[$group['IBLOCK_ELEMENT_ID']][] = $group['ID'];
			}
		}

		return $groupsIds;
	}

	/**
	 * Returns full path for the category with id = $categoryId
	 * @param $categoryId
	 * @return array
	 */
	protected static function getCategoriesPath($categoryId) : array
	{
		if (!\Bitrix\Main\Loader::includeModule('catalog'))
		{
			return [];
		}

		$result = [$categoryId];

		$nav = \CIBlockSection::GetNavChain(false, $categoryId);

		while ($arSectionPath = $nav->GetNext())
		{
			if (!in_array($arSectionPath['ID'], $result))
			{
				$result[] = $arSectionPath['ID'];
			}
		}

		return $result;
	}

	/**
	 * Returns array of 'PRODUCT_CATEGORIES' restriction params
	 * @param int $entityId
	 * @return array
	 */
	public static function getParamsStructure($entityId = 0) : array
	{
		return [
			"CATEGORIES" => [
				"TYPE" => "PRODUCT_CATEGORIES",
				"ID" => 'sale-admin-category-restriction',
				"JS_HANDLER" => static::getJsHandler(),
				"LABEL" => Loc::getMessage('SALE_BASE_RESTRICTION_BY_CATEGORY_LST_LABEL'),
			],
		];
	}


}
