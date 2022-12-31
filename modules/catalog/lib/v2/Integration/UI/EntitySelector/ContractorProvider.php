<?php

namespace Bitrix\Catalog\v2\Integration\UI\EntitySelector;

use Bitrix\Catalog\ContractorTable;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;

class ContractorProvider extends BaseProvider
{
	private const MAX_ITEMS_IN_RECENT = 10;
	private const ENTITY_ID = 'contractor';

	public function __construct(array $options = [])
	{
		parent::__construct();
	}

	public function isAvailable(): bool
	{
		return $GLOBALS["USER"]->IsAuthorized();
	}

	public function getItems(array $ids): array
	{
		return $this->getContractorItemsByFilter(['ID' => $ids]);
	}

	public function getSelectedItems(array $ids): array
	{
		return $this->getContractorItemsByFilter(['ID' => $ids]);
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$searchQuery->setCacheable(false);
		$query = $searchQuery->getQuery();

		$filter = [
			'LOGIC' => 'OR',
			['%PERSON_NAME' => $query],
			['%COMPANY' => $query],
		];
		$items = $this->getContractorItemsByFilter($filter);

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

		if ($recentItemsCount < self::MAX_ITEMS_IN_RECENT)
		{
			$amountToSelect = self::MAX_ITEMS_IN_RECENT - $recentItemsCount;
			$excludedIds = array_keys($dialog->getRecentItems()->getEntityItems(self::ENTITY_ID));
			$contractors = ContractorTable::getList([
				'select' => ['ID', 'PERSON_TYPE', 'PERSON_NAME', 'COMPANY'],
				'order' => ['ID' => 'ASC'],
				'filter' => ['!ID' => $excludedIds],
				'limit' => $amountToSelect,
			])->fetchAll();
			foreach ($contractors as $contractor)
			{
				$dialog->addRecentItem($this->makeItem($contractor));
			}
		}
	}

	private function getContractorItemsByFilter($filter)
	{
		$contractors = ContractorTable::getList([
			'select' => ['ID', 'PERSON_TYPE', 'PERSON_NAME', 'COMPANY'],
			'filter' => $filter,
		])->fetchAll();

		$items = [];
		foreach ($contractors as $contractor)
		{
			$items[] = $this->makeItem($contractor);
		}

		return $items;
	}

	private function makeItem($contractor)
	{
		if ((int)$contractor['PERSON_TYPE'] === (int)\Bitrix\Catalog\ContractorTable::TYPE_INDIVIDUAL)
		{
			$title = $contractor['PERSON_NAME'];
		}
		else
		{
			$title = $contractor['COMPANY'];
		}

		return new Item([
			'id' => $contractor['ID'],
			'entityId' => self::ENTITY_ID,
			'title' => $title,
		]);
	}
}