<?php

namespace Bitrix\Catalog\v2\Helpers;

use Bitrix\Catalog\Product\PropertyCatalogFeature;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Sku\BaseSku;

/**
 * Class PropertyValue
 *
 * @package Bitrix\Catalog\v2\Iblock
 *
 * * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
final class PropertyValue
{
	/**
	 * @param BaseSku $sku
	 * @return string
	 */
	public static function getSkuPropertyDisplayValues(BaseSku $sku): string
	{
		if ($sku->isSimple())
		{
			return '';
		}

		$propertyValues = self::getPropertyValues(
			$sku->getIblockId(),
			[$sku->getId()]
		);

		$skuPropertyValues = $propertyValues[$sku->getId()] ?? [];

		return self::getPropertyDisplayValues($skuPropertyValues);
	}

	/**
	 * @param BaseSku $sku
	 * @return array property code => property fields (including DISPLAY_VALUE)
	 */
	public static function getSkuPropertyDisplayValuesMap(BaseSku $sku): array
	{
		if ($sku->isSimple())
		{
			return [];
		}

		$propertyValues = self::getPropertyValues(
			$sku->getIblockId(),
			[$sku->getId()]
		);

		$skuPropertyValues = $propertyValues[$sku->getId()] ?? [];

		$result = [];

		foreach ($skuPropertyValues as $property)
		{
			$displayValue = self::getPropertyDisplayValue($property);
			if (!$displayValue)
			{
				continue;
			}

			$property['DISPLAY_VALUE'] = $displayValue;

			$result[$property['CODE']] = $property;
		}

		return $result;
	}

	/**
	 * @param $skuIblockId
	 * @param array $skuIds
	 * @return array
	 */
	private static function getPropertyValues($skuIblockId, array $skuIds): array
	{
		$propertyValues = array_fill_keys($skuIds, []);

		\CIBlockElement::GetPropertyValuesArray(
			$propertyValues,
			$skuIblockId,
			['ID' => $skuIds],
			['ID' => PropertyCatalogFeature::getOfferTreePropertyCodes($skuIblockId)]
		);

		return $propertyValues;
	}

	/**
	 * @param BaseSku $sku
	 * @return array
	 */
	public static function getPropertyValuesBySku(BaseSku $sku): array
	{
		return self::getPropertyValues($sku->getIblockId(), [$sku->getId()])[$sku->getId()];
	}

	/**
	 * @param array $properties
	 * @return string
	 */
	private static function getPropertyDisplayValues(array $properties): string
	{
		$result = [];

		foreach ($properties as $property)
		{
			$displayValue = self::getPropertyDisplayValue($property);
			if (!$displayValue)
			{
				continue;
			}

			$result[] = $displayValue;
		}

		return implode(', ', $result);
	}

	/**
	 * @param array $propertyValue
	 * @return string
	 */
	public static function getPropertyDisplayValue(array $propertyValue): string
	{
		if (!empty($propertyValue['USER_TYPE']))
		{
			$userType = \CIBlockProperty::GetUserType($propertyValue['USER_TYPE']);
			$searchMethod = $userType['GetSearchContent'] ?? null;

			if ($searchMethod && is_callable($searchMethod))
			{
				$value = $searchMethod($propertyValue, ['VALUE' => $propertyValue['~VALUE']], []);
			}
			else
			{
				$value = '';
			}
		}
		else
		{
			$value = $propertyValue['~VALUE'] ?? '';
		}

		if (is_array($value))
		{
			$value = implode(', ', $value);
		}

		$value = trim((string)$value);

		return $value;
	}
}
