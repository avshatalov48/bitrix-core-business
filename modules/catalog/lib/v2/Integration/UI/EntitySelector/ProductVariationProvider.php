<?php

namespace Bitrix\Catalog\v2\Integration\UI\EntitySelector;

use Bitrix\UI\EntitySelector\Dialog;

class ProductVariationProvider extends ProductProvider
{
	protected const ENTITY_ID = 'product_variation';

	public function fillDialog(Dialog $dialog): void
	{
		$dialog->loadPreselectedItems();
	}

	protected function getProductsBySearchString(string $searchString = ''): array
	{
		if (trim($searchString) === '')
		{
			return [];
		}

		$iblockInfo = $this->getIblockInfo();
		if (!$iblockInfo)
		{
			return [];
		}

		$productFilter = [
			[
				'LOGIC' => 'OR',
				'*SEARCHABLE_CONTENT' => $searchString,
				'PRODUCT_BARCODE' => $searchString . '%',
			]
		];

		$products = $this->getProducts([
			'filter' => $productFilter,
			'searchString' => $searchString,
			'load_offers' => false,
		]);

		if ($iblockInfo->canHaveSku())
		{
			$subQueryPropererties = [
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R',
				'ACTIVE' => 'Y',
				'ACTIVE_DATE' => 'Y',
				'IBLOCK_ID' => $iblockInfo->getSkuIblockId(),
				'*SEARCHABLE_CONTENT' => $searchString,
			];

			$productFilter = [
				[
					'LOGIC' => 'OR',
					'*SEARCHABLE_CONTENT' => $searchString,
					'PRODUCT_BARCODE' => $searchString . '%',
					'=ID' => \CIBlockElement::SubQuery('PROPERTY_' . $iblockInfo->getSkuPropertyId(), $subQueryPropererties),
				]
			];

			if (!empty($products))
			{
				$productFilter[] = [
					'!=ID' => array_keys($products),
				];
			}

			$offersFilter = [
				[
					'LOGIC' => 'OR',
					'*SEARCHABLE_CONTENT' => $searchString,
					'PRODUCT_BARCODE' => $searchString . '%',
				]
			];

			$offers = $this->getProducts([
				'filter' => $productFilter,
				'offer_filter' => $offersFilter,
				'searchString' => $searchString,
			]);

			array_push($products, ...$offers);
		}

		return $products;
	}
}
