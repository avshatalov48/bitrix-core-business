<?php

namespace Bitrix\Sale\Helpers\Order\Builder\Converter;

use Bitrix\Main;
use Bitrix\Catalog\Product;
use Bitrix\Catalog\VatTable;
use Bitrix\Sale\Internals;

class CatalogJSProductForm
{
	public static function convertToBuilderFormat(array $params) : array
	{
		$result = [];

		foreach ($params as $item)
		{
			if (empty($item['code']))
			{
				$item['code'] = 'n'.(count($result) + 1);
			}

			$product = self::obtainProductFields($item);

			$result[$product['BASKET_CODE']] = $product;
		}

		return $result;
	}

	/**
	 * Brings the fields to a consistent state.
	 *
	 * Checks:
	 * - discount price - must be equals difference base price and price;
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	private static function consistentFields(array $fields): array
	{
		// prices
		if (!empty($fields['discount']))
		{
			$price = (float)($fields['priceExclusive'] ?? $fields['price']);
			$basePrice = (float)$fields['basePrice'];
			$discountPrice = (float)$fields['discount'];

			$realDiscountPrice = $basePrice - $price;
			if ($discountPrice !== $realDiscountPrice)
			{
				$fields['discount'] = $realDiscountPrice;

				if (isset($fields['discountRate']))
				{
					$fields['discountRate'] =
						$basePrice > 0
							? $realDiscountPrice / $basePrice * 100
							: 0
					;
				}
			}
		}

		return $fields;
	}

	/**
	 * @param $fields
	 * @return array
	 * @throws Main\ArgumentException
	 */
	protected static function obtainProductFields($fields) : array
	{
		$fields = self::consistentFields($fields);

		$priceExclusive = $fields['priceExclusive'] ?? $fields['price'] ?? 0;
		$basePrice = $fields['basePrice'] ?? 0;

		$item = [
			'QUANTITY' => (float)$fields['quantity'] > 0 ? (float)$fields['quantity'] : 1,
			'PRODUCT_PROVIDER_CLASS' => '',
			'BASKET_CODE' => $fields['code'] ?? '',
			'PRODUCT_ID' => $fields['skuId'] ?? $fields['productId'] ?? 0,
			'BASE_PRICE' => $basePrice,
			'PRICE' => $priceExclusive,
			'DISCOUNT_PRICE' => 0,
			'ORIGIN_BASKET_ID' => (int)($fields['additionalFields']['originBasketId'] ?? 0),
			'ORIGIN_PRODUCT_ID' => (int)($fields['additionalFields']['originProductId'] ?? 0),
			'MANUALLY_EDITED' => 'Y',
			'XML_ID' => $fields['innerId'],
			'TYPE' => null,
		];

		if (isset($fields['type']))
		{
			$type = (int)$fields['type'];

			$item['TYPE'] = Internals\Catalog\ProductTypeMapper::getType($type);
		}

		if (isset($fields['name']))
		{
			$item['NAME'] = $fields['name'];
		}

		if (isset($fields['sort']))
		{
			$item['SORT'] = (int)$fields['sort'];
		}

		if (isset($fields['isCustomPrice']))
		{
			$item['CUSTOM_PRICE'] = $fields['isCustomPrice'] === 'Y' ? 'Y' : 'N';
		}

		if (isset($fields['measureName']))
		{
			$item['MEASURE_NAME'] = $fields['measureName'];
		}

		if (isset($fields['measureCode']))
		{
			$item['MEASURE_CODE'] = (int)$fields['measureCode'];
		}

		if (isset($fields['weight']))
		{
			$item['WEIGHT'] = $fields['weight'];
		}

		if (isset($fields['dimensions']))
		{
			$item['DIMENSIONS'] = $fields['dimensions'];
		}

		if (
			isset($fields['taxIncluded'], $fields['taxId'])
			&& Main\Loader::includeModule('catalog')
		)
		{
			$rateRow = VatTable::getRowById((int)$fields['taxId']);
			if ($rateRow)
			{
				$item['VAT_RATE'] =
					isset($rateRow['RATE'])
						? (float)$rateRow['RATE'] / 100
						: null
				;
			}

			$item['VAT_INCLUDED'] = $fields['taxIncluded'] === 'N' ? 'N' : 'Y';
		}

		if (isset($fields['skuId']) && $fields['skuId'])
		{
			$item['PRODUCT_ID'] = $fields['skuId'];
		}

		if (
			isset($fields['module'])
			&& $fields['module'] === 'catalog'
			&& Main\Loader::includeModule('catalog')
		)
		{
			$item['MODULE'] = 'catalog';
			$item['PRODUCT_PROVIDER_CLASS'] = Product\Basket::getDefaultProviderName();
		}

		if (
			(int)$fields['discount'] === 0
			&& abs($priceExclusive - $basePrice) > 1e-10
			&& (float)$basePrice > 0
		)
		{
			$fields['discount'] = (int)(100 - ($priceExclusive / $basePrice) * 100);
		}

		if ($fields['discount'] > 0)
		{
			$item['DISCOUNT_PRICE'] = $fields['discount'];
			$item['CUSTOM_PRICE'] = 'Y';
			$item['PRICE'] = $item['BASE_PRICE'] - $item['DISCOUNT_PRICE'];
		}

		if (isset($fields['properties']))
		{
			$allowedProps = self::getAllowedBasketProperties((int)$item['PRODUCT_ID']);
			$item['PROPS'] = [];
			foreach ($fields['properties'] as $property)
			{
				if (!in_array($property['CODE'], $allowedProps))
				{
					continue;
				}

				$value = '';

				if (
					!empty($property['PROPERTY_VALUES'])
					&& is_array($property['PROPERTY_VALUES'])
				)
				{
					$value = current($property['PROPERTY_VALUES']);
					$value = $value['DISPLAY_VALUE'] ?? '';
				}

				$item['PROPS'][] = [
					'NAME' => $property['NAME'],
					'SORT' => $property['SORT'],
					'CODE' => $property['CODE'],
					'VALUE' => $value,
				];
			}
		}

		$item['FIELDS_VALUES'] = Main\Web\Json::encode($item);

		return $item;
	}

	/**
	 * @param int $productId
	 * @return string[]
	 */
	public static function getAllowedBasketProperties(int $productId): array
	{
		if (!Main\Loader::includeModule('catalog') || !Main\Loader::includeModule('iblock'))
		{
			return [];
		}

		$product = \CIBlockElement::GetList([], ['=ID' => $productId], false, false, ['IBLOCK_ID'])->Fetch();
		$iblockId = 0;
		if (is_array($product) && isset($product['IBLOCK_ID']))
		{
			$iblockId = (int)$product['IBLOCK_ID'];
		}

		if ($iblockId <= 0)
		{
			return [];
		}

		static $allowedPropertyCodes = [];
		if (!array_key_exists($iblockId, $allowedPropertyCodes))
		{
			$propertyCodes = Product\PropertyCatalogFeature::getBasketPropertyCodes($iblockId, ['CODE' => 'Y']);
			$allowedPropertyCodes[$iblockId] = is_array($propertyCodes) ? $propertyCodes : [];
		}

		return $allowedPropertyCodes[$iblockId];
	}
}
