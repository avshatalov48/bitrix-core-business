<?php

namespace Bitrix\Catalog\v2\Integration\UI\EntitySelector;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\Permission\PermissionDictionary;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;

class StoreProvider extends BaseProvider
{
	private const STORE_LIMIT = 10;
	private const ENTITY_ID = 'store';

	public function __construct(array $options = [])
	{
		$this->options['searchDisabledStores'] = $options['searchDisabledStores'] ?? true;
		$this->options['useAddressAsTitle'] = $options['useAddressAsTitle'] ?? true;
		$this->options['productId'] = (int)($options['productId'] ?? 0);

		if ($this->options['productId'] > 0)
		{
			$product = ProductTable::getRow([
				'filter' => ['=ID' => $this->options['productId']],
				'select' => ['MEASURE']
			]);

			$this->options['measureSymbol'] = $this->getMeasureSymbol((int)$product['MEASURE']);
		}
		else
		{
			$this->options['measureSymbol'] = '';
		}

		parent::__construct();
	}

	private function getMeasureSymbol(int $measureId = null): string
	{
		$measureResult = \CCatalogMeasure::getList(
			array('CODE' => 'ASC'),
			array(),
			false,
			array(),
			array('CODE', 'SYMBOL_RUS', 'SYMBOL_INTL', 'IS_DEFAULT', 'ID')
		);

		$default = '';
		while ($measureFields = $measureResult->Fetch())
		{
			$symbol = $measureFields['SYMBOL_RUS'] ?? $measureFields['SYMBOL_INTL'];
			if ($measureId === (int)$measureFields['ID'])
			{
				return HtmlFilter::encode($symbol);
			}

			if ($measureFields['IS_DEFAULT'] === 'Y')
			{
				$default = $symbol;
			}
		}

		return HtmlFilter::encode($default);
	}

	protected function isSearchDisabledStores(): bool
	{
		return $this->getOptions()['searchDisabledStores'];
	}

	protected function isUseAddressAsTitle(): bool
	{
		return $this->getOptions()['useAddressAsTitle'];
	}

	protected function getProductId(): int
	{
		return $this->getOptions()['productId'];
	}

	public function isAvailable(): bool
	{
		return $GLOBALS["USER"]->IsAuthorized();
	}

	public function getItems(array $ids): array
	{
		return $this->getStores(['ID' => $ids]);
	}

	public function getSelectedItems(array $ids): array
	{
		return $this->getStores(['=ID' => $ids]);
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$searchQuery->setCacheable(false);
		$query = $searchQuery->getQuery();
		$filter = [
			[
				'%TITLE' => $query,
				'%ADDRESS' => $query,
				'LOGIC' => 'OR',
			]
		];
		$items = $this->getStores($filter);

		$dialog->addItems($items);
	}

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

		$recentItemsCount = count($dialog->getRecentItems()->getEntityItems(self::ENTITY_ID));

		if ($recentItemsCount < self::STORE_LIMIT)
		{
			foreach ($this->getStores() as $store)
			{
				$dialog->addRecentItem($store);
			}
		}
	}

	private function getStores(array $filter = []): array
	{
		$allowedStores = AccessController::getCurrent()->getPermissionValue(ActionDictionary::ACTION_STORE_VIEW);
		if (empty($allowedStores))
		{
			return [];
		}

		if (!in_array(PermissionDictionary::VALUE_VARIATION_ALL, $allowedStores, true))
		{
			$filter['=ID'] = $allowedStores;
		}

		$filter['=ACTIVE'] = 'Y';

		$storeProducts = [];
		if ($this->getProductId() > 0)
		{
			$storeProductRaw = StoreProductTable::getList([
				'filter' => ['=PRODUCT_ID' => $this->getProductId()],
				'select' => ['STORE_ID', 'AMOUNT', 'QUANTITY_RESERVED'],
			]);

			while ($storeProduct = $storeProductRaw->fetch())
			{
				$storeProducts[$storeProduct['STORE_ID']] = [
					'RESERVED' => $storeProduct['QUANTITY_RESERVED'],
					'AMOUNT' => $storeProduct['AMOUNT'],
				];
			}
		}

		$storeRaw = StoreTable::getList([
			'select' => ['ID', 'TITLE', 'ADDRESS', 'IMAGE_ID'],
			'filter' => $filter,
		]);

		$stores = [];
		while ($store = $storeRaw->fetch())
		{
			$store['PRODUCT_AMOUNT'] = 0;
			if (isset($storeProducts[$store['ID']]))
			{
				$store['PRODUCT_AMOUNT'] = $storeProducts[$store['ID']]['AMOUNT'];
				$store['PRODUCT_RESERVED'] = $storeProducts[$store['ID']]['RESERVED'];
			}

			if ($store['IMAGE_ID'] !== null)
			{
				$store['IMAGE_ID'] = (int)$store['IMAGE_ID'];
				if ($store['IMAGE_ID'] <= 0)
				{
					$store['IMAGE_ID'] = null;
				}
			}
			$store['IMAGE'] =
				$store['IMAGE_ID'] !== null
					? $this->getImageSource($store['IMAGE_ID'])
					: null
			;

			$stores[] = $store;
		}

		if ($storeProducts)
		{
			usort(
				$stores,
				static function ($first, $second)
				{
					return ($first['PRODUCT_AMOUNT'] > $second['PRODUCT_AMOUNT']) ? -1 : 1;
				}
			);
		}

		$items = [];
		foreach ($stores as $key => $store)
		{
			$store['SORT'] = 100 * $key;
			$items[] = $this->makeItem($store);
		}

		return $items;
	}

	private function getImageSource(int $id): ?string
	{
		if ($id <= 0)
		{
			return null;
		}

		$file = \CFile::GetFileArray($id);
		if (!$file)
		{
			return null;
		}

		return Tools::getImageSrc($file, false) ?: null;
	}

	private function makeItem($store): Item
	{
		$title = $store['TITLE'];
		if ($title === '')
		{
			$title = ($this->isUseAddressAsTitle())
				? $store['ADDRESS']
				: Loc::getMessage('STORE_SELECTOR_EMPTY_TITLE')
			;
		}

		$item = new Item([
			'id' => $store['ID'],
			'sort' => $store['SORT'],
			'entityId' => self::ENTITY_ID,
			'title' => $title,
			'subtitle' => $store['ADDRESS'],
			'avatar' => $store['IMAGE'],
			'caption' => [
				'text' =>
					$this->getProductId() > 0
						? $store['PRODUCT_AMOUNT'] . ' ' . $this->getOptions()['measureSymbol']
						: ''
				,
				'type' => 'html',
			],
			'customData' => [
				'amount' => (float)$store['PRODUCT_AMOUNT'],
				'availableAmount' => (float)$store['PRODUCT_AMOUNT'] - (float)$store['PRODUCT_RESERVED'],
			],
		]);

		return $item;
	}
}
