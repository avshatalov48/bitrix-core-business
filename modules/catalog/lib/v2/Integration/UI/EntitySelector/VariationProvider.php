<?php

namespace Bitrix\Catalog\v2\Integration\UI\EntitySelector;

use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Main\Loader;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;

class VariationProvider extends ProductProvider
{
	protected const ENTITY_ID = 'variation';

	public function __construct(array $options = [])
	{
		parent::__construct();
		if (Loader::includeModule('crm'))
		{
			$defaultIblockId = \Bitrix\Crm\Product\Catalog::getDefaultOfferId();
		}
		else
		{
			$defaultIblockId = 0;
		}

		if (isset($options['iblockId']) && (int)$options['iblockId'] > 0)
		{
			$iblockId = (int)$options['iblockId'];
		}
		else
		{
			$iblockId = $defaultIblockId;
		}
		$this->options['iblockId'] = $iblockId;
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

		$recentItems = $dialog->getRecentItems()->getEntityItems(self::ENTITY_ID);
		/**
		 * @var Item $recentItem
		 */
		$ids = [];
		foreach ($recentItems as $recentItem)
		{
			$ids[] = $recentItem->getId();
		}
		$filter = [
			'ID' => $ids,
		];

		$offers = $this->getProducts([
			'filter' => $filter,
		]);

		foreach ($offers as $offer)
		{
			$dialog->addRecentItem($this->makeItem($offer));
		}
	}

	protected function getProductsBySearchString(string $searchString = ''): array
	{
		if (trim($searchString) === '')
		{
			return [];
		}

		$filter = [
			'*SEARCHABLE_CONTENT' => $searchString,
		];

		return $this->getProducts([
			'filter' => $filter,
			'searchString' => $searchString,
		]);
	}

	public function getItems(array $ids): array
	{
		if (empty($ids))
		{
			return [];
		}

		$filter = [
			'ID' => $ids,
		];

		$offers = $this->getProducts([
			'filter' => $filter,
		]);

		$items = [];
		foreach ($offers as $offer)
		{
			$items[] = $this->makeItem($offer);
		}

		return $items;
	}

	public function getSelectedItems(array $ids): array
	{
		return $this->getItems($ids);
	}

	protected function getProducts(array $parameters = []): array
	{
		$iblockInfo = $this->getIblockInfo();
		if (!$iblockInfo)
		{
			return [];
		}

		$filter = $this->getDefaultFilter();
		$filter['IBLOCK_ID'] = $iblockInfo->getSkuIblockId();

		$additionalFilter = $parameters['filter'];

		$filter = array_merge($filter, $additionalFilter);

		$offers = $this->loadElements([
			'filter' => $filter,
			'limit' => self::PRODUCT_LIMIT,
		]);
		$offers = $this->loadProperties($offers, $iblockInfo->getSkuIblockId(), $iblockInfo);

		$offers = $this->loadPrices($offers);

		if (isset($parameters['searchString']))
		{
			$offers = $this->loadBarcodes($offers, $parameters['searchString']);
		}

		return $offers;
	}

	protected function getIblockInfo(): ?IblockInfo
	{
		return ServiceContainer::getIblockInfo($this->getIblockId());
	}

	private function getDefaultFilter(): array
	{
		return [
			'CHECK_PERMISSIONS' => 'Y',
			'MIN_PERMISSION' => 'R',
			'ACTIVE' => 'Y',
			'ACTIVE_DATE' => 'Y',
		];
	}
}