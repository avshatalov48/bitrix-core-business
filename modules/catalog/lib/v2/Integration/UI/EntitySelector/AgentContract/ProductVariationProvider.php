<?php

namespace Bitrix\Catalog\v2\Integration\UI\EntitySelector\AgentContract;

use Bitrix\Catalog;

class ProductVariationProvider extends Catalog\v2\Integration\UI\EntitySelector\ProductProvider
{
	protected const ENTITY_ID = 'agent-contractor-product-variation';

	protected function getProducts(array $parameters = []): array
	{
		$iblockInfo = $this->getIblockInfo();
		if (!$iblockInfo)
		{
			return [];
		}

		$productFilter = (array)($parameters['filter'] ?? []);
		$additionalProductFilter = [
			'IBLOCK_ID' => $iblockInfo->getProductIblockId(),
		];
		$filteredTypes = [];
		if ($this->options['restrictedProductTypes'] !== null)
		{
			$filteredTypes = array_intersect(
				$this->options['restrictedProductTypes'],
				Catalog\ProductTable::getProductTypes()
			);
		}
		$filteredTypes[] = Catalog\ProductTable::TYPE_EMPTY_SKU;
		$additionalProductFilter['!=TYPE'] = array_values(array_unique($filteredTypes));

		return $this->loadElements([
			'filter' => array_merge($productFilter, $additionalProductFilter),
			'limit' => self::PRODUCT_LIMIT,
		]);
	}
}
