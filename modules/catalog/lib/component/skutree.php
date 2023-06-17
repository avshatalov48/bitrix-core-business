<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog\Product\PropertyCatalogFeature;
use Bitrix\Main\Type\Collection;
use Bitrix\Main\Web\Json;

class SkuTree
{
	/** @var \Bitrix\Catalog\v2\Iblock\IblockInfo */
	private $iblockInfo;
	private $propertyCodes;
	private $defaultValues = [];

	public function __construct(\Bitrix\Catalog\v2\Iblock\IblockInfo $iblockInfo)
	{
		if (!$iblockInfo || !$iblockInfo->canHaveSku())
		{
			throw new \Bitrix\Main\NotSupportedException(sprintf(
				'Selected iblock {%s} can not have SKU.', $iblockInfo->getProductIblockId()
			));
		}

		$this->iblockInfo = $iblockInfo;
	}

	public function setPropertyCodes(array $propertyCodes): void
	{
		$this->propertyCodes = $propertyCodes;
	}

	public function getPropertyCodes(): array
	{
		if ($this->propertyCodes === null)
		{
			$this->propertyCodes = $this->loadPropertyCodes();
		}

		return $this->propertyCodes;
	}

	private function loadPropertyCodes(): array
	{
		return PropertyCatalogFeature::getOfferTreePropertyCodes(
				$this->iblockInfo->getSkuIblockId(),
				['CODE' => 'Y']
			) ?? [];
	}

	public function setDefaultValues(array $defaultValues): void
	{
		$this->defaultValues = $defaultValues;
	}

	public function getDefaultValues(): array
	{
		return array_merge(
			[
				'PICT' => false,
				'NAME' => '',
			],
			$this->defaultValues
		);
	}

	private function prepareProductsOfferTree(array $productIds): array
	{
		$offers = $this->getOffersForProductIds($productIds);

		return $this->prepareOfferTreeProperties($offers);
	}

	public function loadWithSelectedOffers(array $productToOfferMap): array
	{
		$productIds = array_keys($productToOfferMap);
		$products = $this->prepareProductsOfferTree($productIds);
		$treeProperties = $this->getTreeProperties();

		foreach ($productToOfferMap as $productId => $offerMap)
		{
			if (!isset($products[$productId]))
			{
				continue;
			}

			$product =& $products[$productId];
			$offers = [];

			foreach ((array)$offerMap as $offerId)
			{
				$this->editTemplateOfferProps($product, $treeProperties, $offerId);
				$offers[$offerId] = $product;
			}

			$product = $offers;
		}

		unset($product);

		return $products;
	}

	public function load(array $productIds): array
	{
		$products = $this->prepareProductsOfferTree($productIds);
		$treeProperties = $this->getTreeProperties();

		foreach ($products as &$product)
		{
			$this->editTemplateOfferProps($product, $treeProperties);
		}

		return $products;
	}

	public function loadJsonOffers(array $productToOfferMap): array
	{
		$result = [];
		$offersMap = $this->loadWithSelectedOffers($productToOfferMap);
		foreach ($offersMap as $productId => $offers)
		{
			$result[$productId] = $result[$productId] ?? [];
			foreach ((array)$offers as $offerId => $offerData)
			{
				$offers = [];
				foreach ($offerData['OFFERS'] as $offer)
				{
					$offers[] = array_intersect_key(
						$offer,
						array_flip(['TREE', 'ID'])
					);
				}

				$result[$productId][$offerId] = [
					'SELECTED_VALUES' => $offerData['SELECTED_VALUES'] ?? null,
					'EXISTING_VALUES_JSON' => Json::encode($offerData['EXISTING_VALUES'] ?? null),
					'OFFERS_JSON' => Json::encode($offers),
					'IBLOCK_ID' => $this->iblockInfo->getProductIblockId(),
				];
			}
		}

		return $result;
	}

	protected function editTemplateOfferProps(array &$product, array $skuPropList, int $selectedOfferId = null): void
	{
		$matrix = [];
		$newOffers = [];
		$double = [];
		$product['OFFERS_PROP'] = false;

		$skuPropCodes = array_keys($skuPropList);
		$matrixFields = array_fill_keys($skuPropCodes, false);

		foreach ($product['OFFERS'] as $keyOffer => $offer)
		{
			if (isset($double[$offer['ID']]))
			{
				continue;
			}

			$row = [];
			foreach ($skuPropCodes as $code)
			{
				$row[$code] = $this->getTemplatePropCell($code, $offer, $matrixFields, $skuPropList);
			}

			$matrix[$keyOffer] = $row;

			$double[$offer['ID']] = true;
			$newOffers[$keyOffer] = $offer;
		}

		$product['OFFERS'] = $newOffers;

		$usedFields = [];
		$existingValues = [];
		$sortFields = [];

		foreach ($skuPropCodes as $propCode)
		{
			$boolExist = $matrixFields[$propCode];
			foreach ($matrix as $keyOffer => $row)
			{
				if ($boolExist)
				{
					$offer =& $product['OFFERS'][$keyOffer];
					$rowValue = $matrix[$keyOffer][$propCode]['VALUE'];
					$offer['TREE'][$skuPropList[$propCode]['ID']] = $rowValue;

					if ($selectedOfferId === $offer['ID'])
					{
						$product['SELECTED_VALUES'][$skuPropList[$propCode]['ID']] = $rowValue;
					}

					$offer['SKU_SORT_'.$propCode] = $matrix[$keyOffer][$propCode]['SORT'];
					$sortFields['SKU_SORT_'.$propCode] = SORT_NUMERIC;

					$usedFields[$propCode] = $skuPropList[$propCode];
					$existingValues[$propCode][] = $rowValue;
				}
				else
				{
					unset($matrix[$keyOffer][$propCode]);
				}
			}
		}

		foreach ($existingValues as &$propertyValue)
		{
			$propertyValue = array_unique($propertyValue);
		}

		$product['OFFERS_PROP'] = $usedFields;
		$product['EXISTING_VALUES'] = $existingValues;

		Collection::sortByColumn($product['OFFERS'], $sortFields);
	}

	protected function getTemplatePropCell($code, $offer, &$matrixFields, $skuPropList): array
	{
		$cell = [
			'VALUE' => 0,
			'SORT' => PHP_INT_MAX,
			'NA' => true,
		];

		$skuPropSort = array_column($skuPropList[$code]['VALUES'], 'SORT', 'ID');

		if (isset($offer['DISPLAY_PROPERTIES'][$code]))
		{
			$matrixFields[$code] = true;
			$cell['NA'] = false;

			if ($skuPropList[$code]['USER_TYPE'] === 'directory')
			{
				$intValue = $skuPropList[$code]['XML_MAP'][$offer['DISPLAY_PROPERTIES'][$code]['VALUE']] ?? 0;
				$cell['VALUE'] = $intValue;
			}
			elseif ($skuPropList[$code]['PROPERTY_TYPE'] === 'L')
			{
				$cell['VALUE'] = (int)$offer['DISPLAY_PROPERTIES'][$code]['VALUE_ENUM_ID'];
			}
			elseif ($skuPropList[$code]['PROPERTY_TYPE'] === 'E')
			{
				$cell['VALUE'] = (int)$offer['DISPLAY_PROPERTIES'][$code]['VALUE'];
			}

			$cell['SORT'] = $skuPropSort[$cell['VALUE']];
		}

		return $cell;
	}

	private function getOffersForProductIds(array $productIds): array
	{
		$productProperty = 'PROPERTY_'.$this->iblockInfo->getSkuPropertyId();
		$productPropertyValue = $productProperty.'_VALUE';

		$filter = [
			'IBLOCK_ID' => $this->iblockInfo->getSkuIblockId(),
			'ACTIVE' => 'Y',
			'ACTIVE_DATE' => 'Y',
			'CHECK_PERMISSIONS' => 'N',
			$productProperty => $productIds,
		];

		$offers = [];
		$iterator = \CIBlockElement::GetList(
			[],
			$filter,
			false,
			false,
			[$productProperty, 'ID']
		);
		while ($row = $iterator->getNext())
		{
			$row['ID'] = (int)$row['ID'];
			$row['PARENT_PRODUCT_ID'] = (int)$row[$productPropertyValue];
			$row['PROPERTIES'] = [];

			$offers[$row['ID']] = $row;
		}

		if (!empty($offers))
		{
			\CIBlockElement::GetPropertyValuesArray(
				$offers,
				$this->iblockInfo->getSkuIblockId(),
				$filter,
				[
					'ID' => PropertyCatalogFeature::getOfferTreePropertyCodes($this->iblockInfo->getSkuIblockId()),
				]
			);
		}

		return $offers;
	}

	/**
	 * @return array
	 */
	public function getTreeProperties(): array
	{
		$treeProperties = \CIBlockPriceTools::getTreeProperties(
			$this->iblockInfo->toArray(),
			$this->getPropertyCodes(),
			$this->getDefaultValues()
		);

		$needValues = [];
		\CIBlockPriceTools::getTreePropertyValues($treeProperties, $needValues);

		foreach ($treeProperties as &$treeProperty)
		{
			if (!empty($treeProperty['VALUES']))
			{
				$treeProperty['VALUES'] = array_values($treeProperty['VALUES']);
			}
		}

		return $treeProperties;
	}

	private function prepareOfferTreeProperties(array $offers): array
	{
		if (empty($offers))
		{
			return [];
		}

		$products = [];

		foreach ($offers as &$offer)
		{
			foreach ($this->getPropertyCodes() as $code)
			{
				if (!isset($offer['PROPERTIES'][$code]))
					continue;

				$prop = &$offer['PROPERTIES'][$code];
				$boolArr = is_array($prop['VALUE']);
				if (
					($boolArr && !empty($prop['VALUE']))
					|| (!$boolArr && (string)$prop['VALUE'] !== '')
				)
				{
					$offer['DISPLAY_PROPERTIES'][$code] = \CIBlockFormatProperties::GetDisplayValue($offer, $prop);
				}
			}

			$products[$offer['PARENT_PRODUCT_ID']]['OFFERS'][] = $offer;
		}
		unset($offer);

		return $products;
	}
}
