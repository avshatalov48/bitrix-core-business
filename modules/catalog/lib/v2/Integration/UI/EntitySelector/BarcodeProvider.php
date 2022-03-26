<?php

namespace Bitrix\Catalog\v2\Integration\UI\EntitySelector;

use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\Catalog\StoreBarcodeTable;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;

class BarcodeProvider extends ProductProvider
{
	protected const ENTITY_ID = 'barcode';

	public function fillDialog(Dialog $dialog): void
	{
		$dialog->loadPreselectedItems();

		if ($dialog->getItemCollection()->count() > 0)
		{
			foreach ($dialog->getItemCollection() as $item)
			{
				$dialog->addRecentItem($item);
			}
		}
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$searchQuery->setCacheable(false);
		$productIds = $this->getProductIdsByBarcode($searchQuery->getQuery());
		if (!$productIds)
		{
			return;
		}

		$productIds = array_unique($productIds);
		$products = $this->getProductsByIds($productIds);
		$elementMap = [];
		foreach ($products as $key => $product)
		{
			$elementMap[$product['ID']][] = $key;
		}

		if (!empty($products))
		{
			$barcodeRaw = \Bitrix\Catalog\StoreBarcodeTable::getList([
				'filter' => [
					'=PRODUCT_ID' => $productIds,
					'BARCODE' => $searchQuery->getQuery() . '%'
				],
				'select' => ['BARCODE', 'PRODUCT_ID']
			]);

			while ($barcode = $barcodeRaw->fetch())
			{
				$productId = $barcode['PRODUCT_ID'];
				if (!isset($elementMap[$productId]))
				{
					continue;
				}

				foreach ($elementMap[$productId] as $key)
				{
					$products[$key]['BARCODE'] = $barcode['BARCODE'];
				}
			}

			foreach ($products as $product)
			{
				$dialog->addItem(
					$this->makeItem($product)
				);
			}
		}
	}

	public function handleBeforeItemSave(Item $item): void
	{
		$item->setSaveable(false);
	}

	private function getProductIdsByBarcode(string $barcodeString = ''): array
	{
		$barcodes = [];
		$elementRaw = \CIBlockElement::GetList(
			[],
			[
				'ACTIVE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y',
				'PRODUCT_BARCODE' => $barcodeString . '%',
			],
			false,
			false,
			['ID']
		);

		while ($element = $elementRaw->Fetch())
		{
			$barcodes[] = $element['ID'];
		}

		return $barcodes;
	}
}
